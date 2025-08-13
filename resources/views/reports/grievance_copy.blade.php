<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>{{ $title }}/title>

        <style>
            body {
                font-family: 'kalpurush' !important;
                margin: 0;
                padding: 0;
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
 
<table style="width: 100%;">
    <tr>
        <td style="text-align: left;">
            {{ $request['language'] == 'en' ? 'Listed Report | Department of Social Services' : 'তালিকাভুক্ত  রিপোর্ট | সমাজসেবা অধিদফতর' }}
        </td>
        <td style="text-align: right;">
             @if ($request['language'] == 'en')
               Grievance Application Date: {{ $data->created_at}}
             @else
               অভিযোগের আবেদনের তারিখ: {{ \App\Helpers\Helper::englishToBangla($data->created_at) }}
            @endif

            {{-- {{ $request['language'] == 'en' ? 'Grievance Application Date: '$data->created_at : '
অভিযোগের আবেদনের তারিখ: '{{ \App\Helpers\Helper::englishToBangla($data->created_at) }} }} --}}
        </td>
    </tr>
</table>






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

    <table style="width: 100%; border-collapse: collapse;margin-left:40px;">
        <tbody>
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
            <tr>
                <td class="left" style="width: 40%;font-size: 20px;">
                    {{ $request['language'] == 'en' ? 'Tracking No' : 'ট্ট্র্যাকিং নম্বর' }}
                </td>
                <td class="left" style="width: 60%;font-size: 20px;">
                    <span class="right">:</span>
                    {{ $request['language'] == 'bn' ? $data->tracking_no : $data->tracking_no }}

                    <!-- Notice the space character before the Blade directive -->
                </td>
                <td class="right" style="width: 30%; font-size: 20px;"></td>
            </tr>




        </tbody>
    </table>
    <div class="center" style="width: 100%;text-decoration: underline;margin-left: 60px;font-size: 20px;"> <b>
            {{ $request['personal_info'] }}</b></div>

    <table style="width: 100%; border-collapse: collapse;margin-left:50px;">
        <tbody>
            <tr>
                <td class="left" style="width: 50%;font-size: 25px;">
                    {{ $request['language'] == 'en' ? 'Name' : 'নাম' }}
                </td>
                <td class="left" style="width: 30%;font-size: 25px;">
                    <span class="right">:</span> {{ $data->name }}
                    <!-- Notice the space character before the Blade directive -->
                </td>
                <td class="center" style="width: 20%; font-size: 25px;" rowspan="5">
                    <div style="text-decoration: underline;">
                        {{-- <img src="{{ $image }}" alt="Your Image"> --}}
                    </div>



                </td>

            </tr>
            <tr>
                <td class="left" style="width: 40%;font-size: 25px;">

                </td>
                <td class="left" style="width: 60%;font-size: 25px;">

                    <!-- Notice the space character before the Blade directive -->
                </td>
                <td class="right" style="width: 30%; font-size: 25px;"></td>
            </tr>
            <tr>
                <td class="left" style="width: 40%;font-size: 25px;">
                    {{ $request['language'] == 'bn' ? "লিঙ্গ" : "Gender" }}
                </td>
                <td class="left" style="width: 60%;font-size: 25px;">
                    <span class="right">:</span>  {{ $request['language'] == 'bn' ? $data->gender->value_bn : $data->gender->value_en }}
                    <!-- Notice the space character before the Blade directive -->
                </td>

            </tr>
            <tr>
                <td class="left" style="width: 40%;font-size: 25px;">
                    {{ $request['nid'] }}
                </td>
                <td class="left" style="width: 60%;font-size: 25px;">
                    <span class="right">:</span> {{ $data->verification_number }}
                    <!-- Notice the space character before the Blade directive -->
                </td>

            </tr>
            <tr>
                <td class="left" style="width: 40%;font-size: 25px;">
                    {{ $request['language'] == 'en' ? 'Email' : 'ইমেইল' }}
                </td>
                <td class="left" style="width: 60%;font-size: 25px;">
                    <span class="right">:</span> {{ $data->email }}
                    <!-- Notice the space character before the Blade directive -->
                </td>

            </tr>
            <tr>
                <td class="left" style="width: 40%;font-size: 25px;">
                    {{ $request['mobile'] }}
                </td>
                <td class="left" style="width: 60%;font-size: 25px;">
                    <span class="right">:</span> {{ $data->mobile }}
                    <!-- Notice the space character before the Blade directive -->
                </td>

            </tr>

            <tr>
                <td class="left" style="width: 40%;font-size: 25px;">
                    {{ $request['date_of_birth'] }}
                </td>
                <td class="left" style="width: 60%;font-size: 25px;">
                    <span class="right">:</span> {{ $data->date_of_birth }}
                    <!-- Notice the space character before the Blade directive -->
                </td>

            </tr>


        </tbody>
    </table>
    <div class="center" style="text-decoration: underline;margin-left: 40px;font-size: 20px;"> <b>
            {{ $request['language'] == 'en' ? 'Details of the Complaint ' : 'অভিযোগের বিশদ বিবরণ' }}</b></div>

    <table style="width: 100%; border-collapse: collapse;margin-left:40px;">
        <tbody>
            <tr>
                <td class="left" style="width: 40%;font-size: 20px;">
                    {{ $request['language'] == 'en' ? 'Grievance Type' : 'অভিযোগের ধরন' }}
                </td>
                <td class="left" style="width: 60%;font-size: 20px;">
                    <span class="right">:</span>  {{ $request['language'] == 'bn' ? $data->grievanceType->title_bn : $data->grievanceType->title_en }}
                    <!-- Notice the space character before the Blade directive -->
                </td>
                <td class="right" style="width: 30%; font-size: 20px;"></td>

            </tr>
            <tr>
                <td class="left" style="width: 40%;font-size: 20px;">
                    {{ $request['language'] == 'en' ? 'Grievance Subject' : 'অভিযোগের বিষয়' }}
                </td>
                <td class="left" style="width: 60%;font-size: 20px;">
                    <span class="right">:</span> {{ $request['language'] == 'bn' ? $data->grievanceSubject->title_bn : $data->grievanceSubject->title_en }}
                    <!-- Notice the space character before the Blade directive -->
                </td>
                <td class="right" style="width: 30%; font-size: 20px;"></td>
            </tr>
            <tr>
                <td class="left" style="width: 40%;font-size: 20px;">
                    {{ $request['language'] == 'en' ? 'Complaint Details' : 'অভিযোগের বিবরণ' }}
                </td>
                <td class="left" style="width: 60%;font-size: 20px;">
                    <span class="right">:</span> {{ $data->details }}


                    <!-- Notice the space character before the Blade directive -->
                </td>

            </tr>
            <tr>
                <td class="left" style="width: 40%;font-size: 20px;">

                </td>
                <td class="left" style="width: 60%;font-size: 20px;">

                    <!-- Notice the space character before the Blade directive -->
                </td>

            </tr>

        </tbody>
    </table>
    {{-- <div class="center" style="text-decoration: underline;margin-left: 40px;font-size: 20px;"> <b>
           {{ $request['language'] == 'bn' ?  "অভিযোগকারীর ঠিকানা": "Complainant's Address" }}</b></div> --}}

{{-- <table style="width: 100%; border-collapse: collapse;margin-left:40px;">
 <tbody>
     <tr>
    <td class="left" style="width: 40%;font-size: 20px;">
        {{$request['division']}}
    </td>
 <td class="left" style="width: 60%;font-size: 20px;">
     <span class="right">:</span>  {{ $request['language'] == 'bn' ? $data->division->name_bn : $data->division->name_en }}
    <!-- Notice the space character before the Blade directive -->
</td>

 <td class="right" style="width: 30%; font-size: 20px;">
    
</td>

</tr>
     <tr>
    <td class="left" style="width: 40%;font-size: 20px;">
        {{$request['district']}}
    </td>
 <td class="left" style="width: 60%;font-size: 20px;">
     <span class="right">:</span>  {{ $request['language'] == 'bn' ? $data->district->name_bn : $data->district->name_en }}
    <!-- Notice the space character before the Blade directive -->
</td>
    <td class="right" style="width: 30%; font-size: 20px;">
        
    </td>
</tr>
 <tr>
    <td class="left" style="width: 40%;font-size: 20px;">
        {{$request['location']}}
    </td>
 <td class="left" style="width: 60%;font-size: 20px;">
     <span class="right">:</span> 
  
      
    <!-- Notice the space character before the Blade directive -->
</td>

</tr>
    <tr>
    <td class="left" style="width: 40%;font-size: 20px;">
        {{$request['union_pouro_city']}}
    </td>
 <td class="left" style="width: 60%;font-size: 20px;">
     <span class="right">:</span>
    <!-- Notice the space character before the Blade directive -->
</td>

</tr>
  <tr>
    <td class="left" style="width: 40%;font-size: 20px;">
        {{$request['ward']}}
    </td>
 <td class="left" style="width: 60%;font-size: 20px;">
     <span class="right">:</span>    {{ $request['language'] == 'bn' ?$data->name_bn   : $data->name_en   }}  
    <!-- Notice the space character before the Blade directive -->
</td>

</tr>     
    </tbody>
</table> --}}

    <htmlpageheader name="page-header">
    </htmlpageheader>

    <htmlpagefooter name="page-footer">
        <div class="footer">
             @if ($request['language'] == 'en')
               Copyright <img src="{{ public_path('image/footer.png') }}" alt="N/A" style="width: 12px;height: auto;margin-left: 7px; margin-right: 7px;">, {{ date('Y') }}, DSS
             @else
               কপিরাইট <img src="{{ public_path('image/footer.png') }}" alt="N/A" style="width: 12px;height: auto;margin-left: 7px; margin-right: 7px;">, {{ \App\Helpers\Helper::englishToBangla(date('Y')) }}, ডিএসএস
            @endif
            {{-- {{ $request['language'] == 'en' ? 'Copyright © ' . date('Y ').', DSS' : 'কপিরাইট © ' . \App\Helpers\Helper::englishToBangla(date('Y ')). ', ডিএসএস' }} --}}

        </div>
    </htmlpagefooter>

</body>

</html>
