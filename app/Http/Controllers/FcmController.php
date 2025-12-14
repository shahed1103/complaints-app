<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Google\Client as GoogleClient;
use Illuminate\Support\Facades\Storage;
use App\Services\NotificationService;
use App\Models\User;
use App\Http\Responses\response;
use Illuminate\Http\JsonResponse;


use Illuminate\Support\Facades\Http;

class FcmController extends Controller
{

    public function __construct(NotificationService $notificationService = null){
        $this->notificationService = $notificationService ?? new NotificationService();
    }

    public function getUserNotifications($userId): JsonResponse {
      $data = [] ;
        try{
            $data = $this->notificationService->getUserNotifications($userId);
           return Response::Success($data['notifications'], $data['message']);
        }
        catch(Throwable $th){
            $message = $th->getMessage();
            $errors [] = $message;
            return Response::Error($data , $message , $errors);
        }
    }

    public function getUnreadCount($userId): JsonResponse {
      $data = [] ;
        try{
            $data = $this->notificationService->getUnreadCount($userId);
           return Response::Success($data['unread_notifications_count'], $data['message']);
        }
        catch(Throwable $th){
            $message = $th->getMessage();
            $errors [] = $message;
            return Response::Error($data , $message , $errors);
        }
    }

    public function updateDeviceToken(Request $request)
{
    $request->validate([
        'user_id' => 'required|exists:users,id',
        'fcm_token' => 'required|string',
    ]);

    $user = User::find($request->user_id);

    if (!$user) {
        return response()->json(['error' => 'User not found'], 404);
    }

    $user->fcm_token = $request->fcm_token;
    $user->save();

    return response()->json(['message' => 'Device token updated successfully']);
}

public function sendFcmNotification(Request $request)
{
    $request->validate([
        'user_id' => 'required|exists:users,id',
        'title' => 'required|string',
        // 'body' => 'required|string',
    ]);

    $user = \App\Models\User::find($request->user_id);
    $fcm = $user->fcm_token;

    if (!$fcm) {
        return response()->json(['message' => 'User does not have a device token'], 400);
    }

    $title = $request->title;
    // $description = $request->body;

    // -----------------------------
    // حفظ الإشعار في قاعدة البيانات
    \App\Models\Notification::create([
        'user_id' => $user->id,
        'title' => $title,
        // 'body' => $description,
    ]);
    // -----------------------------

    $projectId = env('FCM_PROJECT_ID');
    $credentialsFilePath = storage_path('app/firebase/complaint-system-6f1f5-firebase-adminsdk-fbsvc-bb348d4a29.json');

    $client = new GoogleClient();
    $client->setAuthConfig($credentialsFilePath);
    $client->addScope('https://www.googleapis.com/auth/firebase.messaging');
    $client->refreshTokenWithAssertion();
    $token = $client->getAccessToken();

    $access_token = $token['access_token'];

    $headers = [
        "Authorization: Bearer $access_token",
        'Content-Type: application/json'
    ];

    $data = [
        "message" => [
            "token" => $fcm,
            "notification" => [
                "title" => $title,
                // "body" => $description,
            ],
        ]
    ];
    $payload = json_encode($data);

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "https://fcm.googleapis.com/v1/projects/{$projectId}/messages:send");
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
    curl_setopt($ch, CURLOPT_VERBOSE, true);
    $response = curl_exec($ch);
    $err = curl_error($ch);
    curl_close($ch);

    if ($err) {
        return response()->json([
            'message' => 'Curl Error: ' . $err
        ], 500);
    } else {
        return response()->json([
            'message' => 'Notification has been sent and stored',
            'response' => json_decode($response, true)
        ]);
    }
}

}