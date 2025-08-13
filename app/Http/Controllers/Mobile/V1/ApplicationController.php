<?php

namespace App\Http\Controllers\Mobile\V1;

use Carbon\Carbon;
use App\Models\Lookup;
use App\Helpers\Helper;
use App\Jobs\SendEmail;
use App\Models\Location;
use App\Models\Committee;
use App\Models\Application;
use App\Models\Beneficiary;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Traits\RoleTrait;
use App\Models\MobileOperator;
use App\Rules\CaptchaVerified;
use App\Models\AllowanceProgram;
use App\Http\Traits\MessageTrait;
use App\Http\Traits\LocationTrait;
use App\Mail\ApplicationEntryMail;
use Illuminate\Support\Facades\DB;
use App\Models\CommitteePermission;
use Illuminate\Support\Facades\Log;
use App\Constants\ApplicationStatus;
use App\Constants\BeneficiaryStatus;
use App\Http\Controllers\Controller;
use App\Models\BeneficiaryVerifyLog;
use Illuminate\Support\Facades\Mail;
use App\Http\Traits\BeneficiaryTrait;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use App\Exceptions\AuthBasicErrorException;
use App\Http\Services\Notification\SMSservice;
use Illuminate\Validation\ValidationException;
use Mccarlosen\LaravelMpdf\Facades\LaravelMpdf;
use App\Http\Services\Admin\Application\NIDService;
use App\Http\Requests\Mobile\Application\ApplicationRequest;
use App\Http\Services\Mobile\Application\ApplicationService;
use App\Http\Requests\Mobile\Application\UpdateStatusRequest;
use App\Http\Services\Mobile\Application\VerificationService;
use App\Http\Services\Mobile\Application\CommitteeListService;
use App\Http\Services\Mobile\BudgetAllotment\AllotmentService;
use App\Http\Requests\Mobile\Application\MobileOperatorRequest;
use App\Http\Services\Mobile\Application\MobileOperatorService;
use App\Http\Resources\Mobile\Application\MobileOperatorResource;
use App\Http\Requests\Mobile\Application\ApplicationVerifyRequest;
use App\Http\Services\Mobile\Application\OfficeApplicationService;
use App\Http\Requests\Mobile\Application\MobileOperatorUpdateRequest;
use App\Http\Services\Mobile\Application\CommitteeApplicationService;



class ApplicationController extends Controller
{
    use MessageTrait, BeneficiaryTrait, LocationTrait, LocationTrait, RoleTrait;
    private $applicationService;

    public function __construct(ApplicationService $applicationService, MobileOperatorService $mobileoperatorService, public SMSservice $SMSservice)
    {
        $this->applicationService = $applicationService;
        $this->mobileoperatorService = $mobileoperatorService;
    }

    public function getBeneficiaryByLocation()
    {
        $beneficiaries = $this->getBeneficiary();
        $applications = $this->applications();
    }

    /* -------------------------------------------------------------------------- */
    /*                         online application Methods                         */
    /* -------------------------------------------------------------------------- */

    /**
     *
     * @OA\Post(
     *      path="/global/online-application/card-verification",
     *      operationId="onlineApplicationVerifyCard",
     *      tags={"GLOBAL"},
     *      summary="Check Application Card",
     *      description="Check Application Card",
     *
     *       @OA\RequestBody(
     *          required=true,
     *          description="enter inputs",
     *
     *            @OA\MediaType(
     *              mediaType="multipart/form-data",
     *           @OA\Schema(
     *                   @OA\Property(
     *                      property="verification_type",
     *                      description="verification type",
     *                      type="text",
     *
     *                   ),
     *                   @OA\Property(
     *                      property="verification_number",
     *                      description="verification card number",
     *                      type="text",
     *                   ),
     *                   @OA\Property(
     *                      property="date_of_birth",
     *                      description="birth date",
     *                      type="text",
     *                   ),
     *
     *                 ),
     *             ),
     *
     *         ),
     *
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(
     *          response=201,
     *          description="Successful Insert operation",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthenticated",
     *      ),
     *      @OA\Response(
     *          response=403,
     *          description="Forbidden"
     *      ),
     *      @OA\Response(
     *          response=422,
     *          description="Unprocessable Entity",
     *
     *          )
     *        )
     *     )
     *
     */
    private function calculateAge($dob)
    {
        $birthDate = Carbon::parse($dob);  // Parse date using Carbon
        $age = $birthDate->diffInYears(Carbon::now());  // Get age in years
        $months = $birthDate->diffInMonths(Carbon::now()) % 12;  // Get remaining months
        return $age . '.' . $months;  // returns "age.year" format
    }

    // Function to map the input data to the required structure
    private function mapData($inputData, $preData)
    {
        $mappedData = [];

        // Map national ID
        $mappedData['nid'] = $preData['nid'] ?? '';

        // Map date of birth
        $mappedData['dob'] = $preData['dob'] ?? '';

        // Map name fields
        $mappedData['name'] = $inputData['name'] ?? '';
        $mappedData['nameEn'] = $inputData['nameEn'] ?? '';

        // Map gender (if available in the input data)
        if (!empty($inputData['gender'])) {
            $mappedData['gender'] = $inputData['gender'] == 'male' ? 'পুরুষ' : 'নারী';  // Assuming 'male' or 'female' values
        } else {
            $mappedData['gender'] = 'অজানা';  // Unknown if not provided
        }

        // Calculate and map age if the date of birth is available
        if (!empty($preData['dob'])) {
            $mappedData['age'] = $this->calculateAge($preData['dob']);
        } else {
            $mappedData['age'] = '';
        }

        return $mappedData;
    }
    public function onlineApplicationVerifyCard(ApplicationVerifyRequest $request)
    {
        $paylodData = [
            'nid' => $request->verification_number,
            'name' => $request->name_bn,
            'nameEn' => $request->name_en,
            'dob' => $request->date_of_birth,
        ];

        // $data = (new VerificationService)->callVerificationApi($data);
        // $data = (new NIDService)->getInfo($data);
        $data = (new NIDService)->getInfo($paylodData);
        if($data == false){
            return response()->json([
                'status' => false,
                'data' => null,
                'message' => "NID information verification failed",
            ], 400);
        }

        if($data->json('verified') == false){
            return response()->json([
                'status' => false,
                'data' => $data->json('fieldVerificationResult'),
                'message' => "NID information mismatched",
            ], 400);
        }
        // $data = $this->mapData($data, $paylodData);
//        $gender_id = '';
//        $Lookup = Lookup::where('value_bn', 'পুরুষ')->orWhere('value_en', 'male')->first();
//        $gender_id = $Lookup['id'];

        // if (request('program_id') && $gender_id) {
        //     $this->verifyAge($data);
        // }

        return response()->json([
            'status' => true,
            // 'data' => $data,
            'message' => $this->appicantSuccessMessage,
        ], 200);
    }

    // application tracking function
    public function applicationTracking(Request $request)
    {
        $application = Application::with('program', 'committeeApplication')
            ->where('application_id', '=', $request->tracking_no)
            ->orWhere('verification_number', '=', $request->nid)
            ->Where('date_of_birth', '=', $request->date_of_birth)
            ->first();

        return response()->json([
            'status' => true,
            'data' => $application,
            'message' => $this->fetchSuccessMessage,
        ], 200);

    }

