<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\ActivityLogController;
use App\Http\Controllers\SuperAdminController;



Route::get('/', function () {
    return view('welcome');
});


Route::get('/log', [ActivityLogController::class , 'index'])->name('log.index');
Route::get('/viewComplaintDepartment', [SuperAdminController::class , 'viewComplaintDepartment'])->name('viewComplaintDepartment');

