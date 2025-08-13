<!DOCTYPE html>
<html lang="{{app()->getLocale()}}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>{{__("beneficiary_exit.page_title")}}</title>

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

        h4, h5, h6{
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
            <h2>{{__("beneficiary_exit.title")}}</h2>
        </td>
        <td width="20%" class="right"><img src="{{ public_path('image/logo.png') }}" alt="Right Image"
                                           style="width: 80px; height: 80px;"></td>
    </tr>
    </tbody>
</table>

<table class="border-table">
    <thead>
    <tr>
        <th>{{__("beneficiary_exit.sl_no")}}</th>
        <th>{{__("beneficiary_exit.beneficiary_id")}}</th>
        <th>{{__("beneficiary_exit.beneficiary_name")}}</th>
        <th>{{__("beneficiary_exit.father_name")}}</th>
        <th>{{__("beneficiary_exit.program_name")}}</th>
        <th>{{__("beneficiary_exit.sub_program_name")}}</th>
        <th>{{__("beneficiary_exit.district")}}</th>
        <th>{{__("beneficiary_exit.city_dis_pouro_upazila")}}</th>
        <th>{{__("beneficiary_exit.thana_union_pouro")}}</th>
        <th>{{__("beneficiary_exit.ward")}}</th>
        <th>{{__("beneficiary_exit.exit_reason")}}</th>
        <th>{{__("beneficiary_exit.exit_date")}}</th>
    </tr>
    </thead>
    <tbody>
    @foreach($beneficiaries as $index => $beneficiary)
        <tr>
            <td>{{app()->isLocale('bn') ? \App\Facades\BengaliUtil::bn_number($index + 1) : $index + 1}}</td>
            <td>{{$beneficiary?->application_id}}</td>
            <td>{{app()->isLocale('bn') ? $beneficiary?->name_bn : $beneficiary?->name_en}}</td>
            <td>{{app()->isLocale('bn') ? $beneficiary?->father_name_bn : $beneficiary?->father_name_en}}</td>
            <td>{{app()->isLocale('bn') ? $beneficiary?->main_program_name_bn : $beneficiary?->main_program_name_en}}</td>
            <td>
                {{
                    ($beneficiary?->main_program_name_en == $beneficiary?->program_name_en)
                        ? ''
                        : (
                            app()->isLocale('bn')
                                ? $beneficiary?->program_name_bn
                                : $beneficiary?->program_name_en
                        )
                }}
            </td>
            <td>{{app()->isLocale('bn') ? $beneficiary?->district_name_bn : $beneficiary?->district_name_en}}</td>
            <td>{{app()->isLocale('bn') ? ($beneficiary?->city_corporation_name_bn ?: ($beneficiary?->district_pourashava_name_bn ?: $beneficiary->upazila_name_bn)) : ($beneficiary?->city_corporation_name_en ?: ($beneficiary?->district_pourashava_name_en ?: $beneficiary->upazila_name_en))}}</td>
            <td>{{app()->isLocale('bn') ? ($beneficiary->thana_bn ?: ($beneficiary->union_bn ?: $beneficiary->pourashava_name_bn)) : ($beneficiary->thana_en ?: ($beneficiary->union_en ?: $beneficiary->pourashava_name_en))}}</td>
            <td>{{app()->isLocale('bn') ? $beneficiary?->ward_bn : $beneficiary?->ward_en}}</td>
            <td>{{app()->isLocale('bn') ? $beneficiary->exit_reason_bn : $beneficiary->exit_reason_en}}</td>
            <td>{{app()->isLocale('bn') ? \App\Facades\BengaliUtil::bn_date(\Illuminate\Support\Carbon::parse($beneficiary->exit_date)->format('d/m/Y')) : \Illuminate\Support\Carbon::parse($beneficiary->exit_date)->format('d/m/Y')}}</td>
        </tr>
    @endforeach
    </tbody>
</table>

<htmlpagefooter name="page-footer">
    <table width="100%">
        <tr>
            <td width="33%">
                {{__("beneficiary_report.print_date")}}{{app()->isLocale('bn') ?  \App\Facades\BengaliUtil::bn_date_time(\Illuminate\Support\Carbon::now()->format('j F Y h:i A')) : \Illuminate\Support\Carbon::now()->format('j F Y h:i A')}}</td>
            <td width="33%" align="center">{PAGENO}/{nbpg}</td>
            <td width="33%" style="text-align: right;">{{__("beneficiary_report.printed_by")}}{{$generated_by}}{{$assign_location}}</td>
        </tr>
    </table>
</htmlpagefooter>

</body>
</html>
