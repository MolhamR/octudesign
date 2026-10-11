<?php

namespace App\Http\Controllers;

use App\Models\Payment_history;
use Illuminate\Support\Facades\Session;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function admin_revenue(Request $request)
    {
        if ($request->eDateRange) {
            $date                    = explode('-', $request->eDateRange);
            $start_date              = strtotime($date[0] . ' 00:00:00');
            $end_date                = strtotime($date[1] . ' 23:59:59');
            $page_data['start_date'] = $start_date;
            $page_data['end_date']   = $end_date;

            $page_data['reports'] = Payment_history::where('admin_revenue', '!=', '')->where('created_at', '>=', date('Y-m-d H:i:s', $start_date))
                ->where('created_at', '<=', date('Y-m-d H:i:s', $end_date))
                ->latest('id')->paginate(10)->appends($request->query());
        } else {
            $start_date              = strtotime('first day of this month');
            $end_date                = strtotime('last day of this month');
            $page_data['start_date'] = $start_date;
            $page_data['end_date']   = $end_date;
            $page_data['reports']    = Payment_history::where('admin_revenue', '!=', '')->where('created_at', '>=', date('Y-m-d H:i:s', $start_date))
                ->where('created_at', '<=', date('Y-m-d H:i:s', $end_date))->latest('id')->paginate(10);
        }

        return view('admin.report.admin_revenue', $page_data);
    }

    public function admin_revenue_filter(Request $request)
    {
        if ($request->eDateRange) {
            $date                            = explode('-', $request->eDateRange);
            $start_date                      = strtotime($date[0] . ' 00:00:00');
            $end_date                        = strtotime($date[1] . ' 23:59:59');
            $page_data['start_date']         = $start_date;
            $page_data['end_date']           = $end_date;
            $page_data['reports'] = Payment_history::where('admin_revenue', '!=', '')->where('created_at', '>=', date('Y-m-d H:i:s', $start_date))
                ->where('created_at', '<=', date('Y-m-d H:i:s', $end_date))
                ->latest('id')->paginate(10)->appends($request->query());
        } else {
            $start_date              = strtotime('first day of this month');
            $end_date                = strtotime('last day of this month');
            $page_data['start_date'] = $start_date;
            $page_data['end_date']   = $end_date;
            $page_data['reports']    = Payment_history::where('admin_revenue', '!=', '')->where('created_at', '>=', date('Y-m-d H:i:s', $start_date))
                ->where('created_at', '<=', date('Y-m-d H:i:s', $end_date))
                ->latest('id')->paginate(10);
        }
        return view('admin.report.admin_revenue');
    }

    public function admin_revenue_delete($id)
    {

        Payment_history::where('id', $id)->delete();
        Session::flash('success', get_phrase('Admin revenue delete successfully'));
        return redirect()->back();
    }

    public function instructor_revenue(Request $request)
    {
        if ($request->eDateRange) {
            $date                            = explode('-', $request->eDateRange);
            $start_date                      = strtotime($date[0] . ' 00:00:00');
            $end_date                        = strtotime($date[1] . ' 23:59:59');
            $page_data['start_date']         = $start_date;
            $page_data['end_date']           = $end_date;
            $page_data['reports'] = Payment_history::where('instructor_revenue', '!=', '')->where('created_at', '>=', date('Y-m-d H:i:s', $start_date))
                ->where('created_at', '<=', date('Y-m-d H:i:s', $end_date))
                ->latest('id')->paginate(10)->appends($request->query());
        } else {
            $start_date              = strtotime('first day of this month');
            $end_date                = strtotime('last day of this month');
            $page_data['start_date'] = $start_date;
            $page_data['end_date']   = $end_date;
            $page_data['reports']    = Payment_history::where('instructor_revenue', '!=', '')->where('created_at', '>=', date('Y-m-d H:i:s', $start_date))
                ->where('created_at', '<=', date('Y-m-d H:i:s', $end_date))
                ->latest('id')->paginate(10);
        }
        return view('admin.report.instructor_revenue', $page_data);
    }

    public function instructor_revenue_delete($id)
    {

        Payment_history::where('id', $id)->delete();
        Session::flash('success', get_phrase('Instructor revenue delete successfully'));
        return redirect()->back();
    }

    public function purchase_history(Request $request)
    {
        if ($request->eDateRange) {
            $date                    = explode('-', $request->eDateRange);
            $start_date              = strtotime($date[0] . ' 00:00:00');
            $end_date                = strtotime($date[1] . ' 23:59:59');
        } else {
            $start_date = strtotime('first day of this month');
            $end_date   = strtotime('last day of this month');
        }
        $page_data['start_date'] = $start_date;
        $page_data['end_date']   = $end_date;

        $from = date('Y-m-d H:i:s', $start_date);
        $to   = date('Y-m-d H:i:s', $end_date);

        // Course / package payments
        $course_reports = Payment_history::where('created_at', '>=', $from)
            ->where('created_at', '<=', $to)
            ->latest('id')->get();

        // Library (book) purchases
        $book_reports = \App\Models\LibraryPurchase::join('library_items', 'library_purchases.library_item_id', '=', 'library_items.id')
            ->leftJoin('users', 'library_items.user_id', '=', 'users.id')
            ->where('library_purchases.created_at', '>=', $from)
            ->where('library_purchases.created_at', '<=', $to)
            ->select('library_purchases.*', 'library_items.title as item_title', 'users.photo as photo')
            ->orderByDesc('library_purchases.id')->get();

        $reports = collect();
        foreach ($course_reports as $report) {
            $report->history_type = 'course';
            $report->item_title   = $report->course_id > 0 ? (App\Models\Course::where('id', $report->course_id)->value('title') ?? '') : '';
            $reports->push($report);
        }
        foreach ($book_reports as $book) {
            $book->history_type = 'book';
            $reports->push($book);
        }
        $reports = $reports->sortByDesc('created_at')->values();

        $per_page = 10;
        $page     = \Illuminate\Pagination\LengthAwarePaginator::resolveCurrentPage();
        $page_data['reports'] = new \Illuminate\Pagination\LengthAwarePaginator(
            $reports->forPage($page, $per_page),
            $reports->count(),
            $per_page,
            $page,
            ['path' => \Illuminate\Pagination\Paginator::resolveCurrentPath(), 'query' => $request->query()]
        );
        $page_data['total_amount'] = $reports->sum('amount');

        return view('admin.report.purchase_history', $page_data);
    }

    public function purchase_history_invoice($id = '')
    {
        $page_data['report'] = Payment_history::where('id', $id)->first();
        return view('admin.report.report_invoice', $page_data);
    }
}
