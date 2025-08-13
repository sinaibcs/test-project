<?php

namespace App\Http\Services\Mobile\Office;

use App\Http\Traits\OfficeTrait;
use App\Models\Office;
use App\Models\OfficeHasWard;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class OfficeService
{


    /* -------------------------------------------------------------------------- */
    /*                              Office Service                              */
    /* -------------------------------------------------------------------------- */

    public function createOffice(Request $request)
    {
        $selectedWardsDetails = json_decode($request->input('selectedWardsDetails'), true);

        //  print_r($request->ward_under_office);
        // return;

        DB::beginTransaction();
        try {

            $office = new Office;
            if ($request->has('office_type')) {
                $office->office_type = $request->office_type;
                if ($request->office_type != 4 || $request->office_type != 5) {
                    if ($request->office_type == 6) {
                        if ($request->has('division_id')) {
                            $office->assign_location_id = $request->division_id;
                        }
                    } elseif ($request->office_type == 7) {
                        if ($request->has('district_id')) {
                            $office->assign_location_id = $request->district_id;
                        }
                    } elseif ($request->office_type == 8 || $request->office_type == 10 || $request->office_type == 11) {
                        if ($request->has('upazila_id')) {
                            $office->assign_location_id = $request->upazila_id;
                        }
                    } elseif ($request->office_type == 9) {
                        if ($request->has('city_id')) {
                            $office->assign_location_id = $request->city_id;
                        }
                    } elseif ($request->office_type == 35) {
                        if ($request->has('dist_pouro_id')) {
                            $office->assign_location_id = $request->dist_pouro_id;
                        }
                    }
                }
            }
            $office->name_en = $request->name_en;
            $office->name_bn = $request->name_bn;
            $office->office_address = $request->office_address;
            $office->comment = $request->comment;
            $office->status = $request->status;
            $office->save();

            $data = $request->x;


            // if (is_array($data) && count($data) > 0) {
            if ($selectedWardsDetails) {
                foreach ($selectedWardsDetails as $wardDetails) {
                    if (is_array($wardDetails) && count($wardDetails)) {
                        // dd($isWardExists);
                        $ward_under_office = new OfficeHasWard;
                        $ward_under_office->office_id = $office->id;
                        $ward_under_office->ward_id = $wardDetails['ward_id'];
                        // You may need to adjust this based on your data structure
                        $ward_under_office->division_id = $wardDetails['division_id'];
                        $ward_under_office->city_id = $wardDetails['city_id'];
                        $ward_under_office->district_id = $wardDetails['district_id'];
                        $ward_under_office->thana_id = $wardDetails['thana_id'];
                        $ward_under_office->save();

                    }
                }
            }

            DB::commit();
            return $office;
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    public function updateOffice(Request $request)
    {
        $selectedWardsDetails = json_decode($request->input('selectedWardsDetails'), true);
        DB::beginTransaction();
        try {
            $office = Office::find($request->id);
            // if ($request->has('office_type')) {
            //     $office->office_type = $request->office_type;
            //     if ($request->office_type != 4 || $request->office_type != 5) {
            //         if ($request->office_type == 6) {
            //             if ($request->has('division_id')) {
            //                 $office->assign_location_id = $request->division_id;
            //             }
            //         } elseif ($request->office_type == 7) {
            //             if ($request->has('district_id')) {
            //                 $office->assign_location_id = $request->district_id;
            //             }
            //         } elseif ($request->office_type == 8 || $request->office_type == 10 || $request->office_type == 11) {
            //             if ($request->has('thana_id')) {
            //                 $office->assign_location_id = $request->thana_id;
            //             }
            //         } elseif ($request->office_type == 9) {
            //             if ($request->has('city_corpo_id')) {
            //                 $office->assign_location_id = $request->city_corpo_id;
            //             }
            //         }
            //     }
            // }
            if ($request->has('office_type')) {
                $office->office_type = $request->office_type;
                if ($request->office_type != 4 || $request->office_type != 5) {
                    if ($request->office_type == 6) {
                        if ($request->has('division_id')) {
                            $office->assign_location_id = $request->division_id;
                        }
                    } elseif ($request->office_type == 7) {
                        if ($request->has('district_id')) {
                            $office->assign_location_id = $request->district_id;
                        }
                    } elseif ($request->office_type == 8 || $request->office_type == 10 || $request->office_type == 11) {
                        if ($request->has('upazila_id')) {
                            $office->assign_location_id = $request->upazila_id;
                        }
                    } elseif ($request->office_type == 9) {
                        if ($request->has('city_id')) {
                            $office->assign_location_id = $request->city_id;
                        }
                    } elseif ($request->office_type == 35) {
                        if ($request->has('dist_pouro_id')) {
                            $office->assign_location_id = $request->dist_pouro_id;
                        }
                    }
                }
            }
            $office->name_en = $request->name_en;
            $office->name_bn = $request->name_bn;
            $office->office_address = $request->office_address;
            $office->comment = $request->comment;
            $office->status = $request->status;
            $office->version = $office->version + 1;
            $office->save();


            // $data = $request->ward_under_office;

            // if (is_array($data) && count($data) > 0) {

            //     foreach ($data as $item) {
            //         $ward_under_office = new OfficeHasWard;
            //         $ward_under_office->office_id = $office->id;
            //         $ward_under_office->ward_id = $item['ward_id'];
            //         $ward_under_office->save();
            //     }
            // }
            $data = $request->selectedWards;


            OfficeHasWard::where('office_id', $request->id)->delete();
            //      $model = OfficeHasWard::where('office_id', $request->id)->firstOrFail();
            //      $delete=
            //    dd( $model['office_id']);


            // if (is_array($data) && count($data) > 0) {
            // dd($selectedWardsDetails);
            if ($selectedWardsDetails) {
                foreach ($selectedWardsDetails as $wardDetails) {
                    if (is_array($wardDetails) && count($wardDetails)) {
                        $ward_under_office = new OfficeHasWard;
                        $ward_under_office->office_id = $office->id;
                        $ward_under_office->ward_id = $wardDetails['ward_id'];
                        // You may need to adjust this based on your data structure
                        $ward_under_office->division_id = $wardDetails['division_id'];
                        $ward_under_office->city_id = $wardDetails['city_id'];
                        $ward_under_office->district_id = $wardDetails['district_id'];
                        $ward_under_office->thana_id = $wardDetails['thana_id'];
                        $ward_under_office->save();
                    }
                }
            }

            DB::commit();
            return $office;
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    public function getAllOfficeList(Request $request)
    {
        $location_id = $request->query('location_id');
        $office_type = $request->query('office_type');
        $query = Office::query();
        $query->select('id', 'name_en', 'name_bn');
        if ($location_id)
            $query = $query->where('assign_location_id', $location_id);
        if ($office_type)
            $query = $query->where('office_type', $office_type);

        return $query->select('id', 'name_en', 'name_bn')->orderBy("office_type")
            ->orderBy("name_en")
            ->get();
    }


}
