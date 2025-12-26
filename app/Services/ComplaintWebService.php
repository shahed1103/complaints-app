<?php


namespace App\Services;

use App\Models\User;
use App\Models\Complaint;
use App\Models\AdditionalInfo;
use App\Models\Employee;
use App\Models\ComplaintVersion;
use App\Models\ComplaintType;
use App\Models\ComplaintStatus;
use App\Models\ComplaintDepartment;
use App\Models\Note;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Support\Facades\Auth;
use Throwable;
use Exception;
use Storage;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\File;
use App\Traits\GetComplaintDepartment;
use Spatie\LaravelPdf\Facades\Pdf;
use Illuminate\Support\Carbon;
use App\Http\Controllers\FcmController;
use Illuminate\Http\Request;

class ComplaintWebService
{

    use GetComplaintDepartment;

//////////////////////////////////////////////////////////////////////employee
        // show complaints for spicific employee departmemt
public function viewComplaintsEmployeeDepartmemt(): array{
    $user = Auth::user();
    $department = Employee::where('user_id' , $user->id)->value('complaint_department_id');
    $complaints =  Complaint::with('complaintType' , 'complaintDepartment' , 'complaintStatus')->where('complaint_department_id' , $department)->get();

    $complaint_det = [];

    foreach ($complaints as $complaint) {
        $complaint_det [] = [
            'id' => $complaint['id'],
            'complaint_type' => ['id' => $complaint->complaintType['id'] , 'type' => $complaint->complaintType['type']],
            'complaint_department' => ['id' => $complaint->complaintDepartment['id'] , 'department_name' => $complaint->complaintDepartment['department_name']],
            'location' => $complaint['location'],
            'complaint_status' => ['id' => $complaint->complaintStatus['id'] , 'status' => $complaint->complaintStatus['status']],
        ];
    }

            $message = 'complaints for spicific employee departmemt are retrived succesfully';
            return ['complaints' => $complaint_det , 'message' => $message];
    }

        // show complaint details for spicific employee departmemt
public function viewComplaintDetailsEmployeeDepartmemt($complaintId): array{
    $complaint =  Complaint::with('complaintType' , 'complaintDepartment' , 'complaintStatus' , 'complaintAttachments')->find($complaintId);
    $employeeId = Employee::where('user_id', Auth::id())->value('id');

    $complaint->lock($employeeId);

    $attachments = [] ;

        foreach ($complaint->complaintAttachments as $complaintAttachment) {
            $attachments [] = [
                'id' => $complaintAttachment->id ,
                    'attachment' => url(Storage::url($complaintAttachment->attachment))
                ];
            }

    $complaint_det = [
        'complaint_type' => ['id' => $complaint->complaintType['id'] , 'type' => $complaint->complaintType['type']],
        'complaint_department' => ['id' => $complaint->complaintDepartment['id'] , 'department_name' => $complaint->complaintDepartment['department_name']],
        'location' => $complaint['location'],
        'problem_description' => $complaint['problem_description'],
        'complaint_status' => ['id' => $complaint->complaintStatus['id'] , 'status' => $complaint->complaintStatus['status']],
        'attachments' => $attachments
    ];

        $message = 'complaint details for spicific employee departmemt are retrived succesfully';
        return ['complaint' => $complaint_det , 'message' => $message];
}

