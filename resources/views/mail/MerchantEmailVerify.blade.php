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
    <div>
        {{-- render variable with html tag  --}}


        {!! $code !!}

    </div>
</body>

</html>
