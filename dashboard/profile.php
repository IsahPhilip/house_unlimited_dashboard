<?php
// dashboard/profile.php
require '../inc/config.php';
require '../inc/auth.php';

$user = $_SESSION['user'];
$user_id = $user['id'];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $bio = trim($_POST['bio'] ?? '');

    // Basic validation
    if (strlen($name) < 2) {
        $error = "Name must be at least 2 characters";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email address";
    } elseif ($phone && !preg_match('/^(\+234|0)[789][01]\d{8}$/', $phone)) {
        $error = "Invalid Nigerian phone number (e.g. 0803 000 0000 or +2348030000000)";
    } else {
        // Handle avatar upload
        $avatar_path = $user['photo'] ?? 'default_avatar.png';
        if (isset($_FILES['photo']) && $_FILES['photo']['error'] === 0) {
            $allowed = ['jpg', 'jpeg', 'png', 'webp'];
            $ext = strtolower(pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION));
            if (in_array($ext, $allowed) && $_FILES['photo']['size'] < 5*1024*1024) {
                $new_name = 'avatar_' . $user_id . '_' . time() . '.' . $ext;
                $upload_path = "../assets/uploads/avatars/" . $new_name;
                if (move_uploaded_file($_FILES['photo']['tmp_name'], $upload_path)) {
                    // Delete old avatar if not default
                    if ($avatar_path && $avatar_path !== 'default_avatar.png' && file_exists("../assets/uploads/avatars/$avatar_path")) {
                        unlink("../assets/uploads/avatars/$avatar_path");
                    }
                    $avatar_path = $new_name;
                }
            }
        }

        // Update database
        $stmt = $db->prepare("UPDATE users SET name = ?, email = ?, phone = ?, bio = ?, photo = ? WHERE id = ?");
        $stmt->bind_param('sssssi', $name, $email, $phone, $bio, $avatar_path, $user_id);
        if ($stmt->execute()) {
            $success = "Profile updated successfully!";
            $_SESSION['user']['name'] = $name;
            $_SESSION['user']['email'] = $email;
            $_SESSION['user']['phone'] = $phone;
            $_SESSION['user']['bio'] = $bio;
            $_SESSION['user']['photo'] = $avatar_path;
            $user = $_SESSION['user']; // Refresh
        } else {
            $error = "Failed to update profile";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>My Profile • House Unlimited</title>
    <link rel="stylesheet" href="../assets/css/style.css" />
    <style>
        .profile-container {
            max-width: 800px;
            margin: 0 auto;
        }
        .profile-header {
            text-align: center;
            margin-bottom: 3rem;
        }
        .avatar-wrapper {
            position: relative;
            display: inline-block;
            margin-bottom: 1.5rem;
        }
        .avatar {
            width: 160px;
            height: 160px;
            border-radius: 50%;
            object-fit: cover;
            border: 6px solid #e2e8f0;
            box-shadow: 0 10px 30px rgba(0,0,0,0.15);
        }
        body.dark .avatar { border-color: #334155; }
        .avatar-upload {
            position: absolute;
            bottom: 8px;
            right: 8px;
            background: #3b82f6;
            color: white;
            width: 44px;
            height: 44px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            font-size: 1.4rem;
            box-shadow: 0 4px 15px rgba(59,130,246,0.4);
        }
        .avatar-upload input { display: none; }

        .profile-form {
            background: white;
            padding: 2.5rem;
            border-radius: 20px;
            box-shadow: 0 15px 40px rgba(0,0,0,0.1);
        }
        body.dark .profile-form { background: #1e1e1e; }

        .form-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 1.5rem;
        }
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
        .form-group textarea {
            width: 100%;
            padding: 1rem 1.2rem;
            border: 2px solid #e2e8f0;
            border-radius: 12px;
            font-size: 1rem;
            transition: all 0.3s;
        }
        .form-group input:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #3b82f6;
            box-shadow: 0 0 0 4px rgba(59,130,246,0.15);
        }
        body.dark .form-group input,
        body.dark .form-group textarea {
            background: #0f172a;
            border-color: #334155;
            color: #e2e8f0;
        }

        .msg {
            padding: 1rem 1.5rem;
            border-radius: 12px;
            margin: 1.5rem 0;
            font-weight: 500;
        }
        .msg.success {
            background: #d1fae5;
            color: #065f46;
            border: 1px solid #a7f3d0;
        }
        .msg.error {
            background: #fee2e2;
            color: #991b1b;
            border: 1px solid #fecaca;
        }

        .btn-save {
            background: #1e40af;
            color: white;
            padding: 1rem 2.5rem;
            border: none;
            border-radius: 12px;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
        }
        .btn-save:hover {
            background: #1e3a8a;
            transform: translateY(-3px);
            box-shadow: 0 10px 25px rgba(30,64,175,0.3);
        }

        .account-info {
            background: #f8f9fc;
            padding: 1.5rem;
            border-radius: 12px;
            margin-top: 2rem;
            font-size: 0.95rem;
        }
        body.dark .account-info { background: #1e293b; color: #cbd5e1; }
    </style>
</head>
<body>
    <?php include '../inc/header.php'; ?>

    <div class="container">
        <?php include '../inc/sidebar.php'; ?>

        <main class="main-content">
            <div class="profile-container">
                <div class="profile-header">
                    <h1>My Profile</h1>
                    <p>Update your personal information and preferences</p>
                </div>

                <?php if (isset($success)): ?>
                    <div class="msg success"><?= htmlspecialchars($success) ?></div>
                <?php endif; ?>
                <?php if (isset($error)): ?>
                    <div class="msg error"><?= htmlspecialchars($error) ?></div>
                <?php endif; ?>

                <form method="POST" enctype="multipart/form-data" class="profile-form">
                    <div class="avatar-wrapper">
                        <?php
                            // Determine the correct avatar URL, falling back to default if not found
                            $avatar_url = '../assets/uploads/avatars/default_avatar.png'; // Default
                            if (!empty($user['photo'])) {
                                $user_avatar_path = '../assets/uploads/avatars/' . basename($user['photo']);
                                if (file_exists($user_avatar_path)) {
                                    $avatar_url = $user_avatar_path;
                                }
                            }
                        ?>
                        <img src="<?= htmlspecialchars($avatar_url) ?>" 
                             alt="Profile Photo" class="avatar" id="avatarPreview">
                            <label class="avatar-upload">
                                <i class="fas fa-camera"></i>
                                <input type="file" name="photo" accept="image/*" onchange="previewAvatar(event)">
                            </label>                    </div>

                    <div class="form-grid">
                        <div class="form-group">
                            <label>Full Name</label>
                            <input type="text" name="name" value="<?= htmlspecialchars($user['name'] ?? '') ?>" required />
                        </div>
                        <div class="form-group">
                            <label>Email Address</label>
                            <input type="email" name="email" value="<?= htmlspecialchars($user['email'] ?? '') ?>" required />
                        </div>
                        <div class="form-group">
                            <label>Phone Number</label>
                            <input type="tel" name="phone" value="<?= htmlspecialchars($user['phone'] ?? '') ?>" 
                                   placeholder="e.g. 0803 000 0000 or +2348030000000" />
                        </div>
                        <div class="form-group">
                            <label>Account Type</label>
                            <input type="text" value="<?= ucfirst($user['role']) ?>" disabled 
                                   style="background:#f1f5f9; cursor:not-allowed;" />
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Bio / About Me (Optional)</label>
                        <textarea name="bio" rows="4" placeholder="Tell us about yourself..."><?= htmlspecialchars($user['bio'] ?? '') ?></textarea>
                    </div>

                    <div style="text-align:center; margin-top:2rem;">
                        <button type="submit" class="btn-save">Save Changes</button>
                    </div>
                </form>

                <div class="account-info">
                    <strong>Member Since:</strong> <?= date('F Y', strtotime($user['created_at'] ?? 'now')) ?><br>
                    <strong>User ID:</strong> #<?= str_pad($user_id, 6, '0', STR_PAD_LEFT) ?><br>
                    <small>Your data is secure and never shared • Lagos, Nigeria</small>
                </div>
            </div>
        </main>
    </div>

    <script>
        function previewAvatar(event) {
            const file = event.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = e => {
                    document.getElementById('avatarPreview').src = e.target.result;
                };
                reader.readAsDataURL(file);
            }
        }

        // Auto-format phone number (optional enhancement)
        document.querySelector('input[name="phone"]').addEventListener('input', function(e) {
            let val = e.target.value.replace(/\D/g, '');
            if (val.startsWith('234')) val = '+' + val;
            else if (val.startsWith('0')) val = '+234' + val.substring(1);
            else if (val.length === 11) val = '+234' + val.substring(1);
            e.target.value = val.substring(0, 14);
        });
    </script>
</body>
</html>