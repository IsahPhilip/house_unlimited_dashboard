<?php
// dashboard/payment_failure.php
require '../inc/config.php';
require '../inc/auth.php';

$user = $_SESSION['user'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Payment Failed • House Unlimited</title>
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
                <h1>Payment Failed</h1>
            </div>

            <div class="payment-failure-container">
                <i class="fas fa-times-circle" style="color: var(--danger); font-size: 4rem; margin-bottom: 1rem;"></i>
                <h2>Unfortunately, your payment could not be processed.</h2>
                <p>Please try again or contact support if the problem persists.</p>
                
                <a href="properties.php" class="btn btn-primary">Back to Properties</a>
            </div>
        </main>
    </div>
</body>
</html>