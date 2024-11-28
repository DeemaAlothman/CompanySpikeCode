<?php

namespace App\Http\Controllers;
use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\Material;
use App\Models\Message;
use App\Models\Nurse;
use App\Models\User;
use Auth;
use Hash;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;
class NurseController extends Controller
{
    //login in your account
    public function login(Request $request)
    {
      
        // التحقق من البيانات
        $credentials = $request->only('phone', 'password');

        // إضافة سجلات للتأكد من صحة البيانات
        // \Log::info('Login attempt with phone: ' . $credentials['phone']);

        $nurse = Nurse::where('phone', $credentials['phone'])->first();

        if (!$nurse) {
            return response()->json(['error' => 'Phone number not found'], 404);
        }
        if (!Hash::check($credentials['password'], $nurse->password)) {
            return response()->json(['error' => 'Incorrect password'], 401);
        }

        //إنشاء توكن JWT
        $token = JWTAuth::fromUser($nurse);


        return response()->json([
            'message' => 'Login successful',
            'nurse' => $nurse,
            'token' => $token
        ], 200);
    }
    //logout nurse and destroy the token 
    public function logout(Request $request)
    {
        try {
            // إبطال صلاحية الـ Token
            JWTAuth::invalidate(JWTAuth::getToken());

            return response()->json([
                'message' => 'Successfully logged out',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Something went wrong',
            ], 500);
        }
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



    //show all detials about you
    public function show()
    {
        $id = Auth::user()->getAuthIdentifier();
        // $doctor = auth('doctor')->user();
        $nurse = Nurse::find($id);

        if (!$nurse) {
            return response()->json(['error' => 'Nurse not found'], 404);
        }

        return response()->json($nurse, 200);
    }

    //update your profile
    public function updateprofile(Request $request)
    {
     $id=$request->id;
        $nurse = Nurse::find($id);
        // $doctor = auth('doctor')->user();
        if (!$nurse) {
            return response()->json(['error' => 'Nurse not found'], 404);
        }

        // التحقق من المدخلات
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'current_password' => 'required_with:password|string', 
            'password' => 'sometimes|string|min:6',
            'profile_picture' => 'sometimes|image|mimes:jpeg,png,jpg,gif|max:2048', // قبول الصور فقط
        ]);

        // تحديث بيانات الطبيب
        if ($request->has('name')) {
            $nurse->name = $validated['name'];
        }

        // إذا كانت كلمة السر الجديدة مرسلة، تحقق من كلمة السر الحالية
    if ($request->has('password')) {
        if (!isset($validated['current_password']) || !\Hash::check($validated['current_password'], $nurse->password)) {
            return response()->json(['error' => 'The current password is incorrect'], 400);
        }
         // إذا كانت كلمة السر الحالية صحيحة، قم بتحديث كلمة السر
         $nurse->password = bcrypt($validated['password']);
    }

        // تحديث صورة البروفايل إذا تم رفعها
        if ($request->hasFile('image_profile')) {
            // حذف الصورة القديمة إذا كانت موجودة
            if ($nurse->profile_picture && \Storage::exists($nurse->profile_pictures)) {
                \Storage::delete($nurse->profile_picture);
            }

            // رفع الصورة الجديدة وتخزين الرابط
            $path = $request->file('profile_picture')->store('images/nurses', 'public');
            $nurse->profile_picture = $path;
        }

        // حفظ التحديثات
        $nurse->save();

