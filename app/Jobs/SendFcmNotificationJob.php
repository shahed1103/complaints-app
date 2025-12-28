<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use App\Http\Controllers\FcmController;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Http\Request;

class SendFcmNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(private int $userId , private string $title , private string $body) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $request = new Request([
            'user_id' => $this->userId,
            'title'   => $this->title,
            'body'    => $this->body,
        ]);

        (new FcmController())->sendFcmNotification($request);
    }
}
