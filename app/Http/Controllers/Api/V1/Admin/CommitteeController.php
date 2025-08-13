<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Beneficiary\StoreCommitteeRequest;
use App\Http\Requests\Admin\Beneficiary\UpdateCommitteeRequest;
use App\Http\Resources\Admin\Beneficiary\Committee\CommitteeResource;
use App\Http\Services\Admin\Beneficiary\CommitteeService;
use App\Http\Traits\MessageTrait;
use App\Models\Committee;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Mccarlosen\LaravelMpdf\Facades\LaravelMpdf;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

/**
 * CommitteeController
 */
class CommitteeController extends Controller
{
    use MessageTrait;

    /**
     * @var CommitteeService
     */
    private CommitteeService $committeeService;

    /**
     * @param CommitteeService $committeeService
     */
    public function __construct(CommitteeService $committeeService)
    {
        $this->committeeService = $committeeService;
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse|AnonymousResourceCollection
     */
    public function list(Request $request): \Illuminate\Http\JsonResponse|AnonymousResourceCollection
    {
        try {
            $committeeList = $this->committeeService->list($request);
            return CommitteeResource::collection($committeeList)->additional([
                'success' => true,
                'message' => $this->fetchSuccessMessage,
            ]);
        } catch (\Throwable $th) {
            //throw $th;
            return $this->sendError($th->getMessage(), [], 500);
        }
    }

    /**
     * @param StoreCommitteeRequest $request
     * @return \Illuminate\Http\JsonResponse|CommitteeResource
     */
    public function add(StoreCommitteeRequest $request): \Illuminate\Http\JsonResponse|CommitteeResource
    {
        try {
            $committee = $this->committeeService->save($request);
            Helper::activityLogInsert($committee, '', 'Committee', 'Committee Created!');
//            activity("Committee")
//                ->causedBy(auth()->user())
//                ->performedOn($committee)
//                ->log('Committee Created !');
            return CommitteeResource::make($committee)->additional([
                'success' => true,
                'message' => $this->insertSuccessMessage,
            ]);
        } catch (\Throwable $th) {
            //throw $th;
            return $this->sendError($th->getMessage(), [], 500);
        }
    }

    /**
     * @param $id
     * @return \Illuminate\Http\JsonResponse|CommitteeResource
     */
    public function show($id): \Illuminate\Http\JsonResponse|CommitteeResource
    {
        try {
            $committee = $this->committeeService->detail($id);
            return CommitteeResource::make($committee)->additional([
                'success' => true,
                'message' => $this->fetchSuccessMessage,
            ]);

        } catch (\Throwable $th) {
            //throw $th;
            return $this->sendError($th->getMessage(), [], 500);
        }
    }

    /**
     * @param $id
     * @return \Illuminate\Http\JsonResponse|CommitteeResource
     */
    public function edit($id): \Illuminate\Http\JsonResponse|CommitteeResource
    {
        try {
            $committee = $this->committeeService->detail($id);
            return CommitteeResource::make($committee)->additional([
                'success' => true,
                'message' => $this->fetchSuccessMessage,
            ]);

        } catch (\Throwable $th) {
            //throw $th;
            return $this->sendError($th->getMessage(), [], 500);
        }
    }


    public function update(UpdateCommitteeRequest $request, $id): \Illuminate\Http\JsonResponse|CommitteeResource
    {
        try {
            $beforeUpdate = Committee::findOrFail($id);
            $committee = $this->committeeService->update($request, $id);
            Helper::activityLogUpdate($committee, $beforeUpdate, "Committee", "Committee Updated!");
//            activity("Committee")
//                ->causedBy(auth()->user())
//                ->performedOn($committee)
//                ->log('Committee Updated !');
            return CommitteeResource::make($committee)->additional([
                'success' => true,
                'message' => $this->updateSuccessMessage,
            ]);
        } catch (\Throwable $th) {
            //throw $th;
            return $this->sendError($th->getMessage(), [], 500);
        }
    }

    /**
     * @param Committee $committee
     * @return \Illuminate\Http\JsonResponse
     */
    public function delete($id): \Illuminate\Http\JsonResponse
    {
        try {
            $committee = Committee::findOrFail($id);
            $this->committeeService->delete($id);
            Helper::activityLogDelete($committee, '', 'Committee', 'Committee Deleted!!');
//            activity("Committee")
//                ->causedBy(auth()->user())
//                ->log('Committee Deleted!!');
            return response()->json([
                'success' => true,
                'message' => $this->deleteSuccessMessage,
            ], ResponseAlias::HTTP_OK);
        } catch (\Throwable $th) {
            return $this->sendError($th->getMessage(), [], 500);
        }
    }

    public function getCommitteeListPdf(Request $request)
    {
        try {
            $committeeList = $this->committeeService->list($request, true);
            $user = auth()->user()->load('assign_location.parent.parent.parent.parent');
            $generated_by = $user->full_name;
            $assign_location = '';
            if ($user->assign_location) {
                $assign_location .= ', ' . (app()->isLocale('bn') ? $user->assign_location?->name_bn : $user->assign_location?->name_en);
                if ($user->assign_location?->parent) {
                    $assign_location .= ', ' . (app()->isLocale('bn') ? $user->assign_location?->parent?->name_bn : $user->assign_location?->parent?->name_en);
                    if ($user->assign_location?->parent?->parent) {
                        $assign_location .= ', ' . (app()->isLocale('bn') ? $user->assign_location?->parent?->parent?->name_bn : $user->assign_location?->parent?->parent?->name_en);
//                    if ($user->assign_location?->parent?->parent?->parent) {
//                        $assign_location .= ', ' . $user->assign_location?->parent?->parent?->parent?->name_bn;
//                    }
                    }
                }
            }
            $data = ['committeeList' => $committeeList, 'generated_by' => $generated_by, 'assign_location' => $assign_location];
            $pdf = LaravelMpdf::loadView('reports.beneficiary.committee_list', $data, [],
                [
                    'mode' => 'utf-8',
                    'format' => 'A4-L',
                    'title' => __("committee_list.page_title"),
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
        } catch (\Throwable $th) {
            //throw $th;
            return $this->sendError($th->getMessage(), [], 500);
        }
    }
}
