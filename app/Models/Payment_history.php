<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment_history extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'course_id',
        'payment_type',
        'amount',
        'admin_revenue',
        'instructor_revenue',
        'tax',
        'coupon',
        'invoice',
        'instructor_payment_status',
        'transaction_id',
        'session_id',
    ];
    
}
