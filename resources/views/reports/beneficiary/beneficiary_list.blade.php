<!DOCTYPE html>
<html lang="{{app()->getLocale()}}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>{{__("beneficiary_list.page_title")}}</title>

    <style>
        body {
            @if(app()->isLocale('bn'))
                   font-family: 'kalpurush', sans-serif !important;
            @else
                 font-family: 'kalpurush' ,"Work Sans", sans-serif !important;
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
            <h2>{{__("beneficiary_list.title")}}</h2>
        </td>
        <td width="20%" class="right"><img src="{{ public_path('image/logo.png') }}" alt="Right Image"
                                           style="width: 80px; height: 80px;"></td>
    </tr>
    </tbody>
</table>

@php
    use App\Helpers\Helper;
    use Carbon\Carbon;
    function headerHas($val){
        return in_array($val, request()->visibleHeaders??[]);
    }
@endphp

<table class="border-table">
    <thead>
    <tr>
        <th>{{__("beneficiary_list.sl_no")}}</th>
        @if(headerHas('beneficiary_id'))
            <th>{{ Helper::lang('MIS Number', 'এম আই এস নাম্বার') }}</th>
        @endif
        @if(headerHas('name_en'))
            <th>{{ Helper::lang('Name (en)', 'নাম (ইংরেজি') }}</th>
        @endif
        @if(headerHas('name_bn'))
            <th>{{ Helper::lang('Name (bn)', 'নাম (বাংলা') }}</th>
        @endif
        @if(headerHas('gender.value_en'))
            <th>{{ Helper::lang('Gender', 'লিঙ্গ') }}</th>
        @endif
        @if(headerHas('date_of_birth'))
            <th>{{ Helper::lang('Date of birth', 'জন্ম তারিখ') }}</th>
        @endif
        @if(headerHas('father_name_en'))
            <th>{{ Helper::lang("Father's Name (en)", 'পিতার নাম (ইংরেজি)') }}</th>
        @endif
        @if(headerHas('father_name_bn'))
            <th>{{ Helper::lang("Father's Name (bn)", 'পিতার নাম (বাংলা)') }}</th>
        @endif
        @if(headerHas('mother_name_en'))
            <th>{{ Helper::lang("Mother's Name (en)", 'মাতার নাম (ইংরেজি)') }}</th>
        @endif
        @if(headerHas('mother_name_bn'))
            <th>{{ Helper::lang("Mother's Name (bn)", 'মাতার নাম (বাংলা)') }}</th>
        @endif
        @if(headerHas('spouse_name_en'))
            <th>{{ Helper::lang("Spouse's Name (en)", 'স্বামী/স্ত্রীর নাম (ইংরেজি)') }}</th>
        @endif
        @if(headerHas('spouse_name_bn'))
            <th>{{ Helper::lang("Spouse's Name (bn)", 'স্বামী/স্ত্রীর নাম (বাংলা)') }}</th>
        @endif
        @if(headerHas('main_program'))
            <th>{{ Helper::lang('Allowance Program', 'ভাতা কার্যক্রম') }}</th>
        @endif
        @if(headerHas('program'))
            <th>{{ Helper::lang('Sub Allowance Program', 'উপ ভাতা কার্যক্রম') }}</th>
        @endif
        @if(headerHas('verification_number'))
            <th>{{ Helper::lang('NID Number', 'জাতীয় পরিচয়পত্র নাম্বার') }}</th>
        @endif
            <th>{{ Helper::lang('Status', 'স্ট্যাটাস') }}</th>
        @if(headerHas('mobile'))
            <th>{{ Helper::lang('Mobile', 'মোবাইল') }}</th>
        @endif
        @if(headerHas('bank_mfs_name'))
            <th>{{ Helper::lang('Bank/MFS', 'ব্যাংক/এমএফএস') }}</th>
        @endif
        @if(headerHas('account_number'))
            <th>{{ Helper::lang('Account Number', 'এ্যাকাউন্ট নাম্বার') }}</th>
        @endif
        @if(headerHas('age_calculated'))
            <th>{{ Helper::lang('Age', 'বয়স') }}</th>
        @endif
        @if(headerHas('permanentDivision.name_en') || headerHas('permanentDivision.name_bn'))
            <th>{{ Helper::lang('Division', 'বিভাগ') }}</th>
        @endif
        @if(headerHas('permanentDistrict.name_en') || headerHas('permanentDistrict.name_bn'))
            <th>{{ Helper::lang('District', 'জেলা') }}</th>
        @endif
        @if(headerHas('location_upccd'))
            <th>{{ Helper::lang('Upazila/City Corp./Zilla Pouroshava', 'উপজেলা/সিটি কর্প/জেলা পৌরসভা') }}</th>
        @endif
        @if(headerHas('location_unpoth'))
            <th>{{ Helper::lang('Union/Thana/Pouroshava', 'ইউনিয়ন/থানা/পৌরসভা') }}</th>
        @endif
        @if(headerHas('permanentWard.name_en') || headerHas('permanentWard.name_bn'))
            <th>{{ Helper::lang('Ward', 'ওয়ার্ড') }}</th>
        @endif
        @if(headerHas('permanent_address'))
            <th>{{ Helper::lang('Permanent Address', 'স্থায়ী ঠিকানা') }}</th>
        @endif
        @if(headerHas('get_office.name_en') || headerHas('get_office.name_bn'))
            <th>{{ Helper::lang('Office Name', 'অফিসের নাম') }}</th>
        @endif
        @if(headerHas('get_office_type.value_en'))
            <th>{{ Helper::lang('Office Type', 'অফিসের ধরন') }}</th>
        @endif
        @if(headerHas('status_name') || headerHas('verify_action'))
            <th>{{ Helper::lang('Verification Status', 'ভ্যারিফিকেশন স্ট্যাটাস') }}</th>
        @endif
        @if(headerHas('disability_id'))
            <th>{{ Helper::lang('Disability ID', 'ডিজাবিলিটি আইডি') }}</th>
        @endif
        @if(headerHas('class_level'))
            <th>{{ Helper::lang('Class Level', 'ক্লাস স্তর') }}</th>
        @endif

        @foreach ($additionalFields as $field)
            <th>{{app()->isLocale('bn') ? $field->name_bn : $field->name_en }} </th>
        @endforeach
    </tr>
    </thead>
    <tbody>
    @foreach($beneficiaries as $index => $beneficiary)
        @php
            $officeCnt = $beneficiary->get_office_id_from_wards->count();
            $get_office = $officeCnt == 0 ? null : $beneficiary->get_office_id_from_wards[0]?->office;
            $get_office_type = $officeCnt == 0 ? null : $beneficiary->get_office_id_from_wards[0]?->office?->officeType;
            $age = Carbon::parse($beneficiary->date_of_birth)->diffInYears(Carbon::now());
        @endphp
        <tr>
            <td>{{app()->isLocale('bn') ? \App\Facades\BengaliUtil::bn_number($index + 1) : $index + 1}}</td>
            @if(headerHas('beneficiary_id'))
                <td>{{ $beneficiary->beneficiary_id }}</td>
            @endif
            @if(headerHas('name_en'))
                <td>{{ $beneficiary->name_en }}</td>
            @endif
            @if(headerHas('name_bn'))
                <td>{{ $beneficiary->name_bn }}</td>
            @endif
            @if(headerHas('gender.value_en') || headerHas('gender.value_bn'))
                <td>{{ Helper::lang($beneficiary->gender->value_en, $beneficiary->gender->value_bn) }}</td>
            @endif
            @if(headerHas('date_of_birth'))
                <td>{{ $beneficiary->date_of_birth == null ? '' : \Carbon\Carbon::parse($beneficiary->date_of_birth)->format('d-m-Y') }}</td>
            @endif
            @if(headerHas('father_name_en'))
                <td>{{ $beneficiary->father_name_en }}</td>
            @endif
            @if(headerHas('father_name_bn'))
                <td>{{ $beneficiary->father_name_bn }}</td>
            @endif
            @if(headerHas('mother_name_en'))
                <td>{{ $beneficiary->mother_name_en }}</td>
            @endif
            @if(headerHas('mother_name_bn'))
                <td>{{ $beneficiary->mother_name_bn }}</td>
            @endif
            @if(headerHas('spouse_name_en'))
                <td>{{ $beneficiary->spouse_name_en }}</td>
            @endif
            @if(headerHas('spouse_name_bn'))
                <td>{{ $beneficiary->spouse_name_bn }}</td>
            @endif
            @if(headerHas('main_program'))
                <td>{{ Helper::lang($beneficiary->mainProgram->name_en, $beneficiary->mainProgram->name_bn) }}</td>
            @endif
            @if(headerHas('program'))
                @if($beneficiary->mainProgram?->id == $beneficiary->program->id)
                    <td></td>
                @else
                    <td>{{ Helper::lang($beneficiary->program->name_en, $beneficiary->program->name_bn) }}</td>
                @endif
            @endif
            @if(headerHas('verification_number'))
                <td>{{ $beneficiary->verification_number }}</td>
            @endif
                <td>{{ Helper::lang($beneficiary->getStatus('en'), $beneficiary->getStatus('bn')) }}</td>
            @if(headerHas('mobile'))
                <td>{{ $beneficiary->mobile }}</td>
            @endif
            @if(headerHas('bank_mfs_name'))
                <td>{{ $beneficiary->mfs_name ?: $beneficiary->bank_name ?: '' }}</td>
            @endif
            @if(headerHas('account_number'))
                <td>{{ $beneficiary->account_number }}</td>
            @endif
            @if(headerHas('age_calculated'))
                <td>{{ $age }}</td>
            @endif
            @if(headerHas('permanentDivision.name_en') || headerHas('permanentDivision.name_bn'))
                <td>{{ Helper::lang($beneficiary->permanentDivision->name_en, $beneficiary->permanentDivision->name_bn) }}</td>
            @endif
            @if(headerHas('permanentDistrict.name_en') || headerHas('permanentDistrict.name_bn'))
                <td>{{ Helper::lang($beneficiary->permanentDistrict->name_en, $beneficiary->permanentDistrict->name_bn) }}</td>
            @endif
            @if(headerHas('location_upccd'))
                <td>
                    {{ Helper::lang(
                        optional($beneficiary->permanentDistrictPourashava)->name_en
                        ?: optional($beneficiary->permanentCityCorporation)->name_en
                        ?: optional($beneficiary->permanentUpazila)->name_en
                            ?: "",
                        optional($beneficiary->permanentDistrictPourashava)->name_bn
                        ?: optional($beneficiary->permanentCityCorporation)->name_bn
                        ?: optional($beneficiary->permanentUpazila)->name_bn
                            ?: ""
                    ) }}
                </td>
            @endif
            @if(headerHas('location_unpoth'))
                <td>
                    {{ Helper::lang(
                        optional($beneficiary->permanentUnion)->name_en
                        ?: optional($beneficiary->permanentPourashava)->name_en
                        ?: optional($beneficiary->permanentThana)->name_en ?: "",
                        optional($beneficiary->permanentUnion)->name_bn
                        ?: optional($beneficiary->permanentPourashava)->name_bn
                        ?: optional($beneficiary->permanentThana)->name_bn ?: ""
                    ) }}
                </td>
            @endif
            @if(headerHas('permanentWard.name_en') || headerHas('permanentWard.name_bn'))
                <td>{{ Helper::lang(optional($beneficiary->permanentWard)->name_en ?: "", optional($beneficiary->permanentWard)->name_bn ?: "") }}</td>
            @endif
            @if(headerHas('permanent_address'))
                <td>{{ $beneficiary->permanent_address }}</td>
            @endif
            @if(headerHas('get_office.name_en') || headerHas('get_office.name_bn'))
                <td>{{ Helper::lang(optional($get_office)->name_en ?: "", optional($get_office)->name_bn ?: "") }}</td>
            @endif
            @if(headerHas('get_office_type.value_en'))
                <td>{{ Helper::lang(optional($get_office_type)->value_en ?: "", optional($get_office_type)->value_bn ?: "") }}</td>
            @endif
            @if(headerHas('status_name') || headerHas('verify_action'))
                <td>{{ $beneficiary->verify_logs_count == 0? Helper::lang('Non-verified', 'যাচাই করা হয়নি') : Helper::lang('Verified', 'যাচাইকৃত') }}</td>
            @endif
            @if(headerHas('disability_id'))
                <td>{{ $beneficiary->DISABILITY_ID }}</td>
            @endif
            @if(headerHas('class_level'))
                <td>{{ Helper::lang($beneficiary->allowance_class->value_en, $beneficiary->allowance_class->value_bn) }}</td>
            @endif

            @foreach ($additionalFields as $field)
                @php
                    $data = $beneficiary->additionalData[$field->id]?? null;
                @endphp
                @if(is_object($data) && $data != null)
                    <td>{{app()->isLocale('bn') ? $data->value_bn : $data->value_en }} </td>
                @elseif($data != null)
                    <td>{{app()->isLocale('bn') ? App\Helpers\Helper::englishToBangla($data) : $data }} </td>
                @else
                    <td></td>
                @endif
            @endforeach
        </tr>
        @if(fmod($index+1, 1000) == 0)
            <html-separator/>
        @endif
    @endforeach
    </tbody>
</table>

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
