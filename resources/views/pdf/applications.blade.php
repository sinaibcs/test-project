<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>


    <style>
        @page {
            header: page-header;
            footer: page-footer;
        }
    </style>

    <style>

        body {
            font-family: 'kalpurush' !important;
        }

        table {
            font-family: 'kalpurush' !important;
            border-collapse: collapse;
            width: 100%;
        }

        td, th {
            border: 1px solid #dddddd;
            text-align: left;
            padding: 8px;
            font-size: 14px;
        }

        .designation {
            width: 33%;
            float: left;
            text-align: center;
            font-weight: bold;
            font-size: 13px;
        }

        .title {
            font-size: 20px;
            text-align: center;
        }

        .para-title {
            text-align: center;
            font-size: 14px;
        }

        /* tr:nth-child(even) {
          background-color: #dddddd;
        } */
        .box {
            width: 530px;
            height: 200px;
            margin-left: 100px;
            padding: 20px;
            border: 1px solid black;
            overflow-y: hidden;
            font-size: 14px;
            /*margin-bottom: 20px;*/
        }
    </style>
</head>
<body>

<htmlpageheader name="page-header">
    Your Header Content
</htmlpageheader>

<htmlpagefooter name="page-footer">
    Your Footer Content
</htmlpagefooter>



<h2 class="title">
    গণপ্রজাতন্ত্রী বাংলাদেশ সরকার
    সমাজসেবা অধিদফতর
    ক্যাশ ট্রান্সফার মডার্নাইজেশন (সিটিএম) প্রকল্প
    শ্যামলী স্কোয়ার, ২৪/১-২, মিরপুর রোড, ঢাকা -১২০৭

    test data

</h2>

<table>

    <thead>

    <tr>
        <th>ক্রঃ নং</th>
        <th>আইডি</th>
        <th>নাম</th>
        <th>পিতার নাম</th>
        <th>প্রোগ্রাম নাম</th>
        <th>জেলা</th>
        <th>সিটি / জেলা পৌর / উপজেলা</th>
        <th>থানা /ইউনিয়ন /পৌর</th>
        <th>ওয়ার্ড</th>
        <th>একাউন্ট</th>
        <th>প্রোভার্টি স্কোর</th>
    </tr>

    </thead>

    <tbody>

    @if($applications->count() > 0)

        @foreach($applications as $key => $row)

            <tr>
                <td> {{ $key+1 }} </td>
                <td>{{ $row->application_id }}</td>
                <td>{{ $row->name_bn }}</td>
                <td>{{ $row->father_name_bn }}</td>
                <td>{{ $row->program?->name }}</td>
                <td>{{ $row->district?->name_bn }}</td>
                <td>{{ $row->cityCorporation?->name_bn ?: ($row->districtPouroshova?->name_bn ?: $row->upazila?->name_bn) }}</td>
                <td>{{ $row->cityCorporation?->name_bn ?: ($row->districtPouroshova?->name_bn ?: $row->upazila?->name_bn) }}</td>
                <td>{{ $row->ward?->name_bn }}</td>
                <td>{{ $row->account_number }}</td>
                <td>{{ $row->score }}</td>
            </tr>

        @endforeach

    @else

        <tr>
            <td colspan="6" style="width: 100%; text-align: center">
                <p>
                    কোন তথ্য পাওয়া যায়নি
                </p>
            </td>
        </tr>

    @endif


    </tbody>

</table>

</body>
</html>











