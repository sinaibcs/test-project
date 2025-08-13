<?php

namespace App\Http\Controllers\Api\V1\Admin;
use App\Helpers\Helper;
use App\Jobs\SendEmail;
use App\Models\Committee;
use App\Models\Grievance;
use App\Models\Beneficiary;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Traits\RoleTrait;
use App\Models\MobileOperator;
use App\Rules\CaptchaVerified;
use App\Events\RealTimeMessage;
use App\Mail\GrievanceEntryMail;
use App\Models\AllowanceProgram;
use App\Models\GrievanceSetting;
use App\Http\Traits\MessageTrait;
use App\Http\Traits\LocationTrait;
use Illuminate\Support\Facades\DB;
use App\Models\CommitteePermission;
use Illuminate\Support\Facades\Log;
use App\Constants\ApplicationStatus;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Mail;
use App\Http\Traits\BeneficiaryTrait;
use App\Models\GrievanceStatusUpdate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use App\Http\Services\Notification\SMSservice;
use Illuminate\Validation\ValidationException;
use Mccarlosen\LaravelMpdf\Facades\LaravelMpdf;
use App\Http\Services\Admin\Application\NIDService;
use App\Http\Services\Admin\Application\VerificationService;
use App\Http\Services\Admin\Application\CommitteeListService;
use App\Http\Requests\Admin\Application\MobileOperatorRequest;
use App\Http\Services\Admin\Application\MobileOperatorService;
use App\Http\Resources\Admin\Application\MobileOperatorResource;
use App\Http\Services\Admin\Application\OfficeApplicationService;
use App\Http\Services\Admin\GrievanceManagement\GrievanceService;
use App\Http\Requests\Admin\Application\MobileOperatorUpdateRequest;
use App\Http\Services\Admin\Application\CommitteeApplicationService;
use App\Http\Services\Admin\GrievanceManagement\GrievanceListService;
use App\Http\Services\Admin\GrievanceManagement\OfficeGrievanceService;
use App\Http\Services\Admin\GrievanceManagement\GrievanceComitteeService;



class GrievanceController extends Controller
{
    use MessageTrait, BeneficiaryTrait, LocationTrait, LocationTrait, RoleTrait;
    private $grievanceService;

    public function __construct(GrievanceService $grievanceService)
    {
        $this->grievanceService = $grievanceService;

    }
 public function getGrievanceCopyById(Request $request)

{
    $id=$request->application_id;
    $application = Grievance::where('id', '=', $id)
        ->with([
            'grievanceType',
            'grievanceSubject',
            'program',
            'gender',
            'division',
            'district',
            'districtPouroshova',
            'cityCorporation',
            'ward'

        ])->first();


    if (!$application) {
        return response()->json(['error' => 'Grievance Application not found'], Response::HTTP_NOT_FOUND);
    }

    // $imagePath = $application->documents;
    // $imageData = Storage::disk('public')->get($imagePath);
    // $image=Helper::urlToBase64($imageData);

     $dynamic=$request->all();

     $title=$request->title;
     $data = ['data' => $application,
                'request'=>$dynamic,
                 'title' => $title,
                //  'image'=>$image

 ];
//  return $data ;
        $pdf = LaravelMpdf::loadView('reports.grievance_copy', $data, [],
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
            ]);




