<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\Message;
use App\Models\User;
use Auth;
use Illuminate\Http\Request;

class UserController extends Controller
{
    //user information

    public function show(Request $request)
    {
        $id=$request->id;

        $user = auth('user')->user();


        if (!$user) {
            return response()->json(['error' => 'user not found'], 404);
        }

        return response()->json($user, 200);
    }

    //update information of user
    public function updateprofile(Request $request)
    {

        $id = $request->id;
        $user = User::find($id);

        if (!$user) {
            return response()->json(['error' => 'user not found'], 404);
        }

        // التحقق من المدخلات
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'current_password' => 'required_with:password|string',
            'password' => 'sometimes|string|min:6',
            'profile_picture' => 'sometimes|image|mimes:jpeg,png,jpg,gif|max:2048', // قبول الصور فقط
        ]);

        // تحديث بيانات لمريض
        if ($request->has('name')) {
            $user->name = $validated['name'];

        }

        if ($request->has('password')) {
            if (!isset($validated['current_password']) || !\Hash::check($validated['current_password'], $user->password)) {
                return response()->json(['error' => 'The current password is incorrect'], 400);
            }
    
            // إذا كانت كلمة السر الحالية صحيحة، قم بتحديث كلمة السر
            $user->password = bcrypt($validated['password']);
        }
    

        // تحديث صورة البروفايل إذا تم رفعها
        if ($request->hasFile('image_profile')) {
            // حذف الصورة القديمة إذا كانت موجودة
            if ($user->profile_picture && \Storage::exists($user->profile_pictures)) {
                \Storage::delete($user->profile_picture);
            }

            // رفع الصورة الجديدة وتخزين الرابط
            $path = $request->file('profile_picture')->store('profile_pictures', 'public');
            $user->profile_picture = $path;
        }

        // حفظ التحديثات
        $user->save();

        return response()->json([
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                // 'password' => $user->password,
                'profile_picture' => $user->image_profile
                    ? url('storage/' . $user->profile_picture)
                    : null,
            ],
        ]);
    }

    //show appointment


    // public function showAppointment()
    // {
    //     // جلب المستخدم المصادق عليه
    //     $user = Auth::user();

    //     $doctorId = $user['doctor_id'];

    //     // التحقق من أن المستخدم المصادق هو طبيب
    //     if (!$user || !$user instanceof \App\Models\User) {
    //         return response()->json(['message' => 'User not found or unauthorized'], 404);
    //     }

    //     // جلب جميع المواعيد المرتبطة بالمريض
    //     //$appointment = $nurse->appointments; // استنادًا إلى العلاقة بين الطبيب والمواعيد
    //     $appointment = Appointment::where('doctor_id', $doctorId)->get();
    //     // إرجاع الاستجابة
    //     return response()->json([
    //         'message' => 'All Appointment by user',
    //         'appointment' => $appointment,
    //     ], 200);

    // }

    public function showAppointment()
    {
        // جلب المستخدم المصادق عليه
        $user = Auth::user();
    
        // التحقق من أن المستخدم المصادق موجود وله علاقة بطبيب
        if (!$user || !$user->doctor_id) {
            return response()->json(['message' => 'User not found or unauthorized'], 404);
        }
    
        // جلب ID الطبيب المرتبط بالمستخدم المصادق عليه
        $doctorId = $user->doctor_id;
    
        // جلب جميع المواعيد المرتبطة بالطبيب المصادق عليه فقط
        $appointments = Appointment::where('doctor_id', $doctorId)
                                    ->where('user_id', $user->id) // لضمان إرجاع المواعيد الخاصة بهذا المستخدم فقط
                                    ->get();
    
        // إرجاع الاستجابة
        return response()->json([
            'message' => 'All Appointments for this user linked to the doctor',
            'appointments' => $appointments,
        ], 200);
    }
    








    //ADD Appointment by  user
    function addAppointment(Request $request)
    {

        // التحقق من صحة البيانات
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'age' => 'required|string|max:255',
            'time' => 'required|string|max:255', // تحقق من العدد
            'date' => 'required|string|max:255',
            'doctor_id' => 'required|integer',
        ]);

        // جلب معرّف المريض المصادق عليه
        $user_id = Auth::id(); // أو auth('doctor')->id();

        // التحقق من المصادقة
        if (!$user_id) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        // إنشاء الموعد وربطها بالمريض
        $appointment = Appointment::create([
            'name' => $validated['name'],
            'age' => $validated['age'],
            'time' => $validated['time'],
            'date' => $validated['date'],
            'doctor_id' => $validated['doctor_id'],
            'user_id' => $user_id,
            'state' => '1', // إضافة الحالة

        ]);

        return response()->json([
            'message' => 'Appointment created successfully',
            'appointment' => [
                'name' => $validated['name'],
                'age' => $validated['age'],
                'time' => $validated['time'],
                'date' => $validated['date'],
                'doctor_id' => $validated['doctor_id'],
                'user_id' => $user_id,
                'state' => '1', // إضافة الحالة هنا
                'updated_at' => $appointment->updated_at,
                'created_at' => $appointment->created_at,
                'id' => $appointment->id,
            ],
        ]);
        
    }
    //destroy apointment 
