<!DOCTYPE html>
<html lang="{{app()->getLocale()}}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>{{__("allotment.page_title")}}</title>

    <style>
        body {
            @if(app()->isLocale('bn'))
                      font-family: 'kalpurush', sans-serif !important;
            @else
                    font-family: "Work Sans", sans-serif !important;
            @endif
                     margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        h4, h5, h6 {
            font-weight: normal !important;
        }

        .header {
            text-align: center;
            margin-bottom: 20px;
        }

        .title-container {
            text-align: center;
            margin-bottom: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px; /* Add margin to separate tables */
        }

        table.img-table img {
            width: 30px; /* Adjust the width of your images */
            height: auto;
        }

        td {
            border: none;
        }

        .border-table th {
            border: 1px solid #dddddd;
            text-align: center;
            background-color: #d1d1d1;
        }

        .border-table td {
            border: 1px solid #dddddd;
            text-align: left;
            padding: 8px;
            font-size: 14px;
        }

        .left {
            text-align: left;
        }

        .center {
            text-align: center;
        }

        .right {
            text-align: right;
        }

        .title {
            font-size: 20px;
            margin: 0; /* Remove default margin */
        }

        .footer {
            position: fixed;
            bottom: 0;
            width: 100%;
            text-align: center;
        }

        @page {
            header: page-header;
            footer: page-footer;
        }
    </style>
</head>
<body>

<div class="title-container">
    <!-- Empty div for the first table -->
</div>

<table border="none" width="100%">
    <tbody>
    <tr>
        <td width="20%" class="left">
            <img src="{{ public_path('image/bangladesh-govt-logo.png') }}" alt="Left Image"
                 style="width: 100px; height: auto;">
        </td>
        <td width="60%" align="center" class="center">
            <h2>{{__("beneficiary_report.header_line_1")}}</h2>
            <h3>{{__("beneficiary_report.header_line_2")}}</h3>
            <h4>{{__("beneficiary_report.header_line_3")}}</h4>
            <h5>{{__("beneficiary_report.header_line_4")}}</h5>
            <p><a target="_blank" href="https://dss.gov.bd/">www.dss.gov.bd</a></p>
            <br/>
            <h2>{{__("allotment.title")}}</h2>
        </td>
        <td width="20%" class="right"><img src="{{ public_path('image/logo.png') }}" alt="Right Image"
                                           style="width: 80px; height: 80px;"></td>
    </tr>
    </tbody>
</table>
@if($allotmentList)
    <table class="border-table">
        <thead>
        <tr>
            <td>
                <b>{{__("allotment.program")}}</b>
            </td>
            <td>{{app()->isLocale('bn') ? $allotmentList['program']['name_bn'] : $allotmentList['program']['name_en']}}</td>
        </tr>
        <tr>
            <td>
                <b>{{__("allotment.financial_year")}}</b>
            </td>
            <td>{{app()->isLocale('bn') ? \App\Facades\BengaliUtil::bn_number($allotmentList['financial_year']['financial_year']) : $allotmentList['financial_year']['financial_year']}}</td>
        </tr>
        <tr>
            <td>
                <b>{{__("allotment.total_beneficiaries")}}</b>
            </td>
            <td>{{app()->isLocale('bn') ? \App\Facades\BengaliUtil::bn_number($allotmentList['total_beneficiaries']) : $allotmentList['total_beneficiaries']}}</td>
        </tr>
        <tr>
            <td>
                <b>{{__("allotment.total_amount")}}</b>
            </td>
            <td>{{app()->isLocale('bn') ? \App\Facades\BengaliUtil::bn_number($allotmentList['total_amount']) : $allotmentList['total_amount']}}</td>
        </tr>
        </thead>
    </table>
<table class="border-table">
    <thead>
    <tr>
        <th>{{__("allotment.sl_no")}}</th>
        <th>{{__("allotment.location")}}</th>
        <th>{{__("allotment.total_beneficiaries")}}</th>
        <th>{{__("allotment.total_amount")}}</th>
    </tr>
    </thead>
    <tbody>
        @foreach($allotmentList['items'] as $index => $allotment)
            <tr>
                <td>{{app()->isLocale('bn') ? \App\Facades\BengaliUtil::bn_number($index + 1) : $index + 1}}</td>
                <td>{{app()->isLocale('bn') ? $allotment['name_bn'] : $allotment['name_en']}}</td>
                <td>{{app()->isLocale('bn') ? \App\Facades\BengaliUtil::bn_number($allotment['total_beneficiaries']) : $allotment['total_beneficiaries']}}</td>
                <td>{{app()->isLocale('bn') ? \App\Facades\BengaliUtil::bn_number($allotment['total_amount']) : $allotment['total_amount']}}</td>
            </tr>
        @endforeach
    </tbody>
</table>
@endif
<htmlpagefooter name="page-footer">
    <table width="100%">
        <tr>
            <td width="33%">
                {{__("beneficiary_report.print_date")}}{{app()->isLocale('bn') ?  \App\Facades\BengaliUtil::bn_date_time(\Illuminate\Support\Carbon::now()->format('j F Y h:i A')) : \Illuminate\Support\Carbon::now()->format('j F Y h:i A')}}</td>
            <td width="33%" align="center">{PAGENO}/{nbpg}</td>
            <td width="33%"
                style="text-align: right;">{{__("beneficiary_report.printed_by")}}{{$generated_by}}{{$assign_location}}</td>
        </tr>
    </table>
</htmlpagefooter>

</body>
</html>
