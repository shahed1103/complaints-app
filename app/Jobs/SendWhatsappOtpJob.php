<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendWhatsappOtpJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;
    public $timeout = 10;

    /**
     * Create a new job instance.
     */
    public function __construct(private string $phone , private int $otp){}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $instance = env('ULTRA_MSG_INSTANCE');
        $token = env('ULTRA_MSG_TOKEN');

        $url = "https://api.ultramsg.com/instance151958/messages/chat";

        $client = new \GuzzleHttp\Client();

        $client->post($url, [
            'form_params' => [
                'token' => $token,
                'to' => $this->phone,          
                'body' => "رمز التحقق الخاص بك هو: $this->otp",
            ]
        ]);
    }
}
