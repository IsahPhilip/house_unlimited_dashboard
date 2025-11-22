<?php
require '../inc/config.php';
require '../inc/auth.php';
if ($_SESSION['user']['role'] !== 'admin') exit;

header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="house_unlimited_transactions_' . date('Y-m-d') . '.csv"');

$output = fopen('php://output', 'w');
fputcsv($output, ['Date', 'Reference', 'Client', 'Email', 'Property', 'Agent', 'Amount', 'Status']);

$sql = "SELECT p.created_at, p.reference, u.name, u.email, prop.title, a.name as agent, p.amount, p.status
        FROM transactions p
        LEFT JOIN users u ON p.user_id = u.id
        LEFT JOIN properties prop ON p.property_id = prop.id
        LEFT JOIN users a ON prop.agent_id = a.id
        ORDER BY p.created_at DESC";

$result = $db->query($sql);
while ($row = $result->fetch_assoc()) {
    fputcsv($output, [
        $row['created_at'],
        $row['reference'],
        $row['name'] ?? '',
        $row['email'] ?? '',
        $row['title'] ?? '',
        $row['agent'] ?? '',
        '₦' . number_format($row['amount']),
        $row['status']
    ]);
}