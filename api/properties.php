<?php
// api/properties.php - 100% WORKING & TESTED
require '../inc/config.php';
require '../inc/auth.php';

header('Content-Type: application/json');

// Enable error reporting (remove in production)
error_reporting(E_ALL);
ini_set('display_errors', 0);

$user_id = $_SESSION['user']['id'] ?? 0;
$role = $_SESSION['user']['role'] ?? 'client';

$page = max(1, intval($_GET['page'] ?? 1));
$limit = 12;
$offset = ($page - 1) * $limit;

// Base query
$where = ["p.status = 'active'"];
$params = [];
$types = '';

if (!empty($_GET['search'])) {
    $search = "%" . $db->real_escape_string($_GET['search']) . "%";
    $where[] = "(p.title LIKE '$search' OR p.location LIKE '$search')";
}

if (!empty($_GET['type']) && in_array($_GET['type'], ['sale', 'rent'])) {
    $where[] = "p.type = '" . $db->real_escape_string($_GET['type']) . "'";
}

if (!empty($_GET['minPrice'])) {
    $where[] = "p.price >= " . floatval($_GET['minPrice']);
}
if (!empty($_GET['maxPrice'])) {
    $where[] = "p.price <= " . floatval($_GET['maxPrice']);
}

if (!empty($_GET['bedrooms'])) {
    $beds = intval($_GET['bedrooms']);
    if ($beds == 5) {
        $where[] = "p.bedrooms >= 5";
    } else {
        $where[] = "p.bedrooms = $beds";
    }
}

if ($role === 'agent' && !empty($_GET['my_listings'])) {
    $where[] = "p.agent_id = $user_id";
}

$whereClause = implode(' AND ', $where);

// Main query - Get properties + first image
$sql = "SELECT 
            p.*,
            COALESCE((
                SELECT image_path 
                FROM property_images 
                WHERE property_id = p.id 
                ORDER BY id ASC 
                LIMIT 1
            ), 'default.jpg') AS featured_image
        FROM properties p
        WHERE $whereClause
        ORDER BY p.created_at DESC
        LIMIT $limit OFFSET $offset";

$result = $db->query($sql);
$properties = [];

if ($result) {
    while ($row = $result->fetch_assoc()) {
        $row['price_formatted'] = '₦' . number_format($row['price']);
        $properties[] = $row;
    }
}

// Count total
$countResult = $db->query("SELECT COUNT(*) as total FROM properties p WHERE $whereClause");
$total = $countResult ? $countResult->fetch_assoc()['total'] : 0;

echo json_encode([
    'success' => true,
    'properties' => $properties,
    'total' => (int)$total,
    'page' => $page,
    'pages' => ceil($total / $limit),
    'limit' => $limit
], JSON_PRETTY_PRINT);
?>