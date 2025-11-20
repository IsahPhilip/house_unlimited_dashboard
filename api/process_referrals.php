<?php
require '../inc/config.php';

// This script should be run periodically by a cron job.

// 1. Get referral settings
$bonus_amount = 1000; // Default bonus
$stmt = $db->query("SELECT bonus_amount FROM referral_settings ORDER BY id DESC LIMIT 1");
if ($stmt && $settings = $stmt->fetch_assoc()) {
    $bonus_amount = $settings['bonus_amount'];
}

// 2. Find pending referrals
$stmt = $db->prepare("
    SELECT r.id AS referral_id, r.referrer_id, r.referee_id
    FROM referrals r
    WHERE r.status = 'pending'
");
$stmt->execute();
$pending_referrals = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

if (empty($pending_referrals)) {
    echo "No pending referrals to process.\n";
    exit;
}

// 3. Check each referral for completion
$update_referral_stmt = $db->prepare("UPDATE referrals SET status = 'completed', bonus_earned = ? WHERE id = ?");
$add_bonus_stmt = $db->prepare("UPDATE users SET wallet_balance = wallet_balance + ? WHERE id = ?");

$processed_count = 0;

foreach ($pending_referrals as $referral) {
    $referee_id = $referral['referee_id'];

    // Check if the referee has at least one 'sold' property
    $check_stmt = $db->prepare("SELECT COUNT(*) AS sold_count FROM properties WHERE agent_id = ? AND status = 'sold'");
    $check_stmt->bind_param('i', $referee_id);
    $check_stmt->execute();
    $result = $check_stmt->get_result()->fetch_assoc();
    $check_stmt->close();

    if ($result && $result['sold_count'] > 0) {
        // Condition met, complete the referral
        $db->begin_transaction();
        try {
            // Update referral status and bonus
            $update_referral_stmt->bind_param('di', $bonus_amount, $referral['referral_id']);
            $update_referral_stmt->execute();

            // Add bonus to referrer's wallet
            $add_bonus_stmt->bind_param('di', $bonus_amount, $referral['referrer_id']);
            $add_bonus_stmt->execute();

            $db->commit();
            $processed_count++;
            echo "Referral ID {$referral['referral_id']} completed. Bonus of $bonus_amount awarded to user ID {$referral['referrer_id']}.\n";

        } catch (mysqli_sql_exception $exception) {
            $db->rollback();
            echo "Failed to process referral ID {$referral['referral_id']}: " . $exception->getMessage() . "\n";
        }
    }
}

$update_referral_stmt->close();
$add_bonus_stmt->close();
$db->close();

echo "Processed $processed_count completed referrals.\n";
?>
