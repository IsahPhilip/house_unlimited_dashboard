<?php require_once 'auth.php'; $user = $_SESSION['user']; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title><?= isset($page_title) ? $page_title . ' • ' : '' ?>House Unlimited</title>
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css" />
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="icon" href="<?= BASE_URL ?>/assets/img/favicon.png" type="image/png">
</head>
<body class="<?= isset($_COOKIE['theme']) && $_COOKIE['theme'] === 'dark' ? 'dark' : '' ?>">
<header class="header" style="background:#1e293b; color:white; padding:1rem 2rem; display:flex; justify-content:space-between; align-items:center; position:fixed; top:0; left:0; right:0; z-index:1000;">
    <div style="display:flex; align-items:center; gap:1rem;">
        <h2 style="margin:0; font-size:1.8rem;">House Unlimited</h2>
    </div>
    <div style="display:flex; align-items:center; gap:1.5rem;">
        <span>Welcome, <strong><?= escape($user['name']) ?></strong></span>
        <img src="<?= BASE_URL ?>/assets/uploads/avatars/<?= $user['photo'] ?? 'default.png' ?>" 
             alt="Avatar" style="width:44px; height:44px; border-radius:50%; object-fit:cover;">
        <a href="<?= BASE_URL ?>/dashboard/profile.php" style="color:#94a3b8; text-decoration:none;">Profile</a>
        <a href="<?= BASE_URL ?>/logout.php" class="btn btn-sm" style="background:#ef4444; color:white; padding:0.6rem 1.2rem; border-radius:8px;">Logout</a>
    </div>
</header>
<div style="height:80px;"></div>