    public function verifyAge($nidInfo)
    {
        $gender_id = '';
        $Lookup = Lookup::where('value_bn', 'পুরুষ')->orWhere('value_en', 'male')->first();
        if ($nidInfo['gender'] == 'পুরুষ') {
            $gender_id = $Lookup['id'];
        } else {
            $gender_id = $Lookup['id'];
        }
        $allowance = AllowanceProgram::find(request('program_id'));

        if ($allowance->is_age_limit == 1) {
            // error code => applicant_marital_status
            if (!in_array($gender_id, $allowance->ages->pluck('gender_id')->toArray())) {
                throw new AuthBasicErrorException(
                    Response::HTTP_UNPROCESSABLE_ENTITY,
                    $this->applicantGenderTypeTextErrorCode,
                    $this->applicationGenderTypeMessage
                );
            } else {
                $genderAge = $allowance->ages->where('gender_id', $gender_id)->first();
                $minAge = $genderAge->min_age;
                $maxAge = $genderAge->max_age;
                $age = $nidInfo['age'];

                if ($age < $minAge || $age > $maxAge) {
                    throw new AuthBasicErrorException(
                        Response::HTTP_UNPROCESSABLE_ENTITY,
                        $this->applicantAgeLimitTextErrorCode,
                        $this->applicantAgeLimitMessage
                    );
                }
            }
        }

    }

    public function nomineeVerifyNID(Request $request)
    {
        $request->validate([
            'captcha_value' => ['required', new CaptchaVerified()],
            'verification_number' => 'required',
            'date_of_birth' => 'required|date',
            'name_en' => 'required',
            'name_bn' => 'required'
        ]);


        $paylodData = [
            'nid' => $request->verification_number,
            'dob' => $request->date_of_birth,
            'name' => $request->name_bn,
            'nameEn' => $request->name_en,
        ];

        // $data = (new VerificationService)->callNomineeVerificationApi($data);
        $data = (new NIDService)->getInfo($paylodData);
        if($data == false){
            return response()->json([
                'status' => false,
                'data' => null,
                'message' => "NID information verification failed",
            ], 400);
        }

        if($data->json('verified') == false){
            return response()->json([
                'status' => false,
                'data' => $data->json('fieldVerificationResult'),
                'message' => "NID information mismatched",
            ], 400);
        }

        return response()->json([
            'status' => true,
            // 'data' => $data,
            'message' => $this->nidSuccessMessage,
        ], 200);
    }
    /**
     *
     * @OA\Post(
     *      path="/global/online-application/dis-card-verification",
     *      operationId="onlineApplicationVerifyDISCard",
     *      tags={"GLOBAL"},
     *      summary="Check Application Card",
     *      description="Check Application Card",
     *
     *       @OA\RequestBody(
     *          required=true,
     *          description="enter inputs",
     *
     *            @OA\MediaType(
     *              mediaType="multipart/form-data",
     *           @OA\Schema(
     *                   @OA\Property(
     *                      property="dis_no",
     *                      description="DIS number",
     *                      type="text",
     *                   ),
     *
     *                 ),
     *             ),
     *
     *         ),
     *
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(
     *          response=201,
     *          description="Successful Insert operation",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthenticated",
     *      ),
     *      @OA\Response(
     *          response=403,
     *          description="Forbidden"
     *      ),
     *      @OA\Response(
     *          response=422,
     *          description="Unprocessable Entity",
     *
     *          )
     *        )
     *     )
     *
     */
    public function onlineApplicationVerifyDISCard(Request $request)
    {
        $data = $this->applicationService->onlineApplicationVerifyCardDIS($request);

        return response()->json([
            'status' => true,
            'data' => $data,
            'message' => $this->fetchSuccessMessage,
        ], 200);
    }

    public function onlineApplicationRegistration(ApplicationRequest $request)
    {
//        Log::info("mobile server", $request->all());
        $allowanceAmount = 0;
        $allowance = AllowanceProgram::find($request->sub_program_id ?? $request->program_id);

        // check is marital
        if ($allowance) {
            if ($allowance->is_age_limit == 1) {
                // error code => applicant_marital_status
                if (!in_array($request->gender_id, $allowance->ages->pluck('gender_id')->toArray())) {
                    throw new AuthBasicErrorException(
                        Response::HTTP_UNPROCESSABLE_ENTITY,
                        $this->applicantGenderTypeTextErrorCode,
                        $this->applicationGenderTypeMessage
                    );
                } else {
                    // $data = [
                    //     'nid' => $request->verification_number,
                    //     'dob' => $request->date_of_birth,
                    // ];

                    // $nidInfo = (new NIDService)->getInfo($data);
                    // if ($nidInfo == false) {
                    //     return response()->json([
                    //         'status' => false,
                    //         'data' => null,
                    //         'message' => "NID information verification failed",
                    //     ], 400);
                    // }
                    // $nidInfo = $this->mapData($nidInfo, $data);

                    // $nidInfo = (new VerificationService())->callVerificationApi($data);

                    $genderAge = $allowance->ages->where('gender_id', $request->gender_id)->first();
                    $minAge = $genderAge->min_age;
                    $maxAge = $genderAge->max_age;
                    $age = Carbon::parse($request->date_of_birth)->age;

                    // get current age form date_of_birth field
                    // return $genderAge;
                    // 60 -90 => age is 73
                    // age range is minAge to maxAge
                    if ($age < $minAge || $age > $maxAge) {
                        throw new AuthBasicErrorException(
                            Response::HTTP_UNPROCESSABLE_ENTITY,
                            $this->applicantAgeLimitTextErrorCode,
                            $this->applicantAgeLimitMessage
                        );
                    }

                    $allowanceAmount = $genderAge->amount;
                }
            }



//            if ($allowance->is_marital == 1) {
//                // error code =>
//                if ($allowance->marital_status != $request->marital_status) {
//                    throw new AuthBasicErrorException(
//                        Response::HTTP_UNPROCESSABLE_ENTITY,
//                        $this->applicantMaritalStatusTextErrorCode,
//                        $this->applicantMaritalStatusMessage
//                    );
//                }
//            }


            if ($allowance->is_disable_class) {
                $allowanceAmount += $this->getClassWiseAmount($allowance, $request);
            }

        }



        $data = [
            'nid' => $request->nominee_verification_number,
            'dob' => $request->nominee_date_of_birth,
        ];
        if ($request->is_nominnee_optional == 1) {
            $nidInfo = (new NIDService)->getInfo($data);
            if ($nidInfo == false) {
                return response()->json([
                    'status' => false,
                    'data' => null,
                    'message' => "NID information verification failed",
                ], 400);
            }
            $nidInfo = $this->mapData($nidInfo, $data);
            //    $nidInfo = (new VerificationService())->callNomineeVerificationApi($data);

        }

        $checkSameApplicant = [
            'dob' => $request->date_of_birth,
            'father_name_en' => $request->father_name_en,
            'mother_name_en' => $request->mother_name_en
        ];

        $ifSameApplicant = Application::where('date_of_birth', '=', $checkSameApplicant['dob'])
            ->where('father_name_en', '=', $checkSameApplicant['father_name_en'])
            ->where('mother_name_en', '=', $checkSameApplicant['mother_name_en'])
            ->first();

        $ifSameBeneficiary = Beneficiary::where('date_of_birth', '=', $checkSameApplicant['dob'])
            ->where('father_name_en', '=', $checkSameApplicant['father_name_en'])
            ->where('mother_name_en', '=', $checkSameApplicant['mother_name_en'])
            ->first();

        if ($ifSameApplicant || $ifSameBeneficiary){
            throw new AuthBasicErrorException(
                Response::HTTP_UNPROCESSABLE_ENTITY,
                $this->applicantExistsErrorCode,
                $this->applicantExistsMessage
            );
        }



        $data = $this->applicationService->onlineApplicationRegistration($request, $allowanceAmount);
        $programName = AllowanceProgram::where('id', $data->program_id)->first('name_bn');
        $programName = $programName->name_bn;

        $trackingLink = env('APP_FRONTEND_URL') . '/application-tracking';
        //        $message = "Dear $data->name_en. "."Your application submitted for the $programName successfully."."Your tracking ID:".$data->application_id ."\n Please visit :"."\n {$trackingLink}"."\n Sincerely,"."DSS, MoSW";

        $message = "আপনার " . $programName . " আবেদন সফলভাবে দাখিল হয়েছে। আবেদনের ট্রাকিং আইডি " . $data->application_id . "। আবেদনের অবস্থা জানতে ভিজিট করুন:" . "\n {$trackingLink} " . "\n
ধন্যবাদ। সমাজসেবা অধিদপ্তর";

//        $this->SMSservice->sendConfirmationSms($data->mobile, $message);
//        if ($data->email) {
//            Mail::to($data->email)->send(new ApplicationEntryMail($data));
//        }

        activity("Online Application Submit")
            ->withProperties(['userInfo' => Helper::BrowserIpInfo(), 'data' => $data])
            ->log("Online Application Submit");
        // return $data->application_id;
        return response()->json([
            'status' => true,
            'data' => $data,
            'id' => $data->id,
            'application_id' => $data->application_id,
            'message' => $this->insertSuccessMessage,
        ], 200);

    }
    public function onlineApplicationEditedRegistration(ApplicationUpdateRequest $request)
    {
        // return $request->all();

        $allowance = AllowanceProgram::find($request->program_id);

        // check is marital
        if ($allowance) {
            if ($allowance->is_age_limit == 1) {
                // error code => applicant_marital_status
                if (!in_array($request->gender_id, $allowance->ages->pluck('gender_id')->toArray())) {
                    throw new AuthBasicErrorException(
                        Response::HTTP_UNPROCESSABLE_ENTITY,
                        $this->applicantGenderTypeTextErrorCode,
                        $this->applicationGenderTypeMessage
                    );
                } else {
                    $data = [
                        'nid' => $request->verification_number,
                        'dob' => $request->date_of_birth,
                    ];

                    $nidInfo = (new VerificationService())->callVerificationApi($data);
                    $genderAge = $allowance->ages->where('gender_id', $request->gender_id)->first();
                    $minAge = $genderAge->min_age;
                    $maxAge = $genderAge->max_age;
                    // get current age form date_of_birth field
                    $birthDate = $request->date_of_birth;
                    $birthDate = explode("-", $birthDate);
                    $age = (date("md", date("U", mktime(0, 0, 0, $birthDate[1], $birthDate[2], $birthDate[0]))) > date("md")
                        ? ((date("Y") - $birthDate[0]) - 1)
                        : (date("Y") - $birthDate[0]));
                    // return $genderAge;
                    // 60 -90 => age is 73
                    // age range is minAge to maxAge
                    if ($age < $minAge || $age > $maxAge) {
                        throw new AuthBasicErrorException(
                            Response::HTTP_UNPROCESSABLE_ENTITY,
                            $this->applicantAgeLimitTextErrorCode,
                            $this->applicantAgeLimitMessage
                        );
                    }

                }

            }
            if ($allowance->is_marital == 1) {
                // error code =>
                if ($allowance->marital_status != $request->marital_status) {
                    throw new AuthBasicErrorException(
                        Response::HTTP_UNPROCESSABLE_ENTITY,
                        $this->applicantMaritalStatusTextErrorCode,
                        $this->applicantMaritalStatusMessage
                    );
                }
            }

        }
        $data = [
            'nid' => $request->nominee_verification_number,
            'dob' => $request->nominee_date_of_birth,
        ];

        $nidInfo = (new VerificationService())->callNomineeVerificationApi($data);

        // return gettype(json_decode($request->application_allowance_values)[19]->value);

        $data = $this->applicationService->onlineApplicationEdit($request);

        //  $message = "Congratulations! Your application has been submitted successfully. "."\n Your tracking ID is ".$data->application_id ."\n Save tracking ID for further tracking.";

        // Log::info('password-'. $user->id, [$message]);

        $this->SMSservice->sendSms($data->mobile, $message);
        return response()->json([
            'status' => true,
            'data' => $data,
            'id' => $data->application_id,
            'message' => $this->insertSuccessMessage,
        ], 200);

    }

