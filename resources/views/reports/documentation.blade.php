<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>API Documentation</title>

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
        .center{
             text-align: center;
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
         .table-with-border {
        border: 1px solid black;
         border-collapse: collapse;
    }
    .table-with-border th,
    .table-with-border td {
       border: 1px solid black;
    }
    </style>
</head>
<body>
  

<p>Listed Report |Department of Social Services </p>

<div class="title-container">
    <!-- Empty div for the first table -->
</div>




<table style="border: none;">
    <tbody>
        <tr>
           <td class="left">
    <img src="{{ public_path('image/bangladesh-govt-logo.png') }}" alt="Left Image" style="width: 100px; height: auto;">
   
</td>
</td>
          
            <td class="center">
                <h3 class="title">
                    Government of the People's Republic of Bangladesh <br>
                    Department of Social Services
                </h3>
                <p style="font-size:15px" class="center">Cash Transfer Modernization(CTM)Project</p>
                <p style="font-size:12px">Social Service Building, E-8/B-1, Agargaon, Sherbangla Nagar, Dhaka-1207, Bangladesh.</p>
                <a target="_blank" href="https://dss.gov.bd/">www.dss.gov.bd</a>
            </td>
       
            <td class="right">  <img src="{{ public_path('image/logo.png') }}" alt="Right Image" style="width: 80px; height: 80px;"></td>
        </tr>
    </tbody>
</table>
<br>
<div class="center" style="font-size: 15px; margin-bottom: 1; padding-bottom: 1;">
    <b>API Receiver Details</b>
    <b><hr style="border-top: 2px solid black; width: 25%; margin-top: 1; padding-top: 1;"></b>
</div>

 <br>
<table style="width: 100%; border-collapse: collapse;margin-left:40px;">
 <tbody>
     <tr>
    <td class="left" style="width:30% ;font-size: 15px;">
        Organization Name
    </td>
 <td class="center" style=" width:10%;font-size: 15px;">
     <span class="right">:</span> 
    <!-- Notice the space character before the Blade directive -->
</td>
 <td class="left" style="width:60%;font-size: 15px;">
   {{ $testDATA->organization_name}}
</td>
</tr>
     <tr>
    <td class="left" style="width:30% ;font-size: 15px;">
        Organization Phone
    </td>
 <td class="center" style=" width:10%;font-size: 15px;">
     <span class="right">:</span> 
    <!-- Notice the space character before the Blade directive -->
</td>
 <td class="left" style="width:60%;font-size: 15px;">
   {{ $testDATA->organization_phone}}
</td>
</tr>
  
   <tr>
    <td class="left" style="width:30% ;font-size: 15px;">
        Organization Email
    </td>
 <td class="center" style=" width:10%;font-size: 15px;">
     <span class="right">:</span> 
    <!-- Notice the space character before the Blade directive -->
</td>
 <td class="left" style="width:60%;font-size: 15px;">
   {{ $testDATA->organization_email}}
</td>
</tr>
   <tr>
    <td class="left" style="width:30% ;font-size: 15px;">
        Responsible Person Email
    </td>
 <td class="center" style=" width:10%;font-size: 15px;">
     <span class="right">:</span> 
    <!-- Notice the space character before the Blade directive -->
</td>
 <td class="left" style="width:60%;font-size: 15px;">
   {{ $testDATA->responsible_person_email}}
</td>
</tr>
   <tr>
    <td class="left" style="width:30% ;font-size: 15px;">
        Responsible Person NID
    </td>
 <td class="center" style=" width:10%;font-size: 15px;">
     <span class="right">:</span> 
    <!-- Notice the space character before the Blade directive -->
</td>
 <td class="left" style="width:60%;font-size: 15px;">
   {{ $testDATA->responsible_person_nid}}
</td>
</tr>
 <tr>
    <td class="left" style="width:30% ;font-size: 15px;">
       Auth Key
    </td>
 <td class="center" style=" width:10%;font-size: 15px;">
     <span class="right">:</span> 
    <!-- Notice the space character before the Blade directive -->
</td>
 <td class="left" style="width:60%;font-size: 15px;">
   {{ $testDATA->username}}
</td>
</tr>
 <tr>
    <td class="left" style="width:30% ;font-size: 15px;">
       Secret Key
    </td>
 <td class="center" style=" width:10%;font-size: 15px;">
     <span class="right">:</span> 
    <!-- Notice the space character before the Blade directive -->
</td>
 <td class="left" style="width:60%;font-size: 15px;">
   {{ $testDATA->api_key}}
</td>
</tr>
 <tr>
    <td class="left" style="width:30% ;font-size: 15px;">
       Server IP Address
    </td>
 <td class="center" style=" width:10%;font-size: 15px;">
     <span class="right">:</span> 
    <!-- Notice the space character before the Blade directive -->
</td>
 <td class="left" style="width:60%;font-size: 15px;">
   {{ $testDATA->whitelist_ip}}
</td>
</tr>
 <tr>
    <td class="left" style="width:30% ;font-size: 15px;">
       Total Hit
    </td>
 <td class="center" style=" width:10%;font-size: 15px;">
     <span class="right">:</span> 
    <!-- Notice the space character before the Blade directive -->
</td>
 <td class="left" style="width:60%;font-size: 15px;">
   {{ $testDATA->total_hit}}
</td>
</tr>
 <tr>
    <td class="left" style="width:30% ;font-size: 15px;">
       Start Date
    </td>
 <td class="center" style=" width:10%;font-size: 15px;">
     <span class="right">:</span> 
    <!-- Notice the space character before the Blade directive -->
</td>
 <td class="left" style="width:60%;font-size: 15px;">
   {{ $testDATA->start_date}}
</td>
</tr>
 <tr>
    <td class="left" style="width:30% ;font-size: 15px;">
       End Date
    </td>
 <td class="center" style=" width:10%;font-size: 15px;">
     <span class="right">:</span> 
    <!-- Notice the space character before the Blade directive -->
</td>
 <td class="left" style="width:60%;font-size: 15px;">
   {{ $testDATA->end_date}}
</td>
</tr>
    
    </tbody>
</table>

<br>
<div class="center" style="font-size: 15px; margin-bottom: 1; padding-bottom: 1;">
    <b>API Details</b>
    <b><hr style="border-top: 2px solid black; width: 20%; margin-top: 1; padding-top: 1;"></b>
</div>





    <table class="table-with-border">
        <thead>
            <tr>
                <th>SL</th>
                <th>API</th>
                <th>Parameter</th>
                <th>URL</th>
               
                <!-- Add more table headers as needed -->
            </tr>
        </thead>
        <tbody>
            @foreach($testDATA->ApiList as $key=>$api)
            <tr>
                   <td class="center">{{ $key+1}}</td>
                <td  class="center">{{ $api->name }}</td>
                <td  class="center">
                    <ul>
                        @foreach($api->selected_columns as $column)
                        <li>{{ $column }}</li>
                        @endforeach
                    </ul>
                </td>
                <td  class="center">{{ $api->purpose->url }}</td>
                <!-- Add more table cells as needed -->
            </tr>
            @endforeach
        </tbody>
    </table>









<htmlpageheader name="page-header">
</htmlpageheader>

<htmlpagefooter name="page-footer">
    <div class="footer">
    Copyright @, " . date("Y ") . ", DSS" 
   
</div>
</htmlpagefooter>

</body>
</html>
