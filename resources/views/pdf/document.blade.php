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
        <th>{{ __('ছাত্র ছাত্রীর নাম ও সম্পর্ক') }}</th>
        <th>{{ __('আবেদনকারীর নাম ও কর্মস্থল') }}</th>
        <th>{{ __('ফলাফল') }}</th>
        <th>{{ __('টাকার পরিমাণ') }}</th>
        <th style="width: 15%;">{{ __('মন্তব্য') }}</th>

    </tr>

    </thead>

    <tbody>

    @if(count($applications) > 0)

        @foreach($applications as $key => $row)

            <tr>

                <td> {{ $key+1 }} </td>

                <td>
                    @include('table-action.applications.education.name')
                </td>
                <td>
                    @include('table-action.applications.education.applicant')
                </td>
                <td>
                    @include('table-action.applications.education.exam_result')
                </td>

                <td>{{ $row->approve_amount }}</td>

                <td></td>


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










