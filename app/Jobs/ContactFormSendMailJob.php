<?php

namespace App\Jobs;

use App\Mail\ContactFormSendMail;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\Mail;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Config;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Contracts\Queue\ShouldBeUnique;

class ContactFormSendMailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    /**
     * Create a new job instance.
     */
    public function __construct(public $info)
    {
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Mail::to(env('MAIL_TO_ADDRESS'))->later(
            now(),
            new ContactFormSendMail($this->info)
        );
    }
}
