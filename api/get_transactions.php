<?php
// api/get_transactions.php - Returns user's payment history
require '../inc/config.php';
require '../inc/auth.php';
header('Content-Type: application/json');

$user_id = $_SESSION['user']['id'];
$role = $_SESSION['user']['role'];

// Clients see only their transactions
// Agents/Admins can see all (optional filter later)
$where = $role === 'client' ? "t.user_id = ?" : "1=1";
$params = $role === 'client' ? [$user_id] : [];
$types = $role === 'client' ? 'i' : '';

$sql = "SELECT t.*, p.title as property_title, p.location as property_location,
               pi.image_path as property_image
        FROM transactions t
        LEFT JOIN properties p ON t.property_id = p.id
        LEFT JOIN property_images pi ON p.id = pi.property_id AND pi.is_featured = 1
        WHERE $where
        ORDER BY t.created_at DESC";

$stmt = $role === 'client' ? $db->prepare($sql) : $db->prepare($sql);
if ($role === 'client') $stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();

$transactions = [];
while ($row = $result->fetch_assoc()) {
    $transactions[] = [
        'id' => $row['id'],
        'amount' => (int)$row['amount'],
        'payment_ref' => $row['payment_ref'],
        'status' => $row['status'],
        'created_at' => $row['created_at'],
        'property_title' => $row['property_title'] ?? 'General Payment',
        'property_location' => $row['property_location'] ?? '',
        'property_image' => $row['property_image'] ?? 'default_property.png'
    ];
}

echo json_encode($transactions);
?>