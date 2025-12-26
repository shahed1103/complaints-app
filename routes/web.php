<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\ActivityLogController;

Route::get('/', function () {
    return view('welcome');
});


Route::get('/log', [ActivityLogController::class , 'index'])->name('log.index');