    public function getStatusyId(Request $request)
    {
        $id = $request->id;
        $nid = $request->nid;

        $application = Application::where('id', '=', $id)
            ->update(['status' => 0]);
        $delete = Application::where('verification_number', '=', $nid)
            ->where('status', '=', 9)
            ->delete();
        $application = Application::find($id);

        if (!$application) {

            return response()->json(['error' => 'Application not found'], Response::HTTP_NOT_FOUND);
        }
        $programName = AllowanceProgram::where('id', $application->program_id)->first('name_en');
        $programName = $programName->name_en;

        $message = " Dear $application->name_en. " . "\n Congratulations! Your application has been submitted for the $programName successfully." . "\n Your tracking ID is " . $application->application_id . "\n Save tracking ID for further tracking." . "\n Sincerely," . "\nDepartment of Social Services";

        $this->SMSservice->sendSms($application->mobile, $message);
        return response()->json([

            'application' => $application,
            'id' => $application->application_id,

        ], Response::HTTP_OK);

    }

    public function getClassWiseAmount($allowance, $request)
    {
        $addFields = json_decode($request->application_allowance_values, true) ?: [];

        $class = $allowance->addtionalfield()->where('name_en', 'class')->first();

        if ($class) {
            foreach ($addFields as $addField) {
                if ($addField['allowance_program_additional_fields_id'] == $class->id) {
                    return $allowance->classAmounts()
                        ->where('type_id', $addField['allowance_program_additional_field_values_id'])
                        ->value('amount')
                    ;
                }
            }
        }

    }

    /* -------------------------------------------------------------------------- */
    /*                        Application Selection Methods                       */
    /* -------------------------------------------------------------------------- */

