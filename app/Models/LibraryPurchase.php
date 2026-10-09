<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LibraryPurchase extends Model
{
    use HasFactory;

    protected $table = 'library_purchases';

    protected $fillable = [
        'user_id', 'library_item_id', 'purchase_type', 'invoice', 'payment_type', 'amount',
        'tax', 'coupon', 'admin_revenue', 'instructor_revenue', 'transaction_id', 'session_id', 'entry_date',
    ];
}
