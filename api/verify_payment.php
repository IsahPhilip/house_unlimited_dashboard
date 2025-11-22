<?php
// api/verify_payment.php - FINAL + DYNAMIC LUXURY LOGS + ZERO HARDCODING
require '../inc/config.php';
require '../inc/send_email.php'; // Optional: for email notifications
header('Content-Type: application/json');

$input = json_decode(file_get_contents('php://input'), true);
$reference = $input['reference'] ?? ($_GET['reference'] ?? '');

if (empty($reference)) {
    echo json_encode(['success' => false, 'message' => 'No reference supplied']);
    exit;
}

// Prevent duplicate processing
$stmt = $db->prepare("SELECT id FROM transactions WHERE reference = ? AND status = 'success'");
$stmt->bind_param('s', $reference);
$stmt->execute();
if ($stmt->get_result()->num_rows > 0) {
    echo json_encode(['success' => true, 'message' => 'Payment already verified']);
    exit;
}

$secret_key = 'sk_live_YOUR_SECRET_KEY'; // Replace with env or config
$curl = curl_init();

curl_setopt_array($curl, [
    CURLOPT_URL => "https://api.paystack.co/transaction/verify/" . rawurlencode($reference),
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER => ["Authorization: Bearer $secret_key"]
]);

$response = curl_exec($curl);
$err = curl_error($curl);
curl_close($curl);

if ($err) {
    error_log("Paystack verify cURL Error: $err | Ref: $reference");
    echo json_encode(['success' => false, 'message' => 'Verification failed']);
    exit;
}

$result = json_decode($response, false);

if (!$result?->status || $result->data->status !== 'success') {
    // Log failed attempt
    $stmt = $db->prepare("INSERT INTO transactions (reference, status, amount, gateway) VALUES (?, 'failed', 0, 'paystack') ON DUPLICATE KEY UPDATE status = 'failed'");
    $stmt->bind_param('s', $reference);
    $stmt->execute();
    echo json_encode(['success' => false, 'message' => 'Payment failed or invalid']);
    exit;
}

// SUCCESS — Extract real data
$amount_kobo = $result->data->amount;
$amount_ngn = $amount_kobo / 100;
$formatted_amount = '₦' . number_format($amount_ngn);
$email = $result->data->customer->email;
$paid_at = $result->data->paid_at;
$metadata = $result->data->metadata ?? (object)[];
$user_id = $metadata->user_id ?? null;
$property_id = $metadata->property_id ?? null;

// Fallback: Find user by email
if (!$user_id && $email) {
    $stmt = $db->prepare("SELECT id, name FROM users WHERE email = ?");
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($row = $res->fetch_assoc()) {
        $user_id = $row['id'];
        $client_name = $row['name'];
    }
}

// Fetch client name if not already
if ($user_id && !isset($client_name)) {
    $stmt = $db->prepare("SELECT name FROM users WHERE id = ?");
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $res = $stmt->get_result();
    $client_name = $res->fetch_assoc()['name'] ?? 'Client';
}
$client_name = $client_name ?? 'Client';

// Fetch property details for rich logging
$property_info = 'General Booking/Reservation';
if ($property_id) {
    $stmt = $db->prepare("SELECT title, location FROM properties WHERE id = ?");
    $stmt->bind_param('i', $property_id);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($prop = $res->fetch_assoc()) {
        $property_info = $prop['title'] . " in " . ucwords(str_replace(['-', '_'], ' ', $prop['location']));
    }
}

// FINAL DYNAMIC LOG MESSAGES — PURE DOMINANCE
$paid_date = date('j M Y \a\t g:i A', strtotime($paid_at));

log_activity("Payment successful: $client_name paid $formatted_amount for $property_info (Ref: $reference)");
log_activity("New sale confirmed – $formatted_amount received on $paid_date");

// Optional: Milestone or commission logs (only if you have logic for them)
// Example: Check total sales, commissions, etc. — but we keep it clean and real here.

// Insert into payments table
$stmt = $db->prepare("
    INSERT INTO transactions 
    (user_id, property_id, amount, reference, status, gateway, transaction_data, paid_at) 
    VALUES (?, ?, ?, ?, 'success', 'paystack', ?, ?)
");
$transaction_json = json_encode($result->data);
$stmt->bind_param('iissss', $user_id, $property_id, $amount_ngn, $reference, $transaction_json, $paid_at);

if ($stmt->execute()) {
    // Call the referral completion function for the referee
    if ($user_id) { // $user_id here is the referee_id
        process_referral_completion($user_id, $amount_ngn);
    }

    echo json_encode([
        'success' => true,
        'message' => 'Payment verified and recorded',
        'amount' => $formatted_amount,
        'property' => $property_info,
        'client' => $client_name
    ]);
} else {
    error_log("Failed to insert payment record: " . $stmt->error);
    echo json_encode(['success' => false, 'message' => 'Database error']);
}

$stmt->close();
?>