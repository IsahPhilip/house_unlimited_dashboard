<?php
// dashboard/payment_process.php
require '../inc/config.php';
require '../inc/auth.php';

$user = $_SESSION['user'];
$user_id = $user['id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $property_id = $_POST['property_id'];
    $amount = $_POST['amount'];

    // Get property price to store in metadata
    $stmt_price = $db->prepare("SELECT price FROM properties WHERE id = ?");
    $stmt_price->bind_param('i', $property_id);
    $stmt_price->execute();
    $price_result = $stmt_price->get_result()->fetch_assoc();
    $purchase_price = $price_result['price'] ?? 0; // Default to 0 if not found
    $stmt_price->close();

    // Create metadata for the transaction
    $metadata = json_encode([
        'type' => 'property_purchase',
        'purchase_price' => $purchase_price
    ]);

    // Insert the transaction with metadata
    $stmt = $db->prepare("INSERT INTO transactions (user_id, property_id, amount, status, gateway, metadata, paid_at) VALUES (?, ?, ?, 'success', 'manual', ?, NOW())");
    $stmt->bind_param('iids', $user_id, $property_id, $amount, $metadata);
    $stmt->execute();
    $payment_id = $db->insert_id;
    $stmt->close();

    // After a successful payment, also update the property status to 'sold'
    // This assumes a single payment means the property is sold. 
    // For installment payments, this logic would need to be more complex.
    $stmt_update = $db->prepare("UPDATE properties SET status = 'sold' WHERE id = ?");
    $stmt_update->bind_param('i', $property_id);
    $stmt_update->execute();
    $stmt_update->close();


    // Redirect to a success page
    header("Location: payment_success.php?payment_id=" . $payment_id);
    exit;
} else {
    header("Location: properties.php");
    exit;
}
?>
