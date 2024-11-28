<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Tymon\JWTAuth\Contracts\JWTSubject;


class Doctor  extends Authenticatable implements JWTSubject
{
    protected $fillable = [
        'name', 'password', 'phone', 'gender', 'age', 'address', 'profile_picture',
    ];
    // protected $hidden = [
    //     'password',
    // ];
    public function getJWTIdentifier()
    {
        return $this->getKey(); // عادةً يعيد الـ id
    }

    public function getJWTCustomClaims()
    {
        return []; // إذا أردت إضافة بيانات مخصصة
    }



    //تعريف العلاقة بين المواد والطبيب 
    public function materials()
{
    return $this->hasMany(Material::class);
}
//تعريف العلاقة بين الطبيب والمواعيد
public function appointments()
{
    return $this->hasMany(Appointment::class);
}


//تعريف العلاقة بين الطبيب والمريض 
    public function users()
    {
        return $this->hasMany(User::class);
    }

    //تعريف العلاقة بين الطبيب والممرضة 
    public function nurses()
    {
        return $this->hasMany(Nurse::class);
    }
//تعريف العلاقة بين الطبيب والملاحظات
    public function messages()
    {
        return $this->hasMany(Message::class);
    }




    use HasFactory;
}
