<?php
// dashboard/payment_process.php
require '../inc/config.php';
require '../inc/auth.php';

$user = $_SESSION['user'];
$user_id = $user['id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $property_id = $_POST['property_id'];
    $amount = $_POST['amount'];

    // For now, we'll simulate a successful payment and insert it into the database.
    // In a real-world scenario, you would integrate a payment gateway here.

    $stmt = $db->prepare("INSERT INTO payments (user_id, property_id, amount, status) VALUES (?, ?, ?, 'successful')");
    $stmt->bind_param('iid', $user_id, $property_id, $amount);
    $stmt->execute();
    $payment_id = $db->insert_id;

    // Redirect to a success page
    header("Location: payment_success.php?payment_id=" . $payment_id);
    exit;
} else {
    header("Location: properties.php");
    exit;
}
?>