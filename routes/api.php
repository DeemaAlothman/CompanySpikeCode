<?php

use App\Http\Controllers\AdmainController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DoctorController;
use App\Http\Controllers\MaterialController;
use App\Http\Controllers\NurseController;
use App\Http\Controllers\NurseMessageController;
use App\Http\Controllers\PatientMessageController;
use App\Http\Controllers\UserController;
use App\Models\Doctor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// }); 


// مسارات المدير (Admin)
Route::post('/admin/signup-doctor', [AdmainController::class, 'signupDoctor']);
Route::get('/admin/showallDoctors',[AdmainController::class,'show']);
Route::post('/admin/destroyaccountDoctor',[AdmainController::class,'destroy']);


//doctor 
Route::post('/doctor/login', [DoctorController::class, 'login']);
Route::middleware(['auth:doctor'])->group(function () {
    Route::post('/doctor/informationdoctor', [DoctorController::class, 'show']);//need id doctor
    Route::post('/doctor/updateprofile',[DoctorController::class,'updateprofile']);//need id doctor
    Route::post('/doctor/updateinfodoctor',[DoctorController::class,'updateinfodoctor']);//need id doctor
    Route::post('/doctor/logout', [DoctorController::class, 'logout']);


    //doctor and nurse
    Route::post('/doctor/signup-nurse', [DoctorController::class, 'signupNurse']);
    Route::post('/doctor/nurses', [DoctorController::class, 'getNurses']);
    Route::post('/doctor/update-nurse', [DoctorController::class, 'updateNurse']);
    Route::post('/doctor/delete-nurse', [DoctorController::class, 'deleteNurse']);



    //doctor material
    Route::post("/doctor/material/show",[DoctorController::class,'showMaterial']);
    Route::post('/doctor/material/addmaterials', [DoctorController::class, 'store']);
    Route::post('/doctor/material/updatematerial',[DoctorController::class,'updatematerial']);//need id material
    Route::post('/doctor/material/destroymaterial',[DoctorController::class,'destroymaterial']);//need id material
 
    //doctor user 

    Route::post('/doctor/allUser',[DoctorController::class,'getAllUser']);


    //doctor and appointment
     
    Route::post('/doctor/showAppointment',[DoctorController::class,'showAppointment']);
    Route::post('/doctor/createAppointment', [DoctorController::class, 'createAppointment']);
    Route::post('/doctor/acceptAppointment', [DoctorController::class, 'acceptAppointment']);
    Route::post('/doctor/updateAppointment', [DoctorController::class, 'updateAppointment']);
    Route::post('/doctor/destroyAppointment', [DoctorController::class, 'destroyAppointment']);


    //doctor and message 
    Route::post('/doctor/showMessages',[DoctorController::class,'showMessages']);
    Route::post('/doctor/createMessage', [DoctorController::class, 'createMessage']);
    Route::post('/doctor/destroyMessage', [DoctorController::class, 'destroyMessage']);


    // Route::post('/doctor/nurse/message', [DoctorController::class, 'createMessagenurse']);
    // Route::post('/doctor/patient/message', [PatientMessageController::class, 'createMessage']);

});



//nurse
Route::post('/nurse/login', [NurseController::class, 'login']);
Route::middleware(['auth:nurse'])->group(function () {
Route::get('/nurse/informationnurse', [NurseController::class, 'show']);
Route::post('/nurse/updateprofile',[NurseController::class,'updateprofile']);
Route::post('nurse/logout', [NurseController::class, 'logout']);

//show information doctor
Route::post('/nurse/showInfoDoctor',[UserController::class,'showInfoDoctor']);

//nurse with material


Route::get("/nurse/material/show",[NurseController::class,'showMaterial']);
Route::post('/nurse/material/addmaterials', [NurseController::class, 'storeMaterial']);
Route::post('/nurse/material/updatematerial',[NurseController::class,'updatematerial']);
Route::post('/nurse/material/destroymaterial',[NurseController::class,'destroymaterial']);

 //nusre and appointment
Route::get('/nurse/appointment/show',[NurseController::class,'showAppointment']);
Route::post('/nurse/createAppointment', [NurseController::class, 'createAppointment']);
Route::post('/nurse/acceptAppointment', [NurseController::class, 'acceptAppointment']);
Route::post('/nurse/updateAppointment', [NurseController::class, 'updateAppointment']);
Route::post('/nurse/destroyAppointment', [NurseController::class, 'destroyAppointment']);

//make message by nurse

Route::post('/nurse/showMessages',[NurseController::class,'showMessages']);
Route::post('/nurse/createMessage', [NurseController::class, 'createMessage']);
Route::post('/nurse/destroyMessage', [NurseController::class, 'destroyMessage']);

});
//user
Route::post('/user/signup', [AuthController::class, 'signup']); // للتسجيل
Route::post('/user/login', [AuthController::class, 'login']);   // لتسجيل الدخول
Route::middleware(['auth:user'])->group(function () {
    Route::post('/user/logout', [AuthController::class, 'logout']);
    Route::post('/user/show', [UserController::class, 'show']);
    Route::post('/user/updateprofile', [UserController::class, 'updateprofile']);
    //add appointment
    Route::post('/user/showAppointment', [UserController::class, 'showAppointment']);
    Route::post('/user/addAppointment', [UserController::class, 'addAppointment']);
    Route::post('/user/destroyAppointment', [UserController::class, 'destroyAppointment']);
    

    //show message 
    Route::post('/user/showMessage', [UserController::class, 'showMessage']);
    Route::post('/user/addMessage', [UserController::class, 'addMessage']);
   //show information doctor
   Route::post('/user/showInfoDoctor',[UserController::class,'showInfoDoctor']);


});

