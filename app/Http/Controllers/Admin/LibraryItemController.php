<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FileUploader;
use App\Models\LibraryCategory;
use App\Models\LibraryItem;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class LibraryItemController extends Controller
{
    public function index(Request $request)
    {
        $query = LibraryItem::join('users', 'library_items.user_id', '=', 'users.id')
            ->leftJoin('library_categories', 'library_items.category_id', '=', 'library_categories.id')
            ->select('library_items.*', 'users.name as instructor_name', 'users.photo as instructor_image', 'library_categories.title as category_title', 'library_categories.slug as category_slug');

        if ($request->search) {
            $query->where('library_items.title', 'like', '%' . $request->search . '%');
        }
        if ($request->category) {
            $category = LibraryCategory::where('slug', $request->category)->first();
            if ($category) {
                $query->whereIn('library_items.category_id', LibraryCategory::descendant_ids($category->id));
            }
        }
        if ($request->status) {
            $query->where('library_items.status', $request->status);
        }
        if ($request->instructor) {
            $query->where('library_items.user_id', $request->instructor);
        }

        $page_data['library_items'] = $query->latest('library_items.id')->paginate(20)->appends($request->query());
        $page_data['categories'] = LibraryCategory::orderBy('title')->get();
        $page_data['instructors'] = User::whereIn('role', ['admin', 'instructor'])->orderBy('name')->get();

        return view('admin.library_item.index', $page_data);
    }

    public function create()
    {
        $page_data['categories'] = LibraryCategory::where('status', 1)->orderBy('sort', 'asc')->orderBy('id', 'asc')->get();
        return view('admin.library_item.create', $page_data);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|max:255',
            'category_id' => 'required|numeric|min:1',
            'is_paid' => 'required|in:0,1',
            'price' => 'nullable|required_if:is_paid,1|numeric|min:0',
            'discount_flag' => 'nullable|in:1',
            'discounted_price' => 'nullable|required_if:discount_flag,1|numeric|min:0',
            'file' => 'required|file|mimes:pdf|max:102400',
            'cover_image' => 'nullable|image|mimes:jpeg,jpg,png,webp|max:4096',
        ]);

        $data['user_id'] = auth()->user()->id;
        $data['category_id'] = $request->category_id;
        $data['title'] = $request->title;
        $data['description'] = $request->description;
        $data['is_paid'] = $request->is_paid;
        $data['price'] = $request->is_paid == 1 ? $request->price : 0;
        $data['discount_flag'] = $request->discount_flag ? 1 : 0;
        $data['discounted_price'] = $request->discount_flag ? $request->discounted_price : 0;
        $data['status'] = in_array($request->status, ['active', 'pending', 'inactive']) ? $request->status : 'active';
        $data['downloads'] = 0;
        $data['slug'] = slugify($request->title);
        $data['created_at'] = date('Y-m-d H:i:s');
        $data['updated_at'] = date('Y-m-d H:i:s');

        // store the pdf file on the private storage disk (never publicly reachable)
        $data['file'] = $this->store_pdf($request->file('file'));

        if ($request->hasFile('cover_image')) {
            $data['cover_image'] = 'uploads/library/cover/' . nice_file_name($request->title . ' cover', $request->cover_image->extension());
            FileUploader::upload($request->cover_image, $data['cover_image'], 400, null, 200, 200);
        }

        $library_item_id = LibraryItem::insertGetId($data);
        LibraryItem::where('id', $library_item_id)->update(['slug' => slugify($request->title . '-' . $library_item_id)]);

        return redirect(route('admin.library.items'))->with('success', get_phrase('Book added successfully'));
    }

    public function edit($id)
    {
        $library_item = LibraryItem::find($id);
        if (!$library_item) {
            return redirect(route('admin.library.items'))->with('error', get_phrase('Data not found.'));
        }

        $page_data['library_item'] = $library_item;
        $page_data['categories'] = LibraryCategory::where('status', 1)->orderBy('sort', 'asc')->orderBy('id', 'asc')->get();
        return view('admin.library_item.edit', $page_data);
    }

    public function update(Request $request, $id)
    {
        $library_item = LibraryItem::find($id);
        if (!$library_item) {
            return redirect(route('admin.library.items'))->with('error', get_phrase('Data not found.'));
        }

        $request->validate([
            'title' => 'required|max:255',
            'category_id' => 'required|numeric|min:1',
            'is_paid' => 'required|in:0,1',
            'price' => 'nullable|required_if:is_paid,1|numeric|min:0',
            'discount_flag' => 'nullable|in:1',
            'discounted_price' => 'nullable|required_if:discount_flag,1|numeric|min:0',
            'file' => 'nullable|file|mimes:pdf|max:102400',
            'cover_image' => 'nullable|image|mimes:jpeg,jpg,png,webp|max:4096',
        ]);

        $data['category_id'] = $request->category_id;
        $data['title'] = $request->title;
        $data['description'] = $request->description;
        $data['is_paid'] = $request->is_paid;
        $data['price'] = $request->is_paid == 1 ? $request->price : 0;
        $data['discount_flag'] = $request->discount_flag ? 1 : 0;
        $data['discounted_price'] = $request->discount_flag ? $request->discounted_price : 0;
        $data['status'] = in_array($request->status, ['active', 'pending', 'inactive']) ? $request->status : $library_item->status;
        $data['updated_at'] = date('Y-m-d H:i:s');

        if ($request->hasFile('file')) {
            if ($library_item->file && file_exists(storage_path('app/' . $library_item->file))) {
                @unlink(storage_path('app/' . $library_item->file));
            }
            $data['file'] = $this->store_pdf($request->file('file'));
        }

        if ($request->hasFile('cover_image')) {
            $data['cover_image'] = 'uploads/library/cover/' . nice_file_name($request->title . ' cover', $request->cover_image->extension());
            FileUploader::upload($request->cover_image, $data['cover_image'], 400, null, 200, 200);
            remove_file($library_item->cover_image);
        }

        LibraryItem::where('id', $id)->update($data);

        return redirect(route('admin.library.items'))->with('success', get_phrase('Book updated successfully'));
    }

    public function status($id)
    {
        $query = LibraryItem::where('id', $id);
        if ($query->exists()) {
            $status = $query->first()->status == 'active' ? 'inactive' : 'active';
            $query->update(['status' => $status]);
            return redirect()->back()->with('success', get_phrase('Status updated successfully.'));
        }
        return redirect()->back()->with('error', get_phrase('Data not found.'));
    }

    public function delete($id)
    {
        $item = LibraryItem::find($id);
        if (!$item) {
            return redirect(route('admin.library.items'))->with('error', get_phrase('Data not found.'));
        }

        remove_file($item->cover_image);
        if ($item->file && file_exists(storage_path('app/' . $item->file))) {
            unlink(storage_path('app/' . $item->file));
        }
        LibraryItem::where('id', $id)->delete();

        return redirect(route('admin.library.items'))->with('success', get_phrase('Book deleted successfully'));
    }

    private function store_pdf($file)
    {
        return Storage::disk('local')->putFileAs('library/pdf', $file, time() . '-' . random(30) . '.pdf');
    }
}
