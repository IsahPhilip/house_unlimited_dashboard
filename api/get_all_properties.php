<?php
require_once '../inc/config.php';
require_once '../inc/auth.php';

// Ensure only admins can access this API
if (!is_admin()) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Unauthorized access.']);
    exit;
}

header('Content-Type: application/json');

// Fetch all active properties
$stmt = $db->prepare("SELECT id, title FROM properties WHERE status = 'active' ORDER BY title ASC");
$stmt->execute();
$result = $stmt->get_result();
$properties = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

echo json_encode(['success' => true, 'properties' => $properties]);
?>