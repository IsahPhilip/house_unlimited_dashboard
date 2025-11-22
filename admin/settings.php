<?php
// admin/settings.php - Master System Settings
require '../inc/config.php';
require '../inc/auth.php';

if ($_SESSION['user']['role'] !== 'admin') {
    header('Location: ../dashboard/');
    exit;
}

// Load current settings
$settings = [];
$result = $db->query("SELECT setting_key, setting_value FROM system_settings");
while ($row = $result->fetch_assoc()) {
    $settings[$row['setting_key']] = $row['setting_value'];
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $updates = [
        'site_name' => $_POST['site_name'] ?? 'House Unlimited',
        'company_phone' => $_POST['company_phone'] ?? '',
        'company_email' => $_POST['company_email'] ?? '',
        'booking_fee' => floatval($_POST['booking_fee'] ?? 0),
        'commission_rate' => floatval($_POST['commission_rate'] ?? 0),
        'maintenance_mode' => isset($_POST['maintenance_mode']) ? 1 : 0,
        'whatsapp_number' => $_POST['whatsapp_number'] ?? '',
        'paystack_public_key' => $_POST['paystack_public_key'] ?? '',
        'paystack_secret_key' => $_POST['paystack_secret_key'] ?? '',
        'sms_api_key' => $_POST['sms_api_key'] ?? '',
        'google_maps_key' => $_POST['google_maps_key'] ?? '',
        'default_currency' => $_POST['default_currency'] ?? 'NGN',
        'property_approval_required' => isset($_POST['property_approval_required']) ? 1 : 0,
    ];

    foreach ($updates as $key => $value) {
        $stmt = $db->prepare("INSERT INTO system_settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = ?");
        $stmt->bind_param('sss', $key, $value, $value);
        $stmt->execute();
    }

    // Handle logo upload
    if (isset($_FILES['site_logo']) && $_FILES['site_logo']['error'] === 0) {
        $allowed = ['png', 'jpg', 'jpeg', 'webp'];
        $ext = strtolower(pathinfo($_FILES['site_logo']['name'], PATHINFO_EXTENSION));
        if (in_array($ext, $allowed) && $_FILES['site_logo']['size'] < 2*1024*1024) {
            $filename = 'logo.' . strtolower($ext);
            $target_dir = "../assets/img/";
            if (!file_exists($target_dir)) {
                mkdir($target_dir, 0777, true);
            }
            move_uploaded_file($_FILES['site_logo']['tmp_name'], $target_dir . $filename);
            $db->query("INSERT INTO system_settings (setting_key, setting_value) VALUES ('site_logo', '$filename') ON DUPLICATE KEY UPDATE setting_value = '$filename'");
        }
    }

    log_activity("Admin updated system settings");
    $success = "Settings saved successfully!";
    $settings = array_merge($settings, $updates);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Admin • System Settings • House Unlimited</title>
    <link rel="stylesheet" href="../assets/css/style.css" />
    <style>
        .admin-header {
            background: linear-gradient(135deg, #dc2626, #991b1b);
            color: white;
            padding: 2.5rem;
            border-radius: 20px;
            margin-bottom: 2.5rem;
            text-align: center;
        }
        .admin-header h1 { margin: 0 0 0.5rem; font-size: 2.8rem; font-weight: 800; }
        .admin-header p { margin: 0; opacity: 0.95; font-size: 1.2rem; }

        .settings-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(500px, 1fr));
            gap: 2rem;
            margin: 2rem 0;
        }
        .settings-card {
            background: white;
            padding: 2rem;
            border-radius: 20px;
            box-shadow: 0 15px 40px rgba(0,0,0,0.1);
            border-left: 5px solid #dc2626;
        }
        body.dark .settings-card { background: #1e1e1e; }

        .settings-card h3 {
            margin: 0 0 1.5rem;
            font-size: 1.4rem;
            color: #dc2626;
            padding-bottom: 0.8rem;
            border-bottom: 2px solid #fee2e2;
        }
        body.dark .settings-card h3 { color: #fca5a5; border-color: #450a0a; }

        .form-group {
            margin-bottom: 1.5rem;
        }
        .form-group label {
            display: block;
            margin-bottom: 0.6rem;
            font-weight: 600;
            color: #1e293b;
        }
        body.dark .form-group label { color: #e2e8f0; }

        .form-group input,
        .form-group textarea,
        .form-group select {
            width: 100%;
            padding: 1rem 1.2rem;
            border: 2px solid #e2e8f0;
            border-radius: 12px;
            font-size: 1rem;
        }
        .form-group input:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #dc2626;
            box-shadow: 0 0 0 4px rgba(220,38,38,0.15);
        }

        .toggle {
            position: relative;
            display: inline-block;
            width: 60px;
            height: 34px;
        }
        .toggle input {
            opacity: 0;
            width: 0;
            height: 0;
        }
        .slider {
            position: absolute;
            cursor: pointer;
            top: 0; left: 0; right: 0; bottom: 0;
            background: #94a3b8;
            transition: .4s;
            border-radius: 34px;
        }
        .slider:before {
            position: absolute;
            content: "";
            height: 26px;
            width: 26px;
            left: 4px;
            bottom: 4px;
            background: white;
            transition: .4s;
            border-radius: 50%;
        }
        input:checked + .slider { background: #dc2626; }
        input:checked + .slider:before { transform: translateX(26px); }

        .current-logo {
            width: 120px;
            height: 120px;
            object-fit: contain;
            border: 3px dashed #dc2626;
            border-radius: 16px;
            padding: 1rem;
            background: #fef2f2;
        }

        .save-btn {
            background: #dc2626;
            color: white;
            padding: 1.2rem 3rem;
            border: none;
            border-radius: 12px;
            font-size: 1.2rem;
            font-weight: 700;
            cursor: pointer;
            box-shadow: 0 10px 30px rgba(220,38,38,0.4);
            transition: all 0.3s;
        }
        .save-btn:hover {
            background: #b91c1c;
            transform: translateY(-5px);
        }

        .msg.success {
            background: #d1fae5;
            color: #065f46;
            padding: 1rem 1.5rem;
            border-radius: 12px;
            border: 1px solid #a7f3d0;
        }
    </style>
</head>
<body class="dark">
    <?php include '../inc/header.php'; ?>

    <div class="container">
        <?php include '../inc/sidebar.php'; ?>

        <main class="main-content">
            <div class="admin-header">
                <h1>System Settings</h1>
                <p>Master control for House Unlimited Nigeria</p>
            </div>

            <?php if (isset($success)): ?>
                <div class="msg success"><?= $success ?></div>
            <?php endif; ?>

            <form method="POST" enctype="multipart/form-data">
                <div class="settings-grid">
                    <!-- Branding -->
                    <div class="settings-card">
                        <h3>Branding & Identity</h3>
                        <div class="form-group">
                            <label>Site Name</label>
                            <input type="text" name="site_name" value="<?= $settings['site_name'] ?? 'House Unlimited & Land Services Nigeria' ?>" required />
                        </div>
                        <div class="form-group">
                            <label>Company Phone (WhatsApp)</label>
                            <input type="text" name="company_phone" value="<?= $settings['company_phone'] ?? '+2348030000000' ?>" placeholder="+234..." />
                        </div>
                        <div class="form-group">
                            <label>Company Email</label>
                            <input type="email" name="company_email" value="<?= $settings['company_email'] ?? 'info@houseunlimited.ng' ?>" />
                        </div>
                        <div class="form-group">
                            <label>Upload Logo</label>
                            <input type="file" name="site_logo" accept="image/*" />
                            <?php if (!empty($settings['site_logo'])): ?>
                                <img src="../assets/img/<?= $settings['site_logo'] ?>" alt="Current Logo" class="current-logo" style="margin-top:1rem;">
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Payments & Fees -->
                    <div class="settings-card">
                        <h3>Payments & Commission</h3>
                        <div class="form-group">
                            <label>Default Booking Fee (₦)</label>
                            <input type="number" name="booking_fee" value="<?= $settings['booking_fee'] ?? '50000000' ?>" step="1000" />
                        </div>
                        <div class="form-group">
                            <label>Agent Commission Rate (%)</label>
                            <input type="number" name="commission_rate" value="<?= $settings['commission_rate'] ?? '5' ?>" min="0" max="100" step="0.1" />
                        </div>
                        <div class="form-group">
                            <label>Paystack Public Key</label>
                            <input type="text" name="paystack_public_key" value="<?= $settings['paystack_public_key'] ?? '' ?>" />
                        </div>
                        <div class="form-group">
                            <label>Paystack Secret Key</label>
                            <input type="password" name="paystack_secret_key" value="<?= $settings['paystack_secret_key'] ?? '' ?>" placeholder="Hidden for security" />
                        </div>
                    </div>

                    <!-- System Controls -->
                    <div class="settings-card">
                        <h3>Platform Controls</h3>
                        <div class="form-group">
                            <label>
                                <input type="checkbox" name="maintenance_mode" <?= ($settings['maintenance_mode'] ?? 0) ? 'checked' : '' ?>>
                                <span style="margin-left:1rem; font-weight:600;">Maintenance Mode (Site Offline)</span>
                            </label>
                        </div>
                        <div class="form-group">
                            <label>
                                <input type="checkbox" name="property_approval_required" <?= ($settings['property_approval_required'] ?? 1) ? 'checked' : '' ?>>
                                <span style="margin-left:1rem; font-weight:600;">Require Admin Approval for New Properties</span>
                            </label>
                        </div>
                        <div class="form-group">
                            <label>WhatsApp Business Number</label>
                            <input type="text" name="whatsapp_number" value="<?= $settings['whatsapp_number'] ?? '+2348030000000' ?>" />
                        </div>
                        <div class="form-group">
                            <label>Google Maps API Key</label>
                            <input type="text" name="google_maps_key" value="<?= $settings['google_maps_key'] ?? '' ?>" />
                        </div>
                    </div>

                    <!-- SMS & Notifications -->
                    <div class="settings-card">
                        <h3>SMS & Notifications</h3>
                        <div class="form-group">
                            <label>Termii / SMS API Key</label>
                            <input type="password" name="sms_api_key" value="<?= $settings['sms_api_key'] ?? '' ?>" placeholder="Hidden" />
                        </div>
                        <div class="form-group">
                            <label>Default Currency</label>
                            <select name="default_currency">
                                <option value="NGN" <?= ($settings['default_currency'] ?? 'NGN') === 'NGN' ? 'selected' : '' ?>>NGN (₦) - Nigerian Naira</option>
                                <option value="USD" <?= ($settings['default_currency'] ?? '') === 'USD' ? 'selected' : '' ?>>USD ($) - US Dollar</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div style="text-align:center; margin-top:3rem;">
                    <button type="submit" class="save-btn">Save All Settings</button>
                </div>
            </form>
        </main>
    </div>
</body>
</html>