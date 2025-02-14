# Password Reset System

This is a forget password reset system using HTML, CSS, JavaScript, and PHP. You can freely use this for your project to reset your users' passwords.

## Features

- Request OTP via email
- Verify OTP
- Generate secure token
- Send reset password link via email
- Reset password using the token

## How It Works

1. **Request OTP**: Users enter their email address to request an OTP.
2. **Verify OTP**: Users enter the OTP received in their email to verify their identity.
3. **Send Reset Link**: After verifying the OTP, a reset password link with a token is sent to the user's email.
4. **Reset Password**: Users click the link in their email, which redirects them to the reset password page. The token is verified, and if matched, users can reset their password.

## Database

An SQL file is provided where you can check the table for password resets. The table contains both the OTP and token. You can use the token to verify the user in the reset password section.

## Usage

After matching the token, you should use the following link format to redirect the user:

```
reset-password.php?token=the_token_from_database
```

In the reset password page, use the GET method to get the token and reverify it with the database. If matched, allow the user to reset their password. Instead of the token, you should directly send the redirection link with the token in the user's email.

## Contact

For more information, you can contact me on:

- **Facebook**: [mahendramahara15](https://www.facebook.com/mahendramahara15)
- **Instagram**: [mahendramahara15](https://www.instagram.com/mahendramahara15)
- **Telegram**: [mahendramahara](https://t.me/mahendramahara)
- **Telegram Group**: [BCATU](https://t.me/BCATU)

Feel free to reach out if you have any questions or need further assistance.