        return response()->json([
            'nurse' => [
                'id' => $nurse->id,
                'name' => $nurse->name,
                // 'password' => $doctor->password,
                'profile_picture' => $nurse->profile_picture
                    // ? url('storage/' . $nurse->profile_picture)
                    // : null,
            ],
        ]);
    }

    //show material by nurse
    function showMaterial()
    {
        // جلب المستخدم المصادق عليه
        $nurse = Auth::user();
        $doctorId = $nurse['doctor_id'];

        // التحقق من أن المستخدم المصادق هو ممرضة
        if (!$nurse || !$nurse instanceof \App\Models\Nurse) {
            return response()->json(['message' => 'NURSE not found or unauthorized'], 404);
        }

        // // جلب جميع المواد المرتبطة بالطبيب
        // $materials = $doctor->materials; // استنادًا إلى العلاقة بين الطبيب والمواد
        $materials = Material::where('doctor_id', $doctorId)->get();
        // إرجاع الاستجابة
        return response()->json([
            'message' => 'All Materials by Nurse',
            'materials' => $materials,
        ], 200);
    }

    //update material by nurse
    function storeMaterial(Request $request)
    {


        // التحقق من صحة البيانات
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'price' => 'required|string|max:255',
            'pices' => 'required|integer|min:1', // تحقق من العدد
            'company' => 'required|string|max:255',
        ]);
        $nurse = Auth::user();
        $doctorId = $nurse['doctor_id'];


        // // التحقق من المصادقة
        // if (!$doctorId) {
        //     return response()->json(['message' => 'Unauthorized'], 401);
        // }

        // إنشاء المادة وربطها بالطبيب
        $material = Material::create([
            'name' => $validated['name'],
            'price' => $validated['price'],
            'pices' => $validated['pices'],
            'company' => $validated['company'],
            'doctor_id' => $doctorId,
        ]);

        return response()->json([
            'message' => 'Material created successfully',
            'material' => $material,
        ], 201);
    }
    //update materials by nurse
    function updatematerial(Request $request)
    { {
        $id=$request->id;
            $product = Material::find($id);

            if (!$product) {
                return response()->json(['message' => 'Product not found'], 404);
            }
            $nurse = Auth::user();
            $doctorId = $nurse['doctor_id'];
            // التحقق من المدخلات
            $validated = $request->validate([
                'name' => 'sometimes|string|max:255',
                'price' => 'sometimes|string|max:255',
                'pices' => 'sometimes|string|max:255',
                'company' => 'sometimes|string|max:255',
            ]);

            // تحديث الحقول المرسلة فقط
            $product->update([
                'name' => $validated['name'] ?? $product->name,
                'price' => $validated['price'] ?? $product->price,
                'pices' => $validated['pices'] ?? $product->pices,
                'company' => $validated['company'] ?? $product->company,
                'doctor_id' => $doctorId,  // حفظ معرف الطبيب الذي قام بالتعديل
            ]);

            return response()->json([
                'message' => 'Product updated successfully',
                'product' => $product,
            ], 200);
        }

    }
    //destroy  material by nurse
    public function destroymaterial(Request $request)
    {
        $id=$request->id;
        $material = Material::find($id);

        if (!$material) {
            return response()->json(['error' => 'material not found'], 404);
        }

        $material->delete();

        return response()->json(['message' => 'material deleted successfully'], 200);
    }


    public function showAppointment()
    {
        // جلب المستخدم المصادق عليه
        $nurse = Auth::user();
        
        $doctorId = $nurse['doctor_id'];
    
        // التحقق من أن المستخدم المصادق هو طبيب
        if (!$nurse || !$nurse instanceof \App\Models\Nurse) {
            return response()->json(['message' => 'Nurse not found or unauthorized'], 404);
        }
    
        // جلب جميع المواعيد المرتبطة بالطبيب
        //$appointment = $nurse->appointments; // استنادًا إلى العلاقة بين الطبيب والمواعيد
        $appointment = Appointment::where('doctor_id', $doctorId)->get();
        // إرجاع الاستجابة
        return response()->json([
            'message' => 'All Appointment by Doctor',
            'appointment' => $appointment,
        ], 200);
    }


//create appointment 
function createAppointment(Request $request)
{
   
        // التحقق من صحة البيانات
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'age' => 'required|string|max:255',
            'time' => 'required|string|max:255', // تحقق من العدد
            'date' => 'required|string|max:255',
            'user_id' => 'required|integer',
        ]);
        // جلب معرّف الطبيب المصادق عليه
        $nurse = Auth::user();
        $doctorId = $nurse['doctor_id']; // أو auth('doctor')->id();
    
        // التحقق من المصادقة
        if (!$doctorId) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }
    
        // إنشاء المادة وربطها بالطبيب
        $appointment = Appointment::create([
            'name' => $validated['name'],
            'age' => $validated['age'],
            'time' => $validated['time'],
            'date' => $validated['date'],
            'user_id' => $validated['user_id'],
            'doctor_id' => $doctorId,
        ]);
    
        return response()->json([
            'message' => 'Appointment created successfully',
            'appointment' => $appointment,
    ], 201);
}
//accept appointment
function acceptAppointment(Request $request)
{

    $id= $request->id;

    $appointment = Appointment::find($id);

    if (!$appointment) {
        return response()->json(['message' => 'Appointment not found'], 404);
    }
    $validated = $request->validate([
        'state'=>'string|max:255',
       ]);
       $appointment->state ='0';
       $appointment->save();  // حفظ معرف الطبيب الذي قام بالتعديل
      return response()->json([
          'message' => 'Appointment updated successfully',
          'appointment' => $appointment,
      ], 200);
  
}

