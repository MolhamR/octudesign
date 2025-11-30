<?php

// app/Http/Controllers/WalletController.php
namespace App\Http\Controllers;

use App\Models\Wallet;
use App\Models\User;
use App\Models\WalletTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use App\Mail\Mailer;

class WalletController extends Controller
{
    public function show()
    {
        $user = Auth::user();
        $wallet = Wallet::firstOrCreate(['user_id' => $user->id], ['balance' => 0.00, 'currency' => 'SYP']);
        $transactions = $wallet->transactions()->orderBy('created_at', 'desc')->paginate(10);

        return view('frontend.default.student.my_wallet', compact('wallet', 'transactions'));
    }

    

    public function adminWallet(Request $request)
{
    $query = User::where('role', 'student');

    if ($request->has('search') && $request->search != '') {
        $query->where(function ($q) use ($request) {
            $q->where('name', 'LIKE', '%' . $request->search . '%')
              ->orWhere('email', 'LIKE', '%' . $request->search . '%');
        });
    }

    // Eager load the wallet relationship and balance for pagination
    $students = $query->with('wallet')->paginate(10);

    // Fetch all students for the dropdown (no pagination)
    $allStudents = User::where('role', 'student')->get();

    if ($request->ajax()) {
        return response()->json([
            'html' => view('admin.wallet.partials.table', compact('students'))->render()
        ]);
    }

    return view('admin.wallet.wallet', compact('students', 'allStudents'));
}



    

    public function addFunds(Request $request)
    {
        $request->validate([
            'user_id' => 'required',
            'amount' => 'required|numeric',
            'transaction_type' => 'required|string',
        ]);

        $wallet = Wallet::firstOrCreate(['user_id' => $request->user_id], ['balance' => 0.00, 'currency' => 'SYP']);

      
        $wallet->increment('balance', $request->amount);
        if( $request->amount > 0)
        {
        WalletTransaction::create([
            'user_id' => $request->user_id,
            'wallet_id' => Wallet::where('user_id', $request->user_id)->first()->id,
            'amount' => $request->amount,
            'transaction_type' => $request->transaction_type,
            'description' => 'Wallet recharge',
        ]);
        $user_email = User::where('id', $request->user_id)->pluck('email')->first();
        $subject = "Wallet recharge";
        $description = "Your wallet has been successfully recharged with " . number_format($request->amount, 2) . " SYP.";
        $this->send_mail($user_email, $subject, $description);
        }

        return back()->with('success', 'Funds added successfully!');
    }

    public function send_mail($user_email, $subject, $description)
    {
        config([
            'mail.mailers.smtp.transport'  => get_settings('protocol'),
            'mail.mailers.smtp.host'       => get_settings('smtp_host'),
            'mail.mailers.smtp.port'       => get_settings('smtp_port'),
            'mail.mailers.smtp.encryption' => get_settings('smtp_crypto'),
            'mail.mailers.smtp.username'   => get_settings('smtp_from_email'),
            'mail.mailers.smtp.password'   => get_settings('smtp_pass'),
            'mail.from.address'            => get_settings('smtp_from_email'),
            'mail.from.name'               => get_settings('system_name'),
        ]);

        $mail_data['subject']     = $subject;
        $mail_data['description'] = $description;

        $send = Mail::to($user_email)->send(new Mailer($mail_data));
        return $send;
    }
  
    
    public function payWithWallet(Request $request)
{
    $balance =  Wallet::where('user_id', $request->user_id)->first()->balance;
    $amount = $request->amount;

    $identifier = $request->input('identifier', 'wallet');

    // Set payment_details back to session if needed
    // session(['payment_details' => [...]]); // if not already there

    if ($balance  >=  $amount ) {
        return redirect()->to(route('payment.success', $identifier));
    } else {
        return redirect()->back()->with('error', 'Insufficient balance or transaction failed.');
    }
}



    public function getWalletBalance()
    {
        $wallet = Wallet::where('user_id', Auth::id())->first();

        return response()->json([
            'status' => (bool)$wallet,
            'balance' => $wallet->balance ?? 0.00,
        ]);
    }
}
