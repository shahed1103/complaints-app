<?php

namespace App\Http\Controllers;

use Storage;
use Illuminate\Http\Request;
use App\Http\Responses\response;
use App\Services\ComplaintWebService;
use App\Http\Requests\Complaint\EditComplaintStatusRequest;
use App\Http\Requests\Complaint\AdditionalInfoRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Throwable;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;

class ComplaintsWebController extends Controller
{
    private ComplaintWebService $complaintWebService;

    public function __construct(ComplaintWebService  $complaintWebService){
        $this->complaintWebService = $complaintWebService;
    }

    // show complaints for spicific employee departmemt
    public function viewComplaintsEmployeeDepartmemt(): JsonResponse{
        $data = [] ;
        try{
            $data = $this->complaintWebService->viewComplaintsEmployeeDepartmemt();
           return Response::Success($data['complaints'], $data['message']);
        }
        catch(Throwable $th){
            $message = $th->getMessage();
            $errors [] = $message;
            return Response::Error($data , $message , $errors);
        }
    }

    // show complaint details for spicific employee departmemt
    public function viewComplaintDetailsEmployeeDepartmemt($complaintId): JsonResponse{
        $data = [] ;
        try{
            $data = $this->complaintWebService->viewComplaintDetailsEmployeeDepartmemt($complaintId);
           return Response::Success($data['complaint'], $data['message']);
        }
        catch(Throwable $th){
            $message = $th->getMessage();
            $errors [] = $message;
            $code = $th->getCode();
            return Response::ErrorX($data , $message , $errors , $code );
        }
    }

    // edit complaint status 
    public function editComplaintStatus(EditComplaintStatusRequest $request , $complaintId): JsonResponse{
        $data = [] ;
        try{
            $data = $this->complaintWebService->editComplaintStatus($request , $complaintId);
           return Response::Success($data['complaint'], $data['message']);
        }
        catch(Throwable $th){
            $message = $th->getMessage();
            $errors [] = $message;
            $code = $th->getCode();
            return Response::ErrorX($data , $message , $errors , $code);        }
    }

        // add notes about complaint 
        public function addNotesAboutComplaint(Request $request , $complaintId): JsonResponse{
        $data = [] ;
        try{
            $data = $this->complaintWebService->addNotesAboutComplaint($request , $complaintId);
           return Response::Success($data['note'], $data['message']);
        }
        catch(Throwable $th){
            $message = $th->getMessage();
            $errors [] = $message;
            $code = $th->getCode();
            return Response::ErrorX($data , $message , $errors , $code);        }
    }
        //additional information 
        public function requestAdditionalInfo(AdditionalInfoRequest $request, $complaintId): JsonResponse{
        $data = [] ;
        try{
            $data = $this->complaintWebService->requestAdditionalInfo($request , $complaintId);
           return Response::Success($data['info_request'], $data['message']);
        }
        catch(Throwable $th){
            $message = $th->getMessage();
            $errors [] = $message;
            $code = $th->getCode();
            return Response::ErrorX($data , $message , $errors , $code);        }
    }
    /////
    ///edit
}
