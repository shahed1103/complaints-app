<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ComplaintsController;
use App\Http\Controllers\ComplaintsWebController;
use App\Http\Controllers\SuperAdminController;
use App\Http\Controllers\FcmController;
use App\Http\Controllers\PdfController;


/*
|--------------------------------------------------------------------------
| API Routes
|-------------------------------------------------------- ------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });

Route::controller(AuthController::class)->group(function(){
    Route::post('register' , 'register')
          ->name('user.register');

    Route::post('checkOtpCode/{userID}' , 'checkOtpCode')
    ->name('user.otp.code.check');

    Route::post('signin' , 'signin')
    ->middleware('throttle:login')
    ->name('user.signin');

    Route::get('resendOtp/{userId}' , 'resendOtp')
    ->name('user.resendOtp');

    Route::post('userForgotPassword' , 'userForgotPassword')
    ->name('user.password.email');

    Route::post('userCheckCode' , 'userCheckCode')
    ->name('user.password.code.check');

Route::post('userResetPassword/{code}' , 'userResetPassword')
->name('user.password.reset');

Route::middleware('auth:sanctum')->get('logout', [AuthController::class, 'logout'])->name('user.logout');
});






Route::controller(ComplaintsController::class)->group(function () {

    Route::get('getComplaintDepartment' , 'getComplaintDepartment')
    ->name('getComplaintDepartment');

    Route::get('getComplaintType' , 'getComplaintType')
    ->name('all.getComplaintType');
});



Route::middleware('auth:sanctum')->controller(ComplaintsController::class)->group(function () {
    Route::post('addComplaint' , 'addComplaint')
    ->name('user.add.complaint')
    ->middleware('can:addComplaint');

    Route::get('viewMyComplaints' , 'viewMyComplaints')
    ->name('user.view.Complaints')
    ->middleware('can:viewMyComplaints');

    Route::get('viewComplaintDetails/{complaintId}' , 'viewComplaintDetails')
    ->name('user.view.Complaint.details')
    ->middleware('can:viewComplaintDetails');

    Route::post('responsedToAdditionalInfo/{complaintId}' , 'responsedToAdditionalInfo')
    ->name('user.responsed.To.Additional.Info')
    ->middleware('can:responsedToAdditionalInfo');
    

});



Route::middleware('auth:sanctum')->controller(ComplaintsWebController::class)->group(function () {
    Route::get('viewComplaintsEmployeeDepartmemt' , 'viewComplaintsEmployeeDepartmemt')
    ->name('employee.view.departmemt.Complaints')
    ->middleware('can:viewComplaintsEmployeeDepartmemt');

    Route::get('viewComplaintDetailsEmployeeDepartmemt/{complaintId}' , 'viewComplaintDetailsEmployeeDepartmemt')
    ->name('employee.view.departmemt.Complaint.details')
    ->middleware('can:viewComplaintDetailsEmployeeDepartmemt');

    Route::post('editComplaintStatus/{complaintId}' , 'editComplaintStatus')
    ->name('employee.edit.complaint.status')
    ->middleware('can:editComplaintStatus');

    Route::post('addNotesAboutComplaint/{complaintId}' , 'addNotesAboutComplaint')
    ->name('employee.add.notes.about.complaint')
    ->middleware('can:addNotesAboutComplaint');

    Route::post('requestAdditionalInfo/{complaintId}' , 'requestAdditionalInfo')
    ->name('employee.request.additional.info.about.complaint')
    ->middleware('can:requestAdditionalInfo');

});






Route::controller(SuperAdminController::class)->group(function () {
Route::get('viewComplaintDepartment' , 'viewComplaintDepartment')
->name('admin.viewComplaintDepartment');

Route::get('viewComplaintsByDepartmemt/{id}' , 'viewComplaintsByDepartmemt')
->name('admin.viewComplaintsByDepartmemt');

Route::post('addNewEmployee' , 'addNewEmployee')
->name('admin.addNewEmployee');

Route::get('getAllEmployees' , 'getAllEmployees')
->name('admin.getAllEmployees');

Route::get('deleteEmployee/{id}' , 'deleteEmployee')
->name('admin.deleteEmployee');

Route::get('getAllUsers' , 'getAllUsers')
->name('admin.getAllUsers');

Route::get('deleteUser/{id}' , 'deleteUser')
->name('admin.deleteUser');

Route::get('lastNewUsers' , 'lastNewUsers')
->name('admin.lastNewUsers');

Route::get('getUserCountsByRoleByYear/{year}' , 'getUserCountsByRoleByYear')
->name('admin.getUserCountsByRoleByYear');

Route::get('totalComplaintByYear/{year}' , 'totalComplaintByYear')
->name('admin.totalComplaintByYear');

Route::get('getComplaintDetailsEmployeeDepartmemt/{id}' , 'getComplaintDetailsEmployeeDepartmemt')
->name('admin.getComplaintDetailsEmployeeDepartmemt');

Route::get('generateAndStorePdf' , 'generateAndStorePdf')
->name('admin.generateAndStorePdf');

Route::get('openTelescope' , 'openTelescope')
->name('admin.openTelescope');

Route::get('getAllComplaintVersion/{id}' , 'getAllComplaintVersion')
->name('admin.getAllComplaintVersion');

});

Route::controller(PdfController::class)->group(function () {
Route::get('generateComplaintsPdf' , 'generateComplaintsPdf')
->name('admin.generateComplaintsPdf');
});



Route::put('/update-device-token', [FcmController::class, 'updateDeviceToken']);
Route::post('/send-notification', [FcmController::class, 'sendFcmNotification']);
Route::get('/get-notifications/{userId}', [FcmController::class, 'getUserNotifications']);
Route::get('/get-unread-notifications-count/{userId}', [FcmController::class, 'getUnreadCount']);
