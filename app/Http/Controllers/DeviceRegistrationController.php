<?php

namespace App\Http\Controllers;

use App\Exceptions\AuthBasicErrorException;
use App\Models\Device;
use App\Models\User;
use Cache;
use App\Http\Traits\MessageTrait;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class DeviceRegistrationController extends Controller
{
    use MessageTrait;

    public function deviceRegistration(Request $request)
    {
//        Log::info('Device Registration Request', [
//            'user_id' => $request->user_id, // Adjust these based on the actual fields
//            'device_token' => $request->device_token,
//            'device_name' => $request->device_name,
//            'device_type' => $request->device_type,
//            // Add any other fields you want to log
//        ]);
        $validatedData = $request->validate([
            'username' => 'required|string',
            'password' => 'required|string|min:6',
        ]);

        // Step 2: Check user credentials
        $user = User::where('username', $validatedData['username'])->first();

        if (!$user) {
            return response()->json(['error' => 'Credentials does not match'], 401);
        }
        if ($user->status == 0) {
            return response()->json(['error' => 'User is not activated yet. Please contact with your system admin.'], 401);
        }
        if (!$user || !Hash::check($user->salt . $request->password, $user->password)) {
            return response()->json(['error' => 'Invalid credentials'], 401);
        }

        // check OTP
        $code = $request->otp;
        $cachedCode = Cache::get($this->userLoginOtpPrefix . $user->id);
        if (!$cachedCode || $code != $cachedCode) {
            throw new AuthBasicErrorException(
                Response::HTTP_UNPROCESSABLE_ENTITY,
                'verify_failed',
                "Verification code invalid !",
                        );
        }

        // Step 3: Check if the user is associated with any device
        $device = Device::where('user_id', $user->id)->where('device_id', $request->device_token)->first();
        if ($device) {
            if ($device->status == 0) {
                return response()->json(['error' => 'This device registration request is pending. Please contact with your system admin.'], 409);
            } else {
                return response()->json(['error' => 'This device has already been registered'], 409);
            }
        }
        if (!$device) {
            $deviceType = 4; // Default to Laptop
            $deviceName = "Laptop";

            if ($request->device_type == "Desktop") {
                $deviceType = 1;
                $deviceName = "Desktop";
            } else if ($request->device_type == "Android Mobile") {
                $deviceType = 2;
                $deviceName = "Android Mobile";
            } else if ($request->device_type == "iOS Mobile") {
                $deviceType = 3;
                $deviceName = "iOS Mobile";
            }

            // Use the create method to insert a new device
            $device = Device::create([
                'user_id' => $user->id,
                'name' => $user->username,
                'device_id' => $request->device_token,
                'ip_address' => $request->device_ip,
                'purpose_use' => $request->purpose_of_use,
                'device_type' => $deviceType,
                'device_name' => $deviceName,
                'status' => 0,
                'createdBy' => $user->id,
            ]);

            return response()->json(['message' => 'This device has been registered successfully', 'device' => $device], 201);
        }

        // Successful response
        return response()->json([
            'message' => 'Device registration successful',
            'device' => $device,
            'user' => $user,
        ]);
    }

    public function getIpAddress()
    {
        $ip = file_get_contents('https://api.ipify.org?format=json');
        return response()->json(json_decode($ip), 200);
    }
}
