<?php


namespace App\Services;

use App\Models\User;
use App\Models\Complaint;
use App\Models\AdditionalInfo;
use App\Models\Employee;
use App\Models\ComplaintVersion;
use App\Models\ComplaintType;
use App\Models\ComplaintStatus;
use App\Models\ComplaintAttachment;
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
use Illuminate\Http\Request;
use App\Support\ComplaintTransactional;
use App\Repositories\Complaint\ComplaintRepositoryInterface;
use App\Jobs\SendFcmNotificationJob;

class ComplaintWebService
{

    use GetComplaintDepartment;
    public function __construct(private ComplaintRepositoryInterface $complaints) {}
//////////////////////////////////////////////////////////////////////employee
    // show complaints for spicific employee departmemt
    public function viewComplaintsEmployeeDepartmemt(): array{
        $user = Auth::user();
        $department = Employee::where('user_id' , $user->id)->value('complaint_department_id');
        $complaints =  Complaint::with('complaintType' , 'complaintDepartment' , 'complaintStatus')->where('complaint_department_id' , $department)->get();

        $complaint_det = [];

        foreach ($complaints as $complaint) {

        $latestVersion = ComplaintVersion::with('complaintStatus:id,status')            
                                            ->where('complaint_id', $complaint->id)
                                            ->latest('id')
                                            ->first();


            $complaint_det [] = [
                'id' => $complaint['id'],
                'complaint_type' => ['id' => $complaint->complaintType['id'] , 'type' => $complaint->complaintType['type']],
                'complaint_department' => ['id' => $complaint->complaintDepartment['id'] , 'department_name' => $complaint->complaintDepartment['department_name']],
                'location' => $complaint['location'],
                'complaint_status' => ['id' => $latestVersion->complaintStatus['id'] ?? $complaint->complaintStatus['id'] ,
                                       'status' => $latestVersion->complaintStatus['status'] ?? $complaint->complaintStatus['status']],
            ];
        }

                $message = 'complaints for spicific employee departmemt are retrived succesfully';
                return ['complaints' => $complaint_det , 'message' => $message];
    }//edit///done

    // show complaint details for spicific employee departmemt
    public function viewComplaintDetailsEmployeeDepartmemt($complaintId): array{
        $complaint =  Complaint::with('complaintType' , 'complaintDepartment' , 'complaintStatus' , 'complaintAttachments')->find($complaintId);
        $employeeId = Employee::where('user_id', Auth::id())->value('id');
        $latestVersion = ComplaintVersion::with('complaintStatus:id,status')            
                                            ->where('complaint_id', $complaint->id)
                                            ->latest('id')
                                            ->first();
        $complaint->lock($employeeId);
        $attachments = [] ;
            $attachmentsQuery = ComplaintAttachment::where('complaint_id', $complaintId);

            if ($latestVersion) {
                $attachmentsQuery->where(function ($q) use ($latestVersion) {
                    $q->whereNull('complaint_version_id')
                    ->orWhere('complaint_version_id', '<=', $latestVersion->id);
                });
            }

            $attachments = $attachmentsQuery->get()->map(function ($attachment) {
                return [
                    'id' => $attachment->id,
                    'attachment' => url(Storage::url($attachment->attachment)),
                ];
            })->toArray();

        $complaint_det = [
            'complaint_type' => ['id' => $complaint->complaintType['id'] , 'type' => $complaint->complaintType['type']],
            'complaint_department' => ['id' => $complaint->complaintDepartment['id'] , 'department_name' => $complaint->complaintDepartment['department_name']],
            'location' => $complaint['location'],
            'problem_description' => $latestVersion->problem_description ?? $complaint['problem_description'],
            'complaint_status' => ['id' => $latestVersion->complaintStatus['id'] ?? $complaint->complaintStatus['id'] ,
                                   'status' => $latestVersion->complaintStatus['status'] ?? $complaint->complaintStatus['status']],
            'attachments' => $attachments,
            'notes' => $latestVersion->note

        ];

            $message = 'complaint details for spicific employee departmemt are retrived succesfully';
            return ['complaint' => $complaint_det , 'message' => $message];
    }//edit//done//question about notes

    // edit complaint status
    public function editComplaintStatus($request , $complaintId): array{

        $result = ComplaintTransactional::run($complaintId, function ($complaint) use ($request) {

        $employeeId = Employee::where('user_id', Auth::id())->value('id');

            if ($complaint->isLocked() && $complaint->locked_by != $employeeId) {
                throw new Exception("You cannot edit this complaint. It is locked by another employee.", 409);
            }

        $complaintVersion = ComplaintVersion::with('complaintStatus:id,status')->where('complaint_id' , $complaint->id)->latest()->first();

        $user = Auth::user();
        $userRole = Role::where('id', $user->role_id)->value('name');

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
            return [
                'newversion' => $newversion,
                'user_id' => $complaint->user_id
            ];
        });

        //Notification
            $user = User::find($result['user_id']);
            if ($user && $user->fcm_token) {
                    $status = $result['newversion']->complaintStatus->status;
                    SendFcmNotificationJob::dispatch($user->id, 'تم تعديل حالة شكواك' , "رقم الشكوى : $complaintId \n حالة الشكوى : $status");
            }

        $this->complaints->clearComplaintDetailsCache($complaintId);
        $this->complaints->clearUserComplaintsCache($result['user_id']);

            $message = 'statuse changed succesfully';
            return ['newComplaintVersion' => $result['newversion'] , 'message' => $message];
    }

