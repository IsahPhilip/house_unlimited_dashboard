<?php
// dashboard/payment_success.php
require '../inc/config.php';
require '../inc/auth.php';

$user = $_SESSION['user'];

if (!isset($_GET['payment_id'])) {
    header("Location: properties.php");
    exit;
}

$payment_id = $_GET['payment_id'];

// Fetch payment details
$stmt = $db->prepare("
    SELECT p.id, pr.title, p.amount, p.created_at as payment_date
    FROM transactions p
    JOIN properties pr ON p.property_id = pr.id
    WHERE p.id = ?
");
$stmt->bind_param('i', $payment_id);
$stmt->execute();
$result = $stmt->get_result();
$payment = $result->fetch_assoc();

if (!$payment) {
    header("Location: properties.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Payment Successful • House Unlimited</title>
    <link rel="stylesheet" href="../assets/css/style.css" />
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Playfair+Display:wght@700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"/>
</head>
<body>
    <?php include '../inc/header.php'; ?>
    <div class="container">
        <?php include '../inc/sidebar.php'; ?>

        <main class="main-content">
            <div class="page-header">
                <h1>Payment Successful</h1>
            </div>

            <div class="payment-success-container">
                <i class="fas fa-check-circle" style="color: var(--success); font-size: 4rem; margin-bottom: 1rem;"></i>
                <h2>Thank you for your payment!</h2>
                <p>Your payment for the property "<?php echo htmlspecialchars($payment['title']); ?>" was successful.</p>
                
                <h3>Payment Details:</h3>
                <ul>
                    <li><strong>Payment ID:</strong> <?php echo htmlspecialchars($payment['id']); ?></li>
                    <li><strong>Property:</strong> <?php echo htmlspecialchars($payment['title']); ?></li>
                    <li><strong>Amount:</strong> ₦<?php echo number_format($payment['amount']); ?></li>
                    <li><strong>Date:</strong> <?php echo date("F j, Y, g:i a", strtotime($payment['payment_date'])); ?></li>
                </ul>

                <a href="properties.php" class="btn btn-primary">Back to Properties</a>
            </div>
        </main>
    </div>
</body>
</html>