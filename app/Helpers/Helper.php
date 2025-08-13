<?php

namespace App\Helpers;

use App\Http\Traits\MessageTrait;
use Cache;
use Illuminate\Support\Str;
use Jenssegers\Agent\Agent;
use Stevebauman\Location\Facades\Location;

class Helper
{
    use MessageTrait;

    public static function GeneratePassword()
    {
        $password = Str::random(8); // Generate a random 10-character password

        // Add special characters to the password
        $specialCharacters = '!@#$%^&*()';
        $randomSpecialCharacter = $specialCharacters[rand(0, strlen($specialCharacters) - 1)];
        $password .= $randomSpecialCharacter;
        // $password = Str::random(8);
        return $password;
    }
    public static function GenerateFourDigitNumber()
    {
        $fourDigitNumber = random_int(1000, 9999);
        return $fourDigitNumber;
    }
    public static function FinancialYear()
    {
        $currentDate = now();
        $startOfFinancialYear = $currentDate->month >= 4 ? $currentDate->year : $currentDate->year - 1;
        $endOfFinancialYear = $startOfFinancialYear + 1;

        return "{$startOfFinancialYear}-{$endOfFinancialYear}";
    }

    public static function generateSalt()
    {

        $salt = Str::random(20) . Str::random(10);
        return $salt;
    }

    public static function lang($en, $bn){
        if(request()->lang == 'bn'){
            return $bn;
        }
        return $en;
    }


    public static function englishToBangla($number)
    {

        $banglaDigits = ['০', '১', '২', '৩', '৪', '৫', '৬', '৭', '৮', '৯'];
        $englishDigits = range(0, 9);

        $banglaNumber = str_replace($englishDigits, $banglaDigits, $number);

        $banglaNumber = str_replace(['AM', 'PM'], ['এএম', 'পিএম'], $banglaNumber);

        return $banglaNumber;
    }

    public static function banglaToEnglish($number)
    {

        $banglaDigits = ['০', '১', '২', '৩', '৪', '৫', '৬', '৭', '৮', '৯'];
        $englishDigits = range(0, 9);

        $banglaNumber = str_replace($banglaDigits, $englishDigits, $number);

        return $banglaNumber;
    }

    public static function urlToBase64($url)
    {
        // return null;
        $url = env('FILE_VIEW_ROOT')."/".$url;

        // if (!preg_match("/^https?:\/\//", $url)) {
        //     $url = env('FILE_VIEW_ROOT') . "/" . $url;
        // }
        // dd($url);
        try{
            $handle = fopen($url, 'rb');
            $contents = stream_get_contents($handle);
            fclose($handle);
            $imageType = getimagesizefromstring($url);
            $imageBase64 = 'data:' . $imageType . ';base64,' . base64_encode($contents);

            return   $imageBase64;
        }catch(\Throwable $t){
            return null;
        }
    }

