<?php
// api/verify_payment.php - Paystack callback & webhook verification
require '../inc/config.php';
header('Content-Type: application/json');

$input = json_decode(file_get_contents('php://input'), true);
$reference = $input['reference'] ?? ($_GET['reference'] ?? '');

if (empty($reference)) {
    echo json_encode(['success' => false, 'message' => 'No reference supplied']);
    exit;
}

// Prevent duplicate verification
$stmt = $db->prepare("SELECT id FROM transactions WHERE payment_ref = ?");
$stmt->bind_param('s', $reference);
$stmt->execute();
if ($stmt->get_result()->num_rows > 0) {
    echo json_encode(['success' => true, 'message' => 'Already processed']);
    exit;
}

$secret_key = 'sk_live_YOUR_SECRET_KEY_HERE'; // Replace with your real Paystack secret key
$curl = curl_init();

curl_setopt_array($curl, [
    CURLOPT_URL => "https://api.paystack.co/transaction/verify/" . rawurlencode($reference),
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER => [
        "Authorization: Bearer $secret_key",
        "Cache-Control: no-cache"
    ],
]);

$response = curl_exec($curl);
$err = curl_error($curl);
curl_close($curl);

if ($err) {
    echo json_encode(['success' => false, 'message' => 'cURL Error: ' . $err]);
    exit;
}

$result = json_decode($response);

if ($result->status && $result->data->status === 'success') {
    $amount_kobo = $result->data->amount;
    $amount_ngn = $amount_kobo / 100;
    $email = $result->data->customer->email;
    $metadata = $result->data->metadata;

    $user_id = $metadata->user_id ?? null;
    $property_id = $metadata->property_id ?? null;

    // Find user by email if user_id not set (fallback)
    if (!$user_id) {
        $stmt = $db->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $res = $stmt->get_result();
        if ($row = $res->fetch_assoc()) $user_id = $row['id'];
    }

    if ($user_id) {
        $stmt = $db->prepare("INSERT INTO transactions 
            (user_id, property_id, amount, payment_ref, status, gateway, metadata) 
            VALUES (?, ?, ?, ?, 'success', 'paystack', ?)");
        $metadata_json = json_encode($metadata);
        $stmt->bind_param('iisss', $user_id, $property_id, $amount_ngn, $reference, $metadata_json);
        $stmt->execute();

        // Optional: Auto-generate receipt PDF
        // include '../api/generate_receipt.php?ref=' . $reference;

        echo json_encode(['success' => true, 'message' => 'Payment verified']);
    } else {
        echo json_encode(['success' => false, 'message' => 'User not found']);
    }
} else {
    // Log failed payment
    $stmt = $db->prepare("INSERT INTO transactions (payment_ref, status, amount) VALUES (?, 'failed', 0)");
    $stmt->bind_param('s', $reference);
    $stmt->execute();

    echo json_encode(['success' => false, 'message' => 'Payment failed']);
}
?>