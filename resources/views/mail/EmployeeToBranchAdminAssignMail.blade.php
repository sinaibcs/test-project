<!DOCTYPE html>
<html>

<head>
    <title>{{$subject}}</title>
    <style>
        /* CSS styles here */
        body {
            font-family: Arial, sans-serif;
            padding: 20px;
        }

        h1 {
            color: #0072c6;
        }

        p {
            line-height: 1.5;
            margin-bottom: 1em;
        }

    </style>
</head>

<body>
    <h1>Welcome to Your New Role as Branch Admin</h1>
    <p>Dear {{ $EmployeeName }},</p>
    <p>We are thrilled to welcome you to your new role as Branch Admin for our {{ $BranchName }} branch. We have no doubt
        that you will excel in this role and make significant contributions to our team and company.</p>
    <p>As Branch Admin, you will be responsible for overseeing the day-to-day operations of the branch, managing and
        motivating the branch team, and ensuring that our branch's objectives are met. You will also be responsible for
        building and maintaining relationships with our clients and partners, and for ensuring that our branch's
        reputation is maintained at the highest level.</p>
    <p>We understand that taking on a new role can be challenging, but we are here to support you every step of the way.
        You will be provided with the necessary training and resources to help you succeed in your new role.</p>
    <p>We look forward to seeing the results of your hard work and leadership. If you have any questions or concerns,
        please don't hesitate to reach out to us.</p>
    <p>Once again, welcome to your new role and we are excited to see all that you will accomplish in this position!</p>
    {{-- <ul>
        <li>Your login credentials for our company branch portal are:</li>
            <ul>
                <li>Username: {{ $EmployeeEmail }}</li>
                <li>Password: {{$EmployeePassword}}</li>
            </ul>
    </ul> --}}

</body>

</html>
