<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password • House Unlimited</title>
    <style>
        body { background: #0f172a; color: white; font-family: 'Inter', sans-serif; text-align: center; padding-top: 10%; }
        .card { max-width: 500px; margin: 0 auto; background: #1e293b; padding: 3rem; border-radius: 24px; }
        input, button { width: 80%; padding: 1rem; margin: 1rem; border-radius: 12px; border: none; font-size: 1.1rem; }
        button { background: #f59e0b; color: white; cursor: pointer; }
    </style>
</head>
<body>
    <div class="card">
        <h1>Forgot Password?</h1>
        <p>We’ll send you a magic link to reset it.</p>
        <form action="authenticate.php" method="POST">
            <input type="email" name="email" placeholder="Your email" required />
            <button type="submit" name="magic_login">Send Reset Link</button>
        </form>
        <p><a href="login.php" style="color:#60a5fa;">Back to Login</a></p>
    </div>
</body>
</html>