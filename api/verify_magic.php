<?php
require 'inc/config.php';

$token = $_GET['token'] ?? '';

$stmt = $db->prepare("SELECT u.*, t.expires FROM magic_tokens t JOIN users u ON t.user_id = u.id WHERE t.token = ?");
$stmt->bind_param('s', $token);
$stmt->execute();
$res = $stmt->get_result();
$login = $res->fetch_assoc();

if ($login && strtotime($login['expires']) > time()) {
    $_SESSION['user'] = [
        'id' => $login['id'],
        'name' => $login['name'],
        'email' => $login['email'],
        'role' => $login['role'],
        'photo' => $login['photo']
    ];
    $_SESSION['expires_at'] = time() + 86400;

    $db->query("DELETE FROM magic_tokens WHERE token = '$token'");
    header('Location: dashboard/');
} else {
    echo "<h2>Invalid or expired link</h2><p><a href='/'>Try again</a></p>";
}
?>