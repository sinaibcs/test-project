<!DOCTYPE html>
<html>
<head>
    <title>Welcome to Our Company</title>
</head>
<body>
    <h1>Welcome to Our Company, {{ $EmployeeName }}!</h1>
    <p>We are excited to have you join our team and look forward to working with you.</p>
    <p>Here is some important information to help you get started:</p>
    <ul>
        <li>Your login credentials for our company portal are:</li>
            <ul>
                <li>Username: {{ $EmployeeEmail }}</li>
                <li>Password: {{$EmployeePassword}}</li>
            </ul>
    </ul>
    <p>If you have any questions, please don't hesitate to reach out to your supervisor or HR.</p>
    <p>Thank you and welcome again!</p>
    <p>Sincerely,<br>
    The HR Team</p>
</body>
</html>
