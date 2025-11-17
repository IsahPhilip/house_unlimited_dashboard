<?php
require '../inc/config.php';
require '../inc/auth.php';

$id = intval($_GET['id'] ?? 0);
if ($id <= 0) die('Invalid document');

$stmt = $db->prepare("SELECT file_path, title FROM documents WHERE id = ? AND user_id = ?");
$stmt->bind_param('ii', $id, $_SESSION['user']['id']);
$stmt->execute();
$result = $stmt->get_result();
$doc = $result->fetch_assoc();

if (!$doc) die('Document not found');

$filepath = "../assets/uploads/documents/" . $doc['file_path'];
if (!file_exists($filepath)) die('File not found');

header('Content-Type: application/pdf');
header('Content-Disposition: attachment; filename="' . $doc['title'] . '.pdf"');
readfile($filepath);
?>