    /**
     * @OA\Get(
     *     path="/admin/application/get",
     *      operationId="getAllApplicationPaginated",
     *       tags={"APPLICATION-SELECTION"},
     *      summary="get paginated Applications with advance search",
     *      description="get paginated applications with advance search",
     *      security={{"bearer_token":{}}},
     *     @OA\Parameter(
     *         name="searchText",
     *         in="query",
     *         description="search by name",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="application_id",
     *         in="query",
     *         description="search by application id",
     *         @OA\Schema(type="text")
     *     ),
     *     @OA\Parameter(
     *         name="nominee_name",
     *         in="query",
     *         description="search by nominee name",
     *         @OA\Schema(type="text")
     *     ),
     *     @OA\Parameter(
     *         name="account_no",
     *         in="query",
     *         description="search by account number",
     *         @OA\Schema(type="number")
     *     ),
     *     @OA\Parameter(
     *         name="nid_no",
     *         in="query",
     *         description="search by nid number",
     *         @OA\Schema(type="number")
     *     ),
     *     @OA\Parameter(
     *         name="list_type_id",
     *         in="query",
     *         description="search by list type name",
     *         @OA\Schema(type="number")
     *     ),
     *     @OA\Parameter(
     *         name="program_id",
     *         in="query",
     *         description="search by program name",
     *         @OA\Schema(type="number")
     *     ),
     *     @OA\Parameter(
     *         name="division_id",
     *         in="query",
     *         description="search by division name",
     *         @OA\Schema(type="number")
     *     ),
     *     @OA\Parameter(
     *         name="district_id",
     *         in="query",
     *         description="search by district name",
     *         @OA\Schema(type="number")
     *     ),
     *     @OA\Parameter(
     *         name="location_type_id",
     *         in="query",
     *         description="search by location type name",
     *         @OA\Schema(type="number")
     *     ),
     *     @OA\Parameter(
     *         name="thana_id",
     *         in="query",
     *         description="search by thana name",
     *         @OA\Schema(type="number")
     *     ),
     *     @OA\Parameter(
     *         name="union_id",
     *         in="query",
     *         description="search by union name",
     *         @OA\Schema(type="number")
     *     ),
     *     @OA\Parameter(
     *         name="city_id",
     *         in="query",
     *         description="search by city name",
     *         @OA\Schema(type="number")
     *     ),
     *     @OA\Parameter(
     *         name="city_thana_id",
     *         in="query",
     *         description="search by city thana name",
     *         @OA\Schema(type="number")
     *     ),
     *     @OA\Parameter(
     *         name="district_pouro_id",
     *         in="query",
     *         description="search by district pouro name",
     *         @OA\Schema(type="number")
     *     ),
     *     @OA\Parameter(
     *         name="perPage",
     *         in="query",
     *         description="number of committee per page",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="page number",
     *         @OA\Schema(type="integer")
     *     ),
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(
     *          response=201,
     *          description="Successful Insert operation",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthenticated",
     *      ),
     *      @OA\Response(
     *          response=403,
     *          description="Forbidden"
     *      ),
     *      @OA\Response(
     *          response=422,
     *          description="Unprocessable Entity",
     *
     *          )
     * )
     */
    public function getAllApplicationPaginated(Request $request)
    {
        $searchText = $request->searchText;
        $application_id = $request->application_id;
        $nominee_name = $request->nominee_name;
        $account_no = $request->account_no;
        $nid_no = $request->nid_no;
        $list_type_id = $request->list_type_id;
        $program_id = $request->program_id;
        $location_type_id = $request->location_type_id;
        $perPage = $request->perPage;
        $page = $request->page;
        $filterArrayNameEn = [];
        $filterArrayNameBn = [];
        $filterArrayFatherNameEn = [];
        $filterArrayFatherNameBn = [];
        $filterArrayMotherNameEn = [];
        $filterArrayMotherNameBn = [];
        $filterArrayApplicationId = [];
        $filterArrayNomineeNameEn = [];
        $filterArrayNomineeNameBn = [];
        $filterArrayAccountNo = [];
        $filterArrayNidNo = [];
        $filterArrayListTypeId = [];
        $filterArrayProgramId = [];

        if ($searchText) {
            $filterArrayNameEn[] = ['name_en', 'LIKE', '%' . $searchText . '%'];
            $filterArrayNameBn[] = ['name_bn', 'LIKE', '%' . $searchText . '%'];
            $filterArrayMotherNameEn[] = ['mother_name_en', 'LIKE', '%' . $searchText . '%'];
            $filterArrayMotherNameBn[] = ['mother_name_bn', 'LIKE', '%' . $searchText . '%'];
            $filterArrayFatherNameEn[] = ['father_name_en', 'LIKE', '%' . $searchText . '%'];
            $filterArrayFatherNameBn[] = ['father_name_bn', 'LIKE', '%' . $searchText . '%'];
            // $page = 1;

        }

        if ($application_id) {
            $filterArrayApplicationId[] = ['application_id', 'LIKE', '%' . $application_id . '%'];
            // $page = 1;

        }

        if ($nominee_name) {
            $filterArrayNomineeNameEn[] = ['nominee_en', 'LIKE', '%' . $nominee_name . '%'];
            $filterArrayNomineeNameBn[] = ['nominee_bn', 'LIKE', '%' . $nominee_name . '%'];
            // $page = 1;

        }

        if ($account_no) {
            $filterArrayAccountNo[] = ['account_number', 'LIKE', '%' . $account_no . '%'];
            // $page = 1;

        }

        if ($nid_no) {
            $filterArrayNidNo[] = ['verification_number', 'LIKE', '%' . $nid_no . '%'];
            // $page = 1;

        }

        if ($list_type_id) {
            $filterArrayListTypeId[] = ['forward_committee_id', '=', $list_type_id];
            // $page = 1;

        }

        if ($program_id) {
            $filterArrayProgramId[] = ['program_id', '=', $program_id];
            // $page = 1;

        }

        $query = Application::query();

        $this->applyUserWiseFiltering($query);

        $query->when($searchText, function ($q) use ($filterArrayNameEn, $filterArrayNameBn, $filterArrayMotherNameEn, $filterArrayMotherNameBn, $filterArrayFatherNameEn, $filterArrayFatherNameBn) {
            $q->where($filterArrayNameEn)
                ->orWhere($filterArrayNameBn)
                ->orWhere($filterArrayMotherNameEn)
                ->orWhere($filterArrayMotherNameBn)
                ->orWhere($filterArrayFatherNameEn)
                ->orWhere($filterArrayFatherNameBn)
            ;
        });

        $query->when($nominee_name, function ($q) use ($filterArrayNomineeNameBn, $filterArrayNomineeNameEn) {
            $q->where($filterArrayNomineeNameEn)
                ->orWhere($filterArrayNomineeNameBn)
            ;
        });

        $query->when($application_id, function ($q) use ($filterArrayApplicationId) {
            $q->where($filterArrayApplicationId);
        });

        $query->when($nid_no, function ($q) use ($filterArrayNidNo) {
            $q->where($filterArrayNidNo);
        });

        $query->when($nid_no, function ($q) use ($filterArrayNidNo) {
            $q->where($filterArrayNidNo);
        });

        $query->when($program_id, function ($q) use ($filterArrayProgramId) {
            $q->where($filterArrayProgramId);
        });

        $query->when($account_no, function ($q) use ($filterArrayAccountNo) {
            $q->where($filterArrayAccountNo);
        });

        if ($request->status) {
            $query->where('status', $request->status);
        }

        if ($request->gender_id) {
            $query->where('gender_id', $request->gender_id);
        }
        if ($request->account_type) {
            $query->where('account_type', $request->account_type);
        }

        if ($request->age_range) {
            $ageRange = $request->age_range;
            if (count($ageRange) === 2) {
                $query->whereBetween('age', $ageRange);
            } elseif (count($ageRange) === 1) {
                $query->where('age', $ageRange[0]);
            }
        }

        $query->with(
            'current_location',
            'permanent_location.parent.parent.parent.parent',
            'program',
            'gender',
            'pmtScore'
        )
            ->orderBy('score');

        return $query->paginate($perPage, ['*'], 'page', $page);
    }

    public function formatApplicationData($applications, $columns)
    {
        $data = [];

        foreach ($applications as $key => $application) {
            foreach ($columns as $column) {
                $data[$key][$column] = $this->getColumnValue($column, $application);
            }
        }

        return $data;
    }

    public function applyUserWiseFiltering($query)
    {
        $user = auth()->user()->load('assign_location.parent.parent.parent.parent');




        if ($user->hasRole($this->committee) && $user->committee_type_id) {
            return (new CommitteeApplicationService())->getApplications($query, $user);
        }

        if ($user->hasRole($this->superAdmin)) {
            return (new OfficeApplicationService())->applyLocationTypeFilter(
                query: $query,
                divisionId: request('division_id'),
                districtId: request('district_id')
            );
        }

        if ($user->office_type) {
            if ($user->programs_id) {
                $query->whereIn('program_id', $user->programs_id);
            }

            return (new OfficeApplicationService())->getApplications($query, $user);
        }

    }

