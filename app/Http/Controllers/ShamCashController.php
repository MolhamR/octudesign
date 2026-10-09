<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use App\Services\ShamCashService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use App\Mail\Mailer;

class ShamCashController extends Controller
{
    public function saveAccount(Request $request)
    {
        $request->validate([
            'sham_cash_account' => 'required|size:32',
        ]);

        $user = Auth::user();
        User::where('id', $user->id)->update(['sham_cash_account' => $request->sham_cash_account]);

        return response()->json([
            'success' => true,
            'message' => get_phrase('ShamCash account saved successfully'),
        ]);
    }

    public function requestTopUp(Request $request)
    {
        // The student does not enter an amount. Any transfer they make from
        // their ShamCash account is detected later and credited in full, so we
        // only need to flag that a top-up is in progress.
        $user = Auth::user();
        if (empty($user->sham_cash_account)) {
            return redirect()->back()->with('error', get_phrase('Please save your ShamCash account first.'));
        }

        $wallet = Wallet::firstOrCreate(['user_id' => $user->id], ['balance' => 0, 'currency' => 'SYP']);

        $pending = WalletTransaction::where('user_id', $user->id)
            ->where('transaction_type', 'ShamCash top-up')
            ->where('status', 'pending')
            ->latest()
            ->first();

        if (!$pending) {
            WalletTransaction::create([
                'user_id'          => $user->id,
                'wallet_id'        => $wallet->id,
                'amount'           => 0,
                'transaction_type' => 'ShamCash top-up',
                'description'      => 'ShamCash wallet recharge',
                'status'           => 'pending',
            ]);
        }

        return redirect()->to(route('wallet.sham_cash.verify'));
    }

    public function showTopUpVerify()
    {
        $user = Auth::user();
        $pending = WalletTransaction::where('user_id', $user->id)
            ->where('transaction_type', 'ShamCash top-up')
            ->where('status', 'pending')
            ->latest()
            ->first();

        if (!$pending) {
            return redirect()->to(route('wallet.show'))->with('error', get_phrase('No pending top-up found.'));
        }

        $page_data['pending']        = $pending;
        $page_data['wallet']         = Wallet::firstOrCreate(['user_id' => $user->id], ['balance' => 0, 'currency' => 'SYP']);
        $page_data['sham_cash_account'] = get_settings('sham_cash_account');
        return view('frontend.default.student.sham_cash_verify', $page_data);
    }

    public function verifyPayments(Request $request)
    {
        $user = Auth::user();
        if (empty($user->sham_cash_account)) {
            return response()->json(['verified' => false, 'message' => get_phrase('Please save your ShamCash account first.')]);
        }

        $service = new ShamCashService();
        if (!$service->isConfigured()) {
            return response()->json(['verified' => false, 'message' => get_phrase('ShamCash is not configured yet.')]);
        }

        $pending = WalletTransaction::where('user_id', $user->id)
            ->where('transaction_type', 'ShamCash top-up')
            ->where('status', 'pending')
            ->latest()
            ->first();

        if (!$pending) {
            return response()->json(['verified' => false, 'message' => get_phrase('No pending top-up.')]);
        }

        $history = $service->getHistory();
        if ($history === false) {
            return response()->json(['verified' => false, 'message' => get_phrase('Could not reach ShamCash, please try again.')]);
        }

        // Every transfer received from this student's account (no amount required).
        $matches = $service->matchingTransactions($history, $user->sham_cash_account, 0);

        // Ignore transactions that were already credited to any wallet.
        $matches = array_values(array_filter($matches, function ($match) {
            $tranId = $match['tranId'] ?? null;
            if (!$tranId) {
                return false;
            }
            return !WalletTransaction::where('sham_cash_tran_id', $tranId)->exists();
        }));

        if (empty($matches)) {
            return response()->json(['verified' => false, 'message' => get_phrase('Still waiting for payment...')]);
        }

        // Credit the newest (last) unused transaction in full.
        usort($matches, function ($a, $b) {
            return strtotime($b['tranDate'] ?? 0) <=> strtotime($a['tranDate'] ?? 0);
        });

        $match          = $matches[0];
        $tranId         = (string) $match['tranId'];
        $receivedAmount = (float) $match['amount'];

        $wallet   = Wallet::firstOrCreate(['user_id' => $user->id], ['balance' => 0, 'currency' => 'SYP']);
        $credited = false;

        \DB::transaction(function () use ($pending, $wallet, $tranId, $receivedAmount, &$credited) {
            $txn = WalletTransaction::where('id', $pending->id)->where('status', 'pending')->lockForUpdate()->first();
            if (!$txn) {
                return;
            }
            if (WalletTransaction::where('sham_cash_tran_id', $tranId)->exists()) {
                return;
            }

            $txn->update([
                'status'            => 'completed',
                'amount'            => $receivedAmount,
                'sham_cash_tran_id' => $tranId,
                'verified_at'       => now(),
            ]);
            $wallet->increment('balance', $receivedAmount);

            \DB::table('system_wallet_transactions')->insert([
                'type'       => 'in',
                'amount'     => $receivedAmount,
                'note'       => 'ShamCash top-up - user #' . $wallet->user_id,
                'entry_date' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $credited = true;
        });

        if ($credited) {
            $this->notifyTopUpSuccess($user, $receivedAmount);
            return response()->json([
                'verified' => true,
                'balance'  => (float) $wallet->fresh()->balance,
                'message'  => get_phrase('Payment verified, your wallet has been recharged.'),
            ]);
        }

        return response()->json(['verified' => false, 'message' => get_phrase('Still waiting for payment...')]);
    }

    public function instructorWallet()
    {
        $user = Auth::user();
        $wallet = Wallet::firstOrCreate(['user_id' => $user->id], ['balance' => 0, 'currency' => 'SYP']);
        $transactions = WalletTransaction::where('user_id', $user->id)->orderBy('id', 'desc')->get();
        return view('instructor.wallet.index', compact('wallet', 'transactions'));
    }

    public function withdraw(Request $request)
    {
        $user = Auth::user();
        if (empty($user->sham_cash_account)) {
            return redirect()->back()->with('error', get_phrase('Please save your ShamCash account first.'));
        }

        $wallet = Wallet::where('user_id', $user->id)->first();
        if (!$wallet || $wallet->balance <= 0) {
            return redirect()->back()->with('error', get_phrase('No balance available to withdraw.'));
        }

        $service = new ShamCashService();
        if (!$service->isConfigured()) {
            return redirect()->back()->with('error', get_phrase('ShamCash is not configured yet.'));
        }

        if (!$service->sendMoney($user->sham_cash_account, $wallet->balance)) {
            return redirect()->back()->with('error', get_phrase('Withdrawal failed.'));
        }

        $amount = $wallet->balance;
        $wallet->decrement('balance', $amount);

        WalletTransaction::create([
            'user_id'          => $user->id,
            'wallet_id'        => $wallet->id,
            'amount'           => $amount,
            'transaction_type' => 'Withdraw',
            'description'      => 'Wallet withdrawal',
            'status'           => 'completed',
            'verified_at'      => now(),
        ]);

        return redirect()->back()->with('success', get_phrase('Withdrawal successful.'));
    }

    private function notifyTopUpSuccess($user, $amount)
    {
        try {
            $mail_data['subject']     = get_phrase('Wallet recharged successfully');
            $mail_data['description'] = get_phrase('Your wallet has been successfully recharged with') . ' ' . number_format($amount, 2) . ' SYP.';
            Mail::to($user->email)->send(new Mailer($mail_data));
        } catch (\Exception $e) {
            // email failure should not break the flow
        }
    }
}