<?php
// api/admin_users.php
require '../inc/config.php';
require '../inc/auth.php';

if ($_SESSION['user']['role'] !== 'admin') {
    http_response_code(403);
    echo json_encode([]);
    exit;
}

header('Content-Type: application/json');

$sql = "SELECT id, name, email, phone, role, status, photo, created_at FROM users ORDER BY created_at DESC";
$result = $db->query($sql);
$users = [];

while ($row = $result->fetch_assoc()) {
    $users[] = [
        'id' => $row['id'],
        'name' => $row['name'],
        'email' => $row['email'],
        'phone' => $row['phone'],
        'role' => $row['role'],
        'status' => $row['status'] ?? 'active',
        'photo' => $row['photo'],
        'created_at' => $row['created_at']
    ];
}

echo json_encode($users);
?>