<?php

namespace App\Http\Controllers;
use App\Models\Appointment;
use App\Models\Material;
use App\Models\Message;
use App\Models\Nurse;
use App\Models\User;
use DB;
use Tymon\JWTAuth\Facades\JWTAuth;
use App\Models\Doctor;
use Auth;
use Hash;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use App\Http\Requests\SignupNurseRequest;
class DoctorController extends Controller
{
    public function login(Request $request)
    {
        // dd($request->phone);
        // التحقق من البيانات
        $credentials = $request->only('phone', 'password');

        // إضافة سجلات للتأكد من صحة البيانات
        \Log::info('Login attempt with phone: ' . $credentials['phone']);

        $doctor = Doctor::where('phone', $credentials['phone'])->first();

        if (!$doctor) {
            return response()->json(['error' => 'Phone number not found'], 404);
        }
        if (!Hash::check($credentials['password'], $doctor->password)) {
            return response()->json(['error' => 'Incorrect password'], 401);
        }

     
        
// تعيين صلاحية مخصصة للتوكن (3 أشهر)
    $customClaims = ['exp' => now()->addMonths(3)->timestamp];
    $token = JWTAuth::claims($customClaims)->fromUser($doctor);


        return response()->json([
            'message' => 'Login successful',
            'doctor' => $doctor,
            'token' => $token
        ], 200);
    }

    //logout in account doctor

    function show(Request $request)
    {

        $id=$request->id;

        $id = $request->id;
        $doctor = Doctor::find($id);
    
        if (!$doctor) {
            return response()->json(['error' => 'Doctor not found'], 404);
        }
       return response()->json([
        'message' => 'All information of Doctor.',
        'doctor' => $doctor,
       ])    ;
    }
   
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

    // public function updateprofile(Request $request)
    // {

    //     $id = $request->id;
    //     $doctor = Doctor::find($id);
    //     // $doctor = auth('doctor')->user();
    //     if (!$doctor) {
    //         return response()->json(['error' => 'Doctor not found'], 404);
    //     }

    //     // التحقق من المدخلات
    //     $validated = $request->validate([
    //         'name' => 'sometimes|string|max:255',
    //         'password' => 'sometimes|string|min:6',
    //         'profile_picture' => 'sometimes|image|mimes:jpeg,png,jpg,gif|max:2048', // قبول الصور فقط
    //     ]);

    //     // تحديث بيانات الطبيب
    //     if ($request->has('name')) {
    //         $doctor->name = $validated['name'];
    //     }

    //     if ($request->has('password')) {
    //         $doctor->password = bcrypt($validated['password']);
    //     }

    //     // تحديث صورة البروفايل إذا تم رفعها
    //     if ($request->hasFile('profile_picture')) {
    //         // حذف الصورة القديمة إذا كانت موجودة
    //         if ($doctor->profile_picture && \Storage::exists($doctor->profile_picture)) {
    //             \Storage::delete($doctor->profile_picture);
    //         }

    //         // رفع الصورة الجديدة وتخزين الرابط
    //         $path = $request->file('profile_picture')->store('images/doctors', 'public');
    //         $doctor->profile_picture = $path;
    //     }

    //     // حفظ التحديثات
    //     $doctor->save();

    //     return response()->json([
    //         'doctor' => [
    //             'id' => $doctor->id,
    //             'name' => $doctor->name,
    //             // 'password' => $doctor->password,
    //             'profile_picture' => $doctor->profile_picture,
    //                 // ? url('storage/' . $doctor->profile_picture)
    //                 // : null,
    //         ],
    //     ]);
    // }
    // //show information of doctor
    // public function show()
    // {
    //     $doctor = auth('doctor')->user();
    //     // $doctor = Doctor::find($id);

    //     if (!$doctor) {
    //         return response()->json(['error' => 'Doctor not found'], 404);
    //     }

