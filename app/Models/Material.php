<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Material extends Model
{
    protected $fillable = [
        'name', 'price', 'pices', 'company','doctor_id',
        // 'nurse_id', 
    ];
//تعريف العلاقة بين المواد والطبيب 
public function doctor()
{
    return $this->belongsTo(Doctor::class);
}


public function nurse() {

    return $this->belongsTo(Nurse::class);
}

    use HasFactory;
}
