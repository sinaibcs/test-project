<?php

namespace App\Http\Traits;

trait MessageTrait
{
    //auth error codes
    protected $employeeEmailVerificationPrefix = 'employee_email_';
    protected $employeeEmailVerificationOtpPrefix = 'employee_email_otp';
    protected $employeeEmailVerificationDirectPrefix = 'employee_email_own_verify_';
    protected $merchantEmailVerificationPrefix = 'merchant_email_';
    protected $merchantEmailVerificationOtpPrefix = 'merchant_email_otp';
    protected $merchantEmailVerificationDirectPrefix = 'merchant_email_own_verify_';
    protected $merchantPhoneOtpPrefix = 'merchant_phone_';
    protected $riderEmailVerificationPrefix = 'rider_email_';
    protected $riderEmailVerificationOtpPrefix = 'rider_email_otp';
    protected $riderEmailVerificationDirectPrefix = 'rider_email_own_verify_';
    //auth success code
    protected $adminEmailVerificationPrefix = 'admin_email_';

    //non allowed user error codes
    protected $adminEmailVerificationOtpPrefix = 'admin_email_otp';

    //approval pending error code
    protected $adminEmailVerificationDirectPrefix = 'admin_email_own_verify_';
    protected $userForgetPasswordPrefix = 'user_forget_password_';
    //account verification otp name prefix
    private $authDeactivateUserErrorCode = 1;
    // text error code


    //email verification code prefix
    private $authBannedUserErrorCode = 10;
    private $authUnverifiedUserErrorCode = 2;
    private $authRegStepOneErrorCode = 7;
    private $authRegStepTwoErrorCode = 8;
    private $authRegStepThreeErrorCode = 9;
    private $authRegStepFourErrorCode = 101;
    private $authBasicErrorCode = 3;
    private $authDefaultPasswordErrorCode = 11;
    private $authInactiveUserErrorCode = 12;
    private $authSuccessCode = 4;
    private $nonAllowedUserErrorCode = 5;
    private $accountNotApprovedErrorCode = 6;
    private $accessTokenSpaDevice = 'spa';
    //forget password prefix
    private $otpPrefix = 'otp_';
    private $NonAllowedAdminTextErrorCode = 'user_non_admin';
    private $bannedUserTextErrorCode = 'user_banned';
    private $inactiveUserTextErrorCode = 'user_inactive';
    private $defaultPasswordTextErrorCode = 'user_default_password';
    private $authUnverifiedUserTextErrorCode = 'user_email_unverified';
    private $authWrongCredentialTextErrorCode = 'wrong_email_or_password';
    private $employeeAlreadyBranchAdminTextErrorCode = 'employee_already_branch_admin';
    private $authMerchantEmailNotExistsTextErrorCode = 'email_not_found';
    private $authMerchantPhoneNotExistsTextErrorCode = 'phone_not_found';
    private $authEmailAlreadyVerifiedUserTextErrorCode = 'user_email_already_verified';
    private $authEmailNotVerifiedUserTextErrorCode = 'user_email_not_verified';
    private $authPhoneNotVerifiedUserTextErrorCode = 'user_phone_not_verified';
    private $authPhoneAlreadyVerifiedUserTextErrorCode = 'user_phone_already_verified';
    private $authUserAccountPendingTextErrorCode = "user_account_pending";
    private $authUserAccountBannedTextErrorCode = "user_account_banned";
    private $authUserAccountRejectedTextErrorCode = "user_account_rejected";
    private $authUserAccountDeactivateTextErrorCode = "user_account_deactivate";
    private $applicantMaritalStatusTextErrorCode = "applicant_marital_status";
    private $applicantGenderTypeTextErrorCode = "applicant_gender_type";
    private $applicantAgeLimitTextErrorCode = "applicant_age_limit";
    private $applicantExistsErrorCode = "applicant_exists";

    private $authExpiredCodeTextErrorCode = 'expired_code';
    private $authInvalidCodeTextErrorCode = 'invalid_code';

    private $userLoginOtpPrefix = "user_login_otp_";
    private $userForgotOtpPrefix = "user_forgot_otp_";

    private $authUserAccountNotPendingTextErrorCode = 'user_account_not_pending';

    private $userAccountNotPendingMessage = 'User Account Not Pending';

    private $applicantMaritalStatusMessage = 'Applicant Marital Status dose not match!';
    private $applicantAgeLimitMessage = 'Applicant Age Limit does not match!';
    private $applicationGenderTypeMessage = 'Applicant Gender is not allowed!';
    private $applicantExistsMessage = 'Applicant already Exists!';

    private $authExpiredCodeMessage = 'Expired Code!';
    private $authInvalidCodeMessage = 'Invalid Code!';

    // already email verified
    private $alreadyEmailVerifiedMessage = 'Email is Already verified!';
    // already phone verified
    private $alreadyPhoneVerifiedMessage = 'Phone is Already verified!';
    private $notEmailVerifiedMessage = 'Email is not verified!';
    private $merchantUserTypeMessage = 'Merchant User Not Found!';
    private $notPhoneVerifiedMessage = 'Phone is not verified!';
    private $userAccountPendingMessage = 'User Account Pending';
    private $userAccountBannedMessage = 'User Account Banned';
    private $userAccountRejectedMessage = 'User Account Rejected';
    private $userAccountDeactivateMessage = 'User Account Deactivate';


    //insert success
    private $insertSuccessMessage = 'Insert Success';
    //update success
    private $updateSuccessMessage = 'Update Success';
    private $deleteSuccessMessage = 'Delete Success';
    //fetch success
    private $fetchSuccessMessage = 'Operation successful';
    private $fetchFailedMessage = 'Operation unsuccessful';
    private $appicantSuccessMessage = 'NID information verified successfully';
    private $nidSuccessMessage = ' Nominee NID information verified successfully';

    private $fetchDataSuccessMessage = 'Data Fetch Successfully Done';
    private $otpSendMessage = 'Otp Send Successfully';
    //not found
    private $unverifiedUserErrorResponse = 'Please Verify Your Account to login';
    private $NonAllowedAdminErrorResponse = 'please try to login your application';
    private $bannedUserErrorResponse = 'Please contact administrator to re-activate your account';
    private $inactiveUserErrorResponse = 'Please contact administrator to re-activate your account';

    private $defaultPasswordErrorResponse = 'please change your default password';

    private $emailVerifySuccessMessage = 'Email verification Completed!';
    private $phoneVerifySuccessMessage = 'Phone verification Completed!';
    private $notFoundMessage = 'Not found!';


    /**
     * Email From mails And Subjects
     * */
    private $WebSiteName = "CTM";

    private $EmployeeRegisterMailFrom = "example@gmail.com";
    private $InfoMailFrom = "info@metroexpress.com.bd";
    private $EmployeeRegisterMailName = "CTM ";
    private $EmployeeRegisterMailSubject = "Thanks for Joining CTM!!";
    private $MerchantEmailVerifyMailSubject = "Merchant Email Verify Mail";


}
