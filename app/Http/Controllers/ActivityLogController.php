<?php

namespace App\Http\Controllers;
use App\Models\ActivityLog;
use Illuminate\Http\Request;



class ActivityLogController extends Controller
{

public function index(){
    $logs = ActivityLog::latest()->paginate(10);
    return view ('log.activity_logs' , compact('logs'));
}



}
