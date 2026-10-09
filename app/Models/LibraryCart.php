<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LibraryCart extends Model
{
    use HasFactory;

    protected $table = 'library_cart_items';

    protected $fillable = ['user_id', 'library_item_id'];
}
