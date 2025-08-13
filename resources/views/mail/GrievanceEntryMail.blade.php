<!DOCTYPE html>
<html>
<head>
    <title>Welcome Mail</title>
</head>
<body>
    <h1>Hello, {{ $name }}!</h1>
    Welcome to the CTM application.

    {{-- <p>Once your device is registered you can access the CTM Application using following credentials:</p> --}}

    <ul>
        <li>Name: {{ $name }}</li>
        {{-- <li>Mobile: {{ $mobile }}</li> --}}
        <li>Tracking No: {{ $tracking_no }}</li>


        <li>
            Tracking URL: <a href="{{env('APP_FRONTEND_URL') . '/system-audit/grievance-tracking'}}">{{env('APP_FRONTEND_URL') . '/system-audit/grievance-tracking'}}</a>
        </li>
    </ul>


    <p>Thank you.</p>
</body>
</html>
