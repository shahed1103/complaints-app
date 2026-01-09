<?php

namespace App\Traits;

use App\Models\ComplaintDepartment;
use Illuminate\Support\Facades\Cache;
trait GetComplaintDepartment
{



public function getComplaintDepartments(): array
{
    // استخدام Cache لتخزين الأقسام لمدة ساعة (3600 ثانية)
    $departments = Cache::remember('departments', 3600, function () {
        // اختيار الأعمدة فقط لتقليل حجم البيانات
        return ComplaintDepartment::select('id', 'department_name')->get();
    });

    // تجهيز المصفوفة للإرسال
    $dep = [];
    foreach ($departments as $department) {
        $dep[] = [
            'id' => $department->id,
            'department_name' => $department->department_name,
        ];
    }

    $message = 'all departments are retrieved successfully';

    return [
        'departments' => $dep,
        'message' => $message,
    ];
}




}
