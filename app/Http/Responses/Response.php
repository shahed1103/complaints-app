<?php

namespace App\Http\Responses;

use Illuminate\Http\JsonResponse;
use Illuminate\Contracts\Validation\Validator;

class Response{

    public static function Success($data , $message , $code=200): JsonResponse{
        return response()->json([
            'status' => 1,
            'data' => $data,
            'message' => $message
        ], $code);
    }

    public static function SuccessReset($role , $message , $code=200): JsonResponse{
        return response()->json([
            'status' => 1,
            'role' => $role,
            'message' => $message
        ], $code);
    }

    public static function Error($data , $message , $errors , $code= 500): JsonResponse{
        return response()->json([
            'status' => 0,
            'data' => $data,
            'errors' => $errors,
            'message' => $message
        ], $code);
    }

    public static function ErrorX($data , $message , $errors , $code = 500): JsonResponse{
        return response()->json([
            'status' => 0,
            'data' => $data,
            'errors' => $errors,
            'message' => $message,

        ] , $code);
    }

    public static function Validation($data , $message , $errors , $code=422): JsonResponse{
        return response()->json([
            'status' => 0,
            'data' => $data,
            'errors' => $errors,
            'message' => $message
        ], $code);
    }

}