        // edit complaint status
public function editComplaintStatus($request , $complaintId): array{

    $complaint =  Complaint::find($complaintId);
    $complaintVersion = ComplaintVersion::where('complaint_id' , $complaintId)->latest()->first();

    $employeeId = Employee::where('user_id', Auth::id())->value('id');

        if ($complaint->isLocked() && $complaint->locked_by != $employeeId) {
            throw new Exception("You cannot edit this complaint. It is locked by another employee.", 409);
        }
    $user = Auth::user();
    $userRole = Role::where('id', $user->role_id)->value('name');
   //   $complaint['complaint_status_id']	= $request['complaint_status_id'];
   //   $complaint->save();


   // new version
   $newversion = ComplaintVersion:: create([
        'complaint_type_id' => $complaint ->complaint_type_id,
        'user_id' => $complaint -> user_id,
        'complaint_department_id' => $complaint->complaint_department_id,
        'complaint_status_id'=> $request['complaint_status_id'] ,
        'problem_description' => $complaintVersion->problem_description ?? $complaint->problem_description,
        'location' => $complaint->location,
        'complaint_id' => $complaint->id,
        'editor_id' => $user->id,
        'editor_name' => $user->name,
        'editor_role' => $userRole,
        'what_edit' => 'تعديل  على الحالة'
   ]);

        $newversion -> save();
        $complaint->unlock();
        ////////////////
        $user = User::where('id', $complaint->user_id)->first();

        if ($user && $user->fcm_token) {
                     $fcmController = new FcmController();
                     $fakeRequest = new Request([
                        'user_id' => $user->id,
                        'title' => "تم التعديل على حالة شكواك",
                     ]);
                     $fcmController->sendFcmNotification($fakeRequest);
        }
        ///////////////
        $message = 'statuse changed succesfully';
        return ['newComplaintVersion' => $newversion , 'message' => $message];
}

        // add notes about complaint
public function addNotesAboutComplaint($request , $complaintId): array{
    $user = Auth::user();
    $userRole = Role::where('id', $user->role_id)->value('name');
    $employeeId = Employee::where('user_id' , $user->id)->value('id');
    $complaintVersion = ComplaintVersion::where('complaint_id' , $complaintId)->latest()->first();

    $complaint = Complaint::find($complaintId);

        if ($complaint->isLocked() && $complaint->locked_by != $employeeId) {
                throw new Exception("This complaint is locked by another employee.", 409);
        }

    $request->validate(['note' => 'required']);

    $newversion = ComplaintVersion:: create([
            'complaint_type_id' => $complaint ->complaint_type_id,
            'user_id' => $complaint -> user_id,
            'complaint_department_id' => $complaint->complaint_department_id,
            'complaint_status_id'=> $complaintVersion->complaint_status_id ?? $complaint->complaint_status_id ,
            'problem_description' => $complaintVersion->problem_description ?? $complaint->problem_description,
            'location' => $complaint->location,
            'complaint_id' => $complaint ->id,
            'editor_id' => $user->id,
            'editor_name' => $user->name,
            'editor_role' => $userRole,
            'note' => $request['note'],
            'what_edit' => 'إضافة ملاحظة '
    ]);

        $newversion -> save();


    // $note = Note::create([
    //     'note' => $request['note'],
    //     'complaint_id' => $complaintId,
    //     'employee_id' => $employeeId
    // ]);
    $complaint->unlock();
    $message = 'note for complaint are added succesfully';
        return ['newversion' => $newversion , 'message' => $message];
}

