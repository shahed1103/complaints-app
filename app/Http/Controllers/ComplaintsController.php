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

    // show my complaints
    public function viewMyComplaints(): JsonResponse{
        $data = [] ;
        try{
            $data = $this->complaintService->viewMyComplaints();
           return Response::Success($data['complaints'], $data['message']);
        }
        catch(Throwable $th){
            $message = $th->getMessage();
            $errors [] = $message;
            return Response::Error($data , $message , $errors);
        }
    }

    // show complaint details
    public function viewComplaintDetails($complaintId): JsonResponse{
        $data = [] ;
        try{
            $data = $this->complaintService->viewComplaintDetails($complaintId);
           return Response::Success($data['complaint'], $data['message']);
        }
        catch(Throwable $th){
            $message = $th->getMessage();
            $errors [] = $message;
            return Response::Error($data , $message , $errors);
        }
    }

        //3 view all cities
    public function getComplaintDepartment(): JsonResponse {
        $data = [] ;
        try{
            $data = $this->complaintService->getComplaintDepartment();
           return Response::Success($data['cities'], $data['message']);
        }
        catch(Throwable $th){
            $message = $th->getMessage();
            $errors [] = $message;
            return Response::Error($data , $message , $errors);
        }
    }

    //4 view all genders
    public function getComplaintType(): JsonResponse {
        $data = [] ;
        try{
            $data = $this->complaintService->getComplaintType();
           return Response::Success($data['gender'], $data['message']);
        }
        catch(Throwable $th){
            $message = $th->getMessage();
            $errors [] = $message;
            return Response::Error($data , $message , $errors);
        }
    }

}
