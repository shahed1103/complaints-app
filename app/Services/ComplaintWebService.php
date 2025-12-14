<?php


namespace App\Services;

use App\Models\User;
use App\Models\Complaint;
use App\Models\AdditionalInfo;
use App\Models\Employee;
use App\Models\Note;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Support\Facades\Auth;
use Throwable;
use Exception;
use Storage;
use App\Models\ComplaintDepartment;
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

        $complaint['complaint_status_id']	= $request['complaint_status_id'];
        $complaint->save();

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
        $message = 'complaint details for spicific employee departmemt are retrived succesfully';
        return ['complaint' => $complaint , 'message' => $message];
}

        // add notes about complaint
public function addNotesAboutComplaint($request , $complaintId): array{
$user = Auth::user();
$employeeId = Employee::where('user_id' , $user->id)->value('id');

$request->validate(['note' => 'required']);

$note = Note::create([
    'note' => $request['note'],
    'complaint_id' => $complaintId,
    'employee_id' => $employeeId
]);

$message = 'note for complaint are added succesfully';
    return ['note' => $note , 'message' => $message];
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


public function deleteEmployee($id): array
{
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

public function deleteUser($id): array
{
    $user = User::find($id);
    $user->delete();
    return [
        'message' => 'User deleted successfully'
    ];
}



public function lastNewUsers(): array
{
    $users = User::where('created_at', '>=', Carbon::now()->subDays(30))->get();

    return [
        'count' => $users,
        'message' => 'New users in the last 30 days retrieved successfully'
    ];
}


public function getUserCountsByRoleByYear(int $year): array
{
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


public function totalComplaintByYear(int $year): array
{
    $startOfYear = Carbon::createFromDate($year, 1, 1)->startOfDay();
    $endOfYear = Carbon::createFromDate($year, 12, 31)->endOfDay();

    $complaints = Complaint::whereBetween('created_at', [$startOfYear, $endOfYear])
                    ->count();

    return [
        'complaints' => $complaints,
        'message' => "Total complaints for year {$year} retrieved successfully"
    ];
}



public function generateAndStorePdf () {

    $complaints = Complaint::all();

    $pdf = Pdf::view('pdf.complaints' , ['complaints' => $complaints ]);
    $fileName = 'complaints_' . now()->format('Y_m_d_H_i') . '.pdf';

    Storage::put("public/pdfs/$fileName" , $pdf->content());
    return "storage/pdfs/$fileName";

}


public function openTelescope(): array
{

    return [
        'url' => 'http://localhost:8000/telescope/requests',
        'message' => "retrieved successfully"
    ];
}




}
