<?php
// api/save_bank_details.php
require '../inc/config.php';
require '../inc/auth.php';

header('Content-Type: application/json');

$user_id = $_SESSION['user']['id'];
$data = json_decode(file_get_contents('php://input'), true);

$bank_name = $data['bank_name'] ?? null;
$account_number = $data['account_number'] ?? null;
$account_name = $data['account_name'] ?? null;

if (!$bank_name || !$account_number || !$account_name) {
    http_response_code(400);
    echo json_encode(['error' => 'All fields are required.']);
    exit;
}

$stmt = $db->prepare("
    INSERT INTO user_bank_details (user_id, bank_name, account_number, account_name) 
    VALUES (?, ?, ?, ?)
    ON DUPLICATE KEY UPDATE 
    bank_name = VALUES(bank_name), 
    account_number = VALUES(account_number), 
    account_name = VALUES(account_name)
");
$stmt->bind_param('isss', $user_id, $bank_name, $account_number, $account_name);

if ($stmt->execute()) {
    echo json_encode(['message' => 'Bank details saved successfully.']);
} else {
    http_response_code(500);
    echo json_encode(['error' => 'Failed to save bank details.']);
}

$stmt->close();
?>
