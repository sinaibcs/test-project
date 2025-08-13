<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class CaptchaVerified implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if(request()->captcha_token == null){
            $fail('Captcha token is required');
        }
        if(request()->captcha_value == null){
            $fail('Captcha value is required');
        }
        if(request()->captcha_token == null || request()->captcha_value == null){
            return;
        }
        $valid = app('captcha')->check_api(request()->captcha_value, request()->captcha_token, 'default');
        if(!$valid){
            $fail('Invalid CAPTCHA');
        }
    }
}
