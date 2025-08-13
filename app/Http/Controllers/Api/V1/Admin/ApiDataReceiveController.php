<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Helpers\Helper;
use App\Mail\UserCreateMail;
use Illuminate\Http\Request;
use App\Models\ApiDataReceive;
use App\Mail\ApiDataReceiveMail;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Mail;
use Mccarlosen\LaravelMpdf\Facades\LaravelMpdf;
use App\Http\Requests\Admin\API\ApiDataReceiveRequest;

class ApiDataReceiveController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $query = ApiDataReceive::query();

        $query->when(request('search'), function ($q, $v) {
            $q->where('organization_phone', 'like', "%$v%")
                ->orWhere('organization_email', 'like', "%$v%")
                ->orWhere('responsible_person_email', 'like', "%$v%")
                ->orWhere('responsible_person_nid', 'like', "%$v%")
                ->orWhere('username', 'like', "%$v%")
            ;
        });

        $query->when(\request('module_id'), function ($q) {
            $q->whereHas('apiList', function ($q) {
                $q->whereHas('purpose', function ($q) {
                    $q->where('api_module_id', \request('module_id'));
                });
            });
        });

        $query->when(\request('org_name'), function ($q, $v) {
            $q->where('organization_name', 'like', "%$v%");
        });

        $query->with('apiList');

        return $this->sendResponse($query->paginate(
            request('perPage')
        ));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(ApiDataReceiveRequest $request, ApiDataReceive $apiDataReceive)
    {
        $apiDataReceive->api_key = bin2hex(random_bytes(16));

        $apiDataReceive = $this->saveApiDataReceive($apiDataReceive, $request);

        Helper::activityLogInsert($apiDataReceive, '','Api Data Receive','Api Data Receive Created !');
        $apiDataReceive->load('apiList.purpose.module');

        $this->sendEmail($apiDataReceive);

        return $this->sendResponse($apiDataReceive, 'API data receive created successfully');



    }


    public function saveApiDataReceive($apiDataReceive, $request)
    {
        $apiDataReceive->organization_name = $request->organization_name;
        $apiDataReceive->organization_phone = $request->organization_phone;
        $apiDataReceive->organization_email = $request->organization_email;
        $apiDataReceive->responsible_person_email = $request->responsible_person_email;
        $apiDataReceive->responsible_person_nid = $request->responsible_person_nid;
        $apiDataReceive->username = $request->username;
        $apiDataReceive->whitelist_ip = $request->whitelist_ip;
        $apiDataReceive->start_date = $request->start_date;
        $apiDataReceive->end_date = $request->end_date;
        $apiDataReceive->save();

        $apiDataReceive->apiList()->sync($request->api_list);

        return $apiDataReceive;
    }


    /**
     * @param ApiDataReceive $apiDataReceive
     * @param $request
     * @return ApiDataReceive
     */
    public function saveApiList($apiDataReceive, $request)
    {
        $apiDataReceive->apiList()->sync($request->api_list);

        return $apiDataReceive;
    }

    /**
     * Display the specified resource.
     */
    public function show(ApiDataReceive $apiDataReceive)
    {
        $apiDataReceive->load('apiList.purpose.module');

        return $this->sendResponse($apiDataReceive);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(ApiDataReceiveRequest $request, ApiDataReceive $apiDataReceive)
    {
        $beforeUpdate = $apiDataReceive->replicate();

        $apiDataReceive = $this->saveApiDataReceive($apiDataReceive, $request);

        Helper::activityLogUpdate($apiDataReceive, $beforeUpdate,'Api Data Receive','Api Data Receive Updated !');

        return $this->sendResponse($apiDataReceive, 'API data receive updated successfully');

    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ApiDataReceive $apiDataReceive)
    {
        $apiDataReceive->delete();

        Helper::activityLogDelete($apiDataReceive, '','Api Data Receive','Api Data Receive Updated !');

        return $this->sendResponse($apiDataReceive, 'API data receive deleted successfully');
    }


    public function getOrganizationList()
    {
        return $this->sendResponse(
            ApiDataReceive::get(['id', 'organization_name'])
        );
    }



    public function sendEmail(ApiDataReceive $apiDataReceive)
    {
        $apiDataReceive->load('apiList.purpose.module');


        $pdf = LaravelMpdf::loadView('reports.api_documentation', compact('apiDataReceive'), [],
            [
                'mode' => 'utf-8',
                'format' => 'A4-P',
                'title' => 'API Documentation',
                'orientation' => 'L',
                'default_font_size' => 10,
                'margin_left' => 10,
                'margin_right' => 10,
                'margin_top' => 10,
                'margin_bottom' => 10,
                'margin_header' => 10,
                'margin_footer' => 10,
            ]);

        $password = Helper::GeneratePassword();

        $pdf->getMpdf()->SetProtection(array(), $password, $password);

        $recipientEmails = [
            $apiDataReceive->responsible_person_email,
            $apiDataReceive->organization_email
        ];

        Mail::to($recipientEmails)
            ->send(new ApiDataReceiveMail($password, $pdf->output()));


        return $this->sendResponse('Email sent successfully');
    }
}
