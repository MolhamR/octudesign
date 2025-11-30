<?php

namespace App\Http\Controllers;

use App\Models\CartItem;
use App\Models\UserPassword;
use App\Models\Category;
use App\Models\Course;
use App\Models\Certificate;
use App\Models\Watch_history;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use App\Models\Enrollment;
use App\Models\Language;
use App\Models\Live_class;
use App\Models\User;
use App\Models\Wallet;
use App\Models\Lesson;
use App\Models\Wishlist;
use App\Models\Coupon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Str;
use App\Models\FileUploader;
use App\Models\Review;
use App\Models\LikeDislikeReview;
use Illuminate\Support\Facades\Password;
use DB;
use Illuminate\Auth\Events\Verified;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\JsonResponse;
use Carbon\Carbon;
use App\Http\Controllers\Controller;
use Illuminate\Validation\ValidationException;
use App\Models\Question;
use App\Models\QuizSubmission;
use App\Models\Forum;
use Illuminate\Support\Facades\Mail;
use App\Mail\Mailer;

class ApiController extends Controller
{

    //student login function
    public function login(Request $request)
    {
        $fields = $request->validate([
            'email' => 'required|string',
            'password' => 'required|string',
        ]);

        // Check email
        $user = User::where('email', $fields['email'])->where('status', 1)->first();

        // Check password
        if (!$user || !Hash::check($fields['password'], $user->password)) {
            if (isset($user) && $user->count() > 0) {
                return response([
                    'message' => 'Invalid credentials!',
                ], 401);
            } else {
                return response([
                    'message' => 'User not found!',
                ], 401);
            }
        } else if ($user->role == 'student') {

            // $user->tokens()->delete();

            $token = $user->createToken('auth-token')->plainTextToken;

            $user->photo = get_photo('user_image', $user->photo);

            $response = [
                'message' => 'Login successful',
                'user' => $user,
                'token' => $token,
            ];

            return response($response, 201);

        } else {

            //user not authorized
            return response()->json([
                'message' => 'User not found!',
            ], 400);
        }
    }

    public function signup(Request $request)
    {
        // return $request->all();
        $response = array();

        $rules = array(
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()]
        );
        $validator = Validator::make($request->all(), $rules);
        // if ($validator->fails()) {
        //     return json_encode(array('validationError' => $validator->getMessageBag()->toArray()));
        // }
        if ($validator->fails()) {
            return response()->json(['validationError' => $validator->errors()], 422);
        }
        // return $response;
        $user_data = [
            'name' => $request->name,
            'email' => $request->email,
            'role' => 'student',
            'password' => Hash::make($request->password),
            'status' => 1,
        ];
        
        if(get_settings('student_email_verification') == 0){
            $user_data['email_verified_at'] = Carbon::now();
        }

        $user = User::create($user_data);
        if ($user) {
            Wallet::create([
                'user_id' => $user->id,
                'balance' => 0,
                'currency' => 'SYP',
            ]);
            $response['success'] = true;
            $response['message'] = 'user create successfully';
        }
        event(new Registered($user));

        return $response;
    }

