<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LibraryCategory;
use App\Models\LibraryItem;
use Illuminate\Http\Request;

class LibraryCategoryController extends Controller
{
    public function index()
    {
        $page_data['categories'] = LibraryCategory::orderBy('sort', 'asc')->orderBy('id', 'asc')->get();
        return view('admin.library_category.index', $page_data);
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|max:255',
            'parent_id' => 'required|numeric|min:0',
        ]);

        if (LibraryCategory::where('parent_id', $request->parent_id)->where('title', $request->title)->count() > 0) {
            return redirect(route('admin.library.categories'))->with('error', get_phrase('There cannot be more than one category with the same name under the same parent.'));
        }

        $data['parent_id'] = $request->parent_id;
        $data['title'] = $request->title;
        $data['slug'] = $this->unique_slug($request->title);
        $data['sort'] = 0;
        $data['status'] = 1;
        $data['created_at'] = date('Y-m-d H:i:s');
        $data['updated_at'] = date('Y-m-d H:i:s');

        LibraryCategory::insert($data);

        return redirect(route('admin.library.categories'))->with('success', get_phrase('Category added successfully'));
    }

    public function edit($id)
    {
        $category = LibraryCategory::find($id);
        if (!$category) {
            return redirect(route('admin.library.categories'))->with('error', get_phrase('Data not found.'));
        }

        $page_data['category'] = $category;
        $page_data['categories'] = LibraryCategory::orderBy('sort', 'asc')->orderBy('id', 'asc')->get();
        return view('admin.library_category.edit', $page_data);
    }

    public function update(Request $request, $id)
    {
        $query = LibraryCategory::where('id', $id);
        if (!$query->first()) {
            return redirect(route('admin.library.categories'))->with('error', get_phrase('Data not found.'));
        }

        $request->validate([
            'title' => 'required|max:255',
            'parent_id' => 'required|numeric|min:0',
        ]);

        if ($request->parent_id == $id) {
            return redirect(route('admin.library.categories'))->with('error', get_phrase('A category cannot be its own parent.'));
        }

        // prevent adding itself or one of its own descendants as parent
        if ($request->parent_id > 0 && in_array($request->parent_id, LibraryCategory::descendant_ids($id))) {
            return redirect(route('admin.library.categories'))->with('error', get_phrase('A category cannot be moved under one of its own sub categories.'));
        }

        if (LibraryCategory::where('parent_id', $request->parent_id)->where('title', $request->title)->where('id', '!=', $id)->count() > 0) {
            return redirect(route('admin.library.categories'))->with('error', get_phrase('There cannot be more than one category with the same name under the same parent.'));
        }

        $data['parent_id'] = $request->parent_id;
        $data['title'] = $request->title;
        $data['updated_at'] = date('Y-m-d H:i:s');

        $query->update($data);

        return redirect(route('admin.library.categories'))->with('success', get_phrase('Category updated successfully'));
    }

    public function delete($id)
    {
        $category = LibraryCategory::find($id);
        if (!$category) {
            return redirect(route('admin.library.categories'))->with('error', get_phrase('Data not found.'));
        }

        foreach (LibraryCategory::descendant_ids($id) as $category_id) {
            foreach (LibraryItem::where('category_id', $category_id)->get() as $item) {
                remove_file($item->cover_image);
                if ($item->file && file_exists(storage_path('app/' . $item->file))) {
                    unlink(storage_path('app/' . $item->file));
                }
                LibraryItem::where('id', $item->id)->delete();
            }
            LibraryCategory::where('id', $category_id)->delete();
        }

        return redirect(route('admin.library.categories'))->with('success', get_phrase('Category deleted successfully'));
    }

    public function status($id)
    {
        $query = LibraryCategory::where('id', $id);
        if ($query->exists()) {
            $status = $query->first()->status == 1 ? 0 : 1;
            $query->update(['status' => $status]);
            return redirect()->back()->with('success', get_phrase('Status updated successfully.'));
        }
        return redirect()->back()->with('error', get_phrase('Data not found.'));
    }

    private function unique_slug($title)
    {
        $slug = slugify($title);
        if (LibraryCategory::where('slug', $slug)->count() > 0) {
            $slug = slugify($title . '-' . time());
        }
        return $slug;
    }
}
