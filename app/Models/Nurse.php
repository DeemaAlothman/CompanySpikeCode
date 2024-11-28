<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Tymon\JWTAuth\Contracts\JWTSubject;

class Nurse extends Authenticatable implements JWTSubject
{
    
    protected $fillable = [
        'name', 'password', 'phone', 'gender', 'age', 'address', 'profile_picture', 'doctor_id','the_jop','time','salary','date'
    ];
    // protected $hidden = [
    //     'password',
    // ];
    public function doctor()
{
    return $this->belongsTo(Doctor::class);
}
    public function getJWTIdentifier()
    {
        return $this->getKey(); // عادةً يعيد الـ id
    }

    public function getJWTCustomClaims()
    {
        return []; // إذا أردت إضافة بيانات مخصصة
    }
    
    public function materials() {
        return $this->hasMany(Material::class);
    }

    
    public function appointments() {
        return $this->hasMany(Appointment::class);
    }

    use HasFactory;
}