    //     return response()->json($doctor, 200);
    // }
    public function updateprofile(Request $request)
{
    $id = $request->id;
    $doctor = Doctor::find($id);

    if (!$doctor) {
        return response()->json(['error' => 'Doctor not found'], 404);
    }

    // التحقق من المدخلات
    $validated = $request->validate([
        'name' => 'sometimes|string|max:255',
        'current_password' => 'required_with:password|string', // التحقق من كلمة السر الحالية إذا كانت كلمة السر الجديدة موجودة
        'password' => 'sometimes|string|min:6',
        'profile_picture' => 'sometimes|image|mimes:jpeg,png,jpg,gif|max:2048', // قبول الصور فقط
    ]);

    // إذا كانت كلمة السر الجديدة مرسلة، تحقق من كلمة السر الحالية
    if ($request->has('password')) {
        if (!isset($validated['current_password']) || !\Hash::check($validated['current_password'], $doctor->password)) {
            return response()->json(['error' => 'The current password is incorrect'], 400);
        }

        // إذا كانت كلمة السر الحالية صحيحة، قم بتحديث كلمة السر
        $doctor->password = bcrypt($validated['password']);
    }

    // تحديث البيانات الأخرى
    if ($request->has('name')) {
        $doctor->name = $validated['name'];
    }

    // تحديث صورة البروفايل إذا تم رفعها
    if ($request->hasFile('profile_picture')) {
        // حذف الصورة القديمة إذا كانت موجودة
        if ($doctor->profile_picture && \Storage::exists($doctor->profile_picture)) {
            \Storage::delete($doctor->profile_picture);
        }

        // رفع الصورة الجديدة وتخزين الرابط
        $path = $request->file('profile_picture')->store('images/doctors', 'public');
        $doctor->profile_picture = $path;
    }

    // حفظ التحديثات
    $doctor->save();

    return response()->json([
        'doctor' => [
            'id' => $doctor->id,
            'name' => $doctor->name,
            'profile_picture' => $doctor->profile_picture,
        ],
    ]);
}

    function updateinfodoctor(Request $request)
    {
        $id = $request->id;
        // الحصول على الطبيب المسجل حاليًا
        // $doctor = auth('doctor')->user();
        $doctor = Doctor::find($id);
        // التحقق من البيانات المُرسلة
        $validated = $request->validate([
            'facebookacount' => 'nullable|string|max:255',
            'instaaccount' => 'nullable|string|max:255',
            'years_experiense' => 'nullable|string|max:255',
            'about_doctor' => 'nullable|string|max:1000',
            'doctors_time' => 'nullable|string|max:255',
        ]);

        // تحديث الحقول المرسلة فقط
        $doctor->facebookacount = $validated['facebookacount'] ?? $doctor->facebookacount;
        $doctor->instaaccount = $validated['instaaccount'] ?? $doctor->instaaccount;
        $doctor->years_experiense = $validated['years_experiense'] ?? $doctor->years_experiense;
        $doctor->about_doctor = $validated['about_doctor'] ?? $doctor->about_doctor;
        $doctor->doctors_time = $validated['doctors_time'] ?? $doctor->doctors_time;

        $doctor->save();

        // إعادة استجابة نجاح مع البيانات المحدثة
        return response()->json([
            'message' => 'Profile updated successfully',
            'doctor' => $doctor,
        ], 200);
    }



