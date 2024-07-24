<?php

namespace App\Http\Controllers;

use App\Models\Appointments;
use App\Models\Doctor;
use App\Models\Reviews;
use App\Models\User;
use App\Models\UserDetails;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Carbon\Carbon;

use function PHPSTORM_META\map;

class UsersController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $start = microtime(true);

        $user = Auth::user(); //trả về 1 object từ bảng user (record của user mà đang login)

        $topDoctors = Doctor::join('users', 'doctors.doc_id','=','users.id')
            ->leftJoin('reviews', 'doctors.doc_id', '=', 'reviews.doc_id')
            ->select(
                'users.name', 
                'doctors.doc_id',
                'doctors.category')
            ->selectRaw('CAST(AVG(reviews.ratings) AS DECIMAL(3,2)) AS average_rating')
            ->groupBy('doctors.doc_id', 'users.name', 'doctors.category')
            ->orderBy('average_rating', 'desc')
            ->take(5)
            ->get();

        $nextAppointment = Doctor::join('users', 'doctors.doc_id','=','users.id')
            ->leftJoin('appointments', 'doctors.doc_id', '=', 'appointments.doc_id')
            ->where('appointments.user_id', '=', $user->id)
            ->where('appointments.status', '=', 'upcoming')
            ->oldest('booking_utc')
            ->select(
                'doctors.doc_id',
                'doctors.category',
                'appointments.status',
                'appointments.booking_utc')
            ->selectRaw('appointments.id AS booking_id')
            ->selectRaw('users.name AS doctor_name')
            ->selectRaw('users.profile_photo_path AS doctor_profile')
            ->first();

        $favDoctors = Doctor::join('users', 'doctors.doc_id', '=', 'users.id')
            ->whereIn('doc_id', json_decode($user->user_details->fav) ?? [])
            ->select(
                'users.name', 
                'doctors.doc_id',
                'doctors.category')
            ->get();

        $user['top_doctors'] = $topDoctors ?? [];
        $user['next_appointment'] = $nextAppointment ?? (object)[];
        $user['fav_doctors'] = $favDoctors;
        $user['now_utc'] = Carbon::now('utc')->format('Y-m-d H:i:s'); //Lấy giờ UTC theo cài đặt múi giờ của máy chủ chứa Laravel

        $time = microtime(true) - $start;
        $user['query_performance'] = $time;

        //KẾT QUẢ:
        return $user; //return all data
    }

    /**
     * Login
     */
    public function login(Request $request)
    {
        //validate incoming inputs
        $request->validate([
            'email'=>'required|email',
            'password'=>'required',
        ]);

        //check matching user
        $user = User::where('email', $request->email)->first();

        //check password
        if(!$user || ! Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email'=>['The provided credentials are incorrect'],
            ]);
        }

        //then return generated token
        return $user->createToken($request->email)->plainTextToken;
    }

    /**
     * Register
     */
    public function register(Request $request)
    {
        //validate incoming inputs
        $request->validate([
            'name'=>'required|string',
            'email'=>'required|email',
            'password'=>'required',
        ]);

        $user = User::create([
            'name'=>$request->name,
            'email'=>$request->email,
            'type'=>'user',
            'password'=>Hash::make($request->password),
        ]);

        $userInfo = UserDetails::create([
            'user_id'=>$user->id,
            'status'=>'active',
        ]);

        return $user;

    }

    /**
     * Update favorit doctor list.
     */
    public function storeFavDoc(Request $request)
    {
        $saveFav = UserDetails::where('user_id', Auth::user()->id)->first();

        $docList = json_encode($request->get('favList'));

        //update fav list into database 
        $saveFav->fav = $docList;   //and remember update this as well
        $saveFav->save();

        $favDoctors = Doctor::join('users', 'doctors.doc_id', '=', 'users.id')
            ->whereIn('doc_id', json_decode($saveFav->fav))
            ->select(
                'users.name', 
                'doctors.doc_id',
                'doctors.category')
            ->get();

        return response()->json([
            'success'=>'The Favorite List is updated!',
            'new_fav_doc' => $favDoctors ?? [],
        ], 200);
    }

    /**
     * Logout
     */
    public function logout()
    {
        $user = Auth::user();
        $user->currentAccessToken()->delete();

        return response()->json([
            'success'=>'Logout successfully!'
        ], 200);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
