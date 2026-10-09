<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;

class PurchaseLibraryItem extends Model
{
    use HasFactory;

    public static function purchase_library($identifier)
    {
        $payment_details = session('payment_details');
        $remove_session_item = [];

        $payment = [];
        if (Session::has('keys')) {
            $payment['transaction_id'] = json_encode(session('keys'));
            $remove_session_item[] = 'keys';
        }
        if (Session::has('session_id')) {
            $payment['session_id'] = session('session_id');
            $remove_session_item[] = 'session_id';
        }

        $payment['invoice'] = Str::random(20);
        $payment_history = false;
        $enroll = false;

        for ($i = 0; $i < count($payment_details['items']); $i++) {
            $price          = $payment_details['items'][$i]['price'];
            $item_discount  = $payment_details['items'][$i]['discount_price'];
            $library_item   = LibraryItem::find($payment_details['items'][$i]['id']);

            $revenue = [
                'admin_revenue'      => 0,
                'instructor_revenue' => 0,
            ];
            if ($library_item && $library_item->uploader && $library_item->uploader->role == 'admin') {
                $revenue['admin_revenue'] = $payment_details['payable_amount'];
            } else {
                $revenue['instructor_revenue'] = $payment_details['payable_amount'] * (get_settings('instructor_revenue') / 100);
                $revenue['admin_revenue']      = $payment_details['payable_amount'] - $revenue['instructor_revenue'];
            }

            $payment['library_item_id'] = $payment_details['items'][$i]['id'];
            $payment['tax']             = $payment_details['tax'];
            $payment['amount']          = $item_discount ? $item_discount : $price;
            $payment['user_id']         = auth()->user()->id;
            $payment['payment_type']    = $identifier;
            $payment['coupon']          = $payment_details['coupon'];
            $payment['purchase_type']   = 'paid';
            $payment['entry_date']      = time();
            $payment = array_merge($payment, $revenue);

            $payment_history = DB::table('library_purchases')->insert($payment);

            if ($payment_history) {
                $enroll = true;
            }
        }

        if ($payment_history && $enroll) {
            $cart_items = $payment_details['custom_field']['cart_id'] ?? [];
            foreach ($cart_items as $item) {
                DB::table('library_cart_items')->where('user_id', auth()->user()->id)->where('library_item_id', $item)->delete();
            }
        }

        $remove_session_item[] = 'payment_details';
        Session::forget($remove_session_item);
        Session::flash('success', get_phrase('Book purchased successfully.'));
        return redirect()->route('my.library');
    }
}
