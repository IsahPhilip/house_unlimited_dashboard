<?php
// dashboard/my_properties.php
require '../inc/config.php';
require '../inc/auth.php';

$user_id = $_SESSION['user']['id'];

// Query: Find all properties where the client has FULLY PAID (success transactions >= purchase_price in metadata)
$sql = "
SELECT 
    p.*,
    pr.phase,
    pr.percentage,
    pr.description AS progress_desc,
    pr.updated_at AS progress_updated,
    COALESCE(SUM(t.amount), 0) AS total_paid,
    JSON_UNQUOTE(JSON_EXTRACT(t.metadata, '$.purchase_price')) AS purchase_price_raw,
    COALESCE(
        CAST(JSON_UNQUOTE(JSON_EXTRACT(t.metadata, '$.purchase_price')) AS DECIMAL(15,2)),
        p.price
    ) AS purchase_price,
    COALESCE(SUM(t.amount), 0) >= COALESCE(
        CAST(JSON_UNQUOTE(JSON_EXTRACT(t.metadata, '$.purchase_price')) AS DECIMAL(15,2)),
        p.price
    ) AS is_fully_paid
FROM properties p
JOIN transactions t ON p.id = t.property_id 
    AND t.client_id = ? 
    AND t.status = 'success'
    AND JSON_EXTRACT(t.metadata, '$.type') = '\"property_purchase\"'
LEFT JOIN property_progress pr ON p.id = pr.property_id
GROUP BY p.id
HAVING is_fully_paid = 1
ORDER BY MAX(t.paid_at) DESC, p.id DESC";

$stmt = $db->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$properties = $result->fetch_all(MYSQLI_ASSOC);

$page_title = "My Properties";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title><?= $page_title ?> • <?= SITE_NAME ?></title>
    <link rel="stylesheet" href="../assets/css/style.css" />
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <?php include '../inc/header.php'; ?>

    <div class="container">
        <?php include '../inc/sidebar.php'; ?>

        <main class="main-content">
            <div class="header">
                <h1><?= $page_title ?></h1>
                <p>Properties you have fully purchased and their current construction status.</p>
            </div>

            <div class="card-grid">
                <?php if (empty($properties)): ?>
                    <div class="card" style="text-align:center; grid-column: 1 / -1; padding: 3rem;">
                        <h3>You have not fully purchased any properties yet.</h3>
                        <p>Once you complete all payments on a property, it will appear here.</p>
                        <a href="properties.php" class="btn btn-primary mt-2">Browse Properties</a>
                    </div>
                <?php endif; ?>

                <?php foreach ($properties as $prop): ?>
                <div class="card property-card">
                    <a href="property_detail.php?id=<?= $prop['id'] ?>">
                        <img src="../assets/uploads/properties/<?= htmlspecialchars($prop['featured_image'] ?? 'default.jpg') ?>" 
                             class="property-img" 
                             alt="<?= htmlspecialchars($prop['title']) ?>">
                    </a>
                    <div class="property-info">
                        <h3><?= htmlspecialchars($prop['title']) ?></h3>
                        <p><strong>₦<?= number_format($prop['price']) ?></strong></p>
                        <p>Location: <?= htmlspecialchars($prop['location']) ?></p>

                        <!-- Payment Summary -->
                        <div class="payment-summary" style="margin:1rem 0; font-size:0.9rem; color:#28a745;">
                            <strong>Fully Paid</strong> 
                            (₦<?= number_format($prop['total_paid']) ?> of ₦<?= number_format($prop['purchase_price']) ?>)
                        </div>

                        <!-- Construction Progress -->
                        <?php if ($prop['phase']): ?>
                        <div style="margin:1rem 0;">
                            <strong>Current Phase: <?= htmlspecialchars($prop['phase']) ?> (<?= (int)$prop['percentage'] ?>%)</strong>
                            <div class="progress-bar-container">
                                <div class="progress-bar" style="width:<?= (int)$prop['percentage'] ?>%;"></div>
                            </div>
                            <small>Last update: <?= date('M j, Y', strtotime($prop['progress_updated'])) ?></small>
                        </div>
                        <?php else: ?>
                        <p style="color:green; font-weight:600;">Project Completed & Delivered</p>
                        <?php endif; ?>

                        <div class="mt-2">
                            <a href="property_progress.php?id=<?= $prop['id'] ?>" class="btn btn-primary btn-sm">View Progress</a>
                            <a href="my_transactions.php?property_id=<?= $prop['id'] ?>" class="btn btn-outline btn-sm ml-1">Payment History</a>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </main>
    </div>

    <script src="../assets/js/main.js"></script>
</body>
</html>