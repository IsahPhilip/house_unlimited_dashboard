<?php
// inc/functions.php - Helper functions

function escape($string) {
    global $db;
    return htmlspecialchars($db->real_escape_string(trim($string)), ENT_QUOTES, 'UTF-8');
}

function redirect($url) {
    header("Location: $url");
    exit;
}

function is_admin() {
    return isset($_SESSION['user']['role']) && $_SESSION['user']['role'] === 'admin';
}

function is_agent() {
    return isset($_SESSION['user']['role']) && in_array($_SESSION['user']['role'], ['agent', 'admin']);
}

function format_ngn($amount) {
    return '₦' . number_format((float)$amount, 0, '.', ',');
}

function format_phone($phone) {
    $phone = preg_replace('/\D/', '', $phone);
    if (strlen($phone) === 11 && $phone[0] === '0') {
        $phone = '234' . substr($phone, 1);
    } elseif (strlen($phone) === 10) {
        $phone = '234' . $phone;
    }
    return '+' . $phone;
}

function generate_ref($prefix = 'HUL') {
    return $prefix . '-' . strtoupper(bin2hex(random_bytes(4)));
}

function generateUniqueReferralCode($db) {
    do {
        $code = substr(md5(uniqid(mt_rand(), true)), 0, 10);
        $stmt = $db->prepare("SELECT id FROM users WHERE referral_code = ?");
        $stmt->bind_param('s', $code);
        $stmt->execute();
        $stmt->store_result();
        $is_unique = $stmt->num_rows === 0;
        $stmt->close();
    } while (!$is_unique);
    return $code;
}

function log_activity($action) {
    global $db, $user;
    $user_id = $_SESSION['user']['id'] ?? 0;
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    $ua = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
    
    $stmt = $db->prepare("INSERT INTO activity_log (user_id, action, ip_address, user_agent) VALUES (?, ?, ?, ?)");
    $stmt->bind_param('isss', $user_id, $action, $ip, $ua);
    $stmt->execute();
}

function send_sms($phone, $message) {
    // Placeholder for Termii, Africa's Talking, or Twilio
    // return true;
}

function generate_referral_link($user_id) {
    $base_url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]";
    $encoded_id = base64_encode("ref=" . $user_id);
    return $base_url . "/register.php?ref=" . $encoded_id;
}

function process_referral_completion($referee_id, $amount_paid) {
    global $db;

    // 1. Fetch pending referral entry for the referee
    $stmt = $db->prepare("SELECT id, referrer_id FROM referrals WHERE referee_id = ? AND status = 'pending'");
    $stmt->bind_param('i', $referee_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $referral = $result->fetch_assoc();
    $stmt->close();

    if (!$referral) {
        // No pending referral found for this referee
        return;
    }

    $referral_id = $referral['id'];
    $referrer_id = $referral['referrer_id'];

    // 2. Check referral_settings for bonus conditions
    // Assuming there's only one active referral setting or the latest one applies
    $stmt = $db->query("SELECT requirement_type, requirement_value, bonus_amount FROM referral_settings ORDER BY id DESC LIMIT 1");
    $settings = $stmt->fetch_assoc();
    $stmt->close();

    if (!$settings) {
        // No referral settings defined
        error_log("No referral settings found in database.");
        return;
    }

    $requirement_type = $settings['requirement_type'];
    $requirement_value = (float)$settings['requirement_value'];
    $bonus_amount = (float)$settings['bonus_amount'];

    $conditions_met = false;

    // Check specific requirement types
    switch ($requirement_type) {
        case 'first_payment':
            // Check if this is indeed the referee's first payment
            // and if the amount meets the requirement
            $stmt = $db->prepare("SELECT COUNT(*) FROM payments WHERE user_id = ? AND status = 'success'");
            $stmt->bind_param('i', $referee_id);
            $stmt->execute();
            $stmt->bind_result($payment_count);
            $stmt->fetch();
            $stmt->close();

            if ($payment_count === 1 && $amount_paid >= $requirement_value) {
                $conditions_met = true;
            }
            break;
        // Add other requirement types here (e.g., 'first_property_listing')
        // case 'first_property_listing':
        //     $stmt = $db->prepare("SELECT COUNT(*) FROM properties WHERE agent_id = ? AND status = 'active'");
        //     $stmt->bind_param('i', $referee_id);
        //     $stmt->execute();
        //     $stmt->bind_result($property_count);
        //     $stmt->fetch();
        //     $stmt->close();
        //     if ($property_count === 1 && $requirement_value === 1) { // Assuming requirement_value for this is always 1
        //         $conditions_met = true;
        //     }
        //     break;
    }

    if ($conditions_met) {
        // 3. Update referral status and bonus_earned
        $db->begin_transaction();
        try {
            $stmt = $db->prepare("UPDATE referrals SET status = 'completed', bonus_earned = ? WHERE id = ?");
            $stmt->bind_param('di', $bonus_amount, $referral_id);
            $stmt->execute();
            $stmt->close();

            // 4. Update referrer's wallet_balance
            $stmt = $db->prepare("UPDATE users SET wallet_balance = wallet_balance + ? WHERE id = ?");
            $stmt->bind_param('di', $bonus_amount, $referrer_id);
            $stmt->execute();
            $stmt->close();

            $db->commit();
            log_activity("Referral #$referral_id completed. Referrer #$referrer_id earned " . format_ngn($bonus_amount) . " for referee #$referee_id's action.");

        } catch (mysqli_sql_exception $e) {
            $db->rollback();
            error_log("Failed to process referral completion for referee $referee_id: " . $e->getMessage());
        }
    }
}

?>