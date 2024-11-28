<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Appointment extends Model
{
    protected $fillable = [
        'name', 'age', 'photo', 'time','date','doctor_id', 'user_id','state'
    ];
    //تعريف العلاقة بين المواعيد والطبيب 
public function doctor()
{
    return $this->belongsTo(Doctor::class);
}
public function user()
{
    return $this->belongsTo(Doctor::class);
}
public function nurse() {

    return $this->belongsTo(Nurse::class);
}
   // علاقة الموعد مع المستخدم (كل موعد مرتبط بمستخدم واحد)

    use HasFactory;
    
}
