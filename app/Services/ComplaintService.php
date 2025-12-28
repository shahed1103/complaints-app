<?php


namespace App\Services;

use App\Models\User;
use App\Models\Complaint;
use App\Models\ComplaintAttachment;
use App\Models\ComplaintDepartment;
use App\Models\ComplaintVersion;
use App\Models\AdditionalInfo;
use App\Models\Employee;
use Spatie\Permission\Models\Role;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Support\Facades\Auth;
use Throwable;
use Exception;
use Storage;
use App\Traits\GetComplaintDepartment;
use Illuminate\Support\Facades\File;
use Illuminate\Http\Request;
use App\Repositories\Complaint\ComplaintRepositoryInterface;
use App\Jobs\SendFcmNotificationJob;


class ComplaintService
{
    use GetComplaintDepartment;

    public function __construct(private ComplaintRepositoryInterface $complaints) {}

    // add new complaint
    public function addComplaint($request): array{

            $user = Auth::user();

            $newComplaint = Complaint::create([
                'complaint_type_id' => $request['complaint_type_id'],
                'user_id' => $user->id,
                'complaint_department_id' => $request['complaint_department_id'],
                'complaint_status_id' => 1,
                'problem_description' => $request['problem_description'],
                'location' => $request['location'],
            ]);

            $files = [];

            if ($request->hasFile('attachments')) {
                foreach ($request->file('attachments') as $file) {
                    $path = $file->store('uploads/complaints', 'public');

                $ComplaintAttachments = ComplaintAttachment::create([
                    'attachment' =>  $path,
                    'complaint_id' => $newComplaint->id,
                ]);
                    $files[] = url(Storage::url($path));
                }

            }

            //Notification
               if ($user && $user->fcm_token) {
                SendFcmNotificationJob::dispatch($user->id , 'تم استلام شكواك وسيتم مراجعتها من قبل الفريق المختص' , "رقم الشكوى : {$newComplaint->id}");
               }

            $all = ['complaint' => $newComplaint , 'attachments' => $files];
            $this->complaints->clearUserComplaintsCache($user->id);

              $message = 'new complaint created succesfully';
             return ['complaint' => $all , 'message' => $message];
    }

    // show my complaints
    public function viewMyComplaints(): array{
        $userId = Auth::id();
        $complaints = $this->complaints->getUserComplaints($userId);
        $message = 'your complaints are retrieved successfully';
        return ['complaints' => $complaints ,'message' => $message];
    }

    // show complaint details
    public function viewComplaintDetails($complaintId): array{
        $complaint_det = $this->complaints->getComplaintDetails($complaintId);
        $message = 'complaint details are retrived succesfully';
        return ['complaint' => $complaint_det , 'message' => $message];
    }

    //3 view all departments
    public function getComplaintDepartment():array{
        return $this->getComplaintDepartments();
    }

    //4 view all ComplaintType
    public function getComplaintType(): array{
        $types = $this->complaints->getComplaintTypes();
        $message = 'all types are retrieved successfully';
        return ['types'   => $types ,'message' => $message];
    }

    //response additional information
    public function responsedToAdditionalInfo($request , $complaintId): array{
        $user = Auth::user();
        $userRole = Role::where('id', $user->role_id)->value('name');
        $complaintVersion = ComplaintVersion::where('complaint_id' , $complaintId)->latest()->first();


        $complaint = Complaint::where('id', $complaintId)
            ->where('user_id', $user->id)
            ->firstOrFail();

        $additionalInfo = AdditionalInfo::where('complaint_id', $complaintId)
            ->where('status', 'PENDING')
            ->latest()
            ->first();

        if (!$additionalInfo) {
            throw new Exception('لا يوجد طلب معلومات مفتوح لهذه الشكوى', 404);
        }

        // Version log
        $complaint_version =  ComplaintVersion::create([
            'complaint_id' => $complaint->id,
            'complaint_type_id' => $complaint->complaint_type_id,
            'complaint_department_id' => $complaint->complaint_department_id,
            'complaint_status_id' => $complaintVersion->complaint_status_id ?? $complaint->complaint_status_id,
            'user_id' => $complaint->user_id,
            'problem_description' => $request->problem_description ?? $complaint->problem_description,
            'location' => $complaint->location,
            'editor_id' => $user->id,
            'editor_name' => $user->name,
            'editor_role' => $userRole,
            'what_edit' => 'رد على طلب معلومات إضافية',
        ]);

        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                    $path = $file->store('uploads/complaints', 'public');

            $ss = ComplaintAttachment::create([
                    'attachment' =>  $path,
                    'complaint_id' => $complaint->id,
                    'complaint_version_id' => $complaint_version->id
                ]);
            }
        }

        $additionalInfo->update([
            'answered_at' => now(),
            'status' => 'ANSWERED'
        ]);

        // Notification
            $employee = Employee::with('user')->find($additionalInfo->employee_id);
            if ($employee->user && $employee->user->fcm_token) {
                    SendFcmNotificationJob::dispatch($employee->user->id, 'تم اضافة المعلومات المطلوبة' , "رقم الشكوى : $complaintId \n تاريخ الرد : $additionalInfo->answered_at");
            }

        $this->complaints->clearComplaintDetailsCache($complaintId);
        $this->complaints->clearUserComplaintsCache($complaint->user_id);

        $message = 'Additional information responsed successfully';
        return ['info_response' => $ss,'message' => $message];
    }
}
