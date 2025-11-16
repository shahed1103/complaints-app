<?php

namespace App\Http\Controllers;

use Storage;
use Illuminate\Http\Request;
use App\Http\Responses\response;
use App\Services\ComplaintService;
use App\Http\Requests\Complaint\AddComplaintRequest;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Throwable;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;

class ComplaintsController extends Controller
{
    private ComplaintService $complaintService;

    public function __construct(ComplaintService  $complaintService){
        $this->complaintService = $complaintService;
    }

    // add new complaint
    public function addComplaint(AddComplaintRequest $request): JsonResponse {
        $data = [] ;
        try{
            $data = $this->complaintService->addComplaint($request);
           return Response::Success($data['complaint'], $data['message']);
        }
        catch(Throwable $th){
            $message = $th->getMessage();
            $errors [] = $message;
            return Response::Error($data , $message , $errors);
        }
    }

}
