<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\frontend\CourseController;
use App\Http\Controllers\ApiController;


/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
Route::post('/login', [ApiController::class, 'login']);
Route::post('/signup', [ApiController::class, 'signup']);
Route::post('/signup_with', [ApiController::class, 'signUpWith']);
Route::post('/forgot-password', [ApiController::class, 'sendResetLinkEmail']);

Route::get('/getCourseReviews', [ApiController::class, 'getCourseReviews']);
Route::get('/forums', [ApiController::class, 'indexForum']);

Route::group(['middleware', ['auth:sanctum']], function () {
    Route::get('/top_courses', [ApiController::class, 'top_courses']);
    Route::get('/all_categories', [ApiController::class, 'all_categories']);
    Route::get('/categories', [ApiController::class, 'categories']);
    Route::get('/category_details', [ApiController::class, 'category_details']);
    Route::get('/sub_categories/{id}', [ApiController::class, 'sub_categories']);
    Route::get('/category_wise_course', [ApiController::class, 'category_wise_course']);
    Route::get('/category_subcategory_wise_course', [ApiController::class, 'category_subcategory_wise_course']);
    Route::get('/filter_course', [ApiController::class, 'filter_course']);
    Route::get('/my_wishlist', [ApiController::class, 'my_wishlist']);
    Route::get('/toggle_wishlist_items', [ApiController::class, 'toggle_wishlist_items']);
    Route::get('/languages', [ApiController::class, 'languages']);
    Route::get('/courses_by_search_string', [ApiController::class, 'courses_by_search_string']);
    Route::get('/my_courses', [ApiController::class, 'my_courses']);
    Route::get('/sections', [ApiController::class, 'sections']);
    Route::get('/course_details_by_id', [ApiController::class, 'course_details_by_id']);
    Route::post('/update_password', [ApiController::class, 'update_password']);
    Route::post('/update_userdata', [ApiController::class, 'update_userdata']);
    Route::post('/account_disable', [ApiController::class, 'account_disable']);
    Route::get('/cart_list', [ApiController::class, 'cart_list']);
    Route::get('/toggle_cart_items', [ApiController::class, 'toggle_cart_items']);
    Route::get('/save_course_progress', [ApiController::class, 'save_course_progress']);
    Route::post('/logout', [ApiController::class, 'logout']);

    //Zoom live class
    Route::get('zoom/settings', [ApiController::class, 'zoom_settings']);
    Route::get('zoom/meetings', [ApiController::class, 'live_class_schedules']);

    Route::get('payment/{token}', [ApiController::class, 'payment']);
    Route::get('token', [ApiController::class, 'token']);

    Route::get('free_course_enroll/{course_id}', [ApiController::class, 'free_course_enroll']);

    Route::get('cart_tools', [ApiController::class, 'cart_tools']);

    Route::get('check_version', [ApiController::class, 'check_version']);

    Route::get('update_watch_history', [ApiController::class, 'update_watch_history_with_duration']);

    Route::get('getWallet', [ApiController::class, 'getWallet']);

    Route::get('quizzes/{quiz_id}', [ApiController::class, 'getQuiz']);
    Route::get('quizzes/section/{section_id}', [ApiController::class, 'getSectionQuizzes']);
    Route::get('quizzes/{quiz_id}/questions', [ApiController::class, 'getQuizQuestions']);
    Route::post('quizzes/submit', [ApiController::class, 'submitQuiz']);
    Route::get('get-quiz-result', [ApiController::class, 'load_result']);

    Route::post('/reviews', [ApiController::class, 'storeReview']);
    Route::put('/reviews/{id}', [ApiController::class, 'updateReview']);
    Route::delete('/reviews/{id}', [ApiController::class, 'deleteReview']);
    Route::post('/reviews/{id}/like', [ApiController::class, 'likeReview']);
    Route::post('/reviews/{id}/dislike', [ApiController::class, 'dislikeReview']);



    Route::post('/forums', [ApiController::class, 'storeForum']);
    Route::put('/forums/{id}', [ApiController::class, 'updateForum']);
    Route::delete('/forums/{id}', [ApiController::class, 'deleteForum']);
    Route::post('/forums/{id}/like', [ApiController::class, 'likeForum']);
    Route::post('/forums/{id}/dislike', [ApiController::class, 'dislikeForum']);

    Route::post('/pay_with_wallet', [ApiController::class, 'payWithWallet']);


    Route::get('/users/certificates/{course_id}', [ApiController::class, 'getUserCertificates']);
    Route::get('/certificates/{identifier}', [ApiController::class, 'getCertificate']);
    Route::get('/certificates/{identifier}/download', [ApiController::class, 'downloadCertificate'])->name('api.certificates.download');
    

    Route::get('/users/courses/{course_id}/completion', [ApiController::class, 'checkCourseCompletion']);
    



});

Route::middleware('auth:sanctum')->group(function () {
    // Route::get('/email/verify/{id}/{hash}', [ApiController::class, 'verify'])
    //     ->middleware(['signed', 'throttle:6,1'])
    //     ->name('api.verification.verify');

    Route::post('/email/verification-notification', [ApiController::class, 'send'])
        ->middleware(['throttle:6,1'])
        ->name('api.verification.send');
});


