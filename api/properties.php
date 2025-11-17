<?php
// api/properties.php
require '../inc/config.php';
require '../inc/auth.php';
header('Content-Type: application/json');

$page = max(1, intval($_GET['page'] ?? 1));
$limit = 12;
$offset = ($page - 1) * $limit;

$where = ["p.status = 'available'"];
$params = [];
$types = '';

if (!empty($_GET['search'])) {
    $where[] = "(p.title LIKE ? OR p.location LIKE ?)";
    $params[] = "%{$_GET['search']}%"; $params[] = "%{$_GET['search']}%";
    $types .= 'ss';
}
if (!empty($_GET['type'])) {
    $where[] = "p.type = ?";
    $params[] = $_GET['type'];
    $types .= 's';
}

$sql = "SELECT p.*, i.image_path as featured_image
        FROM properties p
        LEFT JOIN property_images i ON p.id = i.property_id AND i.id = (SELECT MIN(id) FROM property_images WHERE property_id = p.id)
        WHERE " . implode(' AND ', $where) . "
        ORDER BY p.created_at DESC LIMIT ? OFFSET ?";
$types .= 'ii';
$params[] = $limit; $params[] = $offset;

$stmt = $db->prepare($sql);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();

$properties = [];
while ($row = $result->fetch_assoc()) {
    $row['price_formatted'] = format_ngn($row['price']);
    $properties[] = $row;
}

echo json_encode(['properties' => $properties, 'page' => $page]);
?>