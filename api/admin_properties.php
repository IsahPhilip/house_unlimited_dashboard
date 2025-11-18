<?php
// api/admin_properties.php - Returns ALL properties for Admin
require '../inc/config.php';
require '../inc/auth.php';

header('Content-Type: application/json');

// Only admin can access this endpoint
if ($_SESSION['user']['role'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$sql = "SELECT 
            p.*,
            u.name as agent_name,
            COALESCE((
                SELECT image_path 
                FROM property_images 
                WHERE property_id = p.id 
                ORDER BY id ASC 
                LIMIT 1
            ), 'default.jpg') AS featured_image
        FROM properties p
        LEFT JOIN users u ON p.agent_id = u.id
        ORDER BY p.created_at DESC";

$result = $db->query($sql);

$properties = [];
while ($row = $result->fetch_assoc()) {
    $row['price'] = (float)$row['price']; // Ensure numeric
    $properties[] = $row;
}

echo json_encode($properties);
?>