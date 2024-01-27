<?php

namespace DTApi\Jobs;

use App\Jobs\SendSMSHelper;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use function App\Jobs\env;

class NotifyTranslator implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $translators;

    public $message;

    /**
     * Create a new job instance.
     */
    public function __construct($translators, $message)
    {
        $this->translators = $translators;
        $this->message = $message;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // send messages via sms handler
        foreach ($this->translators as $translator) {
            // send message to translator
            $status = SendSMSHelper::send(env('SMS_NUMBER'), $translator->mobile, $this->message);
            Log::info(
                sprintf("Send SMS to %s (%s) status: %s",
                    $translator->email, $translator->mobile, print_r($status, true)
                )
            );
        }
    }
}
