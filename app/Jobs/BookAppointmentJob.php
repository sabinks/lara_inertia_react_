<?php

namespace App\Jobs;

use App\Mail\Admin\BookAppointmentCreatedMail;
use App\Mail\Client\BookAppointmentCreatedMail as ClientBookAppointmentCreatedMail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class BookAppointmentJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(public $bookAppointment)
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $message = (new BookAppointmentCreatedMail($this->bookAppointment))->onQueue('default');
        $result = Mail::to($this->bookAppointment->email)->queue($message);

        $message = (new ClientBookAppointmentCreatedMail($this->bookAppointment))->onQueue('default');
        $result = Mail::to(env('MAIL_TO_ADDRESS'))->queue($message);
    }
}
