<?php


namespace App\Services;

use App\Models\User;
use App\Models\Complaint;
use App\Models\Employee;
use App\Models\Note;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Support\Facades\Auth;
use Throwable;
use Exception;
use Storage;
use Illuminate\Support\Facades\File;


class ComplaintWebService
{
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
}