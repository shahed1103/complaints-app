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


class SuperAdminController extends Controller
{
    private SuperAdmin $superAdmin;

    public function __construct(ComplaintWebService  $complaintWebService){
        $this->complaintWebService = $complaintWebService;
    }

public function getComplaintDepartment(): JsonResponse {
    $data = [];
    try {
        $data = $this->complaintWebService->getComplaintDepartment();
        return Response::Success($data, $data['message']);
    } catch (Throwable $th) {
        $message = $th->getMessage();
        $errors[] = $message;
        return Response::Error($data, $message, $errors);
    }
}

public function viewComplaintsByDepartmemt($depId): JsonResponse {
    $data = [];
    try {
        $data = $this->complaintWebService->viewComplaintsByDepartmemt($depId);
        return Response::Success($data, $data['message']);
    } catch (Throwable $th) {
        $message = $th->getMessage();
        $errors[] = $message;
        return Response::Error($data, $message, $errors);
    }
}

public function addNewEmployee(AddNewEmployeeRequest $request): JsonResponse {
    $data = [];
    try {
        $data = $this->complaintWebService->addNewEmployee( $request);
        return Response::Success($data, $data['message']);
    } catch (Throwable $th) {
        $message = $th->getMessage();
        $errors[] = $message;
        return Response::Error($data, $message, $errors);
    }
}

public function getAllEmployees(): JsonResponse {
    $data = [];
    try {
        $data = $this->complaintWebService->getAllEmployees();
        return Response::Success($data, $data['message']);
    } catch (Throwable $th) {
        $message = $th->getMessage();
        $errors[] = $message;
        return Response::Error($data, $message, $errors);
    }
}

public function deleteEmployee( $id): JsonResponse {
    $data = [];
    try {
        $data = $this->complaintWebService->deleteEmployee( $id);
        return Response::Success($data, $data['message']);
    } catch (Throwable $th) {
        $message = $th->getMessage();
        $errors[] = $message;
        return Response::Error($data, $message, $errors);
    }
}

public function getAllUsers(): JsonResponse {
    $data = [];
    try {
        $data = $this->complaintWebService->getAllUsers();
        return Response::Success($data, $data['message']);
    } catch (Throwable $th) {
        $message = $th->getMessage();
        $errors[] = $message;
        return Response::Error($data, $message, $errors);
    }
}

public function deleteUser( $id): JsonResponse {
    $data = [];
    try {
        $data = $this->complaintWebService->deleteUser( $id);
        return Response::Success($data, $data['message']);
    } catch (Throwable $th) {
        $message = $th->getMessage();
        $errors[] = $message;
        return Response::Error($data, $message, $errors);
    }
}


public function generateAndStorePdf (){
      $data = [];
    try {
        $data = $this->complaintWebService->generateAndStorePdf();
        return Response::Success($data, $data['message']);
    } catch (Throwable $th) {
        $message = $th->getMessage();
        $errors[] = $message;
        return Response::Error($data, $message, $errors);
    }
}
}
