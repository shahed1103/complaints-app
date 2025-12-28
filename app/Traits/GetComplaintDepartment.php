<?php

namespace App\Traits;

use App\Models\ComplaintDepartment;

trait GetComplaintDepartment
{

public function getComplaintDepartments():array{
    $departments = ComplaintDepartment::all();
    foreach ($departments as $department) {
        $dep [] = ['id' => $department->id  , 'department_name' => $department->department_name];
    }
    $message = 'all departments are retrived successfully';

    return ['departments' =>  $dep , 'message' => $message];
    }




}