    /**
     * @OA\Get(
     *      path="/admin/application/get/{id}",
     *      operationId="getApplicationById",
     *      tags={"APPLICATION-SELECTION"},
     *      summary=" get a single application",
     *      description="Returns application  by id",
     *      security={{"bearer_token":{}}},
     *
     *       @OA\Parameter(
     *         description="id of application to return",
     *         in="path",
     *         name="id",
     *         @OA\Schema(
     *           type="string",
     *         )
     *     ),
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthenticated",
     *      ),
     *      @OA\Response(
     *          response=403,
     *          description="Forbidden"
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Not Found!"
     *      ),
     *      @OA\Response(
     *          response=422,
     *          description="Unprocessable Entity"
     *      ),
     *     )
     */
    public function getApplicationById($id)
    {
        $application = Application::where('application_id', '=', $id)
            ->with([
                'current_location.parent.parent.parent.parent',
                'permanent_location.parent.parent.parent.parent',
                'program',
                'allowAddiFields',
                'allowAddiFieldValue.allowAddiField',
                'variable',
                'subvariable',
                'bank',
                'mfs',
                'branch',
                'religion',
                'classes',
                'maritalStatus',
                'accountOwner',
                'educationStatus',
                'profession',
                'nationality'
            ])->first();

        if (!$application) {
            return response()->json(['error' => 'Application not found'], Response::HTTP_NOT_FOUND);
        }

        // Manually filter subvariable based on application_id

        // $emu = $application->image;
        // $image =Storage::url($application->image);

        //    $image = Storage::disk('public')->url($application->image);
        $image = asset('storage/' . $application->image);

        $signature = asset('storage/' . $application->signature);

        $nominee_image = asset('storage/' . $application->nominee_image);

        $nominee_signature = asset('storage/' . $application->nominee_signature);
        //    url(Storage::url( $application->nominee_signature));
        //  Storage::url($application->image);
        // $signature = url('storage/app/' . $application->signature);
        // $nominee_image = url('uploads/application/app' . $application->nominee_image);
        // $nominee_signature = url('uploads/application/app' . $application->nominee_signature);
        $groupedAllowAddiFields = $application->allowAddiFields->groupBy('id')->values();
        $groupedAllowAddiFields = $application->allowAddiFields->groupBy('pivot.allow_addi_fields_id');

        // Get the first item from each group (assuming it's the same for each 'allow_addi_fields_id')
        $distinctAllowAddiFields = $groupedAllowAddiFields->map(function ($group) {
            return $group->first();
        });

        return response()->json([
            // 'emu' => $emu,
            'application' => $application,
            'unique_additional_fields' => $distinctAllowAddiFields,
            'image' => $image,
            'signature' => $signature,
            'nominee_image' => $nominee_image,
            'nominee_signature' => $nominee_signature,

        ], Response::HTTP_OK);
    }
    //    public function getApplicationById($id){

    //         $application = Application::where('id','=',$id)
//         ->with('current_location.parent.parent.parent.parent',
//                 'permanent_location.parent.parent.parent.parent',
//                 'program',  // Assuming you have defined this relationship in your Application model
//             'allowAddiFields.allowAddiFieldValues', // Assuming you have defined these relationships in your models
//                 )->first();
//                 $image=Application::where('id','=',$id)
//                 ->value('image');
//                 $image= asset('uploads/application/' . $application->nominee_image);

    //                 // Grouping additional fields by ID
//         $groupedAdditionalFields = $application->allowAddiFields->groupBy('id');

    //         // Mapping to get only one instance of each additional field with its values
//         $uniqueAdditionalFields = $groupedAdditionalFields->map(function ($fields) {
//         $additionalField = $fields->first();
//         $additionalField->allowAddiFieldValues = $fields->first()->allowAddiFieldValues;
//         return $additionalField;

    //     });

    //         return \response()->json([
//             'application' => $application,
//             'image'=>$image,
//             // 'id'=>$id

    //             'unique_additional_fields' => $uniqueAdditionalFields->values(), // Convert to values to remove keys

    //             ],Response::HTTP_OK);

    //     }
    /**
     * @OA\Get(
     *      path="/global/applicants copy/{id}",
     *      operationId="getApplicationCopyById",
     *      tags={"GLOBal"},
     *      summary=" get a applicant's copy",
     *      description="Returns application  by id",
     *      security={{"bearer_token":{}}},
     *
     *       @OA\Parameter(
     *         description="id of application to return",
     *         in="path",
     *         name="id",
     *         @OA\Schema(
     *           type="string",
     *         )
     *     ),
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthenticated",
     *      ),
     *      @OA\Response(
     *          response=403,
     *          description="Forbidden"
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Not Found!"
     *      ),
     *      @OA\Response(
     *          response=422,
     *          description="Unprocessable Entity"
     *      ),
     *     )
     */
    public function getApplicationCopyById(Request $request)
    {
        $id = $request->application_id;
        $application = Application::where('application_id', '=', $id)
            ->with([
                'current_location.parent.parent.parent.parent',
                'permanent_location.parent.parent.parent.parent',
                'program',
                'allowAddiFields.allowAddiFieldValues',
                'variable',
                'subvariable',
            ])->first();

        if (!$application) {
            return response()->json(['error' => 'Application not found'], Response::HTTP_NOT_FOUND);
        }

        //  $image = asset('storage/' . $application->image);

        //  $imageUrl = 'https://picsum.photos/200/300';
        //  $imageData = file_get_contents($image);
        $imagePath = $application->image;
        $imageData = Storage::disk('public')->get($imagePath);
        $image = Helper::urlToBase64($imageData);
        $signaturePath = $application->signature;
        $signatureData = Storage::disk('public')->get($signaturePath);
        $signature = Helper::urlToBase64($signatureData);
        $nomineeimagePath = $application->nominee_image;
        $nominee_imageData = Storage::disk('public')->get($nomineeimagePath);
        $nominee_image = Helper::urlToBase64($nominee_imageData);
        $nominee_signaturePath = $application->nominee_signature;
        $nominee_signature_Data = Storage::disk('public')->get($nominee_signaturePath);
        $nominee_signature = Helper::urlToBase64($nominee_signature_Data);

        $dynamic = $request->all();

        $title = $request->title;
        $data = [
            'data' => $application,
            'request' => $dynamic,
            'title' => $title,
            'image' => $image,
            'nominee_image' => $nominee_image,
            'signature' => $signature,
            'nominee_signature' => $nominee_signature,

        ];

        $pdf = LaravelMpdf::loadView(
            'reports.applicant_copy',
            $data,
            [],
            [
                'mode' => 'utf-8',
                'format' => 'A4-P',
                'title' => $title,
                'orientation' => 'L',
                'default_font_size' => 10,
                'margin_left' => 10,
                'margin_right' => 10,
                'margin_top' => 10,
                'margin_bottom' => 10,
                'margin_header' => 10,
                'margin_footer' => 10,
            ]
        );

        return \Illuminate\Support\Facades\Response::stream(
            function () use ($pdf) {
                echo $pdf->output();
            },
            200,
            [
                'Content-Type' => 'application/pdf;charset=utf-8',
                'Content-Disposition' => 'inline; filename="preview.pdf"',
            ]
        );

    }

    public function getPreviewById($id)
    {
        //   $decryptedId = Crypt::decryptString($id);
        $application = Application::where('id', '=', $id)
            ->with([
                'current_location.parent.parent.parent.parent',
                'permanent_location.parent.parent.parent.parent',
                'program',
                'allowAddiFields',
                // 'allowAddiFieldValue.allowAddiField',
                'allowAddiFieldValue.allowAddiField',
                'variable',
                'subvariable',
            ])
            ->where('status', 9)->first();

        if (!$application) {
            return response()->json(['error' => 'Application not found'], Response::HTTP_NOT_FOUND);
        }

        $image = asset('storage/' . $application->image);

        $signature = asset('storage/' . $application->signature);

        $nominee_image = asset('storage/' . $application->nominee_image);

        $nominee_signature = asset('storage/' . $application->nominee_signature);

        $groupedAllowAddiFields = $application->allowAddiFields->groupBy('id')->values();
        $groupedAllowAddiFields = $application->allowAddiFields->groupBy('pivot.allow_addi_fields_id');

        // Get the first item from each group (assuming it's the same for each 'allow_addi_fields_id')
        $distinctAllowAddiFields = $groupedAllowAddiFields->map(function ($group) {
            return $group->first();
        });

        return response()->json([
            // 'emu' => $emu,
            'application' => $application,
            'unique_additional_fields' => $distinctAllowAddiFields,
            'image' => $image,
            'signature' => $signature,
            'nominee_image' => $nominee_image,
            'nominee_signature' => $nominee_signature,

        ], Response::HTTP_OK);
    }

