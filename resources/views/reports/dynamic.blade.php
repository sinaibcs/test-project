<!DOCTYPE html>
<html lang="{{ $language }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>{{ $fileName }}</title>

    <style>
        body {
            font-family: 'kalpurush', sans-serif !important;
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
            color: #312f2f;
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
    </style>
</head>
<body>

<p>{{ $language == 'en' ? "Listed Report | Department of Social Services" : "তালিকাভুক্ত  রিপোর্ট | সমাজসেবা অধিদফতর" }}</p>

<div class="title-container"></div>

<table>
    <tbody>
    <tr>
        <td class="left">
            <img src="{{ public_path('image/bangladesh-govt-logo.png') }}" alt="Left Image"
                 style="width: 100px; height: auto;">
        </td>
        @if($language == "en")
            <td class="center">
                <h3 class="title">
                    Government of the People's Republic of Bangladesh <br>
                    Department of Social Services
                </h3>
                <p style="font-size:15px" class="center">Cash Transfer Modernization(CTM)Project</p>
                <p style="font-size:12px">Social Service Building, E-8/B-1, Agargaon, Sherbangla Nagar, Dhaka-1207,
                    Bangladesh.</p>
                <a target="_blank" href="https://dss.gov.bd/">www.dss.gov.bd</a>
            </td>
        @else
            <td class="center">
                <h3 class="title">
                    গণপ্রজাতন্ত্রী বাংলাদেশ সরকার <br>
                    সমাজসেবা অধিদফতর
                </h3>
                <p style="font-size:15px" class="center">ক্যাশ ট্রান্সফার মডার্নাইজেশন (সিটিএম) প্রকল্প</p>
                <p style="font-size:12px">সমাজসেবা ভবন, ই-৮/বি-১, আগারগাঁও, শেরেবাংলা নগর, ঢাকা-১২০৭, বাংলাদেশ।</p>
                <a target="_blank" href="https://dss.gov.bd/">www.dss.gov.bd</a>
            </td>
        @endif
        <td class="right">
            <img src="{{ public_path('image/logo.png') }}" alt="Right Image" style="width: 80px; height: 80px;">
        </td>
    </tr>
    </tbody>
</table>

<table class="border-table">
    <thead>
    <tr>
        @foreach($headerInfo as $chunkList)
            <th>{{ $chunkList }}</th>
        @endforeach
    </tr>
    </thead>
    <tbody>
    @foreach($dataInfo as $data)
        <tr>
            @foreach($data as $chunkList)
                <td style="max-width: 150px; overflow-wrap: break-word; text-align: center">
                    {{ $chunkList }}
                </td>
            @endforeach
        </tr>
    @endforeach
    </tbody>
</table>

<div class="footer">
    {!! $language == 'en' ? "Copyright @ " . date("Y") . ", DSS" : "কপিরাইট @ " . \App\Helpers\Helper::englishToBangla(date("Y")) . ", ডিএসএস" !!}
    <div align="right"><b>{PAGENO} / {nbpg}</b></div>
</div>

</body>
</html>