         return \Illuminate\Support\Facades\Response::stream(
            function () use ($pdf) {
                echo $pdf->output();
            },
            200,
            [
                'Content-Type' => 'application/pdf;charset=utf-8',
                'Content-Disposition' => 'inline; filename="preview.pdf"',
            ]);

}


     // Grievance tracking function
    public function grievanceTracking(Request $request){
        $application = Grievance::with('grievacneStatusDetails.role','grievanceType', 'grievanceSubject')
                      ->where('tracking_no', '=', $request->tracking_no)
                      ->orWhere('verification_number', '=',$request->nid)
                      ->Where('date_of_birth', '=',  $request->date_of_birth)
                      ->first();

        return response()->json([
            'status' => true,
            'data' => $application,
            'message' => $this->fetchSuccessMessage,
        ], 200);


    }

    public function getBeneficiaryByLocation()
    {
        $beneficiaries = $this->getBeneficiary();
        $applications = $this->applications();
    }
    public function getGrievanceSettings(Request $request)
    {
        $grievanceTypeId = $request->query('typeId');
        $grievanceSubjectId = $request->query('subjectId');
        $grievanceSettings = GrievanceSetting::with('firstOfficer', 'secoundOfficer', 'thirdOfficer')->where('grievance_type_id', $grievanceTypeId)
            ->where('grievance_subject_id', $grievanceSubjectId)
            ->first();
        return $grievanceSettings;
    }

    // public function onlineGrievanceVerifyCard(GrievanceVerifyRequest $request)
    public function onlineGrievanceVerifyCard(Request $request, NIDService $nidServie)
    {
        $request->validate([
            'captcha_value' => [
                'required_unless:is_existing_beneficiary,1',
                new CaptchaVerified()
            ],
        ]);

        if ($request->is_existing_beneficiary == 1) {
            $data = Beneficiary::where('beneficiary_id', $request->beneficiary_id)
                ->where('date_of_birth', $request->date_of_birth)
                ->where('status', '=', 1)
                ->first();

            if ($data != null) {
                return response()->json([
                    'status' => true,
                    'data' => $data,
                    'message' => 'Beneficiary ID Verify Successfully',
                ], 201);
            } else {
                return response()->json([
                    'status' => false,
                    'data' => $data,
                    'message' => "Beneficiary ID Doesn't Match !!",
                ], 300);
            }

        } else {
            $data = [
                'nid' => $request->verification_number,
                'dob' => $request->date_of_birth,
            ];

            $result = $nidServie->getInfo($data);
            if($result == false){
                return response()->json([
                    'status' => true,
                    'data' => null,
                    'message' => "NID info did not match",
                ], 200);
            }
            return response()->json([
                'status' => true,
                'data' => $data,
                'message' => $this->fetchSuccessMessage,
            ], 200);

        }

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

    public function grievanceEntry(Request $request)
    {

        $data = $this->grievanceService->onlineGrievanceEntry($request);
        $trackingLink = env('APP_FRONTEND_URL') . '/system-audit/grievance-tracking';
        // $trackingLink ='/system-audit/grievance-tracking';
        $message = " Dear $data->name. "."\n Your Grievance application has been successfully submitted."."\n Your grievance tracking ID: ".$data->tracking_no ."\n Please save your tracking ID to track your grievance application status "."\n To track please visit :"."\n {$trackingLink}"."\n Sincerely,"."\nDSS, MoSW";
        (new SMSservice())->sendSms($data->mobile, $message);
//        Mail::to($data->email)->send(new GrievanceEntryMail($data));
        Helper::activityLogInsert($data, '', 'Grievance entry', 'Grievance Created !');
        return response()->json([
            'status' => true,
            'data' => $data,
            'message' => $this->insertSuccessMessage,
        ], 200);

    }





    /* -------------------------------------------------------------------------- */
    /*                        Grievance list Methods                       */
    /* -------------------------------------------------------------------------- */

    public function getAllGrievancePaginated(Request $request)
    {
        $startDate = $request->start_date;
        $endDate = $request->end_date;
        $searchText = $request->searchText;
        $verification_number = $request->verification_number;
        $tracking_no = $request->tracking_no;
        $grievanceType = $request->grievanceType;
        $grievanceSubject = $request->grievanceSubject;

        $location_type = $request->location_type;
        $division_id = $request->division_id;
        $district_id = $request->district_id;

        $thana_id = $request->thana_id;
        $union_id = $request->union_id;
        $city_id = $request->city_id;
        $city_thana_id = $request->city_thana_id;
        $district_pouro_id = $request->district_pouro_id;
        $pouro_id = $request->pouro_id;
        $sub_location_type = $request->sub_location_type;
        $ward_id = $request->ward_id;
        $status = $request->status;

        $perPage = $request->query('perPage');
        $page = $request->query('page');

        $name = $request->name;
        $mobile = $request->mobile;
        $status = $request->status;
        $dates = $request->dates;


        $filterArrayTracking_no = [];
        $filterArrayGrievanceType = [];
        $filterArrayGrievanceSubject = [];
        $filterArrayName = [];
        $filterArrayStatus = [];

        $filterArrayVerificationNumber = [];
        $filterArrayLocationType = [];
        $filterArrayDivisionId = [];
        $filterArrayDistrictId = [];

        $filterArrayThanaId = [];
        $filterArrayUnionId = [];
        $filterArrayCityId = [];
        $filterArrayCityThanaId = [];
        $filterArrayDistrictPouroId = [];
        $filterArrayPouroId = [];
        $filterArraysubLocationType = [];
        $filterArrayWardId = [];
        $filterArrayDistrictWardId = [];


        if ($name) {
            $filterArrayName[] = ['name', 'LIKE', '%' . $name . '%'];
        }

        if ($verification_number) {
            $filterArrayVerificationNumber[] = ['verification_number', 'LIKE', '%' . $verification_number . '%'];
            // $page = 1;

        }

        if ($tracking_no) {
            $filterArrayTracking_no[] = ['tracking_no', 'LIKE', '%' . $tracking_no . '%'];
            // $page = 1;

        }

        if ($grievanceType) {
            $filterArrayGrievanceType[] = ['grievance_type_id', '=', $grievanceType];
            // $page = 1;

        }

        if ($grievanceSubject) {
            $filterArrayGrievanceSubject[] = ['grievance_subject_id', '=', $grievanceSubject];
            // $page = 1;

        }
        if ($location_type) {
            $filterArrayLocationType[] = ['location_type', '=', $location_type];
            // $page = 1;

        }
        if ($division_id) {
            $filterArrayDivisionId[] = ['division_id', '=', $division_id];
            // $page = 1;

        }
        if ($district_id) {
            $filterArrayDistrictId[] = ['district_id', '=', $district_id];
            // $page = 1;

        }
        if ($thana_id) {
            $filterArrayThanaId[] = ['thana_id', '=', $thana_id];
            // $page = 1;

        }
        if ($union_id) {
            $filterArrayUnionId[] = ['union_id', '=', $union_id];
            // $page = 1;

        }

        if ($city_id) {
            $filterArrayCityId[] = ['city_id', '=', $city_id];
            // $page = 1;

        }
        if ($city_thana_id) {
            $filterArrayCityThanaId[] = ['city_thana_id', '=', $city_thana_id];
            // $page = 1;

        }

        if ($district_pouro_id) {
            $filterArrayDistrictPouroId[] = ['district_pouro_id', '=', $district_pouro_id];
            // $page = 1;

        }
        if ($pouro_id) {
            $filterArrayPouroId[] = ['pouro_id', '=', $pouro_id];
            // $page = 1;

        }
        if ($sub_location_type) {
            $filterArraysubLocationType[] = ['sub_location_type', '=', $sub_location_type];
            // $page = 1;

        }

        if ($location_type == 3) {
            $filterArrayWardId[] = ['ward_id_city', '=', $ward_id];
            // $page = 1;

        } else if ($location_type == 1) {
            $filterArrayWardId[] = ['ward_id_pouro', '=', $ward_id];
            // $page = 1;

        } else if ($location_type == 2) {


            $filterArrayWardId[] = ['ward_id_union', '=', $ward_id];
            // return  $filterArrayWardId;

            // $page = 1;

        } else {
            $filterArrayWardId[] = ['ward_id_dist', '=', $ward_id];
            // $page = 1;

        }
        if ($ward_id) {
            $filterArrayDistrictWardId[] = ['ward_id_dist', '=', $ward_id];
            // $page = 1;

        }
        if ($status) {
            $filterArrayStatus[] = ['status', '=', $status];
            // $page = 1;

        }
        // return $page;
        // return $filterArrayWardId;
        $query = Grievance::query();
        $this->applyUserWiseGrievacne($query);

        $query->when($name, function ($q) use ($filterArrayName) {
            $q->where($filterArrayName);
        });


        $query->when($verification_number, function ($q) use ($filterArrayVerificationNumber) {
            $q->where($filterArrayVerificationNumber);
        });

        $query->when($filterArrayTracking_no, function ($q) use ($filterArrayTracking_no) {
            $q->where($filterArrayTracking_no);
        });

        $query->when($grievanceType, function ($q) use ($filterArrayGrievanceType) {
            $q->where($filterArrayGrievanceType);
        });
        $query->when($grievanceSubject, function ($q) use ($filterArrayGrievanceSubject) {
            $q->where($filterArrayGrievanceSubject);
        });
        $query->when($location_type, function ($q) use ($filterArrayLocationType) {
            $q->where($filterArrayLocationType);
        });
        $query->when($division_id, function ($q) use ($filterArrayDivisionId) {
            $q->where($filterArrayDivisionId);
        });
        $query->when($district_id, function ($q) use ($filterArrayDistrictId) {
            $q->where($filterArrayDistrictId);
        });


        $query->when($thana_id, function ($q) use ($filterArrayThanaId) {
            $q->where($filterArrayThanaId);
        });
        $query->when($union_id, function ($q) use ($filterArrayUnionId) {
            $q->where($filterArrayUnionId);
        });
        $query->when($city_id, function ($q) use ($filterArrayCityId) {
            $q->where($filterArrayCityId);
        });
        $query->when($city_thana_id, function ($q) use ($filterArrayCityThanaId) {
            $q->where($filterArrayCityThanaId);
        });
        $query->when($district_pouro_id, function ($q) use ($filterArrayDistrictPouroId) {
            $q->where($filterArrayDistrictPouroId);
        });
        $query->when($pouro_id, function ($q) use ($filterArrayPouroId) {
            $q->where($filterArrayPouroId);
        });
        $query->when($sub_location_type, function ($q) use ($filterArraysubLocationType) {
            $q->where($filterArraysubLocationType);
        });
        if ($location_type == 2) {
           $query->when($ward_id, function ($q) use ($filterArrayWardId) {
            $q->where($filterArrayWardId);
           });

         }else{
            $query->when($ward_id, function ($q) use ($filterArrayDistrictWardId) {
            $q->where($filterArrayDistrictWardId);
            // return $q;
            });

         }


        // return  $query->get();
        $query->when($status, function ($q) use ($filterArrayStatus) {
            $q->where($filterArrayStatus);
        });

        if ($request->status) {
            $query->where('status', $request->status);
        }
         if ($request->mobile) {
            $query->where('mobile', $request->mobile);
        }
        if ($startDate && $endDate) {
            $query->whereBetween('created_at', [$startDate, $endDate]);
            // return  $query->get();
        }

        if ($request->gender_id) {
            $query->where('gender_id', $request->gender_id);
        }
        if ($request->grievance_type) {
            $query->where('title_en', $request->grievanceType);
        }
        // auto search
        if ($searchText) {
           $query->orwhere( 'name', 'LIKE', '%' . $searchText . '%');
           $query->orwhere( 'mobile', 'LIKE', '%' . $searchText . '%');
           $query->orwhere( 'tracking_no', 'LIKE', '%' . $searchText . '%');

         }



        $query->with('resolver.userType','resolver.office','grievanceType', 'grievanceSubject', 'program', 'gender', 'division', 'district', 'districtPouroshova', 'cityCorporation', 'ward')
            ->orderBy('id', 'DESC');
        //     if ($query->has('grievance_type')) {
        //         $query->where('title_en', $searchText);
        //    }

    //    return $query->get();
        // return $perPage;
        return $query->paginate($perPage, ['*'], 'page', $page);
        // return $query->paginate(5, ['*'], 'page', 4);

    }

    public function applyUserWiseGrievacne($query)
    {
    //    return $query
         $user = auth()->user()->load('assign_location.parent.parent.parent.parent');
        if($user->user_type==2){
            $roleIds = $user->roles->pluck('id');
            // dd( $roleIds);
            $settings = collect();
           foreach ($roleIds as $roleId) {
             $roleSettings = GrievanceSetting::where('first_tire_officer', $roleId)
               ->orwhere('secound_tire_officer', $roleId)
               ->orwhere('third_tire_officer', $roleId)
               ->select('grievance_type_id', 'grievance_subject_id')
               ->distinct()
               ->get();
            $settings = $settings->merge($roleSettings);
          }
           $settings = $settings->unique();
        //    dd( $settings);
           $query->whereIn('grievance_type_id', $settings->pluck('grievance_type_id'));
           $query->whereIn('grievance_subject_id', $settings->pluck('grievance_subject_id'));

        }

        if ($user->office_type) {
            return (new GrievanceListService())->getGrievance($query, $user);
        }

        if ($user->hasRole($this->committee) && $user->committee_type_id) {
            return (new GrievanceComitteeService())->getGrievance($query, $user);
        }

        if ($user->hasRole($this->superAdmin)) {
            return (new OfficeGrievanceService())->applyLocationTypeFilter(
                query: $query,
                divisionId: request('division_id'),
                districtId: request('district_id')
            );
        }

    }




    public function changeGrievanceStatus(Request $request)
    {
        DB::beginTransaction();
         try {
        //  $user = auth()->user()->roles->pluck('id')[0];
         $user = auth()->user()->id;
        //  return $user;
        $grievance = new GrievanceStatusUpdate();
        $grievance->grievance_id = $request->id;
        $grievance->resolver_id =  $user;
        $grievance->status = $request->status;
        $grievance->remarks = $request->remarks;
        $grievance->solution = $request->solution;
        $grievance->forward_to =$request->forwardOfficer;
        if ($request->file('documents')) {
          $filePath = $request->file('documents')->store('public');
          $grievance->file = $filePath;
        }
         $grievance->save();

         $grievanceApplication=Grievance::where('id',$request->id)->first();
         $grievanceApplication->status=$request->status;
         $grievanceApplication->resolver_id= $user;
         $grievanceApplication->forward_to= $request->forwardOfficer;
         $grievanceApplication->save();
         if($request->status==2){
           $trackingLink = env('APP_FRONTEND_URL') . '/system-audit/grievance-tracking';
            $message = " Dear $grievanceApplication->name. " . "\n Congratulations! Your Grievance has been Solved." . "\n Your tracking ID is " . $grievanceApplication->tracking_no . "\n Save tracking ID for further tracking " . "\n Problem Solution :" .  $request->solution ."\n Remarks :" .   $request->remarks ."\n Please visit :" . "{$trackingLink}" . "\n Sincerely," . "\nDepartment of Social Services";
            (new SMSservice())->sendSms($grievanceApplication->mobile, $message);
            Mail::to($grievanceApplication->email)->send(new GrievanceEntryMail($grievanceApplication));

         }
            DB::commit();

            Helper::activityLogInsert($grievance, '', 'Grievance List', 'Grievance Update Status !');

            return $grievance;
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }




        // if ($user->committee_type_id) {
        //     $this->checkPermission($request, $user);
        // }

        // $query = Application::query();

        // $this->applyUserWiseFiltering($query);
        // $query->with(['committeeApplication']);
        // $query->whereIn('id', $request->applications_id);

        // $query->whereNot('status', ApplicationStatus::REJECTED)
        //     ->whereNot('status', ApplicationStatus::APPROVE);

        // DB::beginTransaction();
        // try {
        //     $this->updateApplications($request, $user, $query->get());
        //     DB::commit();
        //     return $this->sendResponse([], 'Update success');
        // } catch (\Exception $exception) {
        //     DB::rollBack();

        //     return $this->sendError('Internal server error', []);
        // }

    }

}
