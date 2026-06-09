<!DOCTYPE html>
<html>
<head>
    <title>Reset Your Password</title>
</head>
<body style="font-family: Arial, sans-serif; color: #1E293B; line-height: 1.6; padding: 20px;">
    <h2>Reset Password Request</h2>
    <p>You are receiving this email because we received a password reset request for your account.</p>
    
    <p>Please click the link below to reset your password:</p>
    <p>
        <a href="{{ route('password.reset', ['token' => $token, 'email' => $email]) }}" style="display: inline-block; padding: 10px 20px; background-color: #082B5B; color: #fff; text-decoration: none; font-weight: bold; margin-top: 10px; margin-bottom: 10px;">
            Reset Password
        </a>
    </p>

    <p>This password reset link will expire in 60 minutes.</p>

    <p>If you did not request a password reset, no further action is required.</p>

    <br>
    <p>Regards,<br>OC Museum</p>
</body>
</html>
