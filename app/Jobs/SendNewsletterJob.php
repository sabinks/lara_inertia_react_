<?php

namespace App\Jobs;

use App\Mail\SendNewsetterMail;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\Mail;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Contracts\Queue\ShouldBeUnique;

class SendNewsletterJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    public $user, $title, $content;

    public function __construct(User $user, $newsletter)
    {
        $this->user = $user;
        $this->title = $newsletter->name;
        $this->content = $newsletter->content;
    }

    public function handle(): void
    {
        $message = (new SendNewsetterMail($this->user, $this->title, $this->content))->onQueue('newsletter');
        Mail::to($this->user->email)->queue($message);
    }
}
