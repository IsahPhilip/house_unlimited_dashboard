<?php
// dashboard/edit_property.php - FINAL + LUXURY LOGS + 100% WORKING
require '../inc/config.php';
require '../inc/auth.php';

$user_id = $_SESSION['user']['id'];
$role = $_SESSION['user']['role'];

if ($role !== 'agent' && $role !== 'admin') {
    header('Location: index.php');
    exit;
}

$property_id = (int)($_GET['id'] ?? 0);
if ($property_id <= 0) {
    header('Location: properties.php');
    exit;
}

// Fetch property
$stmt = $db->prepare("
    SELECT p.*, GROUP_CONCAT(pi.image_path) as images 
    FROM properties p 
    LEFT JOIN property_images pi ON p.id = pi.property_id 
    WHERE p.id = ? AND (p.agent_id = ? OR ? = 'admin')
    GROUP BY p.id
");
$stmt->bind_param('iis', $property_id, $user_id, $role);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    $_SESSION['error'] = "Property not found or access denied.";
    header('Location: properties.php');
    exit;
}

$property = $result->fetch_assoc();
$old_price = $property['price'];
$old_status = $property['status'];
$old_title = $property['title'];
$images = array_filter(explode(',', $property['images'] ?? ''));

// Handle Update
if ($_POST['action'] ?? '' === 'update') {
    $title       = trim($_POST['title']);
    $price       = (float)preg_replace('/\D/', '', $_POST['price']);
    $location    = trim($_POST['location']);
    $bedrooms    = (int)$_POST['bedrooms'];
    $bathrooms   = (int)$_POST['bathrooms'];
    $area        = (float)$_POST['area'];
    $type        = $_POST['type'];
    $status      = $_POST['status'];
    $description = trim($_POST['description']);
    $features    = implode(', ', $_POST['features'] ?? []);

    if (empty($title) || $price <= 0 || empty($location)) {
        $_SESSION['error'] = "Please fill all required fields.";
    } else {
        $stmt = $db->prepare("
            UPDATE properties SET
                title = ?, price = ?, location = ?, bedrooms = ?, bathrooms = ?,
                area = ?, type = ?, status = ?, description = ?, features = ?
            WHERE id = ? AND (agent_id = ? OR ? = 'admin')
        ");
        $stmt->bind_param('sdsiiissssiss', $title, $price, $location, $bedrooms, $bathrooms, $area, $type, $status, $description, $features, $property_id, $user_id, $role);

        if ($stmt->execute()) {
            $log_parts = [];
            $uploaded = 0;
            $deleted = 0;

            // 1. Price Change
            if ($price != $old_price) {
                $log_parts[] = "Changed price from " . format_ngn($old_price) . " → " . format_ngn($price) . " for $title";
            }

            // 2. Status Change → Especially SOLD
            if ($status !== $old_status) {
                if ($status === 'sold') {
                    log_activity("Marked property as SOLD: $title in " . ucwords(str_replace(['-', '_'], ' ', $location)));
                } else {
                    $log_parts[] = "Changed status from '" . ucfirst($old_status) . "' → '" . ucfirst($status) . "'";
                }
            }

            // 3. Upload New Photos
            if (!empty($_FILES['new_images']['name'][0])) {
                $dir = '../assets/uploads/properties/';
                if (!is_dir($dir)) mkdir($dir, 0755, true);

                foreach ($_FILES['new_images']['tmp_name'] as $k => $tmp) {
                    if ($_FILES['new_images']['error'][$k] === 0) {
                        $ext = pathinfo($_FILES['new_images']['name'][$k], PATHINFO_EXTENSION);
                        $name = 'prop_' . $property_id . '_' . time() . "_$k." . strtolower($ext);
                        if (move_uploaded_file($tmp, $dir . $name)) {
                            $name_esc = $db->real_escape_string($name);
                            $db->query("INSERT INTO property_images (property_id, image_path) VALUES ($property_id, '$name_esc')");
                            $uploaded++;
                        }
                    }
                }
                if ($uploaded > 0) {
                    $log_parts[] = "Uploaded $uploaded new " . ($uploaded == 1 ? 'photo' : 'photos') . " to $title";
                }
            }

            // 4. Delete Photos
            if (!empty($_POST['delete_images'])) {
                $ids = implode(',', array_map('intval', $_POST['delete_images']));
                $res = $db->query("DELETE FROM property_images WHERE id IN ($ids) AND property_id = $property_id");
                $deleted = $res->affected_rows;
                if ($deleted > 0) {
                    $log_parts[] = "Deleted $deleted " . ($deleted == 1 ? 'photo' : 'photos') . " from $title";
                }
            }

            // 5. Title or General Update
            if (empty($log_parts)) {
                $log_parts[] = "Updated property: $title";
            }

            // Final Smart Log
            $final_log = implode(' | ', $log_parts);
            log_activity($final_log);

            $_SESSION['success'] = "Property updated successfully!";
            header("Location: edit_property.php?id=$property_id");
            exit;
        } else {
            $_SESSION['error'] = "Update failed.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit • <?= htmlspecialchars($property['title']) ?> • House Unlimited</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        :root { --blue: #1e40af; --green: #10b981; --amber: #f59e0b; }
        .header { background: linear-gradient(135deg, #1e293b, #0f172a); color: white; padding: 3rem; border-radius: 24px; text-align: center; margin-bottom: 2rem; }
        .header h1 { font-size: 3rem; margin: 0; font-family: 'Playfair Display', serif; }
        .grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 2rem; }
        input, select, textarea { width: 100%; padding: 1rem; border: 2px solid #e5e7eb; border-radius: 12px; font-size: 1rem; }
        input:focus, select:focus, textarea:focus { border-color: var(--blue); outline: none; }
        .btn { padding: 1.2rem 3rem; border: none; border-radius: 16px; font-weight: bold; cursor: pointer; font-size: 1.2rem; }
        .btn-save { background: var(--green); color: white; }
        .btn-cancel { background: #64748b; color: white; margin-left: 1rem; }
        .gallery { display: grid; grid-template-columns: repeat(auto-fill, minmax(180px, 1fr)); gap: 1rem; margin: 2rem 0; }
        .img-box { position: relative; border-radius: 16px; overflow: hidden; box-shadow: 0 10px 30px rgba(0,0,0,0.2); }
        .img-box img { width: 100%; height: 170px; object-fit: cover; }
        .del-check { position: absolute; top: 10px; right: 10px; background: rgba(239,68,68,0.95); color: white; padding: 0.6rem 1.2rem; border-radius: 50px; font-weight: bold; }
    </style>
</head>
<body>
    <?php include '../inc/header.php'; ?>
    <div class="container">
        <?php include '../inc/sidebar.php'; ?>

        <main class="main-content">
            <div class="header">
                <h1>Edit Property</h1>
                <p><strong><?= htmlspecialchars($property['title']) ?></strong> • <?= ucwords($property['location']) ?></p>
            </div>

            <?php if (isset($_SESSION['success'])): ?>
                <div style="background:#d1fae5;color:#065f46;padding:1.5rem;border-radius:16px;text-align:center;margin:2rem 0;font-weight:600;">
                    <?= $_SESSION['success']; unset($_SESSION['success']); ?>
                </div>
            <?php endif; ?>

            <?php if (isset($_SESSION['error'])): ?>
                <div style="background:#fee2e2;color:#991b1b;padding:1.5rem;border-radius:16px;text-align:center;margin:2rem 0;">
                    <?= $_SESSION['error']; unset($_SESSION['error']); ?>
                </div>
            <?php endif; ?>

            <form method="POST" enctype="multipart/form-data">
                <input type="hidden" name="action" value="update">
                <div class="grid">
                    <div>
                        <label>Title *</label>
                        <input type="text" name="title" value="<?= htmlspecialchars($property['title']) ?>" required>
                        <label>Price (₦) *</label>
                        <input type="text" name="price" value="<?= number_format($property['price']) ?>" required>
                        <label>Location *</label>
                        <input type="text" name="location" value="<?= htmlspecialchars($property['location']) ?>" required>
                        <label>Type</label>
                        <select name="type">
                            <option value="duplex" <?= $property['type']=='duplex'?'selected':'' ?>>Duplex</option>
                            <option value="terrace" <?= $property['type']=='terrace'?'selected':'' ?>>Terrace</option>
                            <option value="apartment" <?= $property['type']=='apartment'?'selected':'' ?>>Apartment</option>
                            <option value="land" <?= $property['type']=='land'?'selected':'' ?>>Land</option>
                            <option value="commercial" <?= $property['type']=='commercial'?'selected':'' ?>>Commercial</option>
                        </select>
                        <label>Status</label>
                        <select name="status">
                            <option value="available" <?= $property['status']=='available'?'selected':'' ?>>Available</option>
                            <option value="sold" <?= $property['status']=='sold'?'selected':'' ?>>Sold</option>
                            <option value="pending" <?= $property['status']=='pending'?'selected':'' ?>>Pending</option>
                        </select>
                    </div>
                    <div>
                        <label>Bedrooms</label>
                        <input type="number" name="bedrooms" value="<?= $property['bedrooms'] ?>">
                        <label>Bathrooms</label>
                        <input type="number" name="bathrooms" value="<?= $property['bathrooms'] ?>">
                        <label>Area (sqm)</label>
                        <input type="number" step="0.1" name="area" value="<?= $property['area'] ?>">
                        <label>Description</label>
                        <textarea name="description" rows="6"><?= htmlspecialchars($property['description']) ?></textarea>
                    </div>
                </div>

                <label>Features & Amenities</label>
                <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(160px,1fr));gap:1rem;margin:1.5rem 0;">
                    <?php 
                    $feats = ['Swimming Pool','Gym','Security','Garden','Parking','CCTV','BQs','Smart Home','Generator','Solar','Waterfront','Gated Estate'];
                    $curr = array_map('trim', explode(',', $property['features']??''));
                    foreach($feats as $f): ?>
                        <label style="display:flex;gap:0.5rem;align-items:center;">
                            <input type="checkbox" name="features[]" value="<?= $f ?>" <?= in_array($f,$curr)?'checked':'' ?>>
                            <?= $f ?>
                        </label>
                    <?php endforeach; ?>
                </div>

                <label>Current Images</label>
                <div class="gallery">
                    <?php foreach($images as $img): $img = trim($img); if(!$img) continue; ?>
                        <?php $img_id = $db->query("SELECT id FROM property_images WHERE image_path = '$img'")->fetch_assoc()['id'] ?? 0; ?>
                        <div class="img-box">
                            <img src="../assets/uploads/properties/<?= $img ?>" alt="">
                            <div class="del-check">
                                <input type="checkbox" name="delete_images[]" value="<?= $img_id ?>" id="del<?= $img_id ?>">
                                <label for="del<?= $img_id ?>">Delete</label>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <label>Add New Images</label>
                <input type="file" name="new_images[]" multiple accept="image/*"><br><br>

                <div style="text-align:center;margin:4rem 0;">
                    <button type="submit" class="btn btn-save">Update Property</button>
                    <a href="properties.php" class="btn btn-cancel">Cancel</a>
                </div>
            </form>
        </main>
    </div>

    <script>
        document.querySelector('input[name="price"]').addEventListener('input', e => {
            let v = e.target.value.replace(/\D/g,'');
            e.target.value = v ? Number(v).toLocaleString() : '';
        });
    </script>
</body>
</html>