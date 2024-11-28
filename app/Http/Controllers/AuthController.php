<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthController extends Controller
{
    /**
     * تسجيل مستخدم جديد
     */
    public function signup(Request $request)
    {
           // التحقق من البيانات
           $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:15|regex:/^[0-9]+$/|unique:users',
            'gender' => 'required',
            'age' => 'required|integer|min:18|max:100',
            'address' => 'required|string|max:500',
            'profile_picture' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'doctor_id'=>'required|integer',
            'password' => 'required|string|min:8',
 // الصورة اختيارية
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }
          // رفع الصورة إن وجدت
          $profilePicturePath = null;
          if ($request->hasFile('profile_picture')) {
              // رفع الصورة وتخزينها في مجلد 'profile_pictures' في القرص 'public'
              $profilePicturePath = $request->file('profile_picture')->store('images/users', 'public');
          }

        $user = User::create([
            'name' => $request->name,
           'phone'=> $request->phone,
            'gender'=> $request->gender,
            'age'=> $request->age,
           'address' => $request->address,
           'profile_picture' =>$profilePicturePath ,
           'doctor_id'=>$request->doctor_id,
            'password' => Hash::make($request->password),
        ]);

        $token = JWTAuth::fromUser($user);

        return response()->json(['user' => $user, 'token' => $token], 201);
    }

   //login user
   public function login(Request $request)
   {
    $validator = Validator::make($request->all(), [
        'phone' => 'required|string',
        'password' => 'required|string',
    ]);

    if ($validator->fails()) {
        return response()->json($validator->errors(), 400);
    }
    
       // التحقق من البيانات
       $credentials = $request->only('phone', 'password');

       // إضافة سجلات للتأكد من صحة البيانات
       \Log::info('Login attempt with phone: ' . $credentials['phone']);

       $user = User::where('phone', $credentials['phone'])->first();

       if (!$user) {
           return response()->json(['error' => 'Phone number not found'], 404);
       }
       if (!Hash::check($credentials['password'], $user->password)) {
           return response()->json(['error' => 'Incorrect password'], 401);
       }

       //إنشاء توكن JWT
       $token = JWTAuth::fromUser($user);


       return response()->json([
           'message' => 'Login successful',
           'user' => $user,
           'token' => $token
       ], 200);
   }

    /**
     * تسجيل الخروج
     */
 public function logout(): JsonResponse
{
    try {
        // التحقق من وجود التوكن
        $token = JWTAuth::getToken();
        if (!$token) {
            return response()->json(['error' => 'Token not provided'], 400);
        }

        // إبطال التوكن
        JWTAuth::invalidate($token);

        return response()->json(['success' => true, 'message' => 'Logged out successfully']);
    } catch (\Tymon\JWTAuth\Exceptions\TokenInvalidException $e) {
        // إذا كان التوكن غير صالح
        return response()->json(['error' => 'Token is invalid'], 401);
    } catch (\Exception $e) {
        // أخطاء أخرى
        return response()->json(['error' => 'Failed to logout, please try again.'], 500);
    }
}




    
}
