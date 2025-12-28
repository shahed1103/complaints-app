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
use Illuminate\Support\Facades\Cache;


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
            Cache::forget("user_{$user->id}_complaints");

             $message = 'new complaint created succesfully';
             return ['complaint' => $all , 'message' => $message];
    }

    // show my complaints
    public function viewMyComplaints(): array{
        $userId = Auth::id();
        $complaints = Cache::remember(
            "user_{$userId}_complaints",
            300, // 5 minutes
            function () use ($userId) {

                $complaints = Complaint::with(['complaintType:id,type','complaintDepartment:id,department_name', 'complaintStatus:id,status'])
                                        ->where('user_id', $userId)
                                        ->get();

                $latestVersions = ComplaintVersion::with('complaintStatus:id,status')
                                                    ->whereIn('complaint_id', $complaints->pluck('id'))
                                                    ->latest('id')
                                                    ->get()
                                                    ->groupBy('complaint_id');

                return $complaints->map(function ($complaint) use ($latestVersions) {

                $version = $latestVersions[$complaint->id][0] ?? null;

                return [
                        'id' => $version->id ?? $complaint->id,
                        'complaint_type' => ['id' => $complaint->complaintType->id ,'type' => $complaint->complaintType->type],
                        'complaint_department' => ['id' => $complaint->complaintDepartment->id,'department_name' => $complaint->complaintDepartment->department_name],
                        'location' => $complaint->location,
                        'complaint_status' => ['id' => $version->complaintStatus->id ?? $complaint->complaintStatus->id,'status' => $version->complaintStatus->status ?? $complaint->complaintStatus->status],
                    ];
                })->values()->toArray();
            }
        );
        $message = 'your complaints are retrieved successfully';
        return ['complaints' => $complaints ,'message' => $message];
    }

    // show complaint details
    public function viewComplaintDetails($complaintId): array{

        $complaint_det = Cache::remember(
            "complaint_details_{$complaintId}",
            300, // 5 minutes
            function () use ($complaintId) {

        $complaint = Complaint::with([
                        'complaintType:id,type',
                        'complaintDepartment:id,department_name',
                        'complaintStatus:id,status',
                    ])->findOrFail($complaintId);

        $complaintVersion = ComplaintVersion::with('complaintStatus:id,status')
                ->where('complaint_id', $complaintId)
                ->latest('id')
                ->first();

        $attachments = [] ;
            $attachmentsQuery = ComplaintAttachment::where('complaint_id', $complaintId);

            if ($complaintVersion) {
                $attachmentsQuery->where(function ($q) use ($complaintVersion) {
                    $q->whereNull('complaint_version_id')
                    ->orWhere('complaint_version_id', '<=', $complaintVersion->id);
                });
            }

            $attachments = $attachmentsQuery->get()->map(function ($attachment) {
                return [
                    'id' => $attachment->id,
                    'attachment' => url(Storage::url($attachment->attachment)),
                ];
            })->toArray();


            return [
                'complaint_type' => ['id' => $complaint->complaintType['id'] , 'type' => $complaint->complaintType['type']],
                'complaint_department' => ['id' => $complaint->complaintDepartment['id'] , 'department_name' => $complaint->complaintDepartment['department_name']],
                'location' => $complaint['location'],
                'problem_description' => $complaintVersion->problem_description ?? $complaint['problem_description'],
                'complaint_status' => ['id' => $complaintVersion->complaintStatus->id ?? $complaint->complaintStatus['id'] , 'status' => $complaintVersion->complaintStatus->status ?? $complaint->complaintStatus['status']],
                'attachments' => $attachments
            ];
        });
            $message = 'complaint details are retrived succesfully';
            return ['complaint' => $complaint_det , 'message' => $message];
    }

    //3 view all departments
    public function getComplaintDepartment():array{
            return $this->getComplaintDepartment();
    }

    //4 view all ComplaintType
    public function getComplaintType(): array{
        $types = Cache::remember('complaint_types', 86400, // 24 hours
            function () {
                return ComplaintType::select('id', 'type')
                    ->orderBy('type')
                    ->get()
                    ->map(function ($type) {
                        return ['id'   => $type->id ,'type' => $type->type];
                    })->toArray();
                }
            );

            return ['types'   => $types ,'message' => 'all types are retrieved successfully'];
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

        Cache::forget("complaint_details_{$complaintId}");
        Cache::forget("user_{$complaint->user_id}_complaints");

        $message = 'Additional information responsed successfully';
        return ['info_response' => $ss,'message' => $message];
    }

}
