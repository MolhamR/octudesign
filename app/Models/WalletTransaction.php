<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WalletTransaction extends Model
{
    protected $fillable = ['user_id','wallet_id', 'amount', 'transaction_type', 'description'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

}