    // add notes about complaint
    public function addNotesAboutComplaint($request , $complaintId): array{

        $result = ComplaintTransactional::run($complaintId, function ($complaint) use ($request) {

        $user = Auth::user();
        $userRole = Role::where('id', $user->role_id)->value('name');
        $employeeId = Employee::where('user_id' , $user->id)->value('id');

            if ($complaint->isLocked() && $complaint->locked_by != $employeeId) {
                    throw new Exception("This complaint is locked by another employee.", 409);
            }

        $request->validate(['note' => 'required']);

        $complaintVersion = ComplaintVersion::where('complaint_id' , $complaint->id)->latest()->first();

        $newVersion = ComplaintVersion:: create([
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

        $newVersion->save();

        $complaint->unlock();

        return [
            'version' => $newVersion,
            'user_id' => $complaint->user_id,
        ];

        });

        $this->complaints->clearComplaintDetailsCache($complaintId);
        $this->complaints->clearUserComplaintsCache($result['user_id']);

        $message = 'note for complaint are added succesfully';
        return ['newversion' => $result['version'] , 'message' => $message];
    }

    //request additional information
    public function requestAdditionalInfo($request, $complaintId): array{

            $result = ComplaintTransactional::run($complaintId, function ($complaint) use ($request) {

            $user = Auth::user();
            $userRole = Role::where('id', $user->role_id)->value('name');

            $employeeId = Employee::where('user_id', $user->id)->value('id');

            if ($complaint->isLocked() && $complaint->locked_by != $employeeId) {
                throw new Exception("This complaint is locked by another employee.", 409);
            }

            $complaintVersion = ComplaintVersion::where('complaint_id' , $complaint->id)->latest()->first();


            $openRequest = AdditionalInfo::where('complaint_id', $complaint->id)
                ->where('status', 'PENDING')
                ->first();

            if ($openRequest) {
                throw new Exception("There is already a pending additional info request.", 422);
            }

            $infoRequest = AdditionalInfo::create([
                'complaint_id' => $complaint->id,
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

            return [
                'infoRequest' => $infoRequest,
                'user_id' => $complaint->user_id
            ];
        });
            // Notification
            $complaintUser = User::find($result['user_id']);

            if ($complaintUser && $complaintUser->fcm_token) {
                SendFcmNotificationJob::dispatch($complaintUser->id, 'تم طلب معلومات إضافية بخصوص شكواك' , "رقم الشكوى : $complaintId \n تاريخ الطلب : {$result['infoRequest']->requested_at}");
            }

            $message = 'Additional information requested successfully';
            return ['info_request' => $result['infoRequest'] ,'message' => $message];
    }

//////////////////////////////////////////////////////Admin

    public function viewComplaintDepartment():array{
            return $this->getComplaintDepartments();
    }

    public function viewComplaintsByDepartmemt($depId): array{
        $complaints =  Complaint::with('complaintType' , 'complaintDepartment' , 'complaintStatus')->where('complaint_department_id' , $depId)->get();

        $complaint_det = [];


        foreach ($complaints as $complaint) {

        $latestVersion = ComplaintVersion::with('complaintStatus:id,status')            
                                            ->where('complaint_id', $complaint->id)
                                            ->latest('id')
                                            ->first();
            $complaint_det [] = [
                'id' => $complaint['id'],
                'complaint_type' => ['id' => $complaint->complaintType['id'] , 'type' => $complaint->complaintType['type']],
                'complaint_department' => ['id' => $complaint->complaintDepartment['id'] , 'department_name' => $complaint->complaintDepartment['department_name']],
                'location' => $complaint['location'],
                'complaint_status' => ['id' => $latestVersion->complaintStatus['id'] ?? $complaint->complaintStatus['id'] ,
                                       'status' => $latestVersion->complaintStatus['status'] ?? $complaint->complaintStatus['status']],            ];
        }

            $message = 'complaints for spicific departmemt are retrived succesfully';
            return ['complaints' => $complaint_det , 'message' => $message];
    }//edit//done

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

    public function openTelescope(): array{

        return [
            'url' => 'http://localhost:8000/telescope/requests',
            'message' => "retrieved successfully"
        ];
    }

    public function getAllComplaintVersion($complaint_id):array{
        $versions= ComplaintVersion::where('complaint_id' ,$complaint_id )->get();
        $version_det =[];
        foreach ($versions as $version) {
        $complaintType= ComplaintType::where('id',$version->complaint_type_id)->value('type');
        $complaintDepartment= ComplaintDepartment::where('id',$version->complaint_department_id)->value('department_name');
        $complaintStatus= ComplaintStatus::where('id',$version->complaint_status_id)->value('status');

            $version_det  = [
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
