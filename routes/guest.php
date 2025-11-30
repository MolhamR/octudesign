<?php

use App\Http\Controllers\frontend\AboutController;
use App\Http\Controllers\frontend\BlogController;
use App\Http\Controllers\frontend\BootcampController;
use App\Http\Controllers\frontend\ContactController;
use App\Http\Controllers\frontend\CourseController;
use App\Http\Controllers\frontend\HomeController;
use App\Http\Controllers\frontend\InstructorController;
use App\Http\Controllers\frontend\NewsletterController;
use App\Http\Controllers\frontend\TeamTrainingController;
use App\Http\Controllers\frontend\TutorBookingController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::controller(HomeController::class)->group(function () {
    Route::get('/', 'index')->name('home');
    Route::post('/update_watch_history', 'update_watch_history_with_duration')->name('update_watch_history');
    Route::get('certificate/{identifier}','download_certificate')->name('certificate');
});

// course page
Route::controller(CourseController::class)->group(function () {
    Route::get('courses/{category?}', 'index')->name('courses');
    Route::get('change/layout', 'change_layout')->name('change.layout');
    Route::get('course/{slug}', 'course_details')->name('course.details');
});

// blogs page
Route::controller(BlogController::class)->middleware('blog.visibility')->group(function () {
    Route::get('blogs/{category?}', 'index')->name('blogs');
    Route::get('blog/{slug?}', 'blog_details')->name('blog.details');
    Route::get('blogs-list/{id}', 'blog_by_category')->name('blog.by.category');
});

// newsletter
Route::controller(NewsletterController::class)->group(function () {
    Route::post('newsletter/store', 'store')->name('newsletter.store');
});

// contact us
Route::controller(ContactController::class)->group(function () {
    Route::get('contact-us/', 'index')->name('contact.us');
    Route::post('contact/', 'store')->name('contact.store');
});

// about us
Route::controller(AboutController::class)->group(function () {
    Route::get('about-us/', 'index')->name('about.us');
});

// instructor details
Route::controller(InstructorController::class)->group(function () {
    Route::get('instructors', 'index')->name('instructors');
    Route::get('instructor-details/{name}/{id}', 'show')->name('instructor.details');
});

// privacy and policy
Route::get('/privacy-policy', function () {
    $view_path = 'frontend.' . get_frontend_settings('theme') . '.privacy_policy.index';
    return view($view_path);
})->name('privacy.policy');

// refund policy
Route::get('/refund-policy', function () {
    $view_path = 'frontend.' . get_frontend_settings('theme') . '.refund_policy.index';
    return view($view_path);
})->name('refund.policy');

//FAQ
Route::get('/faq', function () {
    $view_path = 'frontend.' . get_frontend_settings('theme') . '.faq.index';
    return view($view_path);
})->name('faq');

// terms and condition
Route::get('/terms-and-condition', function () {
    $view_path = 'frontend.' . get_frontend_settings('theme') . '.terms_and_condition.index';
    return view($view_path);
})->name('terms.condition');

Route::get('/cookie-policy', function () {
    $view_path = 'frontend.' . get_frontend_settings('theme') . '.cookie_policy.index';
    return view($view_path);
})->name('cookie.policy');

//Bootcamp
Route::controller(BootcampController::class)->group(function () {
    Route::get('bootcamp', 'index')->name('bootcamps');
    Route::get('bootcamp/{slug}', 'show')->name('bootcamp.details');
});

// team training
Route::controller(TeamTrainingController::class)->group(function () {
    Route::get('team-packages/{course_category?}', 'index')->name('team.packages');
    Route::get('team-package/{slug}', 'show')->name('team.package.details');
});

// //Certificate download
// Route::get('certificate/{identifier}', [HomeController::class, 'download_certificate'])->name('certificate');

// tutor booking
Route::controller(TutorBookingController::class)->group(function () {
    Route::get('tutors', 'index')->name('tutor_list');
    Route::get('tutor-schedule/{id}/{user}', 'tutor_schedule')->name('tutor_schedule');
    Route::get('tutor-schedule/{date}', 'getSchedulesForDate')->name('tutor.getSchedulesForDate');
    Route::get('tutor-schedule-by-calender-date/{date}', 'getSchedulesByCalenderDate')->name('tutor.getSchedulesByCalenderDate');
});