    /**
     * @OA\Get(
     *     path="/admin/mobile-operator/get",
     *      operationId="getAllMobileOperatorPaginated",
     *      tags={"APPLICATION-SELECTION"},
     *      summary="get paginated mobileoperator",
     *      description="get paginated mobileoperator",
     *      security={{"bearer_token":{}}},
     *     @OA\Parameter(
     *         name="searchText",
     *         in="query",
     *         description="search by mobileoperator",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="perPage",
     *         in="query",
     *         description="number of mobileoperator per page",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="page number",
     *         @OA\Schema(type="integer")
     *     ),
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(
     *          response=201,
     *          description="Successful Insert operation",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthenticated",
     *      ),
     *      @OA\Response(
     *          response=403,
     *          description="Forbidden"
     *      ),
     *      @OA\Response(
     *          response=422,
     *          description="Unprocessable Entity",
     *
     *          )
     * )
     */

    public function getAllMobileOperatorPaginated(Request $request)
    {
        // Retrieve the query parameters
        $searchText = $request->query('searchText');
        $perPage = $request->query('perPage');
        $page = $request->query('page');

        $filterArrayValue = [];

        if ($searchText) {
            $filterArrayValue[] = ['operator', 'LIKE', '%' . $searchText . '%'];

        }
        $globalsetting = MobileOperator::query()
            ->where(function ($query) use ($filterArrayValue) {
                $query->where($filterArrayValue);

            })

            ->latest()
            ->paginate($perPage, ['*'], 'page');

        return MobileOperatorResource::collection($globalsetting)->additional([
            'success' => true,
            'message' => $this->fetchSuccessMessage,
        ]);
    }
    /**
     *
     * @OA\Post(
     *      path="/admin/",
     *      operationId="insertMobileOperator",
     *      tags={"APPLICATION-SELECTION"},
     *      summary="insert a mobile-operator",
     *      description="insert a mobile-operator",
     *      security={{"bearer_token":{}}},
     *
     *
     *       @OA\RequestBody(
     *          required=true,
     *          description="enter inputs",
     *
     *
     *            @OA\MediaType(
     *              mediaType="multipart/form-data",
     *           @OA\Schema(
     *
     *                   @OA\Property(
     *                      property="operator",
     *                      description="Value of operator",
     *                      type="text",
     *                   ),
     *
     *
     *                 ),
     *             ),
     *
     *         ),
     *
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(
     *          response=201,
     *          description="Successful Insert operation",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthenticated",
     *      ),
     *      @OA\Response(
     *          response=403,
     *          description="Forbidden"
     *      ),
     *      @OA\Response(
     *          response=422,
     *          description="Unprocessable Entity",
     *
     *          )
     *        )
     *     )
     *
     */

    public function insertMobileOperator(MobileOperatorRequest $request)
    {

        try {
            $mobile = $this->mobileoperatorService->createMobileOperator($request);

            return MobileOperatorResource::make($mobile)->additional([
                'success' => true,
                'message' => $this->insertSuccessMessage,
            ]);
        } catch (\Throwable $th) {
            //throw $th;
            return $this->sendError($th->getMessage(), [], 500);
        }
    }

    /**
     * @OA\Get(
     *      path="/admin/mobile-operator/destroy/{id}",
     *      operationId="destroyMobileOperator",
     *      tags={"APPLICATION-SELECTION"},
     *      summary=" destroy global setting",
     *      description="Returns mobile-operator destroy by id",
     *      security={{"bearer_token":{}}},
     *
     *       @OA\Parameter(
     *         description="id of mobile-operator to return",
     *         in="path",
     *         name="id",
     *         @OA\Schema(
     *           type="string",
     *         )
     *     ),
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthenticated",
     *      ),
     *      @OA\Response(
     *          response=403,
     *          description="Forbidden"
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Not Found!"
     *      ),
     *      @OA\Response(
     *          response=422,
     *          description="Unprocessable Entity"
     *      ),
     *     )
     */

    public function destroyMobileOperator($id)
    {

        $validator = Validator::make(['id' => $id], [
            'id' => 'required|exists:mobile_operators,id',
        ]);

        $validator->validated();

        $mobile = MobileOperator::whereId($id)->first();

        if ($mobile) {
            $mobile->delete();
        }

        return $this->sendResponse($mobile, $this->deleteSuccessMessage, Response::HTTP_OK);

    }
    /**
     *
     * @OA\Post(
     *      path="/admin/mobile-operator/update",
     *      operationId="updateMobileOperator",
     *      tags={"APPLICATION-SELECTION"},
     *      summary="update a Mobile Operator",
     *      description="update a Mobile Operator",
     *      security={{"bearer_token":{}}},
     *
     *
     *       @OA\RequestBody(
     *          required=true,
     *          description="enter inputs",
     *
     *            @OA\MediaType(
     *              mediaType="multipart/form-data",
     *           @OA\Schema(
     *                   @OA\Property(
     *                      property="id",
     *                      description="id of the Global Setting",
     *                      type="integer",
     *                   ),
     *                   @OA\Property(
     *                      property="operator",
     *                      description="operator",
     *                      type="text",
     *                   ),
     *
     *                 ),
     *             ),
     *
     *         ),
     *
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(
     *          response=201,
     *          description="Successful Insert operation",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthenticated",
     *      ),
     *      @OA\Response(
     *          response=403,
     *          description="Forbidden"
     *      ),
     *      @OA\Response(
     *          response=422,
     *          description="Unprocessable Entity",
     *
     *          )
     *        )
     *     )
     *
     */
    public function updateMobileOperator(MobileOperatorUpdateRequest $request)
    {

        try {
            $mobile = $this->mobileoperatorService->updateMobileOperator($request);

            return MobileOperatorResource::make($mobile)->additional([
                'success' => true,
                'message' => $this->updateSuccessMessage,
            ]);
        } catch (\Throwable $th) {
            //throw $th;
            return $this->sendError($th->getMessage(), [], 500);
        }
    }

    /**
     * @OA\Get(
     *      path="/admin/application/committee-list",
     *      operationId="getCommitteeList",
     *      tags={"APPLICATION-SELECTION"},
     *      summary="get committee list",
     *      description="Returns committee list",
     *      security={{"bearer_token":{}}},
     *
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthenticated",
     *      ),
     *      @OA\Response(
     *          response=403,
     *          description="Forbidden"
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Not Found!"
     *      ),
     *      @OA\Response(
     *          response=422,
     *          description="Unprocessable Entity"
     *      ),
     *     )
     */
    public function getCommitteeList()
    {
        $user = auth()->user()->load('assign_location.parent.parent.parent.parent');

        $query = Committee::query();
        $query->select('committees.*');
        $query->leftJoin('locations', 'committees.location_id', '=', 'locations.id');

        (new CommitteeListService())->applyCommitteeListFilter($query, $user);

        return $query->get();
    }

    public function checkPermission($request, $user)
    {
        $permission = $user->committeePermission;

        if ($request->status == ApplicationStatus::APPROVE) {
            if (!$permission?->approve) {
                throw ValidationException::withMessages(['Unauthorized action']);
            }
        }

        if ($request->status == ApplicationStatus::FORWARD) {
            if (!$permission?->forward) {
                throw ValidationException::withMessages(['Unauthorized action']);
            }
        }

        if ($request->status == ApplicationStatus::REJECTED) {
            if (!$permission?->reject) {
                throw ValidationException::withMessages(['Unauthorized action']);
            }
        }

        if ($request->status == ApplicationStatus::WAITING) {
            if (!$permission?->waiting) {
                throw ValidationException::withMessages(['Unauthorized action']);
            }
        }

    }

