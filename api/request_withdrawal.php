<?php
// api/request_withdrawal.php
require '../inc/config.php';
require '../inc/auth.php';

header('Content-Type: application/json');

$user_id = $_SESSION['user']['id'];
$data = json_decode(file_get_contents('php://input'), true);
$amount = floatval($data['amount'] ?? 0);

if ($amount <= 0) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid withdrawal amount.']);
    exit;
}

$db->begin_transaction();

try {
    // 1. Lock the user row and get current balance
    $stmt = $db->prepare("SELECT wallet_balance FROM users WHERE id = ? FOR UPDATE");
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    $current_balance = $result['wallet_balance'] ?? 0;
    $stmt->close();

    if ($amount > $current_balance) {
        throw new Exception('Insufficient funds.');
    }

    // 2. Check for bank details
    $stmt = $db->prepare("SELECT bank_name, account_number, account_name FROM user_bank_details WHERE user_id = ?");
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $bank_details = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$bank_details) {
        throw new Exception('Please add your bank details before requesting a withdrawal.');
    }

    // 3. Deduct from wallet
    $new_balance = $current_balance - $amount;
    $stmt = $db->prepare("UPDATE users SET wallet_balance = ? WHERE id = ?");
    $stmt->bind_param('di', $new_balance, $user_id);
    $stmt->execute();
    $stmt->close();

    // 4. Create withdrawal request
    $stmt = $db->prepare("
        INSERT INTO withdrawals (user_id, amount, status, bank_name, account_number, account_name) 
        VALUES (?, ?, 'pending', ?, ?, ?)
    ");
    $stmt->bind_param('idsss', $user_id, $amount, $bank_details['bank_name'], $bank_details['account_number'], $bank_details['account_name']);
    $stmt->execute();
    $stmt->close();

    $db->commit();
    
    echo json_encode(['message' => 'Withdrawal request submitted successfully.']);

} catch (Exception $e) {
    $db->rollback();
    http_response_code(400);
    echo json_encode(['error' => $e->getMessage()]);
}
?>
