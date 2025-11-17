<?php
// login.php - Magic Link + Password Login
require 'inc/config.php';

$email = $_GET['email'] ?? '';
$error = $_GET['error'] ?? '';
$success = $_GET['success'] ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Login • House Unlimited</title>
    <link rel="stylesheet" href="assets/css/style.css" />
    <style>
        body { background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%); font-family: 'Inter', sans-serif; }
        .login-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem;
        }
        .login-card {
            background: white;
            padding: 3rem 2.5rem;
            border-radius: 24px;
            box-shadow: 0 25px 50px rgba(0,0,0,0.25);
            width: 100%;
            max-width: 460px;
        }
        .logo {
            text-align: center;
            margin-bottom: 2rem;
        }
        .logo h1 {
            font-size: 2.8rem;
            color: #1e40af;
            margin: 0;
            font-weight: 800;
        }
        .logo p { color: #64748b; margin: 0.5rem 0 0; font-size: 1.1rem; }

        .form-group { margin-bottom: 1.5rem; }
        .form-group label { display: block; margin-bottom: 0.6rem; font-weight: 600; color: #1e293b; }
        .form-group input {
            width: 100%;
            padding: 1rem 1.2rem;
            border: 2px solid #e2e8f0;
            border-radius: 12px;
            font-size: 1rem;
            transition: all 0.3s;
        }
        .form-group input:focus {
            outline: none;
            border-color: #3b82f6;
            box-shadow: 0 0 0 4px rgba(59,130,246,0.15);
        }

        .btn-magic {
            background: #1e40af;
            color: white;
            padding: 1rem 2rem;
            border: none;
            border-radius: 12px;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            width: 100%;
            margin-top: 1rem;
            transition: all 0.3s;
        }
        .btn-magic:hover {
            background: #1e3a8a;
            transform: translateY(-3px);
            box-shadow: 0 10px 25px rgba(30,64,175,0.3);
        }

        .divider {
            text-align: center;
            margin: 2rem 0;
            color: #64748b;
            position: relative;
        }
        .divider::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 0;
            right: 0;
            height: 1px;
            background: #e2e8f0;
        }
        .divider span {
            background: white;
            padding: 0 1rem;
        }

        .msg {
            padding: 1rem 1.5rem;
            border-radius: 12px;
            margin-bottom: 1.5rem;
            font-weight: 500;
        }
        .msg.success { background: #d1fae5; color: #065f46; border: 1px solid #a7f3d0; }
        .msg.error { background: #fee2e2; color: #991b1b; border: 1px solid #fecaca; }

        .footer-text {
            text-align: center;
            margin-top: 2rem;
            color: #64748b;
            font-size: 0.9rem;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-card">
            <div class="logo">
                <h1>HU</h1>
                <p>House Unlimited & Land Services</p>
            </div>

            <h2 style="text-align:center; margin-bottom:2rem; color:#1e293b;">Welcome Back</h2>

            <?php if ($success): ?>
                <div class="msg success">Magic link sent! Check your email.</div>
            <?php endif; ?>
            <?php if ($error): ?>
                <div class="msg error"><?= htmlspecialchars(urldecode($error)) ?></div>
            <?php endif; ?>

            <!-- Magic Link Form -->
            <form action="authenticate.php" method="POST">
                <div class="form-group">
                    <label>Email Address</label>
                    <input type="email" name="email" value="<?= htmlspecialchars($email) ?>" required placeholder="e.g. admin@houseunlimited.ng" />
                </div>
                <button type="submit" name="magic_login" class="btn-magic">
                    Send Magic Link
                </button>
            </form>

            <div class="divider"><span>OR</span></div>

            <!-- Traditional Password Login -->
            <form action="authenticate.php" method="POST">
                <div class="form-group">
                    <label>Email</label>
                    <input type="email" name="email" required placeholder="Your email" />
                </div>
                <div class="form-group">
                    <label>Password</label>
                    <input type="password" name="password" required placeholder="Enter password" />
                </div>
                <button type="submit" name="password_login" class="btn-magic">
                    Login with Password
                </button>
            </form>

            <div class="footer-text">
                <p>Don't have an account? <a href="register.php" style="color:#3b82f6; font-weight:600;">Register</a></p>
                <p><a href="forgot.php" style="color:#dc2626;">Forgot Password?</a></p>
            </div>
        </div>
    </div>
</body>
</html>