public function signUpWith(Request $request)
{
    $response = [];

    $rules = [
        'name'  => ['required', 'string', 'max:255'],
        'email' => ['required', 'string', 'email', 'max:255'],
        'photoUrl'  => ['nullable', 'string'],
    ];

    $validator = Validator::make($request->all(), $rules);

    if ($validator->fails()) {
        return response()->json(['validationError' => $validator->errors()], 422);
    }

    // Check if email already exists
    $user = User::where('email', $request->email)->first();

    if ($user) {
        $user->photo = get_photo('user_image', $user->photo);

        // If user exists, just create a token (login)
        $token = $user->createToken('auth-token')->plainTextToken;

        $response['success'] = true;
        $response['message'] = 'User logged in successfully';
        $response['token']   = $token;
        $response['user']    = $user;

        return $response;
    }

    $password = substr(str_shuffle('abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'), 0, 8);

    $user_data = [
        'name'     => $request->name,
        'email'    => $request->email,
        'role'     => 'student',
        'photo'     => $request->photoUrl ?? null,
        'password' => Hash::make($password),
        'status'   => 1,
        'email_verified_at' => Carbon::now()

    ];

    

    $user = User::create($user_data);

    if ($user) {
        $token = $user->createToken('auth-token')->plainTextToken;

        UserPassword::create([
            'user_id'  => $user->id,
            'password' => $password,
        ]);

        Wallet::create([
            'user_id'  => $user->id,
            'balance'  => 0,
            'currency' => 'SYP',
        ]);

        $response['success'] = true;
        $response['message'] = 'User created successfully';
        $response['token']   = $token;
        $response['user']    = $user;

        $user_email = $request->email;
        $subject = "User Register";
        $description = "Welcome to Octoacademy your password is  ".$password;
        $this->send_mail($user_email, $subject, $description);

        event(new Registered($user));
        
    }

    return $response;
}

    //student logout function
    public function logout(Request $request)
    {
        auth()->user()->tokens()->delete;

        return response()->json([
            'message' => 'Logged out successfully.',
        ], 201);
    }

        public function send_mail($user_email, $subject, $description)
    {
        config([
            'mail.mailers.smtp.transport'  => get_settings('protocol'),
            'mail.mailers.smtp.host'       => get_settings('smtp_host'),
            'mail.mailers.smtp.port'       => get_settings('smtp_port'),
            'mail.mailers.smtp.encryption' => get_settings('smtp_crypto'),
            'mail.mailers.smtp.username'   => get_settings('smtp_from_email'),
            'mail.mailers.smtp.password'   => get_settings('smtp_pass'),
            'mail.from.address'            => get_settings('smtp_from_email'),
            'mail.from.name'               => get_settings('system_name'),
        ]);

        $mail_data['subject']     = $subject;
        $mail_data['description'] = $description;

        $send = Mail::to($user_email)->send(new Mailer($mail_data));
        return $send;
    }

    // update user data
    public function update_userdata(Request $request)
    {
        $response = array();
        $token = $request->bearerToken();

        if (isset($token) && $token != '') {
            $user_id = auth('sanctum')->user()->id;

            if ($request->name != "") {
                $data['name'] = htmlspecialchars($request->name, ENT_QUOTES, 'UTF-8');
            } else {
                $response['status'] = 'failed';
                $response['error_reason'] = 'Name cannot be empty';
                return $response;
            }

            $data['biography'] = $request->biography;
            $data['about'] = $request->about;
            $data['address'] = $request->address;
            $data['facebook'] = htmlspecialchars($request->facebook, ENT_QUOTES, 'UTF-8');
            $data['twitter'] = htmlspecialchars($request->twitter, ENT_QUOTES, 'UTF-8');
            $data['linkedin'] = htmlspecialchars($request->linkedin, ENT_QUOTES, 'UTF-8');

            if ($request->hasFile('photo')) {
                $file = $request->file('photo');
                $file_name = Str::random(20) . '.' . $file->getClientOriginalExtension();
                $path = 'assets/upload/users/' . auth('sanctum')->user()->role . '/' . $file_name;

                // Assuming FileUploader::upload() is a method that uploads the file
                FileUploader::upload($file, $path, null, null, 300);

                // Save the path to the database
                $data['photo'] = $path;
            }

            User::where('id', $user_id)->update($data);

            $user = auth('sanctum')->user();
            $user->photo = get_photo('user_image', $user->photo);

            $updated_user = User::find($user_id);
            $updated_user['photo'] = url('public/' . $updated_user['photo']);

            $response['status'] = 'success';
            $response['user'] = $updated_user;
            $response['error_reason'] = 'None';

        } else {
            $response['status'] = 'failed';
            $response['error_reason'] = 'Unauthorized login';
        }

        return $response;
    }

    //
    public function top_courses(Request $request,$top_course_id = "")
    {
        $perPage = $request->get('per_page', 10);
        $query = Course::where('status' ,'active')->orderBy('id', 'desc');
    
        if ($top_course_id != "") {
            $query->where('id', $top_course_id);
        }
    
        // Use paginate instead of limit and get
        $courses = $query->paginate($perPage); // 10 is the number of items per page
    
        // Loop through each course and add the watch time data
        foreach ($courses as $course) {
            $lessons = Lesson::where('course_id', $course->id)->get();
    
            $totalSeconds = 0;
            foreach ($lessons as $lesson) {
                if ($lesson->duration) {
                    list($hours, $minutes, $seconds) = explode(':', $lesson->duration);
                    $totalSeconds += ($hours * 3600) + ($minutes * 60) + $seconds;
                }
            }
    
            $watchTime = gmdate('H:i:s', $totalSeconds);
            $course->watch_time_key = $watchTime;
        }
    
        // You can still use your existing function to format the result if needed
        $result = course_data($courses);
    
        return $result;
    }
    

    public function all_categories()
    {
        $all_categories = array();
        $categories = Category::where('parent_id', 0)->get();
        foreach ($categories as $key => $category) {
            $all_categories[$key] = $category;
            $all_categories[$key]['thumbnail'] = get_photo('category_thumbnail', $category['thumbnail']);
            $all_categories[$key]['number_of_courses'] = get_category_wise_courses($category['id'])->count();

            $all_categories[$key]['number_of_sub_categories'] = $category->childs->count();

            // $sub_categories = $category->childs;
        }
        return $all_categories;
    }

    // Get categories
    public function categories($category_id = "")
    {
        if ($category_id != "") {
            $categories = Category::where('id', $category_id)->first();
        } else {
            $categories = Category::where('parent_id', 0)->get();
        }
        foreach ($categories as $key => $category) {
            $categories[$key]['thumbnail'] = get_photo('category_thumbnail', $category['thumbnail']);
            $categories[$key]['number_of_courses'] = get_category_wise_courses($category['id'])->count();

            $categories[$key]['number_of_sub_categories'] = $category->childs->count();
        }
        return $categories;
    }

    // Fetch all the categories
    public function category_details(Request $request)
    {
        $response = array();
        $categories = array();
        $categories = sub_categories($request->category_id);
    
        $response[0]['sub_categories'] = $categories;
    
        $courses = get_category_wise_courses($request->category_id);
    
        // Calculate watch_time_key before formatting course data
        foreach ($courses as $course) {
            $lessons = Lesson::where('course_id', $course->id)->get();
    
            $totalSeconds = 0;
            foreach ($lessons as $lesson) {
                if ($lesson->duration) {
                    list($hours, $minutes, $seconds) = explode(':', $lesson->duration);
                    $totalSeconds += ($hours * 3600) + ($minutes * 60) + $seconds;
                }
            }
    
            $watchTime = gmdate('H:i:s', $totalSeconds);
            $course->watch_time_key = $watchTime;
        }
    
        $response[0]['courses'] = course_data($courses);
    
        return $response;
    }
    

    // Fetch all the categories
    public function sub_categories($parent_category_id = "")
    {

        $categories = array();
        $categories = sub_categories($parent_category_id);

        return $categories;
    }

    // Fetch all the courses belong to a certain category
    public function category_wise_course(Request $request)
    {
        $category_id = $request->category_id;
        $courses = get_category_wise_courses($category_id);
    
        // Add watch_time_key to each course
        foreach ($courses as $course) {
            $lessons = Lesson::where('course_id', $course->id)->get();
    
            $totalSeconds = 0;
            foreach ($lessons as $lesson) {
                if ($lesson->duration) {
                    list($hours, $minutes, $seconds) = explode(':', $lesson->duration);
                    $totalSeconds += ($hours * 3600) + ($minutes * 60) + $seconds;
                }
            }
    
            $watchTime = gmdate('H:i:s', $totalSeconds);
            $course->watch_time_key = $watchTime;
        }
    
        $result = course_data($courses);
    
        return $result;
    }
    
    // Fetch all the courses belong to a certain category
    public function category_subcategory_wise_course(Request $request)
    {
        $category_id = $request->category_id;
        $courses = get_category_wise_courses($category_id);
        $sub = Category::where('id', $category_id)->where('status', 'active')->get();
    
        // Add watch_time_key to each course
        foreach ($courses as $course) {
            $lessons = Lesson::where('course_id', $course->id)->get();
    
            $totalSeconds = 0;
            foreach ($lessons as $lesson) {
                if ($lesson->duration) {
                    list($hours, $minutes, $seconds) = explode(':', $lesson->duration);
                    $totalSeconds += ($hours * 3600) + ($minutes * 60) + $seconds;
                }
            }
    
            $watchTime = gmdate('H:i:s', $totalSeconds);
            $course->watch_time_key = $watchTime;
        }
    
        $result = course_data($courses);
    
        return $result;
    }
    

    public function filter_course(Request $request)
    {
        $query = Course::query();
    
        if (!empty($request->selected_search_string) && $request->selected_search_string !== "null") {
            $query->where('title', 'like', '%' . trim($request->selected_search_string) . '%');
        }
    
        if ($request->selected_category && $request->selected_category !== "all") {
            $query->where('category_id', $request->selected_category);
        }
    
        if ($request->selected_price && $request->selected_price !== "all") {
            if ($request->selected_price === "paid") {
                $query->where('is_paid', 1);
            } elseif ($request->selected_price === "free") {
                $query->where(function($q) {
                    $q->where('is_paid', 0)
                      ->orWhereNull('is_paid');
                });
            }
        }
    
        if ($request->selected_level && $request->selected_level !== "all") {
            $query->where('level', $request->selected_level);
        }
    
        if ($request->selected_language && $request->selected_language !== "all") {
            $query->where('language', $request->selected_language);
        }
    
        $query->where('status', 'active');
    
        // Use pagination instead of get()
        $perPage = $request->get('per_page', 10); // Default is 10 if not provided
        $courses = $query->paginate($perPage);
    
        // Add watch_time_key to each course
        foreach ($courses as $course) {
            $lessons = Lesson::where('course_id', $course->id)->get();
    
            $totalSeconds = 0;
            foreach ($lessons as $lesson) {
                if ($lesson->duration) {
                    list($hours, $minutes, $seconds) = explode(':', $lesson->duration);
                    $totalSeconds += ($hours * 3600) + ($minutes * 60) + $seconds;
                }
            }
    
            $watchTime = gmdate('H:i:s', $totalSeconds);
            $course->watch_time_key = $watchTime;
        }
    
        // Use your custom formatting function
        $result = course_data($courses);
    
        // Optional: include pagination metadata if needed
        return response()->json(
            $result
         
        );
    }
        

    // Fetch all the courses belong to a certain category
    public function languages()
    {
        $response = array();
        $languages = Language::select('name')->distinct()->get();

        foreach ($languages as $key => $language) {
            $response[$key]['id'] = $key + 1;
            $response[$key]['value'] = $language->name;
            $response[$key]['displayedValue'] = ucfirst($language->name);
        }

        return $response;
    }

    // Filter course
    public function courses_by_search_string(Request $request)
    {
        $search_string = $request->search_string;
        $perPage = $request->get('per_page', 10); // default to 10 if not provided
    
        $courses = Course::where('title', 'LIKE', "%{$search_string}%")
            ->where('status', 'active')
            ->paginate($perPage);
    
        // Add watch_time_key to each course
        foreach ($courses as $course) {
            $lessons = Lesson::where('course_id', $course->id)->get();
    
            $totalSeconds = 0;
            foreach ($lessons as $lesson) {
                if ($lesson->duration) {
                    list($hours, $minutes, $seconds) = explode(':', $lesson->duration);
                    $totalSeconds += ($hours * 3600) + ($minutes * 60) + $seconds;
                }
            }
    
            $course->watch_time_key = gmdate('H:i:s', $totalSeconds);
        }
    
        $response = course_data($courses);
    
        return response()->json(
           $response,
           
        );
    }
    

    // Course Details
    public function course_details_by_id(Request $request)
    {
        $response = array();
    
        $course_id = $request->course_id;
        $course = Course::where('id', $course_id)->first();
        $user = auth('sanctum')->user();
        $user_id = $user ? $user->id : 0;

    
        if ($user_id > 0) {
             
            $response = course_details_by_id($user_id, $course_id);
        } else {

            $response = course_details_by_id(0, $course_id);
        }    
        return $response;
    }
    

    //Protected APIs. This APIs will require Authorization.
    // My Courses API
    public function my_courses(Request $request)
    {
        $token = $request->bearerToken();
        if (isset($token) && $token != '') {
            $user_id = auth('sanctum')->user()->id;
            $perPage = $request->get('per_page', 10); // default 10
    
            $paginatedEnrollments = Enrollment::where('user_id', $user_id)
                ->orderBy('id', 'desc')
                ->paginate($perPage);
    
            $my_courses = [];
    
            foreach ($paginatedEnrollments as $enrollment) {
                $course = Course::find($enrollment->course_id);
                if (!$course) continue;
    
                $lessons = Lesson::where('course_id', $course->id)->get();
    
                $totalSeconds = 0;
                foreach ($lessons as $lesson) {
                    if ($lesson->duration) {
                        list($hours, $minutes, $seconds) = explode(':', $lesson->duration);
                        $totalSeconds += ($hours * 3600) + ($minutes * 60) + $seconds;
                    }
                }
    
                $course->watch_time_key = gmdate('H:i:s', $totalSeconds);
                $my_courses[] = $course;
            }
    
            $my_courses = course_data($my_courses);
    
            foreach ($my_courses as $key => $course) {
                if (isset($course['id']) && $course['id'] > 0) {
                    $my_courses[$key]['completion'] = round(course_progress($course['id'], $user_id));
                    $my_courses[$key]['total_number_of_lessons'] = count(get_lessons('course', $course['id']));
                    $my_courses[$key]['total_number_of_completed_lessons'] = get_completed_number_of_lesson($user_id, 'course', $course['id']);
                }
            }
    
            return response()->json([
                'current_page' => $paginatedEnrollments->currentPage(),
                'data' => $my_courses,
                'pagination' => [
                    'total' => $paginatedEnrollments->total(),
                 
                    'per_page' => $paginatedEnrollments->perPage(),
                    'last_page' => $paginatedEnrollments->lastPage(),
                    'next_page_url' => $paginatedEnrollments->nextPageUrl(),
                    'prev_page_url' => $paginatedEnrollments->previousPageUrl(),
                ],
            ]);
        } else {
            return response()->json(null, 401);
        }
    }
    

    // My Courses API
    public function my_wishlist(Request $request)
    {
        $token = $request->bearerToken();
    
        if (isset($token) && $token != '') {
            $user_id = auth('sanctum')->user()->id;
            $wishlist = Wishlist::where('user_id', $user_id)->pluck('course_id');
    
            if ($wishlist->count() > 0) {
                $perPage = $request->get('per_page', 10); // default is 10
    
                $courses = Course::whereIn('id', $wishlist)
                    ->where('status', 'active')
                    ->paginate($perPage);
    
                $transformedCourses = course_data($courses->items());
    
                return response()->json([
                    'current_page' => $courses->currentPage(),
                    'data' => $transformedCourses,
                    'pagination' => [
                        'total' => $courses->total(),
                        'per_page' => $courses->perPage(),
                        'last_page' => $courses->lastPage(),
                        'next_page_url' => $courses->nextPageUrl(),
                        'prev_page_url' => $courses->previousPageUrl(),
                    ],
                ]);
            } else {
                return response()->json([
                    'current_page' => 1,
                    'data' => [],
                    'pagination' => [
                        'total' => 0,
                        'per_page' => 10,
                        'last_page' => 1,
                        'next_page_url' => null,
                        'prev_page_url' => null,
                    ],
                ]);
            }
        } else {
            return response()->json(null, 401);
        }
    }
    

    // Remove from wishlist
    public function toggle_wishlist_items(Request $request)
    {
        $token = $request->bearerToken();

        if (isset($token) && $token != '') {
            $user_id = auth('sanctum')->user()->id;

            $status = "";
            $course_id = $request->course_id;
            $wishlists = array();
            $check_status = Wishlist::where('course_id', $course_id)->where('user_id', $user_id)->first();
            if (empty($check_status)) {
                $wishlist = new Wishlist();
                $wishlist->course_id = $request->course_id;
                $wishlist->user_id = $user_id;
                $wishlist->save();
                $status = "added";
            } else {
                Wishlist::where('user_id', $user_id)->where('course_id', $request->course_id)->delete();
                $status = "removed";
            }
            // $this->my_wishlist($user_id);
            $response['status'] = $status;
            return $response;

        } else {
            return response()->json([
                'message' => 'Please login first',
            ], 400);
        }
    }

    // Get all the sections
    public function sections(Request $request)
    {

        $token = $request->bearerToken();

        if (isset($token) && $token != '') {
            $user_id = auth('sanctum')->user()->id;
            $course_id = $request->course_id;
            $response = sections($course_id, $user_id);
        } else {

        }

        return $response;
    }

    // password reset
    public function update_password(Request $request)
    {

        $token = $request->bearerToken();
        $response = array();

        if (isset($token) && $token != '') {
            $auth = auth('sanctum')->user();

            // The passwords matches
            if (!Hash::check($request->get('current_password'), $auth->password)) {
                $response['status'] = 'failed';
                $response['message'] = 'Current Password is Invalid';

                return $response;
            }

            // Current password and new password same
            if (strcmp($request->get('current_password'), $request->new_password) == 0) {
                $response['status'] = 'failed';
                $response['message'] = 'New Password cannot be same as your current password.';

                return $response;
            }

            // Current password and new password same
            if (strcmp($request->get('confirm_password'), $request->new_password) != 0) {
                $response['status'] = 'failed';
                $response['message'] = 'New Password is not same as your confirm password.';

                return $response;
            }

            $user = User::find($auth->id);
            $user->password = Hash::make($request->new_password);
            $user->save();

            $response['status'] = 'success';
            $response['message'] = 'Password Changed Successfully';

            return $response;

        } else {
            $response['status'] = 'failed';
            $response['message'] = 'Please login first';

            return $response;
        }
    }

    public function account_disable(Request $request)
    {

        $token = $request->bearerToken();
        $response = array();

        if (isset($token) && $token != '') {
            $auth = auth('sanctum')->user();

            $account_password = $request->get('account_password');

            // The passwords matches
            if (Hash::check($account_password, $auth->password)) {
                User::where('id', $auth->id)->update([
                    'status' => 0,
                ]);
                $response['validity'] = 1;
                $response['message'] = 'Account has been removed';

            } else {
                $response['validity'] = 0;
                $response['message'] = 'Mismatch password';
            }
        }

        return $response;
    }

    public function cart_list(Request $request)
    {
        $token = $request->bearerToken();
        $cart_items = array();

        if (isset($token) && $token != '') {
            $auth = auth('sanctum')->user();
            $my_courses_ids = CartItem::where('user_id', $auth->id)->get();

            foreach ($my_courses_ids as $my_courses_id) {
                $course_details = Course::find($my_courses_id['course_id']);
                array_push($cart_items, $course_details);
            }

            $cart_items = course_data($cart_items);
        }

        return $cart_items;
    }

    // Toggle from cart list
    public function toggle_cart_items(Request $request)
    {
        $token = $request->bearerToken();

        if (isset($token) && $token != '') {
            $user_id = auth('sanctum')->user()->id;

            $status = "";
            $course_id = $request->course_id;
            $cart_items = array();
            $check_status = CartItem::where('course_id', $course_id)->where('user_id', $user_id)->first();
            if (empty($check_status)) {
                $cart_item = new CartItem();
                $cart_item->course_id = $request->course_id;
                $cart_item->user_id = $user_id;
                $cart_item->save();
                $status = "added";
            } else {
                CartItem::where('user_id', $user_id)->where('course_id', $request->course_id)->delete();
                $status = "removed";
            }
            // $this->my_wishlist($user_id);
            $response['status'] = $status;
            return $response;

        }
    }

    public function save_course_progress(Request $request)
    {
        $token = $request->bearerToken();

        if (isset($token) && $token != '') {
            $user_id = auth('sanctum')->user()->id;

            $lessons = get_lessons('lesson', $request->lesson_id);

            update_watch_history_manually($request->lesson_id, $lessons[0]->course_id, $user_id);

            return course_completion_data($lessons[0]->course_id, $user_id);
        }
    }

    public function live_class_schedules(Request $request)
    {
        $response = array();

        $classes = array();

        $live_classes = Live_class::where('course_id', $request->course_id)->orderBy('class_date_and_time', 'desc')->get();

        foreach ($live_classes as $key => $live_class) {
            $additional_info = json_decode($live_class->additional_info, true);

            $classes[$key]['class_topic'] = $live_class->class_topic;
            $classes[$key]['provider'] = $live_class->provider;
            $classes[$key]['note'] = $live_class->note;
            $classes[$key]['class_date_and_time'] = $live_class->class_date_and_time;
            $classes[$key]['meeting_id'] = $additional_info['id'];
            $classes[$key]['meeting_password'] = $additional_info['password'];
            $classes[$key]['start_url'] = $additional_info['start_url'];
            $classes[$key]['join_url'] = $additional_info['join_url'];
        }

        $response['live_classes'] = $classes;

        $response['zoom_sdk'] = get_settings('zoom_web_sdk');
        $response['zoom_sdk_client_id'] = get_settings('zoom_sdk_client_id');
        $response['zoom_sdk_client_secret'] = get_settings('zoom_sdk_client_secret');

        return $response;
    }

    public function payment(Request $request)
    {
        $response = array();
        $token = $request->bearerToken();

        if (isset($token) && $token != '') {
            $user = auth('sanctum')->user();
            Auth::login($user);
        }

        if ($request->app_url) {
            session(['app_url' => $request->app_url . '://']);
        }

        return redirect(route('payment'));
        // return $response;
    }
    public function free_course_enroll(Request $request, $course_id)
    {
        $response = array();
        $token = $request->bearerToken();

        if (isset($token) && $token != '') {
          
            $check = Enrollment::where('course_id', $course_id)->where('user_id' , $user = auth('sanctum')->user()->id)->count();
            $isPaid = Course::where('id', $course_id)->pluck('is_paid')->first();
            if ($isPaid == 1) {
          return response()->json(['message' => 'this course is not free']);
            }
            if ($check == 0 ) {
                $enrollment['user_id'] = auth('sanctum')->user()->id;
                $enrollment['course_id'] = $course_id;
                $enrollment['enrollment_type'] = 'free';
                $enrollment['entry_date'] = time();
                $enrollment['expiry_date'] = null;
                $done = Enrollment::insert($enrollment);
                if ($done) {
                    $response['status'] = true;
                    $response['message'] = "Course Successfully enrolled";
                    return $response;
                } else {
                    $response['status'] = false;
                    $response['message'] = "Some error occur,Try again";
                     return $response;
                }
            }
         return response()->json(['message' => 'this course is already enrolled']);
        }

        return response()->json(['message' => 'Invalid Token']);

    }
    public function cart_tools(Request $request)
    {
        $response = array();
        $token = $request->bearerToken();

        if (isset($token) && $token != '') {
            $response['course_selling_tax'] = get_settings('course_selling_tax');
            $response['currency_position'] = get_settings('currency_position');
            $response['currency_symbol'] = DB::table('currencies')->where('code', get_settings('system_currency'))->value('symbol');
        } else {
            $response['status'] = "Not Authorized Credential";
        }
        return $response;
    }

    public function check_version() {
        $data = array(
          'current_version' => "0.0.1",
          'update_available' => false,
          'update_url' => "",
        );
                
      
        return $data;
    }


    // public function verify(EmailVerificationRequest $request): JsonResponse
    // {
    //     if ($request->user()->hasVerifiedEmail()) {
    //         return response()->json(['message' => 'Email already verified.'], 200);
    //     }

    //     if ($request->user()->markEmailAsVerified()) {
    //         event(new Verified($request->user()));
    //     }

    //     return response()->json(['message' => 'Email verified successfully.']);
    // }

    public function send(Request $request): JsonResponse
    {
        if ($request->user()->hasVerifiedEmail()) {
            return response()->json(['message' => 'Email already verified.'], 200);
        }

        $request->user()->sendEmailVerificationNotification();

        return response()->json(['message' => 'Verification email sent.']);
    }





    public function getWallet(Request $request)
    {
        $token = $request->bearerToken();
        $perPage = $request->get('per_page', 10);

        if (isset($token) && $token != '') {
                $user_id = auth('sanctum')->user()->id;
        }
        $user = Auth::user();
        $wallet = Wallet::firstOrCreate(['user_id' => $user_id], ['balance' => 0.00, 'currency' => 'SYP']);
        $transactions = $wallet->transactions()->orderBy('created_at', 'desc')->paginate($perPage);
        return [
            'wallet'=> $wallet,
            'transactions'=> $transactions,
        ];
    }

    public function sendResetLinkEmail(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email'],
        ]);

        $status = Password::sendResetLink(
            $request->only('email')
        );

        if ($status === Password::RESET_LINK_SENT) {
            return response()->json(['message' => __($status)], 200);
        }

        throw ValidationException::withMessages([
            'email' => [__($status)],
        ]);
    }



    public function getQuiz($quiz_id)
    {
        $quiz = Lesson::find($quiz_id);
        if (!$quiz || $quiz->lesson_type !== 'quiz') {
            return response()->json(['message' => 'Quiz not found.'], 404);
        }

        $quiz->attachment = json_decode($quiz->attachment, true);
        return response()->json(['quiz' => $quiz], 200);
    }

    public function getSectionQuizzes($section_id)
    {
        $quizzes = Lesson::where('section_id', $section_id)
            ->where('lesson_type', 'quiz')
            ->get()
            ->map(function ($quiz) {
                $quiz->attachment = json_decode($quiz->attachment, true);
                return $quiz;
            });

        return response()->json(['quizzes' => $quizzes], 200);
    }

    public function getQuizQuestions($quiz_id)
    {
        $questions = Question::where('quiz_id', $quiz_id)->get();

        foreach ($questions as $question) {
            $question->options = json_decode($question->options, true);
            $question->correct_answers = json_decode($question->answer, true);
        }

        return response()->json(['questions' => $questions], 200);
    }

    public function getQuizQuestion($question_id)
    {
        $question = Question::find($question_id);
        if (!$question) {
            return response()->json(['message' => 'Question not found.'], 404);
        }

        $question->options = json_decode($question->options, true);
        $question->correct_answers = json_decode($question->answer, true);

        return response()->json(['question' => $question], 200);
    }

    public function submitQuiz(Request $request)
    {
        $user_id = auth('sanctum')->user()->id;
        $retake = Lesson::where('id', $request->quiz_id)->value('retake');
        $submit = QuizSubmission::where('quiz_id', $request->quiz_id)->where('user_id',  $user_id )->count();
        if ($submit > $retake) {
            return response()->json(['message' => "You have exceeded your retake attempts"], 200);
            
        }

        $inputs  = collect($request->all());
        $quiz_id = $inputs->pull('quiz_id');
        $inputs->forget(['_token', 'quiz_id']);

        $submits = $inputs->whereNotNull();
        foreach ($submits as $key => $submit) {
            if (is_string($submit) && ($submit != 'true' && $submit != 'false')) {
                $submits[$key] = array_column(json_decode($submit), 'value');
            }
        }

        $question_ids      = $submits->keys();
        $submitted_answers = $submits->values();
        $questions         = Question::whereIn('id', $question_ids)->get();

        $right_answers = $wrong_answers = [];
        foreach ($questions as $key => $question) {

            $correct_answer = json_decode($question->answer, true);
            $submitted      = $submitted_answers[$key];

            if ($question->type == 'mcq') {
                $isCorrect = empty(array_diff($correct_answer, $submitted));
            } elseif ($question->type == 'fill_blanks') {
                $isCorrect = count($correct_answer) === count($submitted);

                if ($isCorrect) {
                    for ($i = 0; $i < count($correct_answer); $i++) {
                        if (strtolower($correct_answer[$i]) != strtolower($submitted[$i])) {
                            $isCorrect = false;
                            break;
                        }
                    }
                } else {
                    $isCorrect = false;
                }
            } elseif ($question->type == 'true_false') {
                $isCorrect = strtolower(json_encode($correct_answer)) == strtolower($submitted);
            }

            $isCorrect ? $right_answers[] = $question->id : $wrong_answers[] = $question->id;
        }

        $data['quiz_id']        = $quiz_id;
        $data['user_id']        =   $user_id;
        $data['correct_answer'] = $right_answers ? json_encode($right_answers) : null;
        $data['wrong_answer']   = $wrong_answers ? json_encode($wrong_answers) : null;
        $data['submits']        = $submits->count() > 0 ? json_encode($submits->toArray()) : null;

     QuizSubmission::insert($data);
     $submission = QuizSubmission::where('user_id',$user_id)->where('quiz_id',$quiz_id)->get();
        return response()->json(['data' => $submission], 200);

    }

    public function load_result(Request $request)
    {
        $user_id = auth('sanctum')->user()->id;
        $quiz = Lesson::where('id', $request->quiz_id)->first();
        $questions = Question::where('quiz_id', $request->quiz_id)->get()->keyBy('id');
        $results = QuizSubmission::where('quiz_id', $request->quiz_id)
            ->where('user_id', $user_id)
            ->orderBy('created_at', 'desc')
            ->get();
    
        $total_questions = $questions->count();
        $total_mark = $quiz->total_mark;
        $pass_mark = $quiz->pass_mark;
    
        // Enhance each result
        $results = $results->map(function ($result) use ($total_questions, $total_mark, $pass_mark) {
            $correct_ids = $result->correct_answer ? json_decode($result->correct_answer, true) : [];
            $wrong_ids = $result->wrong_answer ? json_decode($result->wrong_answer, true) : [];
    
            $correct_count = count($correct_ids);
            $wrong_count = count($wrong_ids);
    
            // Calculate per-question mark and obtained marks
            $mark_per_question = $total_questions > 0 ? $total_mark / $total_questions : 0;
            $obtained_marks = $correct_count * $mark_per_question;
            $passed = $obtained_marks >= $pass_mark;
    
            return [
                'id' => $result->id,
                'correct_answer' => $correct_ids,
                'wrong_answer' => $wrong_ids,
                'correct_count' => $correct_count,
                'wrong_count' => $wrong_count,
                'obtained_marks' => round($obtained_marks, 2),
                'result' => $passed ? 'Passed' : 'Failed',
                'created_at' => $result->created_at,
            ];
        });
    
        return response()->json([
            'quiz' => $quiz,
            'questions' => $questions->values(),
            'results' => $results,
        ], 200);
    }
    

    
        
    public function storeReview(Request $request)
    {
        $user_id = auth('sanctum')->user()->id;
        $data = [
            'course_id'   => $request->course_id,
            'user_id'     => $user_id,
            'review'      => $request->review,
            'review_type' => 'course',
            'rating'      => $request->rating,
        ];

        Review::create($data);

        // Update course rating
        $query = Review::where('course_id', $request->course_id)->where('review_type', 'course');
        $total_rating = $query->sum('rating');
        $avg_rating = $query->count() ? $total_rating / $query->count() : 0;
        Course::where('id', $request->course_id)->update(['average_rating' => round($avg_rating)]);

        return response()->json(['message' => 'Your review has been saved.'], 201);
    }

    public function deleteReview($id)
    {
        $user_id = auth('sanctum')->user()->id;

        $review = Review::where('id', $id)->where('user_id', $user_id)->first();

        if (!$review) {
            return response()->json(['message' => 'Data not found.'], 404);
        }

        $review->delete();
        return response()->json(['message' => 'Your review has been deleted.']);
    }

    public function updateReview(Request $request, $id)
    {
        $user_id = auth('sanctum')->user()->id;
        if (!is_numeric($id) || $id < 1) {
            return response()->json(['message' => 'Invalid ID.'], 400);
        }

        $review = Review::where('id', $id)->where('user_id',    $user_id )->first();

        if (!$review) {
            return response()->json(['message' => 'Data not found.'], 404);
        }

        $review->update([
            'course_id'   => $request->course_id,
            'review'      => $request->review,
            'review_type' => 'course',
            'rating'      => $request->rating,
        ]);

        return response()->json(['message' => 'Your review has been updated.']);
    }

    public function likeReview($id)
    {
        $user_id = auth('sanctum')->user()->id;
        if (!is_numeric($id) || $id < 1) {
            return response()->json(['message' => 'Invalid ID.'], 400);
        }

        $status = LikeDislikeReview::where('user_id',  $user_id )
            ->where('review_id', $id)->first();

        if ($status) {
            if ($status->liked) {
                $status->delete();
            } else {
                $status->update(['liked' => 1, 'disliked' => 0]);
            }
        } else {
            LikeDislikeReview::create([
                'user_id'   =>   $user_id ,
                'review_id' => $id,
                'liked'     => 1,
                'disliked'  => 0
            ]);
        }

        return response()->json(['message' => 'Your like has been saved.']);
    }

    public function dislikeReview($id)
    {
            $user_id = auth('sanctum')->user()->id;
        if (!is_numeric($id) || $id < 1) {
            return response()->json(['message' => 'Invalid ID.'], 400);
        }

        $status = LikeDislikeReview::where('user_id',  $user_id )
            ->where('review_id', $id)->first();

        if ($status) {
            if ($status->disliked) {
                $status->delete();
            } else {
                $status->update(['disliked' => 1, 'liked' => 0]);
            }
        } else {
            
            LikeDislikeReview::create([
                'user_id'   =>  $user_id ,
                'review_id' => $id,
                'disliked'  => 1,
                'liked'=> 0,
             
            ]);
        }

        return response()->json(['message' => 'Your dislike has been saved.']);
    }
    
    public function getCourseReviews(Request $request)
    {
        $course_id = $request->course_id;
        $perPage = $request->get('per_page', 10);
    
        $paginator = Review::where('review_type', 'course')
            ->when($course_id, fn($query) => $query->where('course_id', $course_id))
            ->with(['user:id,name,photo'])
            ->paginate($perPage);
    
        $reviews = $paginator->getCollection()->map(function ($review) {
            $likeCount = LikeDislikeReview::where('review_id', $review->id)->where('liked', 1)->count();
            $dislikeCount = LikeDislikeReview::where('review_id', $review->id)->where('disliked', 1)->count();
    
            return [
                'id' => $review->id,
                'course_id' => $review->course_id,
                'user' => [
                    'id' => $review->user->id,
                    'name' => $review->user->name,
                    'photo' => get_photo('user_image',$review->user->photo),
                ],
                'review' => $review->review,
                'rating' => $review->rating,
                'likes' => $likeCount,
                'dislikes' => $dislikeCount,
                'created_at' => $review->created_at,
            ];
        });
    
        return response()->json([
            'current_page' => $paginator->currentPage(),
            'data'=>$reviews->values()
        ]);
    }
    

    public function indexForum(Request $request)
    {
        $perPage = $request->get('per_page', 10); // default items per page
    
        $questions = Forum::join('users', 'forums.user_id', '=', 'users.id')
            ->select('forums.*', 'users.name as user_name', 'users.photo as user_photo')
            ->where('forums.parent_id', 0)
            ->where('forums.course_id', $request->course_id)
            ->latest('forums.id')
            ->paginate($perPage);
    
        // Modify user_photo to be full URL
        $items = $questions->items();
        foreach ($items as $item) {
            $item->user_photo = get_photo('user_image', $item->user_photo);
        }
    
        return response()->json([
            'current_page' => $questions->currentPage(),
            'data' => $items,
            'pagination' => [
                'total' => $questions->total(),
                'per_page' => $questions->perPage(),
                'last_page' => $questions->lastPage(),
                'next_page_url' => $questions->nextPageUrl(),
                'prev_page_url' => $questions->previousPageUrl(),
            ],
        ]);
    }
    
    
    public function storeForum(Request $request)
    {
        $rules = [
            'title' => 'required',
            'description' => 'required',
        ];

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $msg = 'Question added successfully.';
        $data['description'] = $request->description;

        if ($request->title === 'reply') {
            $msg = 'Reply added successfully.';
            $data['description'] = strip_tags($request->description);
        }

        $data['user_id']   = auth('sanctum')->user()->id;
        $data['course_id'] = $request->course_id;
        $data['parent_id'] = $request->parent_id ?? 0;
        $data['title']     = $request->title;

        Forum::create($data);

        return response()->json(['message' => $msg], 201);
    }

    public function updateForum(Request $request, $id)
    {
        $rules = [
            'title' => 'required',
            'description' => 'required',
        ];

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $forum = Forum::findOrFail($id);
        $forum->title = $request->title;
        $forum->description = $request->title === 'reply'
            ? strip_tags($request->description)
            : $request->description;

        $forum->save();

        return response()->json(['message' => 'Updated successfully.']);
    }

    public function deleteForum($id)
    {
        $forum = Forum::where('id', $id)->where('user_id', auth('sanctum')->user()->id)->first();

        if (!$forum) {
            return response()->json(['message' => 'Data not found.'], 404);
        }

        $forum->delete();
        return response()->json(['message' => 'Question deleted successfully.']);
    }

    public function likeForum($id)
    {
        $forum = Forum::findOrFail($id);
        $userId = auth('sanctum')->user()->id;
        $likes = json_decode($forum->likes, true) ?? [];
        $dislikes = json_decode($forum->dislikes, true) ?? [];

        if (in_array($userId, $likes)) {
            $likes = array_values(array_diff($likes, [$userId]));
        } else {
            $likes[] = $userId;
            $dislikes = array_values(array_diff($dislikes, [$userId]));
        }

        $forum->likes = $likes ? json_encode($likes) : null;
        $forum->dislikes = $dislikes ? json_encode($dislikes) : null;
        $forum->save();

        return response()->json(['message' => 'Likes updated.']);
    }

    public function dislikeForum($id)
    {
        $forum = Forum::findOrFail($id);
        $userId = auth('sanctum')->user()->id;
        $likes = json_decode($forum->likes, true) ?? [];
        $dislikes = json_decode($forum->dislikes, true) ?? [];

        if (in_array($userId, $dislikes)) {
            $dislikes = array_values(array_diff($dislikes, [$userId]));
        } else {
            $dislikes[] = $userId;
            $likes = array_values(array_diff($likes, [$userId]));
        }

        $forum->dislikes = $dislikes ? json_encode($dislikes) : null;
        $forum->likes = $likes ? json_encode($likes) : null;
        $forum->save();

        return response()->json(['message' => 'Dislikes updated.']);
    }

    public function payWithWallet(Request $request)
    {
        $identifier = $request->input('identifier', 'wallet');
        $taxPercentage = get_settings('course_selling_tax');
        $user = auth('sanctum')->user();
        $userId =  $user->id;
        
        // Retrieve the coupon if exists
        $coupon = Coupon::where('code', $request->coupon)
            ->where('status', 1)
            ->where('discount', '>', 0)
            ->where('expiry', '>', time())
            ->first();
    
        $couponDiscount = $coupon ? $coupon->discount : 0; // Default to 0 if no valid coupon
        $totalDiscountedPrice = 0;
        $items = [];
        $cartIds = [];
        
        // Loop through each item in the request
        foreach ($request->items as $item) {
            $course = Course::find($item['id']);
            if (!$course) {
                continue; 
            }
           
           $enroll = check_enroll($item['id'] , $request['custom_field']['gifted_user_id']?: $user->id );
           if ($enroll ==  'valid') {
              continue; 
              }
            $price = $course->price;
            $discountPrice = $course->discounted_price ??  $course->price;
            $discountPriceForItemArray = $course->discounted_price;
    
           
            $items[] = [
                'id' => $course->id,
                'price' => $price,
                'title' => $course->title,
                'subtitle' => $course->subtitle,
                'discount_price' => $discountPriceForItemArray
            ];
    
            
            $cartIds[] = $course->id;
            $totalDiscountedPrice += $discountPrice;
        }
    
        $couponDiscountAmount = ($couponDiscount / 100) * $totalDiscountedPrice;
        $totalDiscountedPriceAfterCoupon = $totalDiscountedPrice - $couponDiscountAmount;
    
        
        $tax = ($taxPercentage / 100) * $totalDiscountedPriceAfterCoupon;
        $payableAmount = $totalDiscountedPriceAfterCoupon + $tax;
    
        
        $paymentDetails = [
            'items' => $items,
            'custom_field' => [
                'item_type' => "course",
                'pay_for' => "course payment",
                'user_id' => $userId,
                'user_photo' => $user->photo,
                'gifted_user_id' => $request->custom_field['gifted_user_id'] ?? '',
                'cart_id' => $cartIds,
                'coupon_discount' => number_format($couponDiscountAmount, 2) 
            ],
            'tax' => $tax, 
            'coupon' => $coupon ? $coupon->code : null,     
            'payable_amount' => $payableAmount,
        ];
    
         $paymentDetails;
        


        $status = \App\Models\payment_gateway\Wallet::payment_status($identifier, [],$paymentDetails);

        if ($status) {
            return $this->purchaseCourse($paymentDetails, $identifier);
        } 
        return response()->json([
            'message' => 'Course enrolled faild.',
           
        ]);
    }

    public function purchaseCourse( $payment_details, $identifier)
{
    $user = auth('sanctum')->user();
  

    $payment = [];
    $payment['invoice'] = Str::random(20);

    foreach ($payment_details['items'] as $item) {
        $price = $item['price'];
        $course_discount = $item['discount_price'];

        if (get_course_creator_id($item['id'])->role === 'admin') {
            $payment['admin_revenue'] = $payment_details['payable_amount'];
        } else {
            $instructor_revenue = $payment_details['payable_amount'] * (get_settings('instructor_revenue') / 100);
            $payment['instructor_revenue'] = $instructor_revenue;
            $payment['admin_revenue'] = $payment_details['payable_amount'] - $instructor_revenue;
        }

        $payment['course_id']    = $item['id'];
        $payment['tax']          = $payment_details['tax'];
        $payment['amount']       = $course_discount ? $course_discount : $price;
        $payment['user_id']      = $user->id;
        $payment['payment_type'] = $identifier;
        $payment['coupon']       = $payment_details['coupon'] ?? null;

        // insert payment history
        $payment_history = DB::table('payment_histories')->insert($payment);

        if ($payment_history) {
            $enroll = [
                'course_id'       => $item['id'],
                'user_id'         => $payment_details['custom_field']['gifted_user_id'] ?: $user->id,
                'enrollment_type' => 'paid',
                'entry_date'      => time()
            ];
            DB::table('enrollments')->insert($enroll);
        }
    }

    foreach ($payment_details['custom_field']['cart_id'] as $item_id) {
        DB::table('cart_items')
            ->where('user_id', $user->id)
            ->where('course_id', $item_id)
            ->delete();
    }
 
    return response()->json([
        'status' => true,
        'message' => 'Course enrolled successfully.',
        'paymentDetails' => $payment_details,
    ]);
}


   public function getUserCertificates($course_id)
    {
         $userId = auth('sanctum')->user()->id;
        $user = User::findOrFail($userId);
        $certificates = Certificate::with(['course', 'user'])
            ->where('user_id', $userId)
            ->where('course_id', $course_id)
            ->get();

        return response()->json([
            'status' => 'success',
            'count' => $certificates->count(),
            'certificates' => $certificates->map(function ($certificate) {
                return [
                    'id' => $certificate->id,
                    'identifier' => $certificate->identifier,
                    'course_id' => $certificate->course_id,
                    'course_title' => $certificate->course->title,
                    'created_at' => $certificate->created_at,
                    'download_url' => route('certificate', $certificate->identifier),
                ];
            })
        ]);
    }
                    // 'download_url' => route('api.certificates.download', $certificate->identifier),

    public function getCertificate($identifier)
    {
        $certificate = Certificate::with(['course', 'user'])
            ->where('identifier', $identifier)
            ->firstOrFail();

        $qrCodeContent = route('certificate', ['identifier' => $identifier]);
        $qrCode = QrCode::size(300)->generate($qrCodeContent);

        $course = $certificate->course;
        $user = $certificate->user;

        $certificateData = [
            'id' => $certificate->id,
            'identifier' => $certificate->identifier,
            'created_at' => $certificate->created_at,
            'student_name' => $user->name,
            'course_title' => $course->title,
            'course_duration' => $course->total_duration(),
            'number_of_lessons' => $course->lessons->count(),
            'course_completion_date' => date_formatter($certificate->created_at),
            'course_level' => ucfirst($course->level),
            'course_language' => ucfirst($course->language),
            'instructors' => $course->instructors()->pluck('name'),
            'qr_code' => $qrCode,
            'download_url' => route('certificate', $certificate->identifier),
        ];

        return response()->json([
            'status' => 'success',
            'certificate' => $certificateData
        ]);
    }

 
    public function downloadCertificate($identifier)
    {
        $certificate = Certificate::with(['course', 'user'])
            ->where('identifier', $identifier)
            ->firstOrFail();

        $qrCodeContent = route('certificate', ['identifier' => $identifier]);
        $qrCode = QrCode::size(300)->generate($qrCodeContent);

        $course = $certificate->course;
        $user = $certificate->user;

     return   $certificateContent = $this->generateCertificateContent($certificate, $qrCode);

        $imagePath = $this->generateCertificateImage($certificateContent);

        return response()->download($imagePath, "certificate_{$identifier}.png")
            ->deleteFileAfterSend(true);
    }

    public function checkCourseCompletion( $courseId)
    {
        $userId = auth('sanctum')->user()->id;
        $user = User::findOrFail($userId);
        $course = Course::findOrFail($courseId);

        $totalLessons = $course->lessons()->count();
        $watchHistory = Watch_history::where('course_id', $courseId)
            ->where('student_id', $userId)
            ->first();

        $completedLessons = $watchHistory ? count(json_decode($watchHistory->completed_lesson, true) ?? []) : 0;
        $progress = $totalLessons > 0 ? round(($completedLessons / $totalLessons) * 100) : 0;
        $isCompleted = $progress >= 100;
        $hasCertificate = Certificate::where('user_id', $userId)
            ->where('course_id', $courseId)
            ->exists();

        return response()->json([
            'status' => 'success',
            'progress' => $progress,
            'is_completed' => $isCompleted,
            'has_certificate' => $hasCertificate,
            'certificate_eligible' => $isCompleted && $hasCertificate,
            'certificate' => $hasCertificate ? [
                'identifier' => Certificate::where('user_id', $userId)
                    ->where('course_id', $courseId)
                    ->value('identifier'),
                'download_url' => route('certificate', 
                    Certificate::where('user_id', $userId)
                        ->where('course_id', $courseId)
                        ->value('identifier'))
            ] : null
        ]);
    }

    
    private function generateCertificateContent($certificate, $qrCode)
    {
        $course = $certificate->course;
        $user = $certificate->user;

        $courseDuration = $course->total_duration();
        $studentName = $user->name;
        $courseTitle = $course->title;
        $numberOfLesson = $course->lessons->count();
        $courseCompletionDate = date_formatter($certificate->created_at);
        $certificateDownloadDate = date('d M Y');
        $courseLevel = ucfirst($course->level);
        $courseLanguage = ucfirst($course->language);
         
        $instructorName = '';
        foreach ($course->instructors() as $instructor) {
            $instructorName .= '<p>' . $instructor->name . '</p>';
        }

        $certificateBuilderContent = get_settings('certificate_builder_content');
        $certificateBuilderContent = str_replace('{course_duration}', $courseDuration, $certificateBuilderContent);
        $certificateBuilderContent = str_replace('{instructor_name}', $instructorName, $certificateBuilderContent);
        $certificateBuilderContent = str_replace('{student_name}', $studentName, $certificateBuilderContent);
        $certificateBuilderContent = str_replace('{course_title}', $courseTitle, $certificateBuilderContent);
        $certificateBuilderContent = str_replace('{number_of_lesson}', $numberOfLesson, $certificateBuilderContent);
        $certificateBuilderContent = str_replace('{qr_code}', $qrCode, $certificateBuilderContent);
        $certificateBuilderContent = str_replace('{course_completion_date}', $courseCompletionDate, $certificateBuilderContent);
        $certificateBuilderContent = str_replace('{certificate_download_date}', $certificateDownloadDate, $certificateBuilderContent);
        $certificateBuilderContent = str_replace('{course_level}', $courseLevel, $certificateBuilderContent);
        $certificateBuilderContent = str_replace('{course_language}', $courseLanguage, $certificateBuilderContent);

        $newSrc = get_image(get_settings('certificate_template'));
        $certificateBuilderContent = preg_replace(
            '/(<img[^>]*class=["\']certificate-template["\'][^>]*src=["\'])([^"\']*)(["\'])/i', 
            '${1}' . $newSrc . '${3}', 
            $certificateBuilderContent
        );

        return $certificateBuilderContent;
    }


    private function generateCertificateImage($htmlContent)
    {
        $tempPath = tempnam(sys_get_temp_dir(), 'cert_');
        file_put_contents($tempPath, $htmlContent);
        
        
        return $tempPath;
    }

        public function getUserByEmail(Request $request)
    {
        $email = $request->email;
        $user = User::where('email', $email)->first();

        if ($user) {
            return response()->json([
                'status' => true,
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'photo' => get_photo('user_image', $user->photo),
                ]
            ]);
        } else {
            return response()->json([
                'status' => false,
                'message' => 'User not found.'
            ]);
        }
    }

}