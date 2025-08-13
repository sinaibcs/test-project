<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>{{ $title }}</title>

        <style>
            body {
                font-family: 'kalpurush' !important;
                margin: 0;
                padding: 0 0 50px 0;
                box-sizing: border-box;
            }

            .header {
                text-align: center;
                margin-bottom: 20px;
            }

            .center {
                text-align: center;
            }

            .title-container {
                text-align: center;
                margin-bottom: 20px;
            }

            table {
                width: 100%;
                border-collapse: collapse;
                margin-bottom: 20px;
                /* Add margin to separate tables */
            }

            table.img-table img {
                width: 30px;
                /* Adjust the width of your images */
                height: auto;
            }

            td {
                border: none;
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
                margin: 0;
                /* Remove default margin */
            }

            .footer {
                /*position: fixed;*/
                bottom: 0;
                /*height: 20px;*/
                width: 100%;
                text-align: center;
                margin-top: 25px;
            }

            @page {
                header: page-header;
                footer: page-footer;
            }
        </style>
</head>

<body>


    <p>{{ $request['language'] == 'en' ? 'Listed Report | Department of Social Services' : 'তালিকাভুক্ত  রিপোর্ট | সমাজসেবা অধিদফতর' }}
    </p>



    <div class="title-container">
        <!-- Empty div for the first table -->
    </div>




    <table style="border: none;">
        <tbody>
            <tr>
                <td class="left">
                    <img src="{{ public_path('image/bangladesh-govt-logo.png') }}" alt="Left Image"
                        style="width: 100px; height: auto;">

                </td>
                </td>
                @if ($request['language'] == 'en')
                    <td class="center">
                        <h3 class="title">
                            Government of the People's Republic of Bangladesh <br>
                            Department of Social Services
                        </h3>
                        <p style="font-size:15px" class="center">Cash Transfer Modernization(CTM)Project</p>
                        <p style="font-size:12px">Social Service Building, E-8/B-1, Agargaon, Sherbangla Nagar,
                            Dhaka-1207, Bangladesh.</p>
                        <a target="_blank" href="https://dss.gov.bd/">www.dss.gov.bd</a>
                    </td>
                @else
                    <td class="center">
                        <h3 class="title">
                            গণপ্রজাতন্ত্রী বাংলাদেশ সরকার <br>
                            সমাজসেবা অধিদফতর
                        </h3>
                        <p style="font-size:15px" class="center">ক্যাশ ট্রান্সফার মডার্নাইজেশন (সিটিএম) প্রকল্প</p>
                        <p style="font-size:12px">সমাজসেবা ভবন, ই-৮/বি-১, আগারগাঁও, শেরেবাংলা নগর, ঢাকা-১২০৭, বাংলাদেশ।
                        </p>
                        <a target="_blank" href="https://dss.gov.bd/">www.dss.gov.bd</a>
                    </td>
                @endif
                <td class="right"> <img src="{{ public_path('image/logo.png') }}" alt="Right Image"
                        style="width: 80px; height: 80px;"></td>
            </tr>
        </tbody>
    </table>

    {{-- Program Start --}}
    <table style="width: 100%; border-collapse: collapse;margin-left:40px;">
        <tbody>
            @php
                $parentProgram = $data->program->parent;
            @endphp
            @if ($parentProgram)
                <tr>
                    <td class="left" style="width: 40%;font-size: 20px;">
                        {{ $request['program'] }}
                    </td>
                    <td class="left" style="width: 60%;font-size: 20px;">
                        <span class="right">:</span>
                        {{ $request['language'] == 'bn' ? $parentProgram->name_bn : $parentProgram->name_en }}
                        <!-- Notice the space character before the Blade directive -->
                    </td>
                    <td class="right" style="width: 30%; font-size: 20px;"></td>
                </tr>

                <tr>
                    <td class="left" style="width: 40%;font-size: 20px;">
                        {{ $request['sub_program'] }}
                    </td>
                    <td class="left" style="width: 60%;font-size: 20px;">
                        <span class="right">:</span>
                        {{ $request['language'] == 'bn' ? $data->program->name_bn : $data->program->name_en }}
                        <!-- Notice the space character before the Blade directive -->
                    </td>
                    <td class="right" style="width: 30%; font-size: 20px;"></td>
                </tr>
            @else
                <tr>
                    <td class="left" style="width: 40%;font-size: 20px;">
                        {{ $request['program'] }}
                    </td>
                    <td class="left" style="width: 60%;font-size: 20px;">
                        <span class="right">:</span>
                        {{ $request['language'] == 'bn' ? $data->program->name_bn : $data->program->name_en }}
                        <!-- Notice the space character before the Blade directive -->
                    </td>
                    <td class="right" style="width: 30%; font-size: 20px;"></td>
                </tr>
            @endif
            <tr>
                <td class="left" style="width: 40%;font-size: 20px;">
                    {{ $request['application'] }}
                </td>
                <td class="left" style="width: 60%;font-size: 20px;">
                    <span class="right">:</span>
                     {{ $request['language'] == 'en' ? $data->application_id : \App\Helpers\Helper::englishToBangla($data->application_id) }}


                    <!-- Notice the space character before the Blade directive -->
                </td>
                <td class="right" style="width: 30%; font-size: 20px;"></td>
            </tr>

            <tr>
                <td class="left" style="width: 40%;font-size: 20px;">
                    {{ $request['application_date'] }}
                </td>
                <td class="left" style="width: 60%;font-size: 20px;">
                    <span class="right">:</span>
                    {{ $request['language'] == 'en'
                        ? \Carbon\Carbon::parse($data->created_at)->format('d-m-Y h:i A')
                        : \App\Helpers\Helper::englishToBangla(\Carbon\Carbon::parse($data->created_at)->format('d-m-Y h:i A'))
                    }}
                    <!-- Notice the space character before the Blade directive -->
                </td>
                <td class="right" style="width: 30%; font-size: 20px;"></td>
            </tr>


        </tbody>
    </table>
    {{-- Program End --}}

    {{-- Personal Info Start --}}
    <div class="center" style="text-decoration: underline;margin-left: 40px;font-size: 20px;"> <b>
            {{ $request['personal_info'] }}</b></div>

    <table style="width: 100%; border-collapse: collapse;margin-left:40px;">
        <tbody>
            <tr>
                <td class="left" style="width: 55%;font-size: 25px;">
                    {{ $request['name_en'] }}
                </td>
                <td class="left" style="width: 60%;font-size: 25px;">
                    <span class="right">:</span> {{ $data->name_en }}
                    <!-- Notice the space character before the Blade directive -->
                </td>
                <td class="center" style="width: 30%; font-size: 25px;" rowspan="5">
                    <div style="text-decoration: underline;">
                        @if ($image)
                            <img src="{{ $image }}" alt="{{ $data->name_en }}">
                        @endif
                    </div>
                    <div style="font-size: 20px; ">
                        {{ $request['language'] == 'en' ? " Applicant's Image" : 'আবেদনকারীর ছবি' }}
                    </div>
                </td>
            </tr>
            <tr>
                <td class="left" style="width: 40%;font-size: 25px;">
                    {{ $request['name_bn'] }}
                </td>
                <td class="left" style="width: 60%;font-size: 25px;">
                    <span class="right">:</span> {{ $data->name_bn }}
                </td>
                <td class="right" style="width: 30%; font-size: 25px;"></td>
            </tr>
             <tr>
                <td class="left" style="width: 40%;font-size: 25px;">
                    {{ $request['gender'] }}
                </td>
                <td class="left" style="width: 60%;font-size: 25px;">
                    <span class="right">:</span>
                      {{ $request['language'] == 'en' ? $data->gender->value_en : $data->gender->value_bn }}

                    <!-- Notice the space character before the Blade directive -->
                </td>
                <td class="right" style="width: 30%; font-size: 25px;"></td>
            </tr>
            <tr>
                <td class="left" style="width: 40%;font-size: 25px;">
                    {{ $data->verification_type == 1 ? $request['nid'] : $request['brn'] }}
                </td>
                <td class="left" style="width: 60%;font-size: 25px;">
                    <span class="right">:</span>
                      {{ $request['language'] == 'en' ? $data->verification_number : \App\Helpers\Helper::englishToBangla($data->verification_number) }}
                    <!-- Notice the space character before the Blade directive -->
                </td>

            </tr>
            <tr>
                <td class="left" style="width: 40%;font-size: 25px;">
                     {{ $request['nationality'] }}
                </td>
                <td class="left" style="width: 60%;font-size: 25px;">
                    <span class="right">:</span>
                      {{ $request['language'] == 'en' ? $data->Nationality?->value_en : $data->Nationality?->value_bn }}
                     {{-- {{ $data->Nationality }} --}}
                    <!-- Notice the space character before the Blade directive -->
                </td>

            </tr>
            <tr>
                <td class="left" style="width: 40%;font-size: 25px;">
                    {{ $request['mobile'] }}
                </td>
                <td class="left" style="width: 60%;font-size: 25px;">
                    <span class="right">:</span>
                      {{ $request['language'] == 'en' ? $data->mobile : \App\Helpers\Helper::englishToBangla($data->mobile) }}

                    <!-- Notice the space character before the Blade directive -->
                </td>

            </tr>
            @if($data->date_of_birth)
                <tr>
                    <td class="left" style="width: 40%;font-size: 25px;">
                        {{ $request['date_of_birth'] }}
                    </td>
                    <td class="left" style="width: 60%;font-size: 25px;">
                        <span class="right">:</span>
                          {{ $request['language'] == 'en' ? \Carbon\Carbon::parse($data->date_of_birth)->format('d-m-Y') : \App\Helpers\Helper::englishToBangla(\Carbon\Carbon::parse($data->date_of_birth)->format('d-m-Y')) }}

                        <!-- Notice the space character before the Blade directive -->
                    </td>
                </tr>
            @endif
            <tr>
                <td class="left" style="width: 40%;font-size: 25px;">
                    {{ $request['father_name_en'] }}
                </td>
                <td class="left" style="width: 60%;font-size: 25px;">
                    <span class="right">:</span> {{ $data->father_name_en }}
                    <!-- Notice the space character before the Blade directive -->
                </td>
                <td class="center" style="width: 30%; font-size: 25px;" rowspan="5">
                    <div style="text-decoration: underline;">
                        @if ($signature)
                            <img src="{{ $signature }}" alt="{{ $data->name_en }}">
                        @endif
                    </div>
                    <div style="font-size: 20px; ">
                        {{ $request['language'] == 'en' ? "Applicant's Signature" : 'আবেদনকারীর স্বাক্ষর' }}
                    </div>
                </td>
            </tr>
            <tr>
                <td class="left" style="width: 40%;font-size: 25px;">
                    {{ $request['father_name_bn'] }}
                </td>
                <td class="left" style="width: 60%;font-size: 25px;">
                    <span class="right">:</span> {{ $data->father_name_bn }}
                    <!-- Notice the space character before the Blade directive -->
                </td>

            </tr>
            <tr>
                <td class="left" style="width: 40%;font-size: 25px;">
                    {{ $request['mother_name_en'] }}
                </td>
                <td class="left" style="width: 60%;font-size: 25px;">
                    <span class="right">:</span> {{ $data->mother_name_en }}
                    <!-- Notice the space character before the Blade directive -->
                </td>

            </tr>
            <tr>
                <td class="left" style="width: 40%;font-size: 25px;">
                    {{ $request['mother_name_bn'] }}
                </td>
                <td class="left" style="width: 60%;font-size: 25px;">
                    <span class="right">:</span> {{ $data->mother_name_bn }}
                    <!-- Notice the space character before the Blade directive -->
                </td>

            </tr>
            <tr>
                <td class="left" style="width: 40%;font-size: 25px;">
                    {{ $request['marital_status'] }}
                </td>
                <td class="left" style="width: 60%;font-size: 25px;">
                    <span class="right">:</span>
                      {{ $request['language'] == 'en' ? (isset($data->maritalStatus->value_en) ? $data->maritalStatus->value_en : 'N/A') : (isset($data->maritalStatus->value_bn) ? $data->maritalStatus->value_bn : 'N/A') }}
{{--                     @if ($data->maritalStatus->value_en == 'Married')--}}
{{--                        , &nbsp;--}}
{{--                        {{ $request['language'] == 'bn' ? $request['spouse_name_bn'] : $request['spouse_name_en'] }}:--}}
{{--                        {{ $data->spouse_name_en }}--}}
{{--                    @endif--}}
                    <!-- Notice the space character before the Blade directive -->
                </td>

            </tr>




        </tbody>
    </table>
    {{-- Personal Info End --}}

    {{-- present address start--}}
    <div class="center" style="text-decoration: underline;margin-left: 20px;font-size: 20px;"> <b>
            {{ $request['present_address'] }}</b></div>

    <table style="width: 100%; border-collapse: collapse;margin-left:30px;">
        <tbody>
            <tr>
                <td class="left" style="width: 42%;font-size: 20px;">
                    {{ $request['division'] }}
                </td>
                <td class="left" style="width: 60%;font-size: 20px;">
                    <span class="right">:</span>
                        @if ($data->current_location->location_type == '1')
                            {{ $request['language'] == 'bn' ? $data->current_location?->parent?->parent?->parent?->name_bn : $data->current_location?->parent?->parent?->parent?->name_en }}
                        @endif

                        @if ($data->current_location->location_type == '2' || $data->current_location->location_type == '3')
                            {{ $request['language'] == 'bn' ? $data->current_location?->parent?->parent?->parent?->parent?->name_bn : $data->current_location?->parent?->parent?->parent?->parent?->name_en }}
                        @endif
                    <!-- Notice the space character before the Blade directive -->
                </td>
                <td class="right" style="width: 30%; font-size: 20px;"></td>

            </tr>
            <tr>
                <td class="left" style="width: 40%;font-size: 20px;">
                    {{ $request['district'] }}
                </td>
                <td class="left" style="width: 60%;font-size: 20px;">
                    <span class="right">:</span>
                    @if ($data->current_location->location_type === '1')
                        {{ $request['language'] == 'bn' ? $data->current_location->parent?->parent?->name_bn : $data->current_location->parent?->parent?->name_en }}
                    @endif

                    @if ($data->current_location->location_type == '2' || $data->current_location->location_type == '3')
                        {{ $request['language'] == 'bn' ? $data->current_location->parent?->parent?->parent?->name_bn : $data->current_location->parent?->parent?->parent?->name_en }}
                    @endif
                    <!-- Notice the space character before the Blade directive -->
                </td>
                <td class="right" style="width: 30%; font-size: 20px;"></td>
            </tr>
            <tr>
                <td class="left" style="width: 40%;font-size: 20px;">
                    {{ $request['location'] }}
                </td>
                <td class="left" style="width: 60%;font-size: 20px;">
                    <span class="right">:</span>
                    @if ($data->current_location->location_type === '1')
                        {{ $request['language'] == 'bn' ? $data->current_location->parent?->name_bn : $data->current_location->parent?->name_en }}
                    @endif

                    @if ($data->current_location->location_type == '2' || $data->current_location->location_type == '3')
                        {{ $request['language'] == 'bn' ? $data->current_location->parent?->parent?->name_bn : $data->current_location->parent?->parent?->name_en }}
                    @endif

                    <!-- Notice the space character before the Blade directive -->
                </td>

            </tr>
            <tr>
                <td class="left" style="width: 40%;font-size: 20px;">
                    {{ $request['union_pouro_city'] }}
                </td>
                <td class="left" style="width: 60%;font-size: 20px;">
                    <span class="right">:</span>
                    @if ($data->current_location->location_type == '2' || $data->current_location->location_type == '3')
                        {{ $request['language'] == 'bn' ? $data->current_location->parent?->name_bn : $data->current_location->parent?->name_en }}
                    @endif
                    <!-- Notice the space character before the Blade directive -->
                </td>

            </tr>
            <tr>
                <td class="left" style="width: 40%;font-size: 20px;">
                    {{ $request['ward'] }}
                </td>
                <td class="left" style="width: 60%;font-size: 20px;">
                    <span class="right">:</span>
                    {{ $request['language'] == 'bn' ? $data->current_location->name_bn : $data->current_location->name_en }}
                    <!-- Notice the space character before the Blade directive -->
                </td>

            </tr>
        </tbody>
    </table>
    {{-- present address end--}}

    {{-- permanent address start--}}
    <div class="center" style="text-decoration: underline;margin-left: 40px;font-size: 20px;"> <b>
            {{ $request['permanent_address'] }}</b></div>

    <table style="width: 100%; border-collapse: collapse;margin-left:30px;">
        <tbody>
            <tr>
                <td class="left" style="width: 42%;font-size: 20px;">
                    {{ $request['division'] }}
                </td>
                <td class="left" style="width: 60%;font-size: 20px;">
                    <span class="right">:</span>
                    @if ($data->permanent_location?->location_type == '1')
                        {{ $request['language'] == 'bn' ? $data->permanent_location?->parent?->parent?->parent?->name_bn : $data->permanent_location?->parent?->parent?->parent?->name_en }}
                    @endif

                    @if ($data->permanent_location?->location_type == '2' || $data->permanent_location?->location_type == '3')
                        {{ $request['language'] == 'bn' ? $data->permanent_location?->parent?->parent?->parent?->parent?->name_bn : $data->permanent_location?->parent?->parent?->parent?->parent?->name_en }}
                    @endif
                    <!-- Notice the space character before the Blade directive -->
                </td>
                <td class="right" style="width: 30%; font-size: 20px;"></td>

            </tr>
            <tr>
                <td class="left" style="width: 40%;font-size: 20px;">
                    {{ $request['district'] }}
                </td>
                <td class="left" style="width: 60%;font-size: 20px;">
                    <span class="right">:</span>
                    @if ($data->permanent_location?->location_type == '1')
                        {{ $request['language'] == 'bn' ? $data->permanent_location?->parent?->parent?->name_bn : $data->permanent_location?->parent?->parent?->name_en }}
                    @endif

                    @if ($data->permanent_location?->location_type == '2' || $data->permanent_location?->location_type == '3')
                        {{ $request['language'] == 'bn' ? $data->permanent_location?->parent?->parent?->parent?->name_bn : $data->permanent_location?->parent?->parent?->parent?->name_en }}
                    @endif
                    <!-- Notice the space character before the Blade directive -->
                </td>
                <td class="right" style="width: 30%; font-size: 20px;"></td>
            </tr>
            <tr>
                <td class="left" style="width: 40%;font-size: 20px;">
                    {{ $request['location'] }}
                </td>
                <td class="left" style="width: 60%;font-size: 20px;">
                    <span class="right">:</span>
                    @if ($data->permanent_location?->location_type == '1')
                        {{ $request['language'] == 'bn' ? $data->permanent_location?->parent?->name_bn : $data->permanent_location?->parent?->name_en }}
                    @endif

                    @if ($data->permanent_location?->location_type == '2' || $data->permanent_location?->location_type == '3')
                        {{ $request['language'] == 'bn' ? $data->permanent_location?->parent?->parent?->name_bn : $data->permanent_location?->parent?->parent?->name_en }}
                    @endif

                    <!-- Notice the space character before the Blade directive -->
                </td>

            </tr>
            <tr>
                <td class="left" style="width: 40%;font-size: 20px;">
                    {{ $request['union_pouro_city'] }}
                </td>
                <td class="left" style="width: 60%;font-size: 20px;">
                    <span class="right">:</span>
                    @if ($data->permanent_location?->location_type == '2' || $data->permanent_location?->location_type == '3')
                        {{ $request['language'] == 'bn' ? $data->permanent_location?->parent?->name_bn : $data->permanent_location?->parent?->name_en }}
                    @endif
                    <!-- Notice the space character before the Blade directive -->
                </td>

            </tr>
            <tr>
                <td class="left" style="width: 40%;font-size: 20px;">
                    {{ $request['ward'] }}
                </td>
                <td class="left" style="width: 60%;font-size: 20px;">
                    <span class="right">:</span>
                    {{ $request['language'] == 'bn' ? $data->permanent_location?->name_bn : $data->permanent_location?->name_en }}
                    <!-- Notice the space character before the Blade directive -->
                </td>
            </tr>
        </tbody>
    </table>

    <br>
    <br>
    <br>
    <br>
    <br>
    <br>
{{--    Bank/Mfs Info Start--}}
    <div class="center" style="text-decoration: underline;margin-left: 40px;font-size: 20px;"> <b>
            {{ $request['bank_info'] }}</b></div>

    <table style="width: 100%; border-collapse: collapse;margin-left:30px;">
        <tbody>
        <tr>
            <td class="left" style="width: 40%;font-size: 20px;">
                {{ $request['account_ownership'] }}
            </td>
            <td class="left" style="width: 60%;font-size: 20px;">
                <span class="right">:</span>
                {{ $request['language'] == 'en' ? (isset($data->accountOwner->value_en) ? $data->accountOwner->value_en : 'N/A') : (isset($data->accountOwner->value_bn) ? $data->accountOwner->value_bn : 'N/A') }}

                <!-- Notice the space character before the Blade directive -->
            </td>
            <td class="right" style="width: 30%; font-size: 20px;"></td>

        </tr>
        <tr>
            <td class="left" style="width: 40%;font-size: 20px;">
                {{ $request['account_no'] }}
            </td>
            <td class="left" style="width: 60%;font-size: 20px;">
                <span class="right">:</span>
                {{ $request['language'] == 'en' ? $data->account_number : \App\Helpers\Helper::englishToBangla($data->account_number) }}

                <!-- Notice the space character before the Blade directive -->
            </td>
            <td class="right" style="width: 30%; font-size: 20px;"></td>
        </tr>

        @if ($data->account_type == 1)
            <tr>
                <td class="left" style="width: 40%;font-size: 20px;">
                    {{  $request['bank_name'] }}
                </td>
                <td class="left" style="width: 60%;font-size: 20px;">
                    <span class="right">:</span>
                    {{ $request['language'] == 'en' ? (isset($data->bank->name_en) ? $data->bank->name_en : 'N/A') : (isset($data->bank->name_bn) ? $data->bank->name_bn : 'N/A') }}

                    <!-- Notice the space character before the Blade directive -->
                </td>
                <td class="right" style="width: 30%; font-size: 20px;"></td>
            </tr>
        @endif
        @if ($data->account_type == 2)
            <tr>
                <td class="left" style="width: 40%;font-size: 20px;">
                    {{ $request['mfs_name'] }}
                </td>
                <td class="left" style="width: 60%;font-size: 20px;">
                    <span class="right">:</span>
                    {{ $request['language'] == 'en' ? (isset($data->mfs->name_en) ? $data->mfs->name_en : 'N/A') : (isset($data->mfs->name_bn) ? $data->mfs->name_bn : 'N/A') }}


                    <!-- Notice the space character before the Blade directive -->
                </td>
                <td class="right" style="width: 30%; font-size: 20px;"></td>
            </tr>
        @endif
        @if ($data->account_type == 1)
            <tr>
                <td class="left" style="width: 40%;font-size: 20px;">
                    {{ $request['branch_name'] }}
                </td>
                <td class="left" style="width: 60%;font-size: 20px;">
                    <span class="right">:</span>
                    {{ $request['language'] == 'en' ? (isset($data->branch->name_en) ? $data->branch->name_en : 'N/A') : (isset($data->branch->name_bn) ? $data->branch->name_bn : 'N/A') }}

                    <!-- Notice the space character before the Blade directive -->
                </td>
                <td class="right" style="width: 30%; font-size: 20px;"></td>
            </tr>
        @endif

        </tbody>
    </table>

