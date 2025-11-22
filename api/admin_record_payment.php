<?php
require_once '../inc/config.php';
require_once '../inc/auth.php';
require_once '../inc/functions.php'; // For process_referral_completion and log_activity

header('Content-Type: application/json');

// Ensure only admins can access this API
if (!is_admin()) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access.']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = filter_var($_POST['user_id'] ?? 0, FILTER_VALIDATE_INT);
    $property_id = filter_var($_POST['property_id'] ?? 0, FILTER_VALIDATE_INT); // New: Get property_id
    $amount = filter_var($_POST['amount'] ?? 0, FILTER_VALIDATE_FLOAT);
    $payment_date = $_POST['payment_date'] ?? '';
    $payment_method = htmlspecialchars($_POST['payment_method'] ?? 'offline');
    $status_in = htmlspecialchars($_POST['status'] ?? '');
    $notes = htmlspecialchars($_POST['notes'] ?? ''); // This can be stored in metadata if needed

    // Basic validation
    if (!$user_id || !$property_id || $amount <= 0 || !in_array($status_in, ['pending_offline', 'completed_offline']) || empty($payment_date)) {
        echo json_encode(['success' => false, 'message' => 'Invalid input or missing property ID.']);
        exit;
    }

    // Check if user exists
    $stmt = $db->prepare("SELECT id FROM users WHERE id = ?");
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $stmt->store_result();
    if ($stmt->num_rows === 0) {
        echo json_encode(['success' => false, 'message' => 'User not found.']);
        $stmt->close();
        exit;
    }
    $stmt->close();

    // Generate a unique payment reference for offline payments
    $payment_ref = 'OFFLINE-' . uniqid('HUL', true);
    $gateway = $payment_method; // Use the provided method as the gateway

    // Map incoming status to schema-valid status
    $status = ($status_in === 'completed_offline') ? 'success' : 'pending';

    // Insert into transactions table
    $stmt = $db->prepare("INSERT INTO transactions (user_id, property_id, amount, status, payment_ref, gateway, paid_at) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param('iidssss', $user_id, $property_id, $amount, $status, $payment_ref, $gateway, $payment_date);

    if ($stmt->execute()) {
        $transaction_id = $db->insert_id;
        log_activity("Admin recorded offline payment #$transaction_id for user #$user_id (Amount: $amount, Status: $status)");

        // Process referral completion if payment is marked as completed_offline
        if ($status === 'success') {
            process_referral_completion($user_id, $amount);
        }
        
        echo json_encode(['success' => true, 'message' => 'Offline payment recorded successfully.', 'payment_id' => $transaction_id]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to record payment: ' . $db->error]);
    }
    $stmt->close();
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
}
?>