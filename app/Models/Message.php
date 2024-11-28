<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    protected $fillable = [
        'the_sender', 'consignee', 'body', 'photo','time','date','title','doctor_id','user_id','nurse_id'
        // 'nurse_id', 
    ];
    




    //العلاقة بين الطبيب والملاحظات 

    public function doctor()
{
    return $this->belongsTo(Doctor::class);
}
    use HasFactory;
}