{{--    Bank/Mfs Info End--}}

{{--    Nominee Start--}}
    <br>
    <div class="center" style="text-decoration: underline;margin-left: 40px;font-size: 20px;"> <b>
            {{ $request['nominee_info'] }}</b></div>

    <table style="width: 100%; border-collapse: collapse;margin-left:40px;">
        <tbody>
            <tr>
                <td class="left" style="width: 55%;font-size: 25px;">
                    {{ $request['nominee_en'] }}
                </td>
                <td class="left" style="width: 60%;font-size: 25px;">
                    <span class="right">:</span>
                    {{
                        $request['language'] == 'bn'
                        ?
                        $data->nominee_bn
                        :
                        $data->nominee_en
                    }}
                    <!-- Notice the space character before the Blade directive -->
                </td>
                <td class="center" style="width: 30%; font-size: 25px;" rowspan="5">
                    <div style="text-decoration: underline;">
                        @if ($nominee_image)
                            <img src="{{ $nominee_image }}" alt="{{ $data->nominee_en }}">
                        @endif
                    </div>
                    <div style="font-size: 25px; ">
                        {{ $request['language'] == 'en' ? ' Nominee Image' : 'নমিনীর ছবি' }}
                    </div>
                </td>
            </tr>
            <tr>
                <td class="left" style="width: 40%;font-size: 25px;">
                    {{ $data->nominee_verification_type == 1 ? $request['nid'] : $request['brn'] }}
                </td>
                <td class="left" style="width: 60%;font-size: 25px;">
                    <span class="right">:</span> {{ $request['language'] == 'bn' ? \App\Helpers\Helper::englishToBangla($data->nominee_verification_number) : $data->nominee_verification_number }}
                </td>
                <td class="right" style="width: 30%; font-size: 25px;"></td>
            </tr>
            <tr>
                <td class="left" style="width: 40%;font-size: 25px;">
                    @if($data->nominee_date_of_birth)
                        {{ $request['date_of_birth'] }}
                    @endif
                </td>
                <td class="left" style="width: 60%;font-size: 25px;">
                    @if($data->nominee_date_of_birth)
                        <span class="right">:</span>
                        {{ $request['language'] == 'en' ? \Carbon\Carbon::parse($data->nominee_date_of_birth)->format('d-m-Y') : \App\Helpers\Helper::englishToBangla(\Carbon\Carbon::parse($data->nominee_date_of_birth)->format('d-m-Y')) }}
                        <!-- Notice the space character before the Blade directive -->
                    @endif
                </td>
                <td class="right" style="width: 30%; font-size: 25px;"></td>
            </tr>

            <tr>
                <td class="left" style="width: 40%;font-size: 25px;">
                    {{ $request['nationality'] }}
                </td>
                <td class="left" style="width: 60%;font-size: 25px;">
                    <span class="right">:</span>
                    @if (!empty($data->get_nominee_nationality->value_en) && !empty($data->get_nominee_nationality->value_bn))
                        {{ $request['language'] == 'en' ? $data->get_nominee_nationality->value_en : $data->get_nominee_nationality->value_bn }}
                    @endif
                    <!-- Notice the space character before the Blade directive -->
                </td>
                <td class="right" style="width: 30%; font-size: 25px;"></td>
            </tr>

            <tr>
                <td class="left" style="width: 40%;font-size: 25px;">
                    {{ $request['relationship'] }}
                </td>
                <td class="left" style="width: 60%;font-size: 25px;">
                    <span class="right">:</span>

                    @if (!empty($data->get_nominee_relationship->value_en) && !empty($data->get_nominee_relationship->value_bn))
                        {{ $request['language'] == 'en' ? $data->get_nominee_relationship->value_en : $data->get_nominee_relationship->value_bn }}
                    @endif

                    <!-- Notice the space character before the Blade directive -->
                </td>
                <td class="right" style="width: 30%; font-size: 25px;"></td>
            </tr>
            <tr>
                <td class="left" style="width: 40%;font-size: 25px;">
                    {{ $request['nominee_address'] }}
                </td>
                <td class="left" style="width: 60%;font-size: 25px;">
                    <span class="right">:</span> {{ $data->nominee_address }}
                    <!-- Notice the space character before the Blade directive -->
                </td>
                <td class="center" style="width: 30%; font-size: 25px;" rowspan="5">
                    <div style="text-decoration: underline;">
                        @if ($nominee_signature)
                            <img src="{{ $nominee_signature }}" alt="{{ $data->nominee_en }}">
                        @endif
                    </div>
                    <div style="font-size: 25px; ">
                        {{ $request['language'] == 'en' ? 'Nominee Signature' : 'নমিনীর স্বাক্ষর' }}
                    </div>
                </td>
            </tr>

            <tr>
                <td class="left" style="width: 40%;font-size: 25px;"></td>
                <td class="left" style="width: 60%;font-size: 25px;"></td>
                <td class="right" style="width: 30%; font-size: 25px;"></td>
            </tr>
            <tr>
                <td class="left" style="width: 40%;font-size: 25px;"></td>
                <td class="left" style="width: 60%;font-size: 25px;"></td>
                <td class="right" style="width: 30%; font-size: 25px;"></td>
            </tr><tr>
                <td class="left" style="width: 40%;font-size: 25px;"></td>
                <td class="left" style="width: 60%;font-size: 25px;"></td>
                <td class="right" style="width: 30%; font-size: 25px;"></td>
            </tr><tr>
                <td class="left" style="width: 40%;font-size: 25px;"></td>
                <td class="left" style="width: 60%;font-size: 25px;"></td>
                <td class="right" style="width: 30%; font-size: 25px;"></td>
            </tr>

        </tbody>
    </table>

