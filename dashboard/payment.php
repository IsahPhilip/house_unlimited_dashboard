<?php
// dashboard/payment.php
require '../inc/config.php';
require '../inc/auth.php';

$user = $_SESSION['user'];
$user_id = $user['id'];

if (!isset($_GET['id'])) {
    header("Location: properties.php");
    exit;
}

$property_id = $_GET['id'];

// Fetch property details from the database
$stmt = $db->prepare("SELECT * FROM properties WHERE id = ?");
$stmt->bind_param('i', $property_id);
$stmt->execute();
$result = $stmt->get_result();
$property = $result->fetch_assoc();

if (!$property) {
    header("Location: properties.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Make Payment • House Unlimited</title>
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
                <h1>Make Payment</h1>
            </div>

            <div class="payment-container">
                <h2><?php echo htmlspecialchars($property['title']); ?></h2>
                <p><?php echo htmlspecialchars($property['location']); ?></p>
                <h3>Price: ₦<?php echo number_format($property['price']); ?></h3>

                <form action="payment_process.php" method="POST">
                    <input type="hidden" name="property_id" value="<?php echo $property_id; ?>">
                    <input type="hidden" name="amount" value="<?php echo $property['price']; ?>">
                    <button type="submit" class="btn btn-success">Confirm Payment</button>
                </form>
            </div>
        </main>
    </div>
</body>
</html>