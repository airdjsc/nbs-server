<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Appointments extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'doc_id',
        'booking_utc',
        'status',
    ];

    //chỉ có liên kết tới bảng users (qua user_id) do đây là danh sách appointment 
    //mà 1 user cụ thể chỉ định tới từng bác sĩ khác nhau
    //(nhiều hàng có user_id giống nhau nhưng doc_id khác nhau)
    public function user() {
        return $this->belongsTo(User::class);
    }
}
