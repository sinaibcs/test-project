<!DOCTYPE html>
<html>
<head>
    <title>Welcome Mail</title>
</head>
<body>
    <h1>Hello, {{ $username }}!</h1>
    Welcome to the CTM application.

    {{-- <p>Once your device is registered you can access the CTM Application using following credentials:</p> --}}

    <ul>
        <li>Username: {{ $username }}</li>
        <li>Mobile: {{ $beneficiary->mobile }}</li>
        <li>NID/Brith Certificate Number: {{ $beneficiary->verification_number }}</li>
        <li>Status: {{ $status }}</li>

        {{-- <li>
            Login URL: <a href="{{env('APP_FRONTEND_URL') . '/login'}}">{{env('APP_FRONTEND_URL') . '/login'}}</a>
        </li> --}}
    </ul>


    <p>Thank you.</p>
</body>
</html>
