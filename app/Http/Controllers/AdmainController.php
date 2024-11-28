<?php

namespace App\Http\Controllers;

use App\Models\Doctor;
use Hash;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class AdmainController extends Controller
{
    public function signupDoctor(Request $request)
    {
        // التحقق من البيانات
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:15|regex:/^[0-9]+$/|unique:doctors',
            'gender' => 'required',
            'age' => 'required|integer|min:18|max:100',
            'address' => 'required|string|max:500',
            'profile_picture' => 'nullable|image|mimes:jpg,jpeg,png|max:2048', // الصورة اختيارية
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 400);
        }

        // كلمة المرور يتم إنشاؤها من قبل المسؤول بشكل عشوائي
        $passwordran = bin2hex(random_bytes(4)); // مثال لكلمة مرور عشوائية (8 حروف)
        // رفع الصورة إن وجدت
        $profilePicturePath = null;
        if ($request->hasFile('profile_picture')) {
            // رفع الصورة وتخزينها في مجلد 'profile_pictures' في القرص 'public'
            $profilePicturePath = $request->file('profile_picture')->store('images/doctors', 'public');
        }

        // إنشاء الحساب
        $doctor = Doctor::create([
            'name' => $request->name,
            'phone' => $request->phone,
            'gender' => $request->gender,
            'age' => $request->age,
            'address' => $request->address,
            // 'password'=> $passwordran,
            'password' => Hash::make($passwordran), // كلمة مرور مشفرة
            'profile_picture' => $profilePicturePath,
        ]);
        

        if (!$doctor) {
            return response()->json(['error' => 'Doctor could not be created'], 500);
        }

        // تسجيل الكلمة السرية في ملف Log (اختياري)
        Log::info("Doctor {$doctor->name} created with password: $passwordran");

        // إرجاع استجابة مع كلمة المرور
        return response()->json([
            'message' => 'Doctor account created successfully.',
            'doctor' => $doctor,
            'password' => $passwordran // يتم إرسال الكلمة السرية مع الاستجابة
        ], 201);
    }
    function show()
    {
        $doctor=Doctor::all();
           // إعادة استجابة نجاح
           return response()->json([
            'message' => 'User registered successfully.',
            'doctors' => $doctor
        ], 200);
    }
    function destroy(Request $request)
    {
        $id=$request->id;
        $doctor=Doctor::find($id);
       
      
        
            if (!$doctor) {
                return response()->json(['error' => 'doctor not found'], 404);
            }
        
            $doctor->delete();
        
            return response()->json(['message' => 'doctor deleted successfully'], 200);
        }
    
}

