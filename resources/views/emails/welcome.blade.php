<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Welcome to CalaiaPH</title>
</head>
<body style="font-family: Arial, sans-serif; background-color: #f3f4f6; padding: 40px;">
    <div style="max-width: 600px; margin: auto; background: white; padding: 24px; border-radius: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.05);">
        <h2 style="color: #2563eb;">Welcome, {{ $firstName }}!</h2>
        <p>Thank you for verifying your email and joining <strong>CalaiaPH</strong>.</p>
        <p>You can now log in and start exploring!</p>

        <a href="{{ url('/login') }}" 
           style="display:inline-block; margin-top: 20px; background: #2563eb; color: white; padding: 12px 24px; border-radius: 6px; text-decoration: none;">
           Log In to Your Account
        </a>

        <p style="margin-top: 30px; font-size: 12px; color: #777;">If you didn’t create an account, please ignore this email.</p>
    </div>
</body>
</html>
