<?php
// dashboard/payment_verify.php
require '../inc/config.php';
require '../inc/auth.php';

$ref = $_GET['reference'] ?? '';

if (!$ref) {
    die("No reference supplied");
}

$curl = curl_init();
curl_setopt_array($curl, [
    CURLOPT_URL => "https://api.paystack.co/transaction/verify/" . rawurlencode($ref),
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER => [
        "authorization: Bearer sk_test_your_paystack_secret_key_here",
        "cache-control: no-cache"
    ],
]);

$response = curl_exec($curl);
curl_close($curl);

$res = json_decode($response);

if ($res->status && $res->data->status === 'success') {
    $property_id = $res->data->metadata->property_id;
    $user_id = $res->data->metadata->user_id;
    $amount = $res->data->amount / 100;

    // Record purchase
    $stmt = $db->prepare("INSERT INTO purchases (user_id, property_id, amount, transaction_ref, status) VALUES (?, ?, ?, ?, 'completed')");
    $stmt->bind_param('iids', $user_id, $property_id, $amount, $ref);
    $stmt->execute();

    // Update property status to sold/under contract
    $db->query("UPDATE properties SET status = 'sold' WHERE id = $property_id");

    echo "<h2 style='text-align:center; padding:4rem; color:green;'>Payment Successful! Property is now yours.</h2>";
    echo "<p style='text-align:center;'><a href='my_properties.php'>View My Properties</a></p>";
} else {
    echo "<h2 style='text-align:center; padding:4rem; color:red;'>Payment Failed or Cancelled</h2>";
}
?>