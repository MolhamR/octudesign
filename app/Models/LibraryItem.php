<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LibraryItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'category_id', 'title', 'slug', 'description', 'cover_image', 'file',
        'is_paid', 'price', 'discount_flag', 'discounted_price', 'status', 'downloads',
    ];

    public function uploader()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function category()
    {
        return $this->belongsTo(LibraryCategory::class, 'category_id');
    }
}
