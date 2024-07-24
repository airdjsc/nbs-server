<?php

namespace App\Http\Controllers;

use App\Models\Appointments;
use App\Models\Reviews;
use App\Models\Doctor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Psy\Readline\Hoa\Console;
use SebastianBergmann\Environment\Console as EnvironmentConsole;

class DocsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //get doctor's appointment, patients and display on dashboard
        $doctor = Auth::user(); //trả về 1 object có thông tin lấy từ 1 record trong bảng user (record của user mà đang login)
        $appointments = Appointments::where('doc_id', $doctor->id)->where('status', 'upcoming')->get();
        $reviews = Reviews::where('doc_id', $doctor->id)->where('status', 'active')->get();

        //return all data to dashboard
        return view('dashboard')->with(['doctor'=>$doctor, 'appointments'=>$appointments, 'reviews'=>$reviews]);
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
        $user = Auth::user();
        //this controller is to store booking details post from mobile app
        $reviews = new Reviews();
        //this is to update the appointment status from 'upcoming' to 'complete'
        $appointment = Appointments::where('id', $request->get('appointment_id'))->first();

        //save the ratings and reviews from user...
        $reviews->user_id = $user->id;
        $reviews->doc_id = $request->get('doctor_id');
        $reviews->ratings = $request->get('ratings');
        $reviews->reviews = $request->get('reviews');
        $reviews->reviewed_by = $user->name;
        $reviews->status = 'active';
        $reviews->save();

        //...then change appointment status 
        $appointment->status = 'complete';
        $appointment->save();

        //lấy thông tin next appointment còn lại mà đã book trước cái appointment vừa complete xong
        $newNextAppointment = Doctor::join('users', 'doctors.doc_id','=','users.id')
            ->leftJoin('appointments', 'doctors.doc_id', '=', 'appointments.doc_id')
            ->where('appointments.user_id', '=', $user->id)
            ->where('appointments.status', '=', 'upcoming')
            ->oldest('booking_utc')
            ->select(
                'users.name',
                'users.profile_photo_path',
                'doctors.doc_id',
                'doctors.category',
                'appointments.status',
                'appointments.booking_utc')
            ->selectRaw('appointments.id AS booking_id')
            ->first();

        return response()->json([   //data
            'success' => 'The appointment has been completed and reviewed successfully!',
            'new_next_appointment' => $newNextAppointment ?? (object)[],
        ], 200);    //status code
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
