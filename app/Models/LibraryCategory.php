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

    /**
     * Nested tree of active categories that contain (directly or through a
     * descendant) at least one book purchased by the given user.
     */
    public static function owned_tree($user_id)
    {
        $owned_ids = \Illuminate\Support\Facades\DB::table('library_purchases')
            ->join('library_items', 'library_purchases.library_item_id', '=', 'library_items.id')
            ->where('library_purchases.user_id', $user_id)
            ->whereNotNull('library_items.category_id')
            ->pluck('library_items.category_id')
            ->unique()
            ->all();

        if (empty($owned_ids)) {
            return [];
        }

        $categories = static::where('status', 1)->orderBy('sort')->orderBy('id')->get();

        $by_id = [];
        foreach ($categories as $category) {
            $by_id[$category->id] = $category;
        }

        $keep = [];
        foreach ($owned_ids as $id) {
            while (isset($by_id[$id]) && empty($keep[$id])) {
                $keep[$id] = true;
                $id = $by_id[$id]->parent_id;
            }
        }

        $grouped = [];
        foreach ($categories as $category) {
            if (isset($keep[$category->id])) {
                $grouped[$category->parent_id][] = $category;
            }
        }

        $build = function ($parent_id) use (&$build, $grouped) {
            $nodes = [];
            foreach ($grouped[$parent_id] ?? [] as $category) {
                $category->children = $build($category->id);
                $nodes[] = $category;
            }
            return $nodes;
        };

        return $build(0);
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
