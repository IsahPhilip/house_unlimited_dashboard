<?php
// register.php
require 'inc/config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone'] ?? '');
    $referral_code = trim($_POST['referral_code'] ?? '');
    $referrer_id = null;

    if (!empty($referral_code)) {
        $decoded = base64_decode($referral_code);
        if (strpos($decoded, 'ref=') === 0) {
            $referrer_id = (int)str_replace('ref=', '', $decoded);
        }
    }

    // Check if exists
    $stmt = $db->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->bind_param('s', $email);
    $stmt->execute();
    if ($stmt->get_result()->num_rows > 0) {
        $error = "Email already registered";
    } else {
        $stmt = $db->prepare("INSERT INTO users (name, email, phone, role, referrer_id) VALUES (?, ?, ?, 'client', ?)");
        $stmt->bind_param('ssss', $name, $email, $phone, $referrer_id);
        $stmt->execute();
        $new_user_id = $stmt->insert_id;

        if ($referrer_id && $new_user_id) {
            $stmt = $db->prepare("INSERT INTO referrals (referrer_id, referee_id) VALUES (?, ?)");
            $stmt->bind_param('ii', $referrer_id, $new_user_id);
            $stmt->execute();
        }

        header('Location: login.php?success=Account created! Login now.');
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register • House Unlimited</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        body { background: #0f172a; color: white; font-family: 'Inter', sans-serif; }
        .login-card { max-width: 460px; margin: 4rem auto; background: #1e293b; padding: 3rem; border-radius: 24px; }
        input { width: 100%; padding: 1rem; margin: 0.8rem 0; border-radius: 12px; border: none; }
        button { width: 100%; padding: 1rem; background: #10b981; color: white; border: none; border-radius: 12px; font-size: 1.1rem; cursor: pointer; }
        .error { color: #fca5a5; background: #450a0a; padding: 1rem; border-radius: 12px; margin: 1rem 0; }
    </style>
</head>
<body>
    <div class="login-card">
        <h1 style="text-align:center; font-size:2.5rem;">Create Account</h1>
        <p style="text-align:center; color:#94a3b8;">Join Nigeria's #1 Real Estate Platform</p>

        <?php if (isset($error)): ?>
            <div class="error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="POST">
            <input type="text" name="name" placeholder="Full Name" required />
            <input type="email" name="email" placeholder="Email Address" required />
            <input type="tel" name="phone" placeholder="Phone (e.g. +2348012345678)" />
            <input type="text" name="referral_code" placeholder="Referral Code (Optional)" value="<?= htmlspecialchars($_GET['ref'] ?? '') ?>" />
            <button type="submit">Create My Account</button>
        </form>

        <p style="text-align:center; margin-top:2rem;">
            Already have an account? <a href="login.php" style="color:#60a5fa;">Login</a>
        </p>
    </div>
</body>
</html>