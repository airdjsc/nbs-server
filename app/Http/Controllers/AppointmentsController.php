<?php

namespace App\Http\Controllers;

use App\Models\Appointments;
use App\Models\User;
use App\Models\Doctor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class AppointmentsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //retrieve all appointments from user
        $appointment = Appointments::where('user_id', Auth::user()->id)->get();
        $doctor = User::where('type', 'doctor')->get();
        
        //sorting appointment and doctor details, and get all related appointments
        //tương tự như UserController
        foreach ($appointment as $data) {
            foreach($doctor as $info) {
                $details = $info->doctor;   //get record từ record có 'type'='doctor' trong bảng users tới record tương ứng trong bảng doctors 
                                            //để lấy gía trị của 'category' trong bảng doctors
                                            //(có 'doc_id' ở bảng doctor = 'id' ở bảng users)
                if($data['doc_id'] == $info['id']) {
                    $data['doctor_name'] = $info['name'];
                    $data['doctor_profile'] = $info['profile_photo_url'];
                    $data['category'] = $details['category'];
                }
            }
        }

        return $appointment;
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
        $appointment = new Appointments();
        $appointment->user_id = $user->id;
        $appointment->doc_id = $request->get('doctor_id');
        $appointment->booking_utc = Carbon::parse($request->get('date_time'));  //->format('Y/m/d H:i:s');
        $appointment->status = 'upcoming'; //new appointment will be saved as 'upcoming' by default
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
        
        //if successfully, return status code 200
        return response()->json([   //data
            'success' => 'New Appointment has been made successfully!',
            'new_next_appointment' => $newNextAppointment ?? (object)[],
        ], 200);    //status code
    }

    /**
     * Display the specified resource.
     */
    public function show(string $doc_id)
    {
        $doctorUpcomingBookingUtc = Appointments::where('doc_id', $doc_id)
            ->where('status', '=', 'upcoming')
            ->oldest('booking_utc')
            ->pluck('booking_utc')
            ->toArray();
        return $doctorUpcomingBookingUtc;
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
