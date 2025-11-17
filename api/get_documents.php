<?php
// api/get_documents.php
require '../inc/config.php';
require '../inc/auth.php';
header('Content-Type: application/json');

$user_id = $_SESSION['user']['id'];
$role = $_SESSION['user']['role'];

$sql = "SELECT d.*, p.title as property_title
        FROM documents d
        LEFT JOIN properties p ON d.property_id = p.id
        WHERE " . ($role === 'client' ? "d.user_id = ?" : "1=1") . "
        ORDER BY d.created_at DESC";

$stmt = $role === 'client' ? $db->prepare($sql) : $db->prepare(str_replace("WHERE 1=1", "", $sql));
if ($role === 'client') $stmt->bind_param('i', $user_id);
$stmt->execute();
$result = $stmt->get_result();

$docs = [];
while ($row = $result->fetch_assoc()) {
    $docs[] = [
        'id' => $row['id'],
        'title' => $row['title'],
        'file_path' => $row['file_path'],
        'category' => $row['category'] ?? 'document',
        'property_id' => $row['property_id'],
        'property_title' => $row['property_title'],
        'created_at' => $row['created_at']
    ];
}

echo json_encode($docs);
?>