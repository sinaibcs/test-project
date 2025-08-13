<?php
namespace App\Http\Controllers\Api\V1;

use Illuminate\Http\Request;
use Mews\Captcha\Facades\Captcha;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Crypt;

class CaptchaController extends Controller
{
    // Generates and serves the CAPTCHA image as a URL
    public function getCaptcha()
    {
        $captchaText = app('captcha')->create('default', true);
        $image = base64_encode($captchaText['img']);
        // dd($captchaText['img']);
    
        // Encrypt the CAPTCHA answer using Laravel's encryption helper
        $captchaToken = $captchaText['key'];
    
        // Return the base64-encoded CAPTCHA image and encrypted answer
        return response()->json([
            'captcha_token' => $captchaToken,
            'captcha_image' => "data:image/png;base64,{$image}",
        ]);
    }

    // Validates the CAPTCHA input from the frontend
    public function validateCaptcha(Request $request)
    {
        $request->validate([
            'captcha_input' => 'required',
            'captcha_token' => 'required',
        ]);

        $valid = app('captcha')->check_api($request->captcha_input, $request->captcha_token, 'default');
    
        if ($valid) {
            return response()->json(['success' => true]);
        } else {
            return response()->json(['error' => 'Invalid CAPTCHA'], 422);
        }
    }
}
