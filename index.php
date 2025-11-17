<?php
// index.php – Root entry point (correct version)
require 'inc/config.php';
require 'inc/auth.php';   // ← this checks if user is logged in

// If already logged in → go straight to dashboard
if (isset($_SESSION['user'])) {
    header('Location: dashboard/index.php');
    exit;
}

// If NOT logged in → go to the beautiful login page
header('Location: login.php');
exit;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <meta name="description" content="House Unlimited & Land Services Nigeria - Premium real estate, land sales, and property development in Lagos and beyond."/>
    <title>House Unlimited & Land Services Nigeria</title>
    <link rel="stylesheet" href="assets/css/style.css" />
    <link rel="icon" href="assets/img/favicon.png" type="image/png"/>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        .hero-section {
            min-height: 100vh;
            background: linear-gradient(rgba(0,0,0,0.65), rgba(0,0,0,0.7)), url('assets/img/hero-bg.jpg') center/cover no-repeat;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            color: white;
            padding: 2rem;
        }
        .hero-content h1 {
            font-size: 3.5rem;
            margin-bottom: 1rem;
            font-weight: 700;
        }
        .hero-content p {
            font-size: 1.3rem;
            max-width: 700px;
            margin: 0 auto 2rem;
            opacity: 0.95;
        }
        .login-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(12px);
            border-radius: 16px;
            padding: 3rem 2.5rem;
            width: 100%;
            max-width: 480px;
            box-shadow: 0 20px 50px rgba(0,0,0,0.3);
            color: #1e293b;
        }
        .login-card h2 {
            font-size: 2rem;
            margin-bottom: 0.5rem;
            color: #1e40af;
        }
        .login-card p.subtitle {
            color: #64748b;
            margin-bottom: 2rem;
        }
        .magic-form input {
            width: 100%;
            padding: 1rem 1.2rem;
            font-size: 1.1rem;
            border: 2px solid #e2e8f0;
            border-radius: 12px;
            margin-bottom: 1rem;
            transition: all 0.3s;
        }
        .magic-form input:focus {
            outline: none;
            border-color: #3b82f6;
            box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.2);
        }
        .magic-form button {
            width: 100%;
            padding: 1.1rem;
            background: #1e40af;
            color: white;
            border: none;
            border-radius: 12px;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            transition: 0.3s;
        }
        .magic-form button:hover {
            background: #1e3a8a;
            transform: translateY(-2px);
        }
        .msg { margin-top: 1rem; padding: 1rem; border-radius: 8px; font-weight: 500; }
        .msg.success { background: #d1fae5; color: #065f46; }
        .msg.error { background: #fee2e2; color: #991b1b; }
        .features {
            padding: 5rem 2rem;
            background: #f8f9fc;
            text-align: center;
        }
        .feature-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 2rem;
            max-width: 1200px;
            margin: 3rem auto;
        }
        .feature-card {
            background: white;
            padding: 2rem;
            border-radius: 16px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.08);
        }
        footer {
            background: #1e293b;
            color: #e2e8f0;
            text-align: center;
            padding: 3rem 1rem;
        }
        @media (max-width: 768px) {
            .hero-content h1 { font-size: 2.5rem; }
            .login-card { padding: 2rem 1.5rem; }
        }
    </style>
</head>
<body>

    <!-- Hero + Login Section -->
    <section class="hero-section">
        <div class="hero-content">
            <h1>House Unlimited<br>& Land Services Nigeria</h1>
            <p>Find your dream property in Lagos, Abuja, Port Harcourt & beyond. Luxury homes, verified lands, and off-plan investments — all in one place.</p>

            <div class="login-card">
                <h2>Welcome Back</h2>
                <p class="subtitle">Enter your email to get a secure magic login link — no password needed.</p>

                <form id="magicLoginForm" class="magic-form">
                    <input type="email" name="email" placeholder="you@example.com" required autocomplete="email"/>
                    <button type="submit">Send Magic Link</button>
                </form>

                <div id="loginMsg"></div>
            </div>
        </div>
    </section>

    <!-- Features -->
    <section class="features">
        <h2>Why Choose House Unlimited?</h2>
        <div class="feature-grid">
            <div class="feature-card">
                <h3>Verified Properties</h3>
                <p>All listings are inspected and legally verified by our team in Nigeria.</p>
            </div>
            <div class="feature-card">
                <h3>Direct Owner Contact</h3>
                <p>Chat instantly with agents via WhatsApp or in-app messaging.</p>
            </div>
            <div class="feature-card">
                <h3>Pay with Paystack</h3>
                <p>Secure booking fees and payments with Naira via Paystack.</p>
            </div>
            <div class="feature-card">
                <h3>Track Project Progress</h3>
                <p>Live updates on your off-plan or under-construction property.</p>
            </div>
        </div>
    </section>

    <footer>
        <p>&copy; <?= date('Y') ?> House Unlimited & Land Services Nigeria<br>
        <small>Lagos • Abuja • Port Harcourt | Powered by Secure Magic Login</small></p>
    </footer>

    <script>
        document.getElementById('magicLoginForm').onsubmit = async (e) => {
            e.preventDefault();
            const email = e.target.email.value.trim();
            const msgDiv = document.getElementById('loginMsg');
            msgDiv.innerHTML = '<p>Sending your magic link...</p>';

            try {
                const res = await fetch('api/send_magic.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ email })
                });
                const data = await res.json();

                if (data.message) {
                    msgDiv.innerHTML = `<div class="msg success">${data.message}<br><small>Check your email (including spam/promotions)</small></div>`;
                    e.target.reset();
                } else {
                    msgDiv.innerHTML = `<div class="msg error">${data.error || 'Something went wrong'}</div>`;
                }
            } catch (err) {
                msgDiv.innerHTML = `<div class="msg error">Network error. Please try again.</div>`;
            }
        };
    </script>
</body>
</html>