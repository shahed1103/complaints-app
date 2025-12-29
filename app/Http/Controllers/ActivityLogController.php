<?php

namespace App\Http\Controllers;
use App\Models\ActivityLog;
use Illuminate\Http\Request;



class ActivityLogController extends Controller
{

// public function index(){
//     $logs = ActivityLog::latest()->paginate(10);
//     return view ('log.activity_logs' , compact('logs'));
// }

public function index() {
    // جلب أحدث 10 سجلات
    $logs = ActivityLog::latest()->paginate(10);

    // تعديل البيانات لتكون بالشكل المطلوب في JSON
    $logsData = $logs->map(function($log) {
        return [
            'first_name'   => $log->user->first_name ?? 'System',
            'last_name'    => $log->user->last_name ?? 'System',
            'email'        => $log->user->email ?? 'System',
            'description'  => $log->description . "_id",
            'created_at'   => $log->created_at->diffForHumans(),
            'updated_at'   => $log->updated_at->diffForHumans(),
            'ip_address'   => $log->ip_address,
            'log_name'     => $log->log_name,
        ];
    });

    // إرجاع JSON مع معلومات الصفحة (pagination)
    return response()->json([
        'current_page' => $logs->currentPage(),
        'per_page'     => $logs->perPage(),
        'total'        => $logs->total(),
        'last_page'    => $logs->lastPage(),
        'data'         => $logsData,
    ]);
}


}
