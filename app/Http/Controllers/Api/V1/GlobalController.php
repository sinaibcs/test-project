<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Bank;
use App\Models\Lookup;
use App\Models\Location;
use App\Models\Variable;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Models\MobileOperator;
use App\Models\AllowanceProgram;
use App\Http\Traits\MessageTrait;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Cache;
use App\Http\Services\Global\GlobalService;
use App\Models\PayrollPaymentProcessorArea;
use App\Http\Resources\Admin\CommonResource;
use App\Http\Resources\Admin\Lookup\LookupResource;
use App\Http\Resources\Admin\PMTScore\VariableResource;
use App\Http\Resources\Admin\Geographic\DivisionResource;
use App\Http\Resources\Admin\Systemconfig\Allowance\AllowanceResource;

class GlobalController extends Controller
{
    use MessageTrait;
    private $globalService;

    public function __construct(GlobalService $globalService)
    {
        $this->globalService = $globalService;
    }

    /**
     * @OA\Get(
     *     path="/global/program",
     *      operationId="getAllProgram",
     *     tags={"GLOBAL"},
     *      summary="get all program",
     *      description="get all program",
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
    public function getAllProgram(Request $request)
    {
        // $data = AllowanceProgram::where('is_active', 1)
        //        ->with(['classAmounts.type', 'addtionalfield.additional_field_value'])
        //        ->orderBy('id', 'asc') // Order by id in ascending order
        //        ->get();
        if($request->all == 1){
            $data = AllowanceProgram::get();
        }else{
            $data = AllowanceProgram::where('is_active', 1)
                ->when($request->only_parent, fn($q) => $q->whereNull('parent_id'))
                ->when($request->except_id, function($q)use($request){
                    if(is_array($request->except_id)){
                        $q->whereNotIn('id', $request->except_id);
                    }else{
                        $q->whereNot('id', $request->except_id);
                    }
                })
                ->with([
                'classAmounts' => function ($query) {
                    $query->orderBy('id', 'asc'); // Order classAmounts by id
                },
                'addtionalfield' => function ($query) {
                    $query->orderBy('id', 'asc'); // Order addtionalfield by id
                },
                'classAmounts.type',
                'addtionalfield.additional_field_value',
            ])
            ->orderBy('id', 'asc') // Order AllowanceProgram by id
            ->get();
        }


        return AllowanceResource::collection($data)->additional([
            'success' => true,
            'message' => $this->fetchSuccessMessage,
        ]);
    }

    public function getApplicationPageData(){
        $ttl = now()->addMinutes(env('CACHE_TIMEOUT',10));
        $programsRaw = Cache::tags(['allowance_programs'])->remember('application.active_allowance_programs', $ttl, function () {
            return AllowanceProgram::where('is_active', 1)->whereNull('parent_id')
                ->where('application_status', 1)
                ->with([
                    'classAmounts' => function ($query) {
                        $query->orderBy('id', 'asc');
                    },
                    'addtionalfield' => function ($query) {
                        $query->reorder();
                        $query->orderByPivot('display_order', 'asc');
                    },
                    'classAmounts.type',
                    'addtionalfield.additional_field_value',
                    'subPrograms' => function ($query) {
                        $query->where('is_active', 1)
                        ->where('application_status', 1)
                        ->with([
                            'classAmounts' => function ($query) {
                                $query->orderBy('id', 'asc');
                            },
                            'addtionalfield' => function ($query) {
                                $query->reorder();
                                $query->orderByPivot('display_order', 'asc');
                            },
                            'classAmounts.type',
                            'addtionalfield.additional_field_value',
                        ]);
                    }
                ])
                ->orderBy('id', 'asc')
                ->get();
        });


        $divisionsRaw = Cache::remember('application.divisions', $ttl, function () {

            return Location::query()
                ->whereNull('parent_id')
                ->with('children')
                ->orderBy('name_en')
                ->get();
        });

        $pmtRaw = Cache::remember('application.variables_with_children', $ttl, function () {
            return Variable::whereParentId(null)->with('children')->get();
        });

        $lookupRaw = Cache::remember("applications.lookups", $ttl, function ()  {
            return Lookup::orderBy('display_order', 'asc')
                ->orderBy('id', 'asc')
                ->get();
        });


        $programs = AllowanceResource::collection($programsRaw);
        $divisions = DivisionResource::collection($divisionsRaw);
        $pmt = VariableResource::collection($pmtRaw);
        $lookups = LookupResource::collection($lookupRaw);

        return [
            'data' => [
                'programs' => $programs,
                'divisions' => $divisions,
                'pmt' => $pmt,
                'lookups' => $lookups,
            ],
            'success' => true,
            'message' => $this->fetchSuccessMessage,
        ];

    }

    public function getProgramsForApplication()
    {
        $data = Cache::tags(['allowance_programs'])->remember('active_allowance_programs', now()->addMinutes(env('CACHE_TIMEOUT')), function () {
            return AllowanceProgram::where('is_active', 1)
                ->where('application_status', 1)
                ->with([
                    'classAmounts' => function ($query) {
                        $query->orderBy('id', 'asc');
                    },
                    'addtionalfield' => function ($query) {
                        $query->orderBy('id', 'asc');
                    },
                    'classAmounts.type',
                    'addtionalfield.additional_field_value',
                ])
                ->orderBy('id', 'asc')
                ->get();
        });
        return AllowanceResource::collection($data)->additional([
            'success' => true,
            'message' => $this->fetchSuccessMessage,
        ]);
    }
    /**
     * @OA\Get(
     *     path="/global/mobile-operator",
     *      operationId="getAllMobileOperator",
     *     tags={"GLOBAL"},
     *      summary="get all mobile operator",
     *      description="get all mobile operator",
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
    public function getAllMobileOperator()
    {
        $data = MobileOperator::get();
        return $data;
    }
    /**
     * @OA\Get(
     *     path="/global/pmt",
     *      operationId="getAllPMTVariableWithSub",
     *     tags={"GLOBAL"},
     *      summary="get all PMT variable with sub-variable",
     *      description="get all PMT variable with sub-variables",
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
    public function getAllPMTVariableWithSub()
    {
        $data = Cache::remember('variables_with_children', now()->addMinutes(env('CACHE_TIMEOUT', 10)), function () {
            return Variable::whereParentId(null)->with('children')->get();
        });
        return VariableResource::collection($data)->additional([
            'success' => true,
            'message' => $this->fetchSuccessMessage,
        ]);
    }


    public function dropdownList(Request $request)
    {
        $data = $this->globalService->getdropdownList($request);
        return handleResponse($data, null);
    }
//      public function coverageArea($location_type,$sub_location,$location_id)

//     {
//     //    return $location_type;
//         if($location_type == 3){
//         $area = PayrollPaymentProcessorArea::where('location_id', $location_id)
//         ->whereHas('payment_processor', function ($query) {
//             $query->where('processor_type', 'bank');
//         })
//         ->with(['payment_processor' => function ($query) {
//             $query->with('bank','branch');
//         }])
//         ->get();
//         $mfs=PayrollPaymentProcessorArea::where('location_id', $location_id)
//         ->whereHas('payment_processor', function ($query) {
//             $query->where('processor_type', 'mfs');
//         })
//         ->with(['payment_processor' => function ($query) {
//             $query->with('mfs');
//         }])
//         ->get();

//         }
//         if($location_type == 1){
//          $area = PayrollPaymentProcessorArea::where('location_id', $location_id)
//         ->whereHas('payment_processor', function ($query) {
//             $query->where('processor_type', 'bank');
//         })
//         ->with(['payment_processor' => function ($query) {
//             $query->with('bank','branch');
//         }])
//         ->get();
//         $mfs=PayrollPaymentProcessorArea::where('location_id', $location_id)
//         ->whereHas('payment_processor', function ($query) {
//             $query->where('processor_type', 'mfs');
//         })
//         ->with(['payment_processor' => function ($query) {
//             $query->with('mfs');
//         }])
//         ->get();

//         }
//         if($location_type == 2){
//             if($sub_location==1){
//                 $area = PayrollPaymentProcessorArea::where('location_id', $location_id)
//                 ->whereHas('payment_processor', function ($query) {
//                     $query->where('processor_type', 'bank');
//                 })
//                 ->with(['payment_processor' => function ($query) {
//                     $query->with('bank','branch');
//                 }])
//                 ->get();
//                 // dd($area);
//                 $mfs=PayrollPaymentProcessorArea::where('location_id', $location_id)
//                 ->whereHas('payment_processor', function ($query) {
//                     $query->where('processor_type', 'mfs');
//                 })
//                 ->with(['payment_processor' => function ($query) {
//                     $query->with('mfs');
//                 }])
//                 ->get();


//             }
//               if($sub_location==2){

//                 $area = PayrollPaymentProcessorArea::where('location_id', $location_id)
//                 ->whereHas('payment_processor', function ($query) {
//                     $query->where('processor_type', 'bank');
//                 })
//                 ->with(['payment_processor' => function ($query) {
//                     $query->with('bank','branch');
//                 }])
//                 ->get();
//                 $mfs=PayrollPaymentProcessorArea::where('location_id', $location_id)
//                 ->whereHas('payment_processor', function ($query) {
//                     $query->where('processor_type', 'mfs');
//                 })
//                 ->with(['payment_processor' => function ($query) {
//                     $query->with('mfs');
//                 }])
//                 ->get();

//             }


//         }
//       $banks=[];
//       $branch=[];
//       $mfss=[];
//         // Extract bank information from $area
// foreach ($area as $a) {
//     if ($a->payment_processor && $a->payment_processor->bank) {
//         $banks[] = $a->payment_processor->bank;
//         $branch[] = $a->payment_processor->branch;
//     }
// }
// //   return $mfs;
// // Extract MFS information from $
// if(isset($mfs)){
//     foreach ($mfs as $m) {
//     if ($m->payment_processor && $m->payment_processor->mfs) {
//         $mfss[] = $m->payment_processor->mfs;
//     }
// }

// }


// return ([
//     'success' => true,
//     'message' => $this->fetchSuccessMessage,
//     // 'bank' => $banks,
//     'bank' => $banks,
//     'branch' => $branch,
//     'mfs' => $mfss,
// ]);





//     }

public function coverageArea($division_id, $district_id,$default_id=null, $location_type, $sub_location, $location_id)
{
    // return $city_id;
$banks = collect();
$branches = collect();
$mfss = collect();


    // Function to get payment processors by location
    $getPaymentProcessors = function ($location_id) use (&$banks, &$branches, &$mfss) {
        $areas = PayrollPaymentProcessorArea::where('location_id', $location_id)
            ->with(['payment_processor' => function ($query) {
                $query->with('bank', 'branch', 'mfs');
            }])
            ->get();
        foreach ($areas as $area) {
            if ($area->payment_processor) {
                if ($area->payment_processor->bank) {
                    $banks[] = $area->payment_processor->bank;
                    $branches[] = $area->payment_processor->branch;

                }
                if ($area->payment_processor->mfs) {
                    $mfss[] = $area->payment_processor->mfs;
                }
            }
        }
    };

    // print_r($getPaymentProcessors);

    // Fetch payment processors for division level
    $getPaymentProcessors($division_id);

    // Fetch payment processors for district level
    $getPaymentProcessors($district_id);
    if($location_type==3){
       $getPaymentProcessors($default_id);

    }
     if ($sub_location == 2 && $sub_location == 2) {
        $getPaymentProcessors($default_id); // District
      }
      if ($sub_location == 2 && $sub_location == 1) {
       $getPaymentProcessors($default_id); // District
       }



    // Fetch payment processors for the specific location level
     // Handle location_type and sub_location to fetch processors at the specific location level
    switch ($location_type) {
        case 1: // Division level
            $getPaymentProcessors($location_id);
            break;
        // case 2: // District level
        case 2: // Division level
            $getPaymentProcessors($location_id);
            break;


        //     if ($sub_location == 1) {
        //         $getPaymentProcessors($district_id);  // District
        //     } elseif ($sub_location == 2) {
        //         $getPaymentProcessors($location_id);  // Thana/Union/Upazila
        //     }
        //     break;
        case 3: // Specific Location (e.g., Thana/Union/Upazila)
            $getPaymentProcessors($location_id);
            break;
        default:
            // Handle other cases if necessary
            break;
    }



    // return $mfss;

  // Get unique banks, branches, and MFS
$uniqueBanks = $banks->unique('id')->values(); // Assuming 'id' is the unique identifier for banks
$uniqueBranches = $branches->unique('id')->values(); // Assuming 'id' is the unique identifier for branches
$uniqueMfss = $mfss->unique('id')->values(); // Assuming 'id' is the unique identifier for MFS

return [
    'success' => true,
    'message' => $this->fetchSuccessMessage,
    'bank' => $uniqueBanks,
    // 'branch' => $uniqueBranches,
    'mfs' => $uniqueMfss,
];

}


public function paymentProcessors($location_id)
{
    $banks = collect();
    $branches = collect();
    $mfss = collect();

    // Function to fetch payment processors
    $getPaymentProcessors = function ($location_ids) use (&$banks, &$branches, &$mfss) {
        $areas = PayrollPaymentProcessorArea::whereIn('location_id', $location_ids)
            ->with(['payment_processor.bank', 'payment_processor.branch', 'payment_processor.mfs'])
            ->get();

        foreach ($areas as $area) {
            if ($area->payment_processor) {
                if ($area->payment_processor->bank) {
                    $banks->push($area->payment_processor->bank);
                    $branches->push($area->payment_processor->branch);
                }
                if ($area->payment_processor->mfs) {
                    $mfss->push($area->payment_processor->mfs);
                }
            }
        }
    };

    $ward = Location::where('id',$location_id)->with('parent.parent.parent.parent')->first();

    // Define locations to check
    $locations[] = $ward->id;
    $locations[] = $ward->parent?->id;
    $locations[] = $ward->parent?->parent?->id;
    $locations[] = $ward->parent?->parent?->parent?->id;
    $locations[] = $ward->parent?->parent?->parent?->parent?->id;
    // $locations[] = $ward->parent?->parent?->parent?->parent?->parent?->id;
    // $locations[] = $ward->parent?->parent?->parent?->parent?->parent?->parent?->id;


    // $locations[] = $location_id; // Always check specific location level

    $getPaymentProcessors(array_filter($locations, function($loc){
        return $loc != null;
    }));
    // Get unique values
    return [
        'success' => true,
        'message' => $this->fetchSuccessMessage,
        'bank' => $banks->unique('id')->values(),
        'mfs' => $mfss->unique('id')->values(),
    ];
}


}
