<?php

namespace App\Services;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Support\Facades\Session;
use Exception;
use App\Models\Notification;
use Illuminate\Http\Request;
use App\Http\Responses\Response;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\ValidationException;
use Illuminate\Http\JsonResponse;
use Throwable;

class NotificationService
{


public function getUserNotifications($userId)
{
   Notification::where('user_id', $userId)
        ->where('read_at', false)
        ->update(['read_at' => true]);

    $notifications = Notification::where('user_id', $userId)
        ->orderBy('created_at', 'desc')
        ->get();

        $message = "all notifications retrived successfully";

    return ['notifications' => $notifications  , 'message' => $message];
}

    public function getUnreadCount($userId)
    {
        $count = Notification::where('user_id', $userId)
            ->where('read_at', false)
            ->count();

            $message = "count of unread notifications retrived successfully";

            return ['unread_notifications_count' => $count  , 'message' => $message];

    }

}