{{--    Nominee End--}}


{{--    <table style="width: 100%; border-collapse: collapse; margin-top: 20px;">--}}
{{--        <tr>--}}
{{--            <!-- Present Address Column -->--}}
{{--            <td style="width: 50%; border: 1px solid #000; padding: 10px; vertical-align: top;">--}}
{{--                <div style="text-align: center; text-decoration: underline; font-weight: bold; font-size: 18px;">--}}
{{--                    Present Address--}}
{{--                </div>--}}
{{--                <p style="margin: 10px 0;">--}}
{{--                    <strong>Division</strong>: RANGPUR--}}
{{--                </p>--}}
{{--            </td>--}}

{{--            <!-- Permanent Address Column -->--}}
{{--            <td style="width: 50%; border: 1px solid #000; padding: 10px; vertical-align: top;">--}}
{{--                <div style="text-align: center; text-decoration: underline; font-weight: bold; font-size: 18px;">--}}
{{--                    Permanent Address--}}
{{--                </div>--}}
{{--                <p style="margin: 10px 0;">--}}
{{--                    <strong>Division</strong>: RANGPUR--}}
{{--                </p>--}}
{{--            </td>--}}
{{--        </tr>--}}
{{--    </table>--}}




    <htmlpageheader name="page-header">
    </htmlpageheader>

    <htmlpagefooter name="page-footer">
        <div class="footer mt-2 pt-2">
             @if ($request['language'] == 'en')
               Copyright <img src="{{ public_path('image/footer.png') }}" alt="N/A" style="width: 12px;height: auto;margin-left: 7px; margin-right: 7px; margin-top: 15px; ">{{ date('Y') }}, DSS
             @else
               কপিরাইট <img src="{{ public_path('image/footer.png') }}" alt="N/A" style="width: 12px;height: auto;margin-left: 7px; margin-right: 7px; margin-top: 15px; ">{{ \App\Helpers\Helper::englishToBangla(date('Y')) }}, ডিএসএস
            @endif
              {{-- <img src="{{ public_path('image/footer.png') }}" alt="Left Image"
                        style="width: 15px; height: auto;">
            {{ $request['language'] == 'en' ? 'Copyright  ©, ' . date('Y '). ', DSS' : 'কপিরাইট ©, ' . \App\Helpers\Helper::englishToBangla(date('Y ')). ', ডিএসএস' }} --}}

        </div>
    </htmlpagefooter>


</body>

</html>
