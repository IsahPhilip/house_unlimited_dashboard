<?php
// dashboard/add_property.php
require '../inc/config.php';
require '../inc/auth.php';

if (!in_array($_SESSION['user']['role'], ['admin', 'agent'])) {
    header('Location: properties.php');
    exit;
}

$user_id = $_SESSION['user']['id'];
$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title       = trim($_POST['title'] ?? '');
    $type        = $_POST['type'] ?? 'sale';
    $price       = floatval($_POST['price'] ?? 0);
    $location    = trim($_POST['location'] ?? '');
    $bedrooms    = intval($_POST['bedrooms'] ?? 0);
    $bathrooms   = intval($_POST['bathrooms'] ?? 0);
    $size        = trim($_POST['size'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $status      = $_POST['status'] ?? 'active';

    // Validation
    if (empty($title)) $errors[] = "Title is required";
    if ($price <= 0) $errors[] = "Valid price is required";
    if (empty($location)) $errors[] = "Location is required";
    if ($bedrooms < 1) $errors[] = "At least 1 bedroom";
    if ($bathrooms < 1) $errors[] = "At least 1 bathroom";
    if (empty($description)) $errors[] = "Description is required";

    // Handle image uploads
    $uploaded_images = [];
    if (empty($errors) && isset($_FILES['images']) && $_FILES['images']['error'][0] !== 4) {
        $allowed = ['jpg', 'jpeg', 'png', 'webp'];
        $max_size = 5 * 1024 * 1024; // 5MB

        foreach ($_FILES['images']['tmp_name'] as $key => $tmp_name) {
            if ($_FILES['images']['error'][$key] === 0) {
                $file_name = $_FILES['images']['name'][$key];
                $file_size = $_FILES['images']['size'][$key];
                $file_ext  = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

                if (!in_array($file_ext, $allowed)) {
                    $errors[] = "File $file_name: Only JPG, PNG, WebP allowed";
                    continue;
                }
                if ($file_size > $max_size) {
                    $errors[] = "File $file_name: Max 5MB allowed";
                    continue;
                }

                $new_name = uniqid('prop_') . '.' . $file_ext;
                $dest = PROPERTY_PATH . $new_name;

                if (move_uploaded_file($tmp_name, $dest)) {
                    $uploaded_images[] = $new_name;
                } else {
                    $errors[] = "Failed to upload $file_name";
                }
            }
        }
    }

    // Save to database
    if (empty($errors)) {
        $stmt = $db->prepare("INSERT INTO properties 
            (agent_id, title, type, price, location, bedrooms, bathrooms, size, description, status, created_at) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())");

        $stmt->bind_param('issdsissss', $user_id, $title, $type, $price, $location, $bedrooms, $bathrooms, $size, $description, $status);
        
        if ($stmt->execute()) {
            $property_id = $db->insert_id;

            // Save images
            if (!empty($uploaded_images)) {
                $imgStmt = $db->prepare("INSERT INTO property_images (property_id, image_path) VALUES (?, ?)");
                foreach ($uploaded_images as $img) {
                    $imgStmt->bind_param('is', $property_id, $img);
                    $imgStmt->execute();
                }
            }

            $success = true;
        } else {
            $errors[] = "Failed to save property. Try again.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Property • House Unlimited</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link href="https://cdn.jsdelivr.net/npm/quill@2.0.2/dist/quill.snow.css" rel="stylesheet">
    <style>
        .form-container {
            max-width: 900px;
            margin: 2rem auto;
            background: white;
            padding: 2.5rem;
            border-radius: 20px;
            box-shadow: 0 15px 40px rgba(0,0,0,0.1);
        }
        body.dark .form-container { background: #1e1e1e; }

        .form-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
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

        .form-group input, .form-group select, .form-group textarea {
            width: 100%;
            padding: 1rem;
            border: 2px solid #e2e8f0;
            border-radius: 12px;
            font-size: 1rem;
        }
        .form-group input:focus, .form-group select:focus {
            outline: none;
            border-color: #3b82f6;
            box-shadow: 0 0 0 4px rgba(59,130,246,0.15);
        }

        .ql-container {
            min-height: 200px;
            border-radius: 12px;
        }

        .image-preview {
            display: flex;
            flex-wrap: wrap;
            gap: 1rem;
            margin-top: 1rem;
        }
        .image-preview img {
            width: 120px;
            height: 120px;
            object-fit: cover;
            border-radius: 12px;
            border: 3px solid #e2e8f0;
        }

        .msg {
            padding: 1rem 1.5rem;
            border-radius: 12px;
            margin: 1.5rem 0;
            font-weight: 500;
        }
        .msg.success { background: #d1fae5; color: #065f46; border: 1px solid #a7f3d0; }
        .msg.error { background: #fee2e2; color: #991b1b; border: 1px solid #fecaca; }
    </style>
</head>
<body>
    <?php include '../inc/header.php'; ?>

    <div class="container">
        <?php include '../inc/sidebar.php'; ?>

        <main class="main-content">
            <div class="page-header">
                <h1>Add New Property</h1>
                <a href="properties.php" class="btn btn-secondary">← Back to Properties</a>
            </div>

            <div class="form-container">
                <?php if ($success): ?>
                    <div class="msg success">
                        Property added successfully! <a href="property_detail.php?id=<?= $property_id ?>">View it here</a>
                    </div>
                <?php endif; ?>

                <?php if (!empty($errors)): ?>
                    <div class="msg error">
                        <ul>
                            <?php foreach ($errors as $e): ?>
                                <li><?= htmlspecialchars($e) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <form method="POST" enctype="multipart/form-data">
                    <div class="form-grid">
                        <div class="form-group">
                            <label>Title *</label>
                            <input type="text" name="title" required placeholder="e.g. Luxurious 5-Bedroom Duplex in Lekki Phase 1">
                        </div>

                        <div class="form-group">
                            <label>Type</label>
                            <select name="type">
                                <option value="sale">For Sale</option>
                                <option value="rent">For Rent</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label>Price (₦) *</label>
                            <input type="number" name="price" required placeholder="500000000" step="1000">
                        </div>

                        <div class="form-group">
                            <label>Location *</label>
                            <input type="text" name="location" required placeholder="e.g. Lekki Phase 1, Lagos">
                        </div>

                        <div class="form-group">
                            <label>Bedrooms *</label>
                            <select name="bedrooms" required>
                                <?php for($i=1; $i<=10; $i++): ?>
                                    <option value="<?= $i ?>"><?= $i ?> Bedroom<?= $i>1?'s':'' ?></option>
                                <?php endfor; ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label>Bathrooms *</label>
                            <select name="bathrooms" required>
                                <?php for($i=1; $i<=8; $i++): ?>
                                    <option value="<?= $i ?>"><?= $i ?> Bathroom<?= $i>1?'s':'' ?></option>
                                <?php endfor; ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label>Size (sqm)</label>
                            <input type="text" name="size" placeholder="e.g. 450 sqm">
                        </div>

                        <div class="form-group">
                            <label>Status</label>
                            <select name="status">
                                <option value="active">Active</option>
                                <option value="under_offer">Under Offer</option>
                                <option value="sold">Sold/Rented</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Description *</label>
                        <div id="editor" style="height:250px;"></div>
                        <textarea name="description" id="description" style="display:none;"></textarea>
                    </div>

                    <div class="form-group">
                        <label>Upload Images (Multiple)</label>
                        <input type="file" name="images[]" multiple accept="image/*" id="imageInput">
                        <div class="image-preview" id="preview"></div>
                        <small>Max 5MB each • JPG, PNG, WebP</small>
                    </div>

                    <button type="submit" class="btn btn-primary" style="padding:1rem 3rem; font-size:1.1rem;">
                        Publish Property
                    </button>
                </form>
            </div>
        </main>
    </div>

    <!-- Quill Rich Text Editor -->
    <script src="https://cdn.jsdelivr.net/npm/quill@2.0.2/dist/quill.js"></script>
    <script>
        const quill = new Quill('#editor', {
            theme: 'snow',
            placeholder: 'Describe this beautiful property... Mention amenities, neighborhood, features...',
            modules: {
                toolbar: [
                    [{ 'header': [1, 2, false] }],
                    ['bold', 'italic', 'underline'],
                    ['link', 'image'],
                    [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                    ['clean']
                ]
            }
        });

        // Sync Quill to hidden textarea
        const form = document.querySelector('form');
        form.onsubmit = () => {
            document.getElementById('description').value = quill.root.innerHTML;
        };

        // Image Preview
        document.getElementById('imageInput').onchange = function(e) {
            const preview = document.getElementById('preview');
            preview.innerHTML = '';
            [...e.target.files].forEach(file => {
                if (file.type.startsWith('image/')) {
                    const img = document.createElement('img');
                    img.src = URL.createObjectURL(file);
                    preview.appendChild(img);
                }
            });
        };
    </script>
</body>
</html>