//update appointment by doctor 

function updateAppointment( Request $request)
{
     $id= $request->id;

       $appointment = Appointment::find($id);
   
       if (!$appointment) {
           return response()->json(['message' => 'Appointment not found'], 404);
       }
   
       // التحقق من المدخلات
       $validated = $request->validate([
        'the_jop' => 'nullable|string|max:255',
        'photo' => 'nullable|string|max:255',
        'payments' => 'nullable|string|max:255',
        'state'=>'string|max:255',
        
       ]);
   
     
        $appointment->the_jop = $validated['the_jop'] ?? $appointment->the_jop;
        $appointment->payments = $validated['payments'] ?? $appointment->payments;
        
        if ($request->hasFile('photo')) {
            $profilePicturePath = $request->file('photo')->store('images/appointment', 'public');
            $appointment->photo = $profilePicturePath;
        }
        
        $appointment->photo= $profilePicturePath;
        $appointment->state ='2';

        $appointment->save();  // حفظ معرف الطبيب الذي قام بالتعديل
       
   
       return response()->json([
           'message' => 'Appointment updated successfully',
           'appointment' => $appointment,
       ], 200);
   
   
}

//destroy appointment 
public function destroyAppointment(Request $request)
{
    $id=$request->id;

    $appointment = Appointment::find($id);

    if (!$appointment) {
        return response()->json(['error' => 'appointment not found'], 404);
    }

    $appointment->delete();

    return response()->json(['message' => 'appointment deleted successfully'], 200);
}

//show message by nurse 

function  showMessages()
{
    // جلب المستخدم المصادق عليه
    $nurse = Auth::user();
   
    // التحقق من أن المستخدم المصادق هو ممرضة
    if (!$nurse || !$nurse instanceof \App\Models\Nurse) {
        return response()->json(['message' => 'Nurse not found or unauthorized'], 404);
    }
    $doctorId = $nurse['doctor_id'];
    $message = Message::where('doctor_id', $doctorId)->get();
    // إرجاع الاستجابة
    return response()->json([
        'message' => 'All Appointment by Doctor',
        'messages' => $message,
    ], 200);
}


//create message by nurse
function createMessage(Request $request)
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
    $name_consignee = $user->name;

    // جلب بيانات الطبيب
    $doctor = Doctor::find($validated['doctor_id']);
    if (!$doctor) {
        return response()->json(['message' => 'Doctor not found'], 404);
    }
    $name_sender = $doctor->name;

    // جلب معرّف الطبيب المصادق عليه
    $doctorId = Auth::id();
    if (!$doctorId) {
        return response()->json(['message' => 'Unauthorized'], 401);
    }

    // معالجة الصورة إذا تم رفعها
    $photoPath = null;
    if ($request->hasFile('photo')) {
        $photoPath = $request->file('photo')->store('images/messages', 'public'); // يتم تخزين الصورة في `public/uploads/messages`
    }
    $nurse = Auth::user();
    $doctorId = $nurse['doctor_id']; // أو auth('doctor')->id();

    // إنشاء الرسالة وربطها بالطبيب
    $message = Message::create([
        'body' => $validated['body'],
        'time' => $validated['time'],
        'date' => $validated['date'],
        'title' => $validated['title'],
        'the_sender' =>  "Doctor {$name_sender}",
 ,
        'consignee' => "User {$name_consignee}",
        'photo' => $photoPath ?? 'no', // إذا لم يتم رفع صورة، يتم تخزين 'no'
        'user_id' => $validated['user_id'],
        'doctor_id' => $doctorId,
    ]);

    return response()->json([
        'message' => 'Message created successfully',
        'message_data' => $message,
    ], 201);
}
//destroy message by nurse
public function destroyMessage(Request $request)
{
    $id=$request->id;

    $message = Message::find($id);

    if (!$message) {
        return response()->json(['error' => 'message not found'], 404);
    }

    $message->delete();

    return response()->json(['message' => 'message deleted successfully'], 200);
}


}






