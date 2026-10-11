<?php

namespace App\Http\Controllers\student;

use App\Http\Controllers\Controller;
use App\Models\Coupon;
use App\Models\LibraryCart;
use App\Models\LibraryCategory;
use App\Models\LibraryItem;
use App\Models\LibraryPurchase;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class LibraryController extends Controller
{
    public function index(Request $request, $category = null)
    {
        $query = LibraryItem::join('users', 'library_items.user_id', '=', 'users.id')
            ->leftJoin('library_categories', 'library_items.category_id', '=', 'library_categories.id')
            ->select('library_items.*', 'users.name as instructor_name', 'users.photo as instructor_image', 'library_categories.title as category_title', 'library_categories.slug as category_slug')
            ->where('library_items.status', 'active');

        $category_details = null;
        if ($category) {
            $category_details = LibraryCategory::where('slug', $category)->where('status', 1)->first();
            if ($category_details) {
                $query->whereIn('library_items.category_id', LibraryCategory::descendant_ids($category_details->id));
            }
        }

        if ($request->search) {
            $query->where('library_items.title', 'like', '%' . $request->search . '%');
        }

        $page_data['library_items'] = $query->latest('library_items.id')->paginate(12)->appends($request->query());
        $page_data['category_details'] = $category_details;

        return view('frontend.' . get_frontend_settings('theme') . '.library.index', $page_data);
    }

    public function details($slug)
    {
        $page_data['library_item'] = LibraryItem::join('users', 'library_items.user_id', '=', 'users.id')
            ->select('library_items.*', 'users.name as instructor_name', 'users.photo as instructor_image', 'users.id as uploader_user_id')
            ->where('library_items.slug', $slug)
            ->where('library_items.status', 'active')
            ->first();

        if (!$page_data['library_item']) {
            Session::flash('error', get_phrase('Data not found.'));
            return redirect()->route('library');
        }

        $page_data['category_breadcrumb'] = LibraryCategory::breadcrumb($page_data['library_item']->category_id);
        $page_data['is_purchased'] = auth()->check() && LibraryPurchase::where('user_id', auth()->user()->id)
            ->where('library_item_id', $page_data['library_item']->id)->exists();
        $page_data['in_cart'] = auth()->check() && LibraryCart::where('user_id', auth()->user()->id)
            ->where('library_item_id', $page_data['library_item']->id)->exists();

        return view('frontend.' . get_frontend_settings('theme') . '.library.details', $page_data);
    }

    public function purchase($id)
    {
        $library_item = LibraryItem::where('id', $id)->where('status', 'active')->first();
        if (!$library_item) {
            Session::flash('error', get_phrase('Data not found.'));
            return redirect()->back();
        }

        if ($library_item->user_id == auth()->user()->id) {
            Session::flash('error', get_phrase('Ops! You own this book.'));
            return redirect()->back();
        }

        if (LibraryPurchase::where('user_id', auth()->user()->id)->where('library_item_id', $id)->exists()) {
            Session::flash('success', get_phrase('You already purchased this book.'));
            return redirect()->route('my.library');
        }

        if ($library_item->is_paid == 0) {
            LibraryPurchase::insert([
                'user_id' => auth()->user()->id,
                'library_item_id' => $id,
                'purchase_type' => 'free',
                'amount' => 0,
                'entry_date' => time(),
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ]);
            Session::flash('success', get_phrase('Book added to your library.'));
            return redirect()->route('my.library');
        }

        if (LibraryCart::where('user_id', auth()->user()->id)->where('library_item_id', $id)->doesntExist()) {
            LibraryCart::insert([
                'user_id' => auth()->user()->id,
                'library_item_id' => $id,
                'created_at' => date('Y-m-d H:i:s'),
            ]);
        }
        return redirect(route('library.cart'));
    }

    public function cart(Request $request)
    {
        $discount = 0;
        $coupon = null;
        if ($request->has('coupon')) {
            $code = $request->query('coupon');
            $coupon = Coupon::where('code', $code)->first();
            if (!$coupon) {
                Session::flash('error', get_phrase('This coupon is not valid.'));
                return redirect()->back();
            }
            if ($coupon->status && (time() >= $coupon->expiry)) {
                Session::flash('error', get_phrase('Ops! coupon is expired.'));
                return redirect()->back();
            }
            $discount = $coupon->discount;
        }

        $page_data['cart_items'] = LibraryCart::join('library_items', 'library_cart_items.library_item_id', '=', 'library_items.id')
            ->leftJoin('users', 'library_items.user_id', '=', 'users.id')
            ->select('library_cart_items.id as cart_id', 'library_items.*', 'users.name as instructor_name')
            ->where('library_cart_items.user_id', auth()->user()->id)->get();
        $page_data['discount'] = $discount;
        $page_data['coupon'] = $coupon;

        return view('frontend.' . get_frontend_settings('theme') . '.library.cart', $page_data);
    }

    public function cart_store($id)
    {
        $library_item = LibraryItem::where('id', $id)->where('status', 'active')->first();
        if (!$library_item) {
            Session::flash('error', get_phrase('Data not found.'));
            return redirect()->back();
        }

        if (LibraryPurchase::where('user_id', auth()->user()->id)->where('library_item_id', $id)->exists()) {
            Session::flash('error', get_phrase('You already purchased the book.'));
            return redirect()->back();
        }

        if (LibraryCart::where('user_id', auth()->user()->id)->where('library_item_id', $id)->doesntExist()) {
            LibraryCart::insert([
                'user_id' => auth()->user()->id,
                'library_item_id' => $id,
                'created_at' => date('Y-m-d H:i:s'),
            ]);
        }

        Session::flash('success', get_phrase('Item added to the cart.'));
        return redirect()->back();
    }

    public function cart_delete($id)
    {
        $query = LibraryCart::where('library_item_id', $id)->where('user_id', auth()->user()->id);
        if ($query->exists()) {
            $query->delete();
            Session::flash('success', get_phrase('Item removed from cart.'));
        } else {
            Session::flash('error', get_phrase('Data not found.'));
        }
        return redirect()->back();
    }

    public function payout(Request $request)
    {
        $items_id = json_decode($request->items);
        if (!$items_id || count($items_id) == 0) {
            Session::flash('error', get_phrase('Data not found.'));
            return redirect()->route('library.cart');
        }

        $selected_items = LibraryItem::whereIn('id', $items_id)->where('status', 'active')->get();
        if ($selected_items->count() == 0) {
            Session::flash('error', get_phrase('Data not found.'));
            return redirect()->route('library.cart');
        }

        $items = [];
        foreach ($selected_items as $item) {
            $items[] = [
                'id' => $item->id,
                'title' => $item->title,
                'subtitle' => '',
                'price' => $item->price,
                'discount_price' => $item->discount_flag ? $item->discounted_price : 0,
            ];
        }

        $payment_details = [
            'items' => $items,

            'custom_field' => [
                'item_type' => 'library',
                'pay_for' => 'book payment',
                'user_id' => auth()->user()->id,
                'user_photo' => auth()->user()->photo,
                'cart_id' => $items_id,
                'coupon_discount' => $request->coupon_discount,
            ],

            'success_method' => [
                'model_name' => 'PurchaseLibraryItem',
                'function_name' => 'purchase_library',
            ],

            'tax' => round($request->tax, 2),
            'coupon' => $request->coupon_code,
            'payable_amount' => round($request->payable, 2),
            'cancel_url' => route('library.cart'),
            'success_url' => route('payment.success', ''),
        ];

        Session::put(['payment_details' => $payment_details]);
        return redirect()->route('payment');
    }

    public function my_library(Request $request)
    {
        $query = LibraryPurchase::join('library_items', 'library_purchases.library_item_id', '=', 'library_items.id')
            ->leftJoin('users', 'library_items.user_id', '=', 'users.id')
            ->leftJoin('library_categories', 'library_items.category_id', '=', 'library_categories.id')
            ->select('library_purchases.*', 'library_items.title', 'library_items.slug', 'library_items.cover_image', 'library_items.file', 'library_items.description', 'library_items.category_id', 'users.name as instructor_name', 'users.photo as instructor_photo', 'library_categories.title as category_title', 'library_categories.slug as category_slug')
            ->where('library_purchases.user_id', auth()->user()->id);

        $category_details = null;
        if ($request->filled('category')) {
            $category_details = LibraryCategory::where('slug', $request->category)->where('status', 1)->first();
            if ($category_details) {
                $query->whereIn('library_items.category_id', LibraryCategory::descendant_ids($category_details->id));
            }
        }

        if ($request->filled('search')) {
            $query->where('library_items.title', 'like', '%' . $request->search . '%');
        }

        $page_data['purchased_items'] = $query->orderBy('library_categories.title')->orderByDesc('library_purchases.id')->paginate(12)->appends($request->query());
        $page_data['category_details'] = $category_details;
        $page_data['my_library_tree'] = LibraryCategory::owned_tree(auth()->user()->id);

        return view('frontend.' . get_frontend_settings('theme') . '.student.my_library', $page_data);
    }

    public function read($slug)
    {
        $library_item = LibraryItem::where('slug', $slug)->where('status', 'active')->first();
        if (!$library_item || !$library_item->file) {
            Session::flash('error', get_phrase('Data not found.'));
            return redirect()->route('library');
        }

        $is_owner = $library_item->user_id == auth()->user()->id;
        $is_admin = auth()->user()->role == 'admin';
        $purchased = LibraryPurchase::where('user_id', auth()->user()->id)
            ->where('library_item_id', $library_item->id)->exists();

        if (!$is_owner && !$is_admin && !$purchased) {
            Session::flash('error', get_phrase('Please purchase this book to read it.'));
            return redirect()->route('library.details', $library_item->slug);
        }

        $page_data['library_item'] = $library_item;
        return view('frontend.' . get_frontend_settings('theme') . '.library.read', $page_data);
    }

    public function stream($id)
    {
        // Only serve the raw PDF to the in-app reader (never on direct navigation / hotlinking).
        if (!isset($_SERVER['HTTP_REFERER']) || !isset($_SERVER['HTTP_SEC_FETCH_MODE'])) {
            return response(get_phrase('Access denied'), 403, ['Content-Type' => 'text/plain; charset=UTF-8']);
        }

        $library_item = LibraryItem::where('id', $id)->where('status', 'active')->first();
        if (!$library_item || !$library_item->file) {
            return response(get_phrase('Data not found.'), 404, ['Content-Type' => 'text/plain; charset=UTF-8']);
        }

        $is_owner = $library_item->user_id == auth()->user()->id;
        $is_admin = auth()->user()->role == 'admin';
        $purchased = LibraryPurchase::where('user_id', auth()->user()->id)
            ->where('library_item_id', $id)->exists();

        if (!$is_owner && !$is_admin && !$purchased) {
            return response(get_phrase('Access denied'), 403, ['Content-Type' => 'text/plain; charset=UTF-8']);
        }

        $file_path = storage_path('app/' . $library_item->file);
        if (!file_exists($file_path)) {
            return response(get_phrase('Data not found.'), 404, ['Content-Type' => 'text/plain; charset=UTF-8']);
        }

        return response()->file($file_path, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="book-' . $library_item->id . '.pdf"',
            'X-Content-Type-Options' => 'nosniff',
            'Cache-Control' => 'no-store, no-cache, must-revalidate, max-age=0',
            'Pragma' => 'no-cache',
        ]);
    }

    public function download($id)
    {
        $library_item = LibraryItem::where('id', $id)->first();

        // Only the uploader (owner) or an admin may download the original file.
        $is_owner = $library_item && $library_item->user_id == auth()->user()->id;
        $is_admin = auth()->user()->role == 'admin';

        if (!$library_item || !$is_owner && !$is_admin || !$library_item->file) {
            Session::flash('error', get_phrase('Data not found.'));
            return redirect()->back();
        }

        $file_path = storage_path('app/' . $library_item->file);
        if (!file_exists($file_path)) {
            Session::flash('error', get_phrase('File does not exist.'));
            return redirect()->back();
        }

        return response()->download($file_path, ($library_item->slug ?? 'book') . '.pdf', [
            'Content-Type' => 'application/pdf',
        ]);
    }
}
