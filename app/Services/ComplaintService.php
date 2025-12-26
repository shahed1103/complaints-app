<?php


namespace App\Services;

use App\Models\User;
use App\Models\Complaint;
use App\Models\ComplaintAttachment;
use App\Models\ComplaintDepartment;
use App\Models\ComplaintVersion;
use App\Models\ComplaintType;
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
use App\Http\Controllers\FcmController;
use Illuminate\Http\Request;


class ComplaintService
{
    use GetComplaintDepartment;
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

            ///////////
               if ($user && $user->fcm_token) {
                     $fcmController = new FcmController();
                     $fakeRequest = new Request([
                        'user_id' => $user->id,
                        'title' => "تم استلام شكواك وسيتم مراجعتها",
                     ]);
                     $fcmController->sendFcmNotification($fakeRequest);
               }
            //////////

            $all = ['complaint' => $newComplaint , 'attachments' => $files];

             $message = 'new complaint created succesfully';
             return ['complaint' => $all , 'message' => $message];
    }

        // show my complaints
        public function viewMyComplaints(): array{
            $user = Auth::user();
            $complaints =  Complaint::with('complaintType' , 'complaintDepartment' , 'complaintStatus')->where('user_id' , $user->id)->get();
            $complaint_det = [];
            foreach ($complaints as $complaint) {
                $complaintVersion = ComplaintVersion::where('complaint_id' , $complaint->id)->latest()->first();

                $complaint_det [] = [
                    'id' => $complaintVersion->id ?? $complaint['id'],
                    'complaint_type' => ['id' => $complaint->complaintType['id'] , 'type' => $complaint->complaintType['type']],
                    'complaint_department' => ['id' => $complaint->complaintDepartment['id'] , 'department_name' => $complaint->complaintDepartment['department_name']],
                    'location' => $complaint['location'],
                    'complaint_status' => ['id' => $complaintVersion->complaintStatus->id ?? $complaint->complaintStatus['id'] , 'status' => $complaintVersion->complaintStatus->status ?? $complaint->complaintStatus['status']],
                ];
            }

             $message = 'your complaints are retrived succesfully';
             return ['complaints' => $complaint_det , 'message' => $message];
        }

        // show complaint details
        public function viewComplaintDetails($complaintId): array{
            $complaint =  Complaint::with('complaintType' , 'complaintDepartment' , 'complaintStatus' , 'complaintAttachments')->find($complaintId);

            $complaintVersion = ComplaintVersion::where('complaint_id' , $complaintId)->latest()->first();

            $attachments = [] ;

                foreach ($complaintVersion->complaintAttachments ?? $complaint->complaintAttachments as $complaintAttachment) {
                    $attachments [] = [
                        'id' => $complaintAttachment->id ,
                        'attachment' => url(Storage::url($complaintAttachment->attachment))
                    ];
                }

                $complaint_det = [
                    'complaint_type' => ['id' => $complaint->complaintType['id'] , 'type' => $complaint->complaintType['type']],
                    'complaint_department' => ['id' => $complaint->complaintDepartment['id'] , 'department_name' => $complaint->complaintDepartment['department_name']],
                    'location' => $complaint['location'],
                    'problem_description' => $complaintVersion->problem_description ?? $complaint['problem_description'],
                    'complaint_status' => ['id' => $complaintVersion->complaintStatus->id ?? $complaint->complaintStatus['id'] , 'status' => $complaintVersion->complaintStatus->status ?? $complaint->complaintStatus['status']],
                    'attachments' => $attachments
                ];

             $message = 'complaint details are retrived succesfully';
             return ['complaint' => $complaint_det , 'message' => $message];
        }

    //3 view all departments
    
public function getComplaintDepartment():array{
        return $this->getComplaintDepartment();
}

    //4 view all ComplaintType
    public function getComplaintType():array{
        $complaintTypes = ComplaintType::all();
        foreach ($complaintTypes as $complaintType) {
            $types [] = ['id' => $complaintType->id  , 'type' => $complaintType->type];
        }
        $message = 'all types are retrived successfully';

        return ['gender' =>  $types , 'message' => $message];
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
                (new FcmController())->sendFcmNotification(new Request([
                    'user_id' => $employee->user->id,
                    'title' => 'تم إضافة المعلومات المطلوبة'
                ]));
            }

        $message = 'Additional information responsed successfully';
        return ['info_response' => $ss,'message' => $message];
    }

}
