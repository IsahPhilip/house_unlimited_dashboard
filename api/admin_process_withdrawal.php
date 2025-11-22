<?php
// api/admin_process_withdrawal.php
require '../inc/config.php';
require '../inc/auth.php';

if ($_SESSION['user']['role'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

header('Content-Type: application/json');
$data = json_decode(file_get_contents('php://input'), true);

$withdrawal_id = intval($data['id'] ?? 0);
$new_status = $data['status'] ?? ''; // 'approved' or 'declined'

if ($withdrawal_id <= 0 || !in_array($new_status, ['approved', 'declined'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid request data.']);
    exit;
}

$db->begin_transaction();

try {
    // 1. Fetch withdrawal data and lock the row
    $stmt = $db->prepare("SELECT * FROM withdrawals WHERE id = ? AND status = 'pending' FOR UPDATE");
    $stmt->bind_param('i', $withdrawal_id);
    $stmt->execute();
    $withdrawal = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$withdrawal) {
        throw new Exception('Withdrawal request not found or already processed.');
    }

    if ($new_status === 'declined') {
        // Refund the amount to the user's wallet
        $stmt = $db->prepare("UPDATE users SET wallet_balance = wallet_balance + ? WHERE id = ?");
        $stmt->bind_param('di', $withdrawal['amount'], $withdrawal['user_id']);
        $stmt->execute();
        $stmt->close();

    } elseif ($new_status === 'approved') {
        // Integration with Paystack Transfers
        // NOTE: This is a placeholder. In a real app, you would use Paystack's PHP library.
        // Also, you need to resolve bank codes and recipient codes before transferring.
        
        $paystack_secret_key = $_ENV['PAYSTACK_SECRET_KEY']; // Make sure this is in your .env
        
        // Step 1: Create Transfer Recipient (you should store this and reuse it)
        $recipient_data = [
            'type' => 'nuban',
            'name' => $withdrawal['account_name'],
            'account_number' => $withdrawal['account_number'],
            'bank_code' => '058', // This needs to be dynamic, fetched from Paystack's Bank API
            'currency' => 'NGN'
        ];

        // Step 2: Initiate Transfer
        $transfer_data = [
            'source' => 'balance',
            'amount' => $withdrawal['amount'] * 100, // Paystack expects amount in kobo
            'recipient' => 'RCP_...', // The recipient code from Step 1
            'reason' => 'Wallet Withdrawal'
        ];
        
        // Here you would make the API calls to Paystack
        // For now, we'll just simulate a successful transfer
        $transfer_successful = true;

        if (!$transfer_successful) {
            throw new Exception('Paystack transfer failed.');
        }
    }

    // 2. Update withdrawal status
    $stmt = $db->prepare("UPDATE withdrawals SET status = ?, processed_at = NOW() WHERE id = ?");
    $stmt->bind_param('si', $new_status, $withdrawal_id);
    $stmt->execute();
    $stmt->close();

    $db->commit();
    echo json_encode(['message' => "Withdrawal has been $new_status."]);

} catch (Exception $e) {
    $db->rollback();
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
?>
