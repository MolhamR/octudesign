<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LibraryCategory extends Model
{
    use HasFactory;

    protected $fillable = ['parent_id', 'title', 'slug', 'sort', 'status'];

    public function childs()
    {
        return $this->hasMany(LibraryCategory::class, 'parent_id');
    }

    public function parent()
    {
        return $this->belongsTo(LibraryCategory::class, 'parent_id');
    }

    public function items()
    {
        return $this->hasMany(LibraryItem::class, 'category_id');
    }

    public static function descendant_ids($category_id, $ids = [])
    {
        $ids[] = $category_id;
        $children = static::where('parent_id', $category_id)->pluck('id');
        foreach ($children as $child_id) {
            $ids = static::descendant_ids($child_id, $ids);
        }
        return $ids;
    }

    public static function breadcrumb($category_id)
    {
        $trail = [];
        $category = static::find($category_id);
        while ($category) {
            array_unshift($trail, $category);
            $category = $category->parent_id > 0 ? static::find($category->parent_id) : null;
        }
        return $trail;
    }
}