     //request additional information
    public function requestAdditionalInfo($request, $complaintId): array{

        $complaint = Complaint::findOrFail($complaintId);
        $complaintVersion = ComplaintVersion::where('complaint_id' , $complaintId)->latest()->first();

        $user = Auth::user();
        $userRole = Role::where('id', $user->role_id)->value('name');

        $employeeId = Employee::where('user_id', $user->id)->value('id');

        if ($complaint->isLocked() && $complaint->locked_by != $employeeId) {
            throw new Exception("This complaint is locked by another employee.", 409);
        }

        $openRequest = AdditionalInfo::where('complaint_id', $complaintId)
            ->where('status', 'PENDING')
            ->first();

        if ($openRequest) {
            throw new Exception("There is already a pending additional info request.", 422);
        }

        $infoRequest = AdditionalInfo::create([
            'complaint_id' => $complaintId,
            'employee_id' => $employeeId,
            'request_message' => $request['request_message'],
            'status' => 'PENDING',
            'requested_at' => now()
        ]);

        // Version log
        $complaint_version = ComplaintVersion::create([
            'complaint_type_id' => $complaint->complaint_type_id,
            'user_id' => $complaint->user_id,
            'complaint_department_id' => $complaint->complaint_department_id,
            'complaint_status_id'=> $complaintVersion->complaint_status_id ?? $complaint->complaint_status_id,
            'problem_description' => $complaintVersion->problem_description ?? $complaint->problem_description,
            'location' => $complaint->location,
            'complaint_id' => $complaint->id,
            'editor_id' => $user->id,
            'editor_name' => $user->name,
            'editor_role' => $userRole,
            'what_edit' => 'طلب معلومات إضافية'
        ]);

        $complaint->unlock();

        // Notification
        $complaintUser = User::find($complaint->user_id);
        if ($complaintUser && $complaintUser->fcm_token) {
            (new FcmController())->sendFcmNotification(new Request([
                'user_id' => $complaintUser->id,
                'title' => 'تم طلب معلومات إضافية بخصوص شكواك'
            ]));
        }
        $message = 'Additional information requested successfully';
        return ['info_request' => $infoRequest,'message' => $message];
    }

//////////////////////////////////////////////////////Admin

public function viewComplaintDepartment():array{
        return $this->getComplaintDepartment();
}

public function viewComplaintsByDepartmemt($depId): array{
    $complaints =  Complaint::with('complaintType' , 'complaintDepartment' , 'complaintStatus')->where('complaint_department_id' , $depId)->get();

    $complaint_det = [];


    foreach ($complaints as $complaint) {
        $complaint_det [] = [
            'id' => $complaint['id'],
            'complaint_type' => ['id' => $complaint->complaintType['id'] , 'type' => $complaint->complaintType['type']],
            'complaint_department' => ['id' => $complaint->complaintDepartment['id'] , 'department_name' => $complaint->complaintDepartment['department_name']],
            'location' => $complaint['location'],
            'complaint_status' => ['id' => $complaint->complaintStatus['id'] , 'status' => $complaint->complaintStatus['status']],
        ];
    }

        $message = 'complaints for spicific departmemt are retrived succesfully';
        return ['complaints' => $complaint_det , 'message' => $message];
}

public function addNewEmployee($request): array{
    $employee = User::factory()->create([
        'role_id' => 3,
        'gender_id' => $request['gender_id'],
        'phone' => $request['phone'],
        'city_id' => $request['city_id'],
        'age' => $request['age'],
        'name' => $request['name'],
        'email' => $request['email'],
        'password' => bcrypt($request['password']) ,
        'photo' => url(Storage::url($request['photo'])),
        'is_verified' => true
    ]);
    $employee->save();

    $employeeDep = Employee::create([
    'user_id' => $employee->id,
    'complaint_department_id' => $request['complaint_department_id'],
    'name' => $request['name']
    ]);

    $message = 'Employee added succesfully';
    return ['employee' => $employee , 'message' => $message];
}


public function getAllEmployees():array{

    $employeeRole = Role::where('name', 'Employee')->value('id');
    $employees = User::whereIn('role_id', [$employeeRole])
        ->select('id','name', 'email', 'phone' , 'age')
        ->get();


    $message = 'all employees are retrived successfully';

    return ['employees' =>  $employees , 'message' => $message];
}

public function deleteEmployee($id): array{
    $user = User::find($id);
    $user->delete();
    return [
        'message' => 'Employee deleted successfully'
    ];
}

public function getAllUsers():array{

    $userRole = Role::where('name', 'Client')->value('id');
    $users = User::whereIn('role_id', [$userRole])
        ->select('id','name', 'email', 'phone' , 'age')
        ->get();
    $message = 'all users are retrived successfully';

    return ['users' =>  $users , 'message' => $message];
}

public function deleteUser($id): array{
    $user = User::find($id);
    $user->delete();
    return [
        'message' => 'User deleted successfully'
    ];
}

public function lastNewUsers(): array{
    $users = User::where('created_at', '>=', Carbon::now()->subDays(30))->get();

    return [
        'count' => $users,
        'message' => 'New users in the last 30 days retrieved successfully'
    ];
}

public function getUserCountsByRoleByYear(int $year): array{
    $startOfYear = Carbon::createFromDate($year, 1, 1)->startOfDay();
    $endOfYear = Carbon::createFromDate($year, 12, 31)->endOfDay();

    $clientRole = Role::where('name', 'Client')->value('id');
    $employeeRole = Role::where('name', 'Employee')->value('id');

    $clientCount = User::where('role_id', $clientRole)
        ->whereBetween('created_at', [$startOfYear, $endOfYear])
        ->count();

    $employeeCount = User::where('role_id', $employeeRole)
        ->whereBetween('created_at', [$startOfYear, $endOfYear])
        ->count();

    $data = [
        'client'     => $clientCount,
        'employee'     => $employeeCount,
        'total'      => $clientCount + $employeeCount ,
    ];

    $message = "User counts by role for year {$year} retrieved successfully";

    return [
        'data' => $data,
        'message' => $message
    ];
}

public function totalComplaintByYear(int $year): array{
    $complaints = Complaint::whereYear('created_at', $year)
        ->selectRaw('MONTH(created_at) as month, COUNT(id) as total')
        ->groupBy('month')
        ->orderBy('month')
        ->get();

    $monthlyTotals = [];
    for ($i = 1; $i <= 12; $i++) {
        $monthlyTotals[$i] = 0;
    }

    foreach ($complaints as $row) {
        $monthlyTotals[(int)$row->month] = (float)$row->total;
    }

    $message = "Monthly complaints totals for year {$year} retrieved successfully";

    return [
        'data' => $monthlyTotals,
        'message' => $message
    ];
}
// public function totalComplaintByYear(int $year): array
// {
//     $startOfYear = Carbon::createFromDate($year, 1, 1)->startOfDay();
//     $endOfYear = Carbon::createFromDate($year, 12, 31)->endOfDay();

//     $complaints = Complaint::whereBetween('created_at', [$startOfYear, $endOfYear])
//                     ->count();

//     return [
//         'complaints' => $complaints,
//         'message' => "Total complaints for year {$year} retrieved successfully"
//     ];
// }

public function generateAndStorePdf () {

    $complaints = Complaint::all();

    $pdf = Pdf::view('pdf.complaints' , ['complaints' => $complaints ]);
    $fileName = 'complaints_' . now()->format('Y_m_d_H_i') . '.pdf';

    Storage::put("public/pdfs/$fileName" , $pdf->content());
    return "storage/pdfs/$fileName";

}

public function openTelescope(): array{

    return [
        'url' => 'http://localhost:8000/telescope/requests',
        'message' => "retrieved successfully"
    ];
}

public function getAllComplaintVersion($complaint_id):array{


    $versions= ComplaintVersion::where('complaint_id' ,$complaint_id )->get();


    foreach ($versions as $version) {
    $complaintType= ComplaintType::where('id',$version->complaint_type_id)->value('type');
    $complaintDepartment= ComplaintDepartment::where('id',$version->complaint_department_id)->value('department_name');
    $complaintStatus= ComplaintStatus::where('id',$version->complaint_status_id)->value('status');

            $version_det [] = [
                'id' => $version['id'],
                'complaint_id' =>$version['complaint_id'],
                'complaint_type' => $complaintType,
                'complaint_department' => $complaintDepartment,
                'complaint_status' => $complaintStatus,
                'location' => $version['location'],
                'problem_description' => $version['problem_description'],
                'editor_name' => $version ['editor_name'],
                'editor_role' => $version ['editor_role'],
                'what_edit' => $version ['what_edit'] ,
                'note' => $version ['note']
            ];
        }
    $message = 'all versions are retrived successfully';

    return ['versions' =>  $version_det , 'message' => $message];
}

}