    function signupNurse(Request $request)
    {

        // التحقق من البيانات
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:15|regex:/^[0-9]+$/|unique:nurses',
            'gender' => 'required',
            'age' => 'required|integer|min:18|max:100',
            'address' => 'string|max:500',
            'the_jop' => 'required|string|max:500',
            'time' => 'required|string|max:500',
            'salary' => 'required|string|max:500',
            'date' => 'required|string|max:500',

            'profile_picture' => 'nullable|image|mimes:jpg,jpeg,png|max:2048', // الصورة اختيارية
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 400);
        }
        if (!$request->has('the_jop')) {
            return response()->json(['error' => 'The job field is required.'], 400);
        }
        // كلمة المرور يتم إنشاؤها من قبل المسؤول بشكل عشوائي
        $passwordran = bin2hex(random_bytes(4)); // مثال لكلمة مرور عشوائية (8 حروف)
        // رفع الصورة إن وجدت
        $profilePicturePath = null;
        if ($request->hasFile('profile_picture')) {
            // رفع الصورة وتخزينها في مجلد 'profile_pictures' في القرص 'public'
            $profilePicturePath = $request->file('profile_picture')->store('images/nurses', 'public');
        }


        // إنشاء الحساب
        $nurse = Nurse::create([
            'name' => $request->name,
            'phone' => $request->phone,
            'gender' => $request->gender,
            'age' => $request->age,
            'address' => $request->address,
            'the_jop' => $request->the_jop,
            'time' => $request->time,
            'salary' => $request->salary,
            'date' => $request->date,
            'password' => Hash::make($passwordran),
            // كلمة مرور مشفرة
            'profile_picture' => $profilePicturePath,
            'doctor_id' => Auth::user()->getAuthIdentifier()
        ]);

        if (!$nurse) {
            return response()->json(['error' => 'Nurse could not be created'], 500);
        }

        // تسجيل الكلمة السرية في ملف Log (اختياري)
        Log::info("Nurse {$nurse->name} created with password: $passwordran");

        // إرجاع استجابة مع كلمة المرور
        return response()->json([
            'message' => 'Nurse account created successfully.',
            'nurse' => $nurse,
            'password' => $passwordran // يتم إرسال الكلمة السرية مع الاستجابة
        ], 201);
    }


    function getNurses()
    {
        $doctor = Auth::user();

        // جلب قائمة الممرضات المرتبطات بالطبيب
        $nurses = $doctor->nurses;

        // إرجاع الاستجابة
        return response()->json([
            'doctor' => [
                'id' => $doctor->id,

            ],
            'nurses' => $nurses,
        ], 200);

    }
    //update nurse
    public function updateNurse(Request $request)
    {
        // التحقق من صحة البيانات
        $validator = Validator::make($request->all(), [
            'id' => 'required|exists:nurses,id', // التأكد من أن الممرضة موجودة
            'name' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:15|regex:/^[0-9]+$/|unique:nurses,phone,' . $request->id, // التأكد من عدم تكرار رقم الهاتف
            'gender' => 'nullable|string',
            'age' => 'nullable|integer|min:18|max:100',
            'address' => 'nullable|string|max:500',
            'the_jop' => 'nullable|string|max:500',
            'time' => 'nullable|string|max:500',
            'salary' => 'nullable|string|max:500',
            'date' => 'nullable|string|max:500',
            'profile_picture' => 'nullable|image|mimes:jpg,jpeg,png|max:2048', // صورة اختيارية
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 400);
        }

        // العثور على الممرضة
        $nurse = Nurse::find($request->id);
        if (!$nurse) {
            return response()->json(['error' => 'Nurse not found'], 404);
        }

        // التحقق من أن الطبيب الذي يحاول التعديل هو نفسه الذي أنشأ الحساب (إذا كان هذا هو المطلوب)
        if ($nurse->doctor_id != Auth::user()->id) {
            return response()->json(['error' => 'Unauthorized to update this nurse'], 403);
        }

        // تحديث البيانات
        $nurse->name = $request->name ?? $nurse->name;
        $nurse->phone = $request->phone ?? $nurse->phone;
        $nurse->gender = $request->gender ?? $nurse->gender;
        $nurse->age = $request->age ?? $nurse->age;
        $nurse->address = $request->address ?? $nurse->address;
        $nurse->the_jop = $request->the_jop ?? $nurse->the_jop;
        $nurse->time = $request->time ?? $nurse->time;
        $nurse->salary = $request->salary ?? $nurse->salary;
        $nurse->date = $request->date ?? $nurse->date;

          // تحديث صورة البروفايل إذا تم رفعها
          if ($request->hasFile('profile_picture')) {
            // حذف الصورة القديمة إذا كانت موجودة
            if ($nurse->profile_picture && \Storage::exists($nurse->profile_picture)) {
                \Storage::delete($nurse->profile_picture);
            }

            // رفع الصورة الجديدة وتخزين الرابط
            $path = $request->file('profile_picture')->store('images/nurses', 'public');
            $nurse->profile_picture = $path;
        }
        // حفظ التعديلات
        $nurse->save();

        // إرجاع الاستجابة
        return response()->json([
            'message' => 'Nurse details updated successfully.',
            'nurse' => $nurse
        ], 200);
    }

    //delete nurse
    public function deleteNurse(Request $request)
    {

        // التحقق من أن المعرف `id` للممرضة تم إرساله
        $validator = Validator::make($request->all(), [
            'id' => 'required|exists:nurses,id', // التأكد من أن الممرضة موجودة في قاعدة البيانات
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 400);
        }

        // العثور على الممرضة باستخدام الـ id
        $nurse = Nurse::find($request->id);
        if (!$nurse) {
            return response()->json(['error' => 'Nurse not found'], 404);
        }

        // التحقق من أن الطبيب الذي يطلب الحذف هو نفسه الذي قام بإنشاء الحساب (إذا كانت هذه هي السياسة المطلوبة)
        if ($nurse->doctor_id != Auth::user()->id) {
            return response()->json(['error' => 'Unauthorized to delete this nurse'], 403);
        }

        // حذف الممرضة من قاعدة البيانات
        $nurse->delete();

        // إرجاع استجابة بنجاح الحذف
        return response()->json([
            'message' => 'Nurse deleted successfully.',
        ], 200);
    }
    //show material
    public function showMaterial()
    {
        // جلب المستخدم المصادق عليه
        $doctor = Auth::user();

        // التحقق من أن المستخدم المصادق هو طبيب
        if (!$doctor || !$doctor instanceof \App\Models\Doctor) {
            return response()->json(['message' => 'Doctor not found or unauthorized'], 404);
        }

        // جلب جميع المواد المرتبطة بالطبيب
        $materials = $doctor->materials; // استنادًا إلى العلاقة بين الطبيب والمواد

        // إرجاع الاستجابة
        return response()->json([
            'message' => 'All Materials by Doctor',
            'materials' => $materials,
        ], 200);
    }
    //addmaterials by doctors
    public function store(Request $request)
    {
        // التحقق من صحة البيانات
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'price' => 'required|string|max:255',
            'pices' => 'required|integer|min:1', // تحقق من العدد
            'company' => 'required|string|max:255',
        ]);

        // جلب معرّف الطبيب المصادق عليه
        $doctorId = Auth::id(); // أو auth('doctor')->id();

        // التحقق من المصادقة
        if (!$doctorId) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

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
    //update material by doctor
    function updatematerial(Request $request)
    {
        $id = $request->id;
        $product = Material::find($id);

        if (!$product) {
            return response()->json(['message' => 'Product not found'], 404);
        }

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
            'doctor_id' => auth('doctor')->user()->id,  // حفظ معرف الطبيب الذي قام بالتعديل
        ]);

        return response()->json([
            'message' => 'Product updated successfully',
            'product' => $product,
        ], 200);


    }

    //delete material by doctor

    public function destroymaterial(Request $request)
    {
        $id = $request->id;
        $material = Material::find($id);

        if (!$material) {
            return response()->json(['error' => 'material not found'], 404);
        }

        $material->delete();

        return response()->json(['message' => 'material deleted successfully'], 200);
    }
    // get all user 
    function getAllUser()
    {
        $doctor = Auth::user();

        // التحقق من أن المستخدم المصادق هو طبيب
        if (!$doctor || !$doctor instanceof \App\Models\Doctor) {
            return response()->json(['message' => 'Doctor not found or unauthorized'], 404);
        }
        $user = $doctor->users;
        // إعادة استجابة نجاح
        return response()->json([
            'message' => 'All User.',
            'user' => $user
        ], 201);
    }


    //show all appointment of doctor 
    public function showAppointment()
    {
        // جلب المستخدم المصادق عليه
        $doctor = Auth::user();

        // التحقق من أن المستخدم المصادق هو طبيب
        if (!$doctor || !$doctor instanceof \App\Models\Doctor) {
            return response()->json(['message' => 'Doctor not found or unauthorized'], 404);
        }

        // جلب جميع المواعيد المرتبطة بالطبيب
        $appointment = $doctor->appointments; // استنادًا إلى العلاقة بين الطبيب والمواعيد

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
        // dd($request);
        // جلب معرّف الطبيب المصادق عليه
        $doctorId = Auth::id(); // أو auth('doctor')->id();

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
        //   إنشاء المادة وربطها بالطبيب
        // $appointment = Appointment::create([
        //     'name' => $request->name,
        //     'age' => $request->age,
        //     'time' => $request->time,
        //     'date' => $request->date,
        //     'user_id' => $request->user_id,
        //     'doctor_id' => $doctorId,
        // ]);


        return response()->json([
            'message' => 'Appointment created successfully',
            'appointment' => $appointment,
        ], 201);
    }
    //update appointment by doctor 

    function acceptAppointment(Request $request)
    {
        $id = $request->id;
        $appointment = Appointment::find($id);

        if (!$appointment) {
            return response()->json(['message' => 'Appointment not found'], 404);
        }
        $validated = $request->validate([
            'state' => 'string|max:255',
        ]);

        $appointment->state = '0';
        $appointment->save();  // حفظ معرف الطبيب الذي قام بالتعديل


        return response()->json([
            'message' => 'Appointment updated successfully',
            'appointment' => $appointment,
        ], 200);

    }
    function updateAppointment(Request $request)
    {
        $id = $request->id;

        $appointment = Appointment::find($id);

        if (!$appointment) {
            return response()->json(['message' => 'Appointment not found'], 404);
        }

        // التحقق من المدخلات
        $validated = $request->validate([
            'the_jop' => 'nullable|string|max:255',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048', // تعديل التحقق للصورة
            'payments' => 'nullable|string|max:255',
            'state' => 'nullable|string|max:255',
        ]);

        // تحديث البيانات النصية
        $appointment->the_jop = $validated['the_jop'] ?? $appointment->the_jop;
        $appointment->payments = $validated['payments'] ?? $appointment->payments;

        if ($request->hasFile('photo')) {
            $profilePicturePath = $request->file('photo')->store('images/appointment', 'public');
            $appointment->photo = $profilePicturePath;
        }
        
        $appointment->photo= $profilePicturePath;
        // ضبط الحالة دائمًا إلى 2
        $appointment->state = '2';

        // حفظ التغييرات
        $appointment->save();

        return response()->json([
            'message' => 'Appointment updated successfully',
            'appointment' => $appointment,
        ]);
    }






    // function updateAppointment( Request $request)
