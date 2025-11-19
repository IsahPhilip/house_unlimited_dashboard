<?php
// admin/update_progress.php
require '../inc/config.php';
require '../inc/auth.php';

if ($_SESSION['user']['role'] !== 'admin' && $_SESSION['user']['role'] !== 'agent') {
    die("Access denied");
}

if ($_POST['property_id'] && isset($_POST['phase']) && isset($_POST['percentage'])) {
    $property_id = intval($_POST['property_id']);
    $phase = $_POST['phase'];
    $percentage = intval($_POST['percentage']);
    $desc = trim($_POST['description'] ?? '');

    $stmt = $db->prepare("INSERT INTO property_progress (property_id, phase, percentage, description, updated_by) 
                          VALUES (?, ?, ?, ?, ?) 
                          ON DUPLICATE KEY UPDATE percentage = ?, description = ?, updated_by = ?, updated_at = NOW()");
    $stmt->bind_param('isiisisi', $property_id, $phase, $percentage, $desc, $_SESSION['user']['id'], $percentage, $desc, $_SESSION['user']['id']);
    $stmt->execute();

    header("Location: property_progress.php?id=$property_id&success=1");
}
?>