    public static function clientIp()
    {
        foreach (array('HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_FORWARDED', 'HTTP_X_CLUSTER_CLIENT_IP', 'HTTP_FORWARDED_FOR', 'HTTP_FORWARDED', 'REMOTE_ADDR') as $key) {
            if (array_key_exists($key, $_SERVER) === true) {
                foreach (explode(',', $_SERVER[$key]) as $ip) {
                    $ip = trim($ip); // just to be safe
                    if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) !== false) {
                        return $ip;
                    }
                }
            }
        }
        return request()->ip();
    }

    public static function BrowserIpInfo()
    {
        $agent = new Agent();
        $agentinfo = $agent->browser() . " in " . $agent->platform();
        if ($agentinfo == ' in ') {
            $agentinfo =  request()->header('User-Agent');
        }
        $ip = self::clientIp();
        $currentUserInfo = Location::get($ip);

        if ($currentUserInfo == true) {
            $data = [
                'User Agent' => $agentinfo,
                'Browser' => $agent->browser(),
                'Platform' => $agent->platform(),
                'Device Type' => $agent->deviceType(),
                'Ip Address' => $ip,
                'Country Name' => $currentUserInfo->countryName ?? null,
                'Country Code' => $currentUserInfo->countryCode ?? null,
                'Region Name' => $currentUserInfo->regionName ?? null,
                'City Name' => $currentUserInfo->cityName ?? null,
                'Latitude' => $currentUserInfo->latitude ?? null,
                'Longitude' => $currentUserInfo->longitude ?? null,
                'Timezone' => $currentUserInfo->timezone ?? null,
            ];
            //            $jsonString = json_encode($data);
        } else {
            $data = [
                'User Agent' => $agentinfo,
                'Browser' => $agent->browser(),
                'Platform' => $agent->platform(),
                'Device Type' => $agent->deviceType(),
                'Ip Address' => $ip,
                'Country Name' => $currentUserInfo->countryName ?? null,
                'Country Code' => $currentUserInfo->countryCode ?? null,
                'Region Name' => $currentUserInfo->regionName ?? null,
                'City Name' => $currentUserInfo->cityName ?? null,
                'Latitude' => $currentUserInfo->latitude ?? null,
                'Longitude' => $currentUserInfo->longitude ?? null,
                'Timezone' => $currentUserInfo->timezone ?? null,
            ];
            //            $jsonString = json_encode($data);
        }
        return $data;
    }

    public static function activityLogInsert($newData, $beforeUpdateData, $logName, $logDescription)
    {

        $changesWithPreviousValues = [
            'previous' => null,
            'new' => $newData,
        ];

        activity($logName)
            ->causedBy(auth()->user())
            ->performedOn($newData)
            ->withProperties(['changes' => $changesWithPreviousValues, 'userInfo' => Helper::BrowserIpInfo()])
            ->log($logDescription);
    }

    public static function activityLogInsertCustomPerforme($newData, $beforeUpdateData, $performedOn, $logName, $logDescription)
    {

        $changesWithPreviousValues = [
            'previous' => null,
            'new' => $newData,
        ];

        activity($logName)
            ->causedBy(auth()->user())
            ->performedOn($performedOn)
            ->withProperties(['changes' => $changesWithPreviousValues, 'userInfo' => Helper::BrowserIpInfo()])
            ->log($logDescription);
    }
    public static function activityLogUpdate($newData, $beforeUpdateData, $logName, $logDescription)
    {

        $changes = $newData->getChanges();
        $previousValues = [];
        $newValues = [];
        foreach ($changes as $attribute => $newValue) {
            $previousValues[$attribute] = $beforeUpdateData->$attribute ?? null;
            $newValues[$attribute] = $newValue;
        }
        $changesWithPreviousValues = [
            'previous' => $previousValues,
            'new' => $newValues,
        ];

        activity($logName)
            ->causedBy(auth()->user())
            ->performedOn($newData)
            ->withProperties(['changes' => $changesWithPreviousValues, 'userInfo' => Helper::BrowserIpInfo()])
            ->log($logDescription);
    }
    public static function activityLogSpecificColumnUpdate($newData, $beforeUpdateData, $performedOn, $logName, $logDescription)
    {

        $previousValues = [];
        $newValues = [];
        foreach ($newData as $attribute => $newValue) {
            $previousValues[$attribute] = $beforeUpdateData;
            $newValues[$attribute] = $newValue;
        }
        $changesWithPreviousValues = [
            'previous' => $previousValues,
            'new' => $newValues,
        ];

        activity($logName)
            ->causedBy(auth()->user())
            ->performedOn($performedOn)
            ->withProperties(['changes' => $changesWithPreviousValues, 'userInfo' => Helper::BrowserIpInfo()])
            ->log($logDescription);
    }

    public static function activityLogDelete($newData, $beforeUpdateData, $logName, $logDescription)
    {

        $changesWithPreviousValues = [
            'previous' => null,
            'new' => $newData,
        ];

        activity($logName)
            ->causedBy(auth()->user())
            ->withProperties(['changes' => $changesWithPreviousValues, 'userInfo' => Helper::BrowserIpInfo()])
            ->log($logDescription);
    }

    public static function uploadImage($image, $destinationPath)
    {
        // $imageName = time() . '.' . $image->getClientOriginalExtension();
        // $image->move(public_path($destinationPath), $imageName);
        $img = $image->store($destinationPath);
        // dd($img);
        return $img;
        // return $destinationPath . '/' . $imageName;
    }
}
