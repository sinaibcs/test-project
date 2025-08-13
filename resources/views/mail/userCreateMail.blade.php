<!DOCTYPE html>
<html>
<head>
    <title>Welcome Mail</title>
</head>
<body>
    <h1>Hello, {{ $fullName }}!</h1>

{{--        <li>Your login credentials are:</li>--}}
{{--            <ul>--}}
{{--                <li>Username: {{ $name }}</li>--}}
{{--                <li>Password: {{$password}}</li>--}}
{{--            </ul>--}}
{{--    </ul>--}}




    Welcome to the CTM application.Your account has been approved.

    <div>
        To register your device please visit <a
            href="{{env('APP_FRONTEND_URL') . '/browser-token'}}">{{env('APP_FRONTEND_URL') . '/browser-token'}}</a> then copy the browser fingerprint code and provide it to your authority.
    </div>

    <p>Once your device is registered you can access the CTM Application using following credentials:</p>

    <ul>
        <li>Username: {{ $username }}</li>
        <li>Password: {{$password}}</li>
        <li>
            Login URL: <a href="{{env('APP_FRONTEND_URL') . '/login'}}">{{env('APP_FRONTEND_URL') . '/login'}}</a>
        </li>
    </ul>


    <p>Thank you.</p>
</body>
</html>