    /**
     *
     * @OA\Post(
     *      path="/admin/application/update-status",
     *      operationId="updateApplicationStatus",
     *      tags={"APPLICATION-SELECTION"},
     *      summary="update application status",
     *      description="update status",
     *      security={{"bearer_token":{}}},
     *
     *
     *       @OA\RequestBody(
     *          required=true,
     *          description="enter inputs",
     *
     *            @OA\MediaType(
     *              mediaType="multipart/form-data",
     *           @OA\Schema(
     *                   @OA\Property(
     *                      property="applications_id",
     *                      description="id of applications",
     *                      type="array",
     *                      @OA\Items(type="string")
     *                   ),
     *                   @OA\Property(
     *                      property="committee_id",
     *                      description="id of committee",
     *                      type="integer",
     *                   ),
     *                  @OA\Property(
     *                      property="status",
     *                      description="application status",
     *                      type="integer",
     *                   ),
     *                  @OA\Property(
     *                      property="remark",
     *                      description="remark",
     *                      type="string",
     *                   ),
     *
     *                 ),
     *             ),
     *
     *         ),
     *
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(
     *          response=201,
     *          description="Successful Insert operation",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthenticated",
     *      ),
     *      @OA\Response(
     *          response=403,
     *          description="Forbidden"
     *      ),
     *      @OA\Response(
     *          response=422,
     *          description="Unprocessable Entity",
     *
     *          )
     *        )
     *     )
     *
     */
    public function changeApplicationsStatus(UpdateStatusRequest $request)
    {
        if (!$request->applications_id) {
            return response()->json([
                'success' => false,
                'error' => 'You have to select atleast one applicant .',
            ]);
        }

        $user = auth()->user();

        if ($user->committee_type_id) {
            $this->checkPermission($request, $user);
        }

        $query = Application::query();

        $this->applyUserWiseFiltering($query);
        $query->with(['committeeApplication']);
        $query->whereIn('id', $request->applications_id);

        $query->whereNot('status', ApplicationStatus::REJECTED)
            ->whereNot('status', ApplicationStatus::APPROVE);

        DB::beginTransaction();
        try {
            $this->updateApplications($request, $user, $query->get());
            DB::commit();
            return $this->sendResponse([], 'Update success');
        } catch (\Exception $exception) {
            DB::rollBack();

            return $this->sendError('Internal server error', []);
        }

    }

    public function updateApplications($request, $user, $applications, )
    {
        $data['status'] = $request->status;
        $data['remark'] = $request->remark;

        //Upazila committee & office user
        if ($request->status == ApplicationStatus::FORWARD) {
            $data['forward_committee_id'] = $request->committee_id;
            $this->forwardApplication($request, $applications);

        } else {
            //committee user only
            if ($user->committee_id) {
                $this->changeCommitteeApplicationsStatus($request, $applications, $user->committee_id);
            }
        }

        if($request->status == ApplicationStatus::APPROVE){
            $application_ids = $applications->pluck('id');

            $applications = Application::whereIn('id', $applications->pluck('id'))->get();
            // Check for program_id
            $program = Application::whereIn('id', $application_ids)
            ->selectRaw("count(distinct(program_id)) as program_cnt")
            ->first();

            if ($program->program_cnt > 1) {
            throw new \Exception("Multiple allowance programs applications are not allowed, please select only a single type of allowance program's applications");
            }

            // Check for ward_id
            $ward = Application::whereIn('id', $application_ids)->whereNull('permanent_union_id')
            ->selectRaw("count(distinct(permanent_ward_id)) as ward_cnt")
            ->first();

            if ($ward->ward_cnt > 1) {
            throw new \Exception("Multiple wards applications are not allowed, please select only a single ward's applications");
            }

            // Check for union_id
            $union = Application::whereIn('id', $application_ids)
            ->selectRaw("count(distinct(permanent_union_id)) as union_cnt")
            ->first();

            if ($union->union_cnt > 1) {
            throw new \Exception("Multiple unions applications are not allowed, please select only a single union's applications");
            }
            $count = $applications->count();
            if($count > 0){
                $remaining = AllotmentService::remainingSpaceForApplication($applications[0]);
                if($remaining < $count){
                    throw new \Exception("Max allotment exceeds");
                }
            }
            foreach($applications as $application){
                $application->update($data);
                $this->createBeneficiary($application, BeneficiaryStatus::ACTIVE);
            }
        }

        if ($request->status == ApplicationStatus::REJECTED) {
            Beneficiary::whereIn('application_table_id', $applications->pluck('id'))->delete();
        }

    }

    public function changeCommitteeApplicationsStatus($request, $applications, $committeeId)
    {
        foreach ($applications as $application) {
            $committeeApplication = $application->committeeApplication()->updateOrCreate(
            [
                'committee_id' => $committeeId,
            ]
            );

            $committeeApplication->status = $request->status;
            $committeeApplication->remark = $request->remark;
            $committeeApplication->save();

            activity("Application Status Change")
                ->causedBy(auth()->user())
                ->performedOn($committeeApplication)
                ->withProperties(['userInfo' => Helper::BrowserIpInfo(), 'data' => $committeeApplication])
                ->log("Application Status Change");

            if ($request->status == ApplicationStatus::APPROVE && !$application->approve_date) {
                $application->approve_date = now();
                $application->save();

                activity("Application Status Change APPROVE")
                    ->causedBy(auth()->user())
                    ->performedOn($application)
                    ->withProperties(['userInfo' => Helper::BrowserIpInfo(), 'data' => $application])
                    ->log("Application Status Change");
            }

            if ($request->status == ApplicationStatus::APPROVE || $request->status == ApplicationStatus::WAITING) {
                $status = ApplicationStatus::APPROVE == $request->status ? 1 : 3;
                $this->createBeneficiary($application, $status);
                activity("Application Status Change Approve/Waiting")
                    ->causedBy(auth()->user())
                    ->performedOn($application)
                    ->withProperties(['userInfo' => Helper::BrowserIpInfo(), 'data' => $status])
                    ->log("Application Status Change");
            }
        }
    }

