<?php

namespace App\Models\payment_gateway;

use App\Models\Wallet as UserWallet;
use App\Models\CartItem;
use App\Models\WalletTransaction;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class Wallet
{
    public static function payment_status($identifier, $transaction_keys = [] ,  $payment_details = null)
    {
        if(!$payment_details ){
        $payment_details = session('payment_details');
        }
        $user_id = Auth::id();
        if(!$user_id){
            $user_id =  $payment_details['custom_field']['user_id'];
        }
        $payable_amount = $payment_details['payable_amount'] ?? 0;

      
        $wallet = UserWallet::where('user_id', $user_id)->first();
        $balance =$wallet->balance;
        $wallet_id =$wallet->id;
        if ($balance >= $payable_amount) {

            UserWallet::where('user_id', $user_id)
                ->decrement('balance', $payable_amount);
            //       WalletTransaction::create([
            //     'user_id' => $user_id,
            //     'wallet_id' => $wallet_id,
            //     'amount' => $payable_amount,
            //     'transaction_type' => 'Payment',
            //     'description' => 'Payment made via wallet',
            // ]);
               CartItem::where('user_id', auth()->id())->delete();

            return true;
        }

        return false;
    }

    public static function payment_create($identifier)
    {
        // Wallet payment is immediate, redirect to success
        $payment_details = session('payment_details');

        return $payment_details['success_url'] . '/' . $identifier;
    }
}
