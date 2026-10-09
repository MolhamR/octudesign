<?php

namespace App\Http\Controllers\instructor;

use App\Http\Controllers\Controller;
use App\Models\FileUploader;
use App\Models\LibraryCategory;
use App\Models\LibraryItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;

class LibraryController extends Controller
{
    public function index(Request $request)
    {
        $query = LibraryItem::leftJoin('library_categories', 'library_items.category_id', '=', 'library_categories.id')
            ->select('library_items.*', 'library_categories.title as category_title')
            ->where('library_items.user_id', auth()->user()->id);

        if ($request->search) {
            $query->where('library_items.title', 'like', '%' . $request->search . '%');
        }
        if ($request->status) {
            $query->where('library_items.status', $request->status);
        }

        $page_data['library_items'] = $query->latest('library_items.id')->paginate(20)->appends($request->query());

        $user_id = auth()->user()->id;
        $page_data['all_books'] = LibraryItem::where('user_id', $user_id)->count();
        $page_data['active_books'] = LibraryItem::where('user_id', $user_id)->where('status', 'active')->count();
        $page_data['pending_books'] = LibraryItem::where('user_id', $user_id)->where('status', 'pending')->count();
        $page_data['inactive_books'] = LibraryItem::where('user_id', $user_id)->where('status', 'inactive')->count();

        return view('instructor.library.index', $page_data);
    }

    public function create()
    {
        $page_data['categories'] = LibraryCategory::where('status', 1)->orderBy('sort', 'asc')->orderBy('id', 'asc')->get();
        return view('instructor.library.create', $page_data);
    }

    public function store(Request $request)
    {
        $request->validate([
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
        $data['created_at'] = date('Y-m-d H:i:s');
        $data['updated_at'] = date('Y-m-d H:i:s');

        $data['slug'] = slugify($request->title);

        // store the pdf file on the private storage disk
        $data['file'] = $this->store_pdf($request->file('file'));

        if ($request->hasFile('cover_image')) {
            $data['cover_image'] = 'uploads/library/cover/' . nice_file_name($request->title . ' cover', $request->cover_image->extension());
            FileUploader::upload($request->cover_image, $data['cover_image'], 400, null, 200, 200);
        }

        $library_item_id = LibraryItem::insertGetId($data);

        LibraryItem::where('id', $library_item_id)->update(['slug' => slugify($request->title . '-' . $library_item_id)]);

        Session::flash('success', get_phrase('Book added successfully'));
        return redirect(route('instructor.library'));
    }

    public function edit($id)
    {
        $library_item = LibraryItem::where('id', $id)->where('user_id', auth()->user()->id)->first();
        if (!$library_item) {
            Session::flash('error', get_phrase('Data not found.'));
            return redirect(route('instructor.library'));
        }

        $page_data['library_item'] = $library_item;
        $page_data['categories'] = LibraryCategory::where('status', 1)->orderBy('sort', 'asc')->orderBy('id', 'asc')->get();
        return view('instructor.library.edit', $page_data);
    }

    public function update(Request $request, $id)
    {
        $query = LibraryItem::where('id', $id)->where('user_id', auth()->user()->id);
        $library_item = $query->first();
        if (!$library_item) {
            Session::flash('error', get_phrase('Data not found.'));
            return redirect(route('instructor.library'));
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
                unlink(storage_path('app/' . $library_item->file));
            }
            $data['file'] = $this->store_pdf($request->file('file'));
        }

        if ($request->hasFile('cover_image')) {
            $data['cover_image'] = 'uploads/library/cover/' . nice_file_name($request->title . ' cover', $request->cover_image->extension());
            FileUploader::upload($request->cover_image, $data['cover_image'], 400, null, 200, 200);
            remove_file($library_item->cover_image);
        }

        $query->update($data);

        Session::flash('success', get_phrase('Book updated successfully'));
        return redirect(route('instructor.library'));
    }

    public function delete($id)
    {
        $library_item = LibraryItem::where('id', $id)->where('user_id', auth()->user()->id)->first();
        if (!$library_item) {
            Session::flash('error', get_phrase('Data not found.'));
            return redirect(route('instructor.library'));
        }

        remove_file($library_item->cover_image);
        if ($library_item->file && file_exists(storage_path('app/' . $library_item->file))) {
            unlink(storage_path('app/' . $library_item->file));
        }
        LibraryItem::where('id', $id)->delete();

        Session::flash('success', get_phrase('Book deleted successfully'));
        return redirect(route('instructor.library'));
    }

    public function status($id)
    {
        $query = LibraryItem::where('id', $id)->where('user_id', auth()->user()->id);
        if ($query->exists()) {
            $status = $query->first()->status == 'active' ? 'inactive' : 'active';
            $query->update(['status' => $status]);
            Session::flash('success', get_phrase('Status updated successfully.'));
        } else {
            Session::flash('error', get_phrase('Data not found.'));
        }
        return redirect()->back();
    }

    private function store_pdf($file)
    {
        return Storage::disk('local')->putFileAs('library/pdf', $file, time() . '-' . random(30) . '.pdf');
    }
}
