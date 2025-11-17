<?php
// api/send_magic.php
require '../inc/config.php';
header('Content-Type: application/json');

$input = json_decode(file_get_contents('php://input'), true);
$email = filter_var($input['email'] ?? '', FILTER_VALIDATE_EMAIL);

if (!$email) {
    echo json_encode(['error' => 'Valid email required']);
    exit;
}

$stmt = $db->prepare("SELECT id, name, role FROM users WHERE email = ?");
$stmt->bind_param('s', $email);
$stmt->execute();
$res = $stmt->get_result();
$user = $res->fetch_assoc();

if (!$user) {
    // Auto-register as client
    $name = ucfirst(explode('@', $email)[0]);
    $stmt = $db->prepare("INSERT INTO users (name, email, role) VALUES (?, ?, 'client')");
    $stmt->bind_param('ss', $name, $email);
    $stmt->execute();
    $user = ['id' => $db->insert_id, 'name' => $name, 'role' => 'client'];
}

$token = bin2hex(random_bytes(32));
$expires = date('Y-m-d H:i:s', strtotime('+15 minutes'));

$stmt = $db->prepare("REPLACE INTO magic_tokens (user_id, token, expires) VALUES (?, ?, ?)");
$stmt->bind_param('iss', $user['id'], $token, $expires);
$stmt->execute();

$link = "https://houseunlimited.ng/verify_magic.php?token=$token"; // Change to your domain
$subject = "Your Magic Login Link – House Unlimited";
$message = "Hi {$user['name']},\n\nClick below to log in (valid for 15 mins):\n$link\n\nWelcome back!\nHouse Unlimited & Land Services Nigeria";

mail($email, $subject, $message, "From: no-reply@houseunlimited.ng");

echo json_encode(['message' => 'Magic link sent! Check your email (including spam).']);
?>