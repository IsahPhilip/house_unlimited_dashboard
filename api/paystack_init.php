<?php
// api/paystack_init.php
require '../inc/config.php';
require '../inc/auth.php';

header('Content-Type: application/json');

if (!isset($_POST['property_id']) || !isset($_POST['amount'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
    exit;
}

$property_id = intval($_POST['property_id']);
$amount = intval($_POST['amount']) * 100; // Paystack uses kobo

$user = $_SESSION['user'];
$email = $user['email'];
$name = $user['name'];

// Fetch property title
$title = $db->query("SELECT title FROM properties WHERE id = $property_id")->fetch_assoc()['title'] ?? 'Property Payment';

$curl = curl_init();
curl_setopt_array($curl, [
    CURLOPT_URL => "https://api.paystack.co/transaction/initialize",
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_CUSTOMREQUEST => "POST",
    CURLOPT_POSTFIELDS => json_encode([
        'amount' => $amount,
        'email' => $email,
        'reference' => 'HU_' . time() . '_' . $user['id'],
        'callback_url' => 'https://houseunlimited.ng/dashboard/payment_verify.php',
        'metadata' => [
            'property_id' => $property_id,
            'user_id' => $user['id'],
            'purpose' => "Payment for $title"
        ]
    ]),
    CURLOPT_HTTPHEADER => [
        "authorization: Bearer sk_test_your_paystack_secret_key_here", // CHANGE THIS
        "content-type: application/json",
        "cache-control: no-cache"
    ],
]);

$response = curl_exec($curl);
$err = curl_error($curl);
curl_close($curl);

if ($err) {
    echo json_encode(['success' => false, 'message' => 'Payment gateway error']);
} else {
    $res = json_decode($response);
    if ($res->status) {
        echo json_encode([
            'success' => true,
            'authorization_url' => $res->data->authorization_url
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => $res->message]);
    }
}
?>