    /**
     * @param Application $application
     * @return mixed
     */
    public function createBeneficiary($application, $status)
    {
        $program_code = $application->program_id;
        $district_geo_code = Application::permanentDistrict($application->permanent_location_id);
        $district_geo_code = $district_geo_code->code;
        // $district_geo_code = 02;
        $remaining_digits = 11 - strlen($program_code) - strlen($district_geo_code);
        $incremental_value = DB::table('beneficiaries')->count() + 1;
        $incremental_value_formatted = str_pad($incremental_value, $remaining_digits, '0', STR_PAD_LEFT);
        $beneficiary_id = $program_code . $district_geo_code . $incremental_value_formatted;
        $is_unique = DB::table('beneficiaries')->where('beneficiary_id', $beneficiary_id)->doesntExist();
        while (!$is_unique) {
            $incremental_value++;
            $incremental_value_formatted = str_pad($incremental_value, $remaining_digits, '0', STR_PAD_LEFT);
            $beneficiary_id = $program_code . $district_geo_code . $incremental_value_formatted;
            $is_unique = DB::table('beneficiaries')->where('beneficiary_id', $beneficiary_id)->doesntExist();
        }
        $currentFinancialYear = getCurrentFinancialYear();
        $currentFinancialYearId = $currentFinancialYear?->id;
        $verified_at = now();
        $verified_by_id = \Auth::user()->id;
        // $application->application_id = $application_id;
        $beneficiary = Beneficiary::firstOrNew(
            [
                "application_table_id" => $application->id,
            ],

            [
                "program_id" => $application->program_id,
                "application_id" => $application->application_id,
                "beneficiary_id" => $beneficiary_id,
                "name_en" => $application->name_en,
                "name_bn" => $application->name_bn,
                "mother_name_en" => $application->mother_name_en,
                "mother_name_bn" => $application->mother_name_bn,
                "father_name_en" => $application->father_name_en,
                "father_name_bn" => $application->father_name_bn,
                "spouse_name_en" => $application->spouse_name_en,
                "spouse_name_bn" => $application->spouse_name_bn,
                "identification_mark" => $application->identification_mark,
                "age" => $application->age,
                "date_of_birth" => $application->date_of_birth,
                "nationality" => $application->nationality,
                "gender_id" => $application->gender_id,
                "education_status" => $application->education_status,
                "profession" => $application->profession,
                "religion" => $application->religion,
                "marital_status" => $application->marital_status,
                "email" => $application->email,
                "verification_type" => $application->verification_type,
                "verification_number" => $application->verification_number,
                "image" => $application->image,
                "signature" => $application->signature,
                "current_division_id" => $application->current_division_id,
                "current_district_id" => $application->current_district_id,
                "current_city_corp_id" => $application->current_city_corp_id,
                "current_district_pourashava_id" => $application->current_district_pourashava_id,
                "current_upazila_id" => $application->current_upazila_id,
                "current_pourashava_id" => $application->current_pourashava_id,
                "current_thana_id" => $application->current_thana_id,
                "current_union_id" => $application->current_union_id,
                "current_ward_id" => $application->current_ward_id,
                "current_post_code" => $application->current_post_code,
                "current_address" => $application->current_address,
                "mobile" => $application->mobile,
                "permanent_division_id" => $application->permanent_division_id,
                "permanent_district_id" => $application->permanent_district_id,
                "permanent_city_corp_id" => $application->permanent_city_corp_id,
                "permanent_district_pourashava_id" => $application->permanent_district_pourashava_id,
                "permanent_upazila_id" => $application->permanent_upazila_id,
                "permanent_pourashava_id" => $application->permanent_pourashava_id,
                "permanent_thana_id" => $application->permanent_thana_id,
                "permanent_union_id" => $application->permanent_union_id,
                "permanent_ward_id" => $application->permanent_ward_id,
                "permanent_post_code" => $application->permanent_post_code,
                "permanent_address" => $application->permanent_address,
                "permanent_mobile" => $application->permanent_mobile,
                "nominee_en" => $application->nominee_en,
                "nominee_bn" => $application->nominee_bn,
                "nominee_verification_number" => $application->nominee_verification_number,
                "nominee_address" => $application->nominee_address,
                "nominee_date_of_birth" => $application->nominee_date_of_birth,
                "nominee_image" => $application->nominee_image,
                "nominee_signature" => $application->nominee_signature,
                "nominee_relation_with_beneficiary" => $application->nominee_relation_with_beneficiary,
                "nominee_nationality" => $application->nominee_nationality,
                "account_name" => $application->account_name,
                "account_number" => $application->account_number,
                "account_owner" => $application->account_owner,
                "permanent_location_type_id" => $application->permanent_location_type_id,

                "score" => $application->score,
                "forward_committee_id" => $application->forward_committee_id,
                "remarks" => $application->remark,
                "monthly_allowance" => $application->allowance_amount,
                "application_date" => $application->created_at,
                "type_id" => $application->type_id,
                "is_verified" => true,
                "last_ver_fin_year_id" => $currentFinancialYearId,
                "last_verified_at" => $verified_at
            ]
        );

        $beneficiary->status = $status;
        $beneficiary->approve_date = $application->approve_date;
        $beneficiary->save();

        $logData = [
            'beneficiary_id' => $beneficiary->id,
            'financial_year_id' => $currentFinancialYearId,
            'remarks' => null,
            'verified_at' => $verified_at,
            'verified_by_id' => $verified_by_id,
        ];

        BeneficiaryVerifyLog::upsert($logData, uniqueBy: ['beneficiary_id', 'financial_year_id']);

        if ($status === 1) {
            $programName = AllowanceProgram::where('id', $application->program_id)->first('name_en');
            $program = $programName->name_en;

            //  $message = " Dear $application->name_en, "."\n We are thrilled to inform you that you have been selected as a recipient for the ". $program ."\n Sincerely,"."\nDepartment of Social Services";
            $message = "Dear $application->name_en," . "\nWe are thrilled to inform you that you have been selected as a recipient for the $program.\n\nYour Beneficiary ID is $beneficiary_id.\n\nSincerely," . "\nDepartment of Social Services";

            $message = " Dear $application->name_en, " . "\n We are thrilled to inform you that you have been selected as a recipient for the " . $program . "\n Sincerely," . "\nDepartment of Social Services";

            $this->SMSservice->sendSms($application->mobile, $message);
            if ($application->email) {
                $this->dispatch(new SendEmail($application->email, $application->name_en, $program));

            }

        }

    }

    public function forwardApplication($request, $applications)
    {
        foreach ($applications as $application) {
            $committeeApplication = $application->committeeApplication()->firstOrNew(
                [
                    'committee_id' => $request->committee_id,
                ]
            );

            $committeeApplication->status = $request->status;
            $committeeApplication->remark = $request->remark;
            $committeeApplication->save();
        }
    }

    /**
     * @param $request
     * @param Application[] $applications
     * @return mixed
     */
    public function insertCommitteeApplications($request, $applications, $committeeId)
    {
        foreach ($applications as $application) {
            $committeeApplication = $application->committeeApplication()->firstOrNew(
                [
                    'committee_id' => $committeeId,
                ]
            );

            $committeeApplication->status = $request->status;
            $committeeApplication->remark = $request->remark;
            $committeeApplication->save();

            $application->forward_committee_id = $committeeId;
            $application->status = $request->status;
            $application->remark = $request->remark;
            $application->save();

            return $committeeApplication;

            $committeeApplication->save();
        }
    }

    /**
     * @OA\Get(
     *      path="/admin/application/permissions",
     *      operationId="getApplicationPermissions",
     *      tags={"APPLICATION-SELECTION"},
     *      summary=" get permission of user",
     *      description="Returns application  permission",
     *      security={{"bearer_token":{}}},
     *
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthenticated",
     *      ),
     *      @OA\Response(
     *          response=403,
     *          description="Forbidden"
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Not Found!"
     *      ),
     *      @OA\Response(
     *          response=422,
     *          description="Unprocessable Entity"
     *      ),
     *     )
     */
    public function getApplicationPermission()
    {

        $user = auth()->user();

        $user->load('assign_location.parent.parent.parent.parent', 'committeePermission');

        return $this->sendResponse(
            [
                'user' => $user,
                'permission' => $this->getPermission($user),
            ]
        );

    }

    public function getPermission($user)
    {
        //superadmin
        if ($user->user_type == 1) {
            return [
                'approve' => false,
                'recommendation' => false,
                'forward' => false,
                'reject' => false,
                'waiting' => false,
            ];
        }

        //if office user
        if ($user->office_type) {
            $canForward = in_array($user->office_type, [8, 9, 10, 11, 35]);

            return [
                'approve' => false,
                'recommendation' => false,
                'forward' => $canForward,
                'reject' => false,
                'waiting' => false,
            ];
        }

        //committee user

        return [
            'approve' => (bool) $user->committeePermission?->approve,
            'recommendation' => (bool) $user->committeePermission?->recommendation,
            'forward' => (bool) $user->committeePermission?->forward,
            'reject' => (bool) $user->committeePermission?->reject,
            'waiting' => (bool) $user->committeePermission?->waiting,
        ];
    }

}
