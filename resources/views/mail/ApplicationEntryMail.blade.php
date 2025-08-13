<!DOCTYPE html>
<html>
<head>
    <title>Online Application for the {{ $program_name }}</title> <!-- Dynamically set the program name -->
</head>
<body>
    <h1>Dear {{ $name }},</h1> <!-- The user's name is passed as $name -->

    <p>
        Your application has been submitted for the {{ $program_name }} successfully. <!-- Include the program name -->
    </p>

    <ul>
        <li>Tracking ID: {{ $tracking_no }}</li> <!-- Tracking ID -->
        <li>
            To track your application, please visit: 
            <a href="{{ env('APP_FRONTEND_URL') . '/application-tracking' }}">
                {{ env('APP_FRONTEND_URL') . '/application-tracking' }}
            </a>
        </li>
    </ul>

    <p>Thanking you,</p>
    <p>CTM Application Team</p> <!-- You can modify the closing note here as per your need -->
</body>
</html>
