<?php

namespace App\Jobs;

use App\Mail\SendCodeResetPassword;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendResetPasswordCodeJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;
    public $timeout = 10;

    /**
     * Create a new job instance.
     */
    public function __construct(private string $email , private int $code){}


    /**
     * Execute the job.
     */
    public function handle(): void{
        Mail::to($this->email)
            ->send(new SendCodeResetPassword($this->code));
    }
}
