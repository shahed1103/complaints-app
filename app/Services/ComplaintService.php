<?php


namespace App\Services;

use App\Models\User;
use App\Models\Complaint;
use App\Models\ComplaintAttachment;
use App\Models\ComplaintDepartment;

use App\Models\ComplaintType;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Support\Facades\Auth;
use Throwable;
use Exception;
use Storage;
use Illuminate\Support\Facades\File;


class ComplaintService
{
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
                $complaint_det [] = [
                    'id' => $complaint['id'],
                    'complaint_type' => ['id' => $complaint->complaintType['id'] , 'type' => $complaint->complaintType['type']],
                    'complaint_department' => ['id' => $complaint->complaintDepartment['id'] , 'department_name' => $complaint->complaintDepartment['department_name']],
                    'location' => $complaint['location'],
                    'complaint_status' => ['id' => $complaint->complaintStatus['id'] , 'status' => $complaint->complaintStatus['status']],
                ]; 
            }

             $message = 'your complaints are retrived succesfully';
             return ['complaints' => $complaint_det , 'message' => $message];
        }

        // show complaint details
        public function viewComplaintDetails($complaintId): array{
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

             $message = 'complaint details are retrived succesfully';
             return ['complaint' => $complaint_det , 'message' => $message];
        }

            //3 view all cities
    public function getComplaintDepartment():array{
        $cities = ComplaintDepartment::all();
        foreach ($cities as $city) {
            $cities_name [] = ['id' => $city->id  , 'department_name' => $city->department_name];
        }
        $message = 'all cities are retrived successfully';

        return ['cities' =>  $cities_name , 'message' => $message];
     }

    //4 view all genders
    public function getComplaintType():array{
        $gender = ComplaintType::all();
        foreach ($gender as $gen) {
            $gender_name [] = ['id' => $gen->id  , 'type' => $gen->type];
        }
        $message = 'all genders are retrived successfully';

        return ['gender' =>  $gender_name , 'message' => $message];
     }
}