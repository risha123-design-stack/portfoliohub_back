<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">

    <title>Login Verification Code</title>
</head>

<body
    style="
        margin:0;
        padding:30px;
        background:#f4f6fb;
        font-family:Arial,sans-serif;
        color:#172033;
    "
>
    <div
        style="
            max-width:520px;
            margin:auto;
            padding:32px;
            background:#ffffff;
            border-radius:18px;
        "
    >
        <p
            style="
                color:#6c5ce7;
                font-size:12px;
                font-weight:bold;
                text-transform:uppercase;
            "
        >
            Platinum Portfolio
        </p>

        <h1 style="font-size:25px;">
            Login verification
        </h1>

        <p>
            Use the following code to complete your admin login:
        </p>

        <div
            style="
                margin:24px 0;
                padding:18px;
                background:#f0edff;
                border-radius:12px;
                color:#5142c7;
                font-size:32px;
                font-weight:bold;
                letter-spacing:10px;
                text-align:center;
            "
        >
            {{ $otp }}
        </div>

        <p>
            This code expires in 10 minutes.
        </p>

        <p>
            Ignore this email when you did not attempt to sign in.
        </p>
    </div>
</body>
</html>