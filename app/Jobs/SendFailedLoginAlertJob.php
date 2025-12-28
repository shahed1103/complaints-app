<?php

namespace App\Jobs;

use App\Models\User;
use App\Mail\FailedLoginAlertMail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendFailedLoginAlertJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public User $user;

    public function __construct(User $user)
    {
        $this->user = $user;
    }

    public function handle(): void
    {
        if (!$this->user->email) {
            return;
        }

        Mail::to($this->user->email)
            ->send(new FailedLoginAlertMail($this->user));
    }
}
