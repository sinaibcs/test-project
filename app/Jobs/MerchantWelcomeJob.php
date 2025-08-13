<?php

namespace App\Jobs;

use App\Mail\MerchantWelcomeMail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Mail;

class MerchantWelcomeJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    public $merchantWelcomeMail,$merchantEmail;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($merchantWelcomeMail, $merchantEmail)
    {
        $this->merchantWelcomeMail = $merchantWelcomeMail;
        $this->merchantEmail = $merchantEmail;
    }


    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        Mail::to($this->merchantEmail)->send(new MerchantWelcomeMail($this->merchantWelcomeMail));
    }
}