public function destroyAppointment(Request $request)
    {
        $id = $request->id;

        $appointment = Appointment::find($id);

        if (!$appointment) {
            return response()->json(['error' => 'appointment not found'], 404);
        }

        $appointment->delete();

        return response()->json(['message' => 'appointment deleted successfully'], 200);
    }
    










    function showMessage()
    {
          // جلب المستخدم المصادق عليه
          $user = Auth::user();

          $doctorId = $user['doctor_id'];
  
          // التحقق من أن المستخدم المصادق هو طبيب
          if (!$user || !$user instanceof \App\Models\User) {
              return response()->json(['message' => 'User not found or unauthorized'], 404);
          }
  
          // جلب جميع المواعيد المرتبطة بالمريض
          //$appointment = $nurse->appointments; // استنادًا إلى العلاقة بين الطبيب والمواعيد
          $messages = Message::where('doctor_id', $doctorId)->get();
          // إرجاع الاستجابة
          return response()->json([
              'message' => 'All Message by User',
              'messages' => $messages,
          ], 200);
          
    }
    function addMessage(Request $request)
    {
        // التحقق من صحة البيانات
    $validated = $request->validate([
        'body' => 'required|string|max:255',
        'time' => 'required|string|max:255',
        'date' => 'required|string|max:255',
        'title' => 'required|string|max:255',
        'photo' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        'user_id' => 'required|integer|exists:users,id', // التحقق من وجود المستخدم
        'doctor_id' => 'required|integer|exists:doctors,id', // التحقق من وجود الطبيب
    ]);

    // جلب بيانات المستخدم
    $user = User::find($validated['user_id']);
    if (!$user) {
        return response()->json(['message' => 'User not found'], 404);
    }
    $name_sender= $user->name;

    // جلب بيانات الطبيب
    $doctor = Doctor::find($validated['doctor_id']);
    if (!$doctor) {
        return response()->json(['message' => 'Doctor not found'], 404);
    }
    $name_consignee= $doctor->name;

    // جلب معرّف المريض المصادق عليه
    $userId = Auth::id();
    if (!$userId) {
        return response()->json(['message' => 'Unauthorized'], 401);
    }

    // معالجة الصورة إذا تم رفعها
    $photoPath = null;
    if ($request->hasFile('photo')) {
        $photoPath = $request->file('photo')->store('images/messages', 'public'); // يتم تخزين الصورة في `public/uploads/messages`
    }

    // إنشاء الرسالة وربطها بالطبيب
    $message = Message::create([
        'body' => $validated['body'],
        'time' => $validated['time'],
        'date' => $validated['date'],
        'title' => $validated['title'],
        'the_sender' =>"User {$name_sender}",
        'consignee' => "Doctor {$name_consignee}",
        'photo' => $photoPath ?? 'no', // إذا لم يتم رفع صورة، يتم تخزين 'no'
        'user_id' =>$userId  ,
        'doctor_id' => $validated['doctor_id'],
    ]);

    return response()->json([
        'message' => 'Message created successfully',
        'message_data' => $message,
    ], 201);
}

    function showInfoDoctor(Request $request)
    {
        $doctor_id=$request->doctor_id;
        $date=Doctor::find($doctor_id);
        return response()->json([
            'message' => 'Data get successfully',
            'message_data' => $date,
        ], 201);
    }
}
