<?php

namespace App\Jobs;

use App\Mail\MerchantEmapilVerifyMail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Mail;

class MerchantEmapilVerifyJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    public $MerchantEmail,$code,$GlobalSettings;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($MerchantEmail,$code,$GlobalSettings)
    {
        $this->MerchantEmail = $MerchantEmail;
        $this->code = $code;
        $this->GlobalSettings = $GlobalSettings;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
Mail::to($this->MerchantEmail)->send(new MerchantEmapilVerifyMail($this->MerchantEmail,$this->code,$this->GlobalSettings));
    }
}
