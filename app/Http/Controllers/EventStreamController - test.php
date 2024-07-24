<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\Appointments;

class EventStreamController extends Controller
{
    /**
     * The stream source.
     *
     * @return \Illuminate\Http\Response
     */
    public function stream(){
        $response = new StreamedResponse(function() use ($request) {
            while(true) {
                
                    //lấy thông tin booked datetime của doctor cụ thể (căn cứ vào id) theo thời gian thực để cho vào stream
                    $doctorAppointments = Appointments::select('booking_utc')/*->where('doc_id', $id)*/->where('status', 'upcoming')->pluck('booking_utc');
                ob_flush();
                flush();
                usleep(200000);
            }
        });
        $response->headers->set('Content-Type', 'text/event-stream');
        $response->headers->set('X-Accel-Buffering', 'no');
        $response->headers->set('Cach-Control', 'no-cache');
        return $response;    
    }
}