// {
//      $id= $request->id;

    //        $appointment = Appointment::find($id);

    //        if (!$appointment) {
//            return response()->json(['message' => 'Appointment not found'], 404);
//        }

    //        // التحقق من المدخلات
//        $validated = $request->validate([
//         'the_jop' => 'nullable|string|max:255',
//         'photo' => 'nullable|string|max:255',
//         'payments' => 'nullable|string|max:255',
//         'state'=>'string|max:255',

    //        ]);


    //         $appointment->the_jop = $validated['the_jop'] ?? $appointment->the_jop;
//         $appointment->photo = $validated['photo'] ?? $appointment->photo;
//         $appointment->payments = $validated['payments'] ?? $appointment->payments;
//          $appointment->state ='2';


    //         $appointment->save();  // حفظ معرف الطبيب الذي قام بالتعديل


    //        return response()->json([
//            'message' => 'Appointment updated successfully',
//            'appointment' => $appointment,
//        ], 200);


    // }

    //destroy appointment 
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

    function showMessages()
    {
        // جلب المستخدم المصادق عليه
        $doctor = Auth::user();

        // التحقق من أن المستخدم المصادق هو طبيب
        if (!$doctor || !$doctor instanceof \App\Models\Doctor) {
            return response()->json(['message' => 'Doctor not found or unauthorized'], 404);
        }

        // جلب جميع الملاحظات  المرتبطة بالطبيب
        $message = $doctor->messages; // استنادًا إلى العلاقة بين الطبيب والملاحظات

        // إرجاع الاستجابة
        return response()->json([
            'message' => 'All Message by Doctor',
            'messages' => $message,
        ], 200);
    }

    function createMessage(Request $request)
    {
        // التحقق من صحة البيانات
        $validated = $request->validate([
            'body' => 'required|string|max:255',
            'time' => 'required|string|max:255',
            'date' => 'required|string|max:255',
            'title' => 'required|string|max:255',
            'photo' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'user_id' => 'integer|exists:users,id',
            'nurse_id' => 'integer|exists:nurses,id',
            // التحقق من وجود المستخدم
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

        // إنشاء الرسالة وربطها بالطبيب
        $message = Message::create([
            'body' => $validated['body'],
            'time' => $validated['time'],
            'date' => $validated['date'],
            'title' => $validated['title'],
            'the_sender' => "Doctor {$name_sender}",


            'consignee' =>"User {$name_consignee}",
            'photo' => $photoPath ?? 'no', // إذا لم يتم رفع صورة، يتم تخزين 'no'
            'user_id' => $validated['user_id'],
            'doctor_id' => $doctorId,
        ]);

        return response()->json([
            'message' => 'Message created successfully',
            'message_data' => $message,
        ], 201);
    }

    //destroy message by doctor
    public function destroyMessage(Request $request)
    {
        $id = $request->id;

        $message = Message::find($id);

        if (!$message) {
            return response()->json(['error' => 'message not found'], 404);
        }

        $message->delete();

        return response()->json(['message' => 'message deleted successfully'], 200);
    }


}

