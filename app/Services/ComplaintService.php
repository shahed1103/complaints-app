<?php


namespace App\Services;

use App\Models\User;
use App\Models\Complaint;
use App\Models\ComplaintAttachment;



use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Support\Facades\Session;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Illuminate\Http\Request;
use App\Http\Responses\Response;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\ValidationException;
use Illuminate\Http\JsonResponse;
use App\Models\ResetCodePassword;
use App\Mail\SendCodeResetPassword;
use Illuminate\Support\Facades\Mail;
use Throwable;
use Storage;
use Illuminate\Support\Facades\File;


class ComplaintService
{
        public function addComplaint($request): array{

            $user = Auth::user();

            $newComplaint = Complaint::create([
                'complaint_type_id' => $request['complaint_type_id'],
                'user_id' => 1 ,//$user->id,
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

            $message = 'new complaint created succesfully';
            return ['complaint' => [$newComplaint , $files] , 'message' => $message];
        }
}