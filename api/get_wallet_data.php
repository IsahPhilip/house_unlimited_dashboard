<?php
// api/get_wallet_data.php
require '../inc/config.php';
require '../inc/auth.php';

header('Content-Type: application/json');

$user_id = $_SESSION['user']['id'];

// 1. Fetch Wallet Balance
$stmt = $db->prepare("SELECT wallet_balance FROM users WHERE id = ?");
$stmt->bind_param('i', $user_id);
$stmt->execute();
$balance_result = $stmt->get_result()->fetch_assoc();
$wallet_balance = $balance_result['wallet_balance'] ?? 0;
$stmt->close();

// 2. Fetch Bank Details
$stmt = $db->prepare("SELECT bank_name, account_number, account_name FROM user_bank_details WHERE user_id = ?");
$stmt->bind_param('i', $user_id);
$stmt->execute();
$bank_details = $stmt->get_result()->fetch_assoc();
$stmt->close();

// 3. Fetch Withdrawal History
$stmt = $db->prepare("SELECT amount, status, created_at FROM withdrawals WHERE user_id = ? ORDER BY created_at DESC");
$stmt->bind_param('i', $user_id);
$stmt->execute();
$withdrawals = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

echo json_encode([
    'wallet_balance' => $wallet_balance,
    'bank_details' => $bank_details,
    'withdrawals' => $withdrawals
]);
?>
