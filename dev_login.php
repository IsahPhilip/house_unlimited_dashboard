<?php
// dev_login.php — WORKS ON VALET, SAIL, DOCKER, XAMPP, PHONE, EVERYTHING
require 'inc/config.php';

// ALLOWED IPS OR HOSTS — ADD YOURS IF NEEDED
$allowed = [
    '127.0.0.1',
    '::1',                    // IPv6 localhost
    '192.168.',               // All local networks (safe enough for dev)
    '10.0.',                  // Docker / common private ranges
    '172.16.', '172.17.', '172.18.', '172.19.', '172.20.', '172.21.', '172.22.', '172.23.', '172.24.', '172.25.', '172.26.', '172.27.', '172.28.', '172.29.', '172.30.', '172.31.',
];

$ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
$allowed_ip = false;

foreach ($allowed as $range) {
    if (str_starts_with($ip, $range) || $ip === $range) {
        $allowed_ip = true;
        break;
    }
}

// FINAL FALLBACK: if you have a secret key in URL → allow
if (isset($_GET['godmode']) && $_GET['godmode'] === 'houseunlimited2025') {
    $allowed_ip = true;
}

if (!$allowed_ip) {
    die('<h1 style="color:#ef4444; font-family:Arial; text-align:center; margin-top:10rem;">
            Access Denied<br><br>
            <small>Only local development machines allowed</small>
         </h1>');
}

// NOW SHOW THE PANEL
$users = $db->query("SELECT id, name, email, role FROM users ORDER BY role DESC, name LIMIT 100")->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>DEV LOGIN — House Unlimited</title>
    <style>
        body {font-family: 'Segoe UI', sans-serif; background:#0f172a; color:#e2e8f0; padding:3rem; margin:0;}
        h1 {color:#3b82f6; text-align:center; margin-bottom:2rem;}
        .user {display:inline-block; margin:0.7rem; padding:1rem 1.3rem; background:#1e293b; border-radius:12px; min-width:320px;}
        .user a {color:white; text-decoration:none; font-weight:600;}
        .admin {background:#991b1b !important;}
        .agent {background:#92400e;}
        .client {background:#166534;}
    </style>
</head>
<body>
    <h1>HOUSE UNLIMITED — GOD MODE LOGIN</h1>
    <div style="text-align:center;">
        <?php foreach($users as $u): ?>
            <div class="user <?= $u['role'] ?>">
                <a href="login_as.php?id=<?= $u['id'] ?>">
                    [<?= strtoupper($u['role']) ?>] <?= htmlspecialchars($u['name']) ?>
                    <br><small><?= $u['email'] ?> • ID: <?= $u['id'] ?></small>
                </a>
            </div>
        <?php endforeach; ?>
    </div>

    <p style="text-align:center; margin-top:4rem; color:#64748b;">
        Your IP: <strong><?= $ip ?></strong><br>
        <a href="?godmode=houseunlimited2025" style="color:#60a5fa;">Click here if still blocked (emergency pass)</a>
    </p>
</body>
</html>