<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\ShamCashService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class SystemWalletController extends Controller
{
    public function index()
    {
        $total_in  = DB::table('system_wallet_transactions')->where('type', 'in')->sum('amount');
        $total_out = DB::table('system_wallet_transactions')->where('type', 'out')->sum('amount');
        $withdrawable = $total_in - $total_out;

        $page_data['total_in']       = $total_in;
        $page_data['total_out']      = $total_out;
        $page_data['withdrawable']   = max(0, $withdrawable);
        $page_data['transactions']   = DB::table('system_wallet_transactions')->orderBy('id', 'desc')->paginate(20);
        $page_data['withdraw_account'] = get_settings('system_withdraw_account');
        return view('admin.system_wallet.index', $page_data);
    }

    public function transfer(Request $request)
    {
        $withdraw_account = get_settings('system_withdraw_account');
        if (empty($withdraw_account)) {
            return redirect()->back()->with('error', get_phrase('System withdrawal account is not configured.'));
        }

        $total_in  = DB::table('system_wallet_transactions')->where('type', 'in')->sum('amount');
        $total_out = DB::table('system_wallet_transactions')->where('type', 'out')->sum('amount');
        $withdrawable = max(0, $total_in - $total_out);

        if ($withdrawable <= 0) {
            return redirect()->back()->with('error', get_phrase('No withdrawable profit available.'));
        }

        $service = new ShamCashService();
        if (!$service->isConfigured()) {
            return redirect()->back()->with('error', get_phrase('ShamCash is not configured yet.'));
        }

        if (!$service->sendMoney($withdraw_account, $withdrawable)) {
            return redirect()->back()->with('error', get_phrase('Profit transfer failed.'));
        }

        DB::table('system_wallet_transactions')->insert([
            'type'       => 'out',
            'amount'     => $withdrawable,
            'note'       => get_phrase('Profit transfer to system withdrawal account'),
            'entry_date' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        Session::flash('success', get_phrase('Profit transferred successfully'));
        return redirect()->back();
    }
}