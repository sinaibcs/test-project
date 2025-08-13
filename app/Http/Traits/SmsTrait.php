<?php

namespace App\Http\Traits;

trait SmsTrait
{
    private $userRegOtpTemplate = 'Thank you for registering on our platform. Your OTP for verification is {OTP}. It will expire in 10 minutes. Please enter this OTP on the registration page to complete the verification process. Thank you!';
    private $userMailRegOtpTemplate = "Thank you for registering as a merchant on our platform. We have sent a one-time passcode (OTP) to your registered email address for verification purposes. <br/> <br/> Please enter the <h3> {OTP} </h3> on the registration page to complete the verification process. <br/> <br/> The OTP will expire in 10 minutes. If you didn't receive the OTP, please check your spam folder or request for a new one. Thanks for your cooperation!";


    private $userMailAfterRegTemplate = " <div> <p>Dear {FULL_NAME},</p> <br/>
    <p>Thank you for signing up with us! We are excited to have you on board.</p>
    <br/><br/>
    <p>Your merchant account is currently under review. We will inform you as soon as it is approved. In the meantime, if you have any questions or concerns, please feel free to reach out to us.</p>
    <br/><br/>
    <p>Thank you for your patience and we look forward to working with you.</p>
    <br/>
    Best Regards,
    <br/>
    {Company_Name}
    </div>
    ";

    private $userSmsAfterRegTemplate = "Welcome to our platform! Your merchant account is being reviewed for approval. We appreciate your patience and will notify you as soon as your account is active. Thank you for signing up!";
}
