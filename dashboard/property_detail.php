<?php
// dashboard/property_detail.php
require '../inc/config.php';
require '../inc/auth.php';

$id = intval($_GET['id'] ?? 0);
if ($id <= 0) {
    header('Location: properties.php');
    exit;
}

// Fetch property + agent + images
$stmt = $db->prepare("
    SELECT p.*, u.name as agent_name, u.phone as agent_phone, u.email as agent_email, u.photo as agent_photo
    FROM properties p
    LEFT JOIN users u ON p.agent_id = u.id
    WHERE p.id = ?
");
$stmt->bind_param('i', $id);
$stmt->execute();
$result = $stmt->get_result();
$property = $result->fetch_assoc();
$stmt->close();

if (!$property) {
    die("<h2 style='text-align:center; padding:4rem; color:#ef4444;'>Property not found.</h2>");
}

// Fetch all images
$images = $db->query("SELECT image_path FROM property_images WHERE property_id = $id ORDER BY id ASC")->fetch_all(MYSQLI_ASSOC);
$featured = $images[0]['image_path'] ?? 'default.jpg';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($property['title']) ?> • House Unlimited</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        .property-hero {
            position: relative;
            height: 80vh;
            background: linear-gradient(rgba(0,0,0,0.4), rgba(0,0,0,0.6)), url('../assets/uploads/properties/<?= $featured ?>') center/cover no-repeat;
            color: white;
            display: flex;
            align-items: flex-end;
            padding: 4rem 2rem;
            border-radius: 24px;
            overflow: hidden;
        }
        .hero-content {
            max-width: 1200px;
            margin: 0 auto;
            width: 100%;
        }
        .property-title {
            font-size: 3.5rem;
            font-weight: 800;
            margin: 0 0 1rem;
            text-shadow: 0 4px 20px rgba(0,0,0,0.6);
        }
        .property-price {
            font-size: 2.8rem;
            font-weight: 700;
            color: #fbbf24;
            margin: 0.5rem 0;
        }
        .property-location {
            font-size: 1.5rem;
            opacity: 0.95;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .details-grid {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 3rem;
            margin: 3rem 0;
            max-width: 1200px;
            margin-left: auto;
            margin-right: auto;
        }

        .gallery {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1rem;
            margin: 2rem 0;
        }
        .gallery img {
            width: 100%;
            height: 200px;
            object-fit: cover;
            border-radius: 16px;
            cursor: pointer;
            transition: all 0.3s;
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
        }
        .gallery img:hover { transform: scale(1.05); }

        .features {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 1.5rem;
            margin: 2rem 0;
        }
        .feature-item {
            background: white;
            padding: 1.5rem;
            border-radius: 16px;
            text-align: center;
            box-shadow: 0 8px 30px rgba(0,0,0,0.08);
        }
        body.dark .feature-item { background: #1e1e1e; }
        .feature-value { font-size: 2rem; font-weight: 800; color: #1e40af; }
        body.dark .feature-value { color: #60a5fa; }

        .description {
            background: white;
            padding: 2.5rem;
            border-radius: 20px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.1);
            line-height: 1.8;
            font-size: 1.1rem;
        }
        body.dark .description { background: #1e1e1e; color: #e2e8f0; }

        .agent-card {
            background: white;
            padding: 2rem;
            border-radius: 20px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.1);
            text-align: center;
        }
        body.dark .agent-card { background: #1e1e1e; }
        .agent-photo {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            object-fit: cover;
            border: 5px solid #3b82f6;
            margin-bottom: 1rem;
        }
        .whatsapp-btn {
            background: #25d366;
            color: white;
            padding: 1rem 2rem;
            border-radius: 50px;
            text-decoration: none;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 0.8rem;
            margin-top: 1rem;
            font-size: 1.1rem;
        }
        .whatsapp-btn img { width: 24px; }

        .badge {
            padding: 0.6rem 1.2rem;
            border-radius: 50px;
            font-weight: 600;
            font-size: 0.9rem;
            display: inline-block;
            margin-right: 0.5rem;
        }
        .badge-sale { background: #dbeafe; color: #1e40af; }
        .badge-rent { background: #ccfbf1; color: #0d9488; }
        .badge-status {
            background: <?= $property['status'] === 'active' ? '#d1fae5' : ($property['status'] === 'rejected' ? '#fee2e2' : '#fef3c7') ?>;
            color: <?= $property['status'] === 'active' ? '#065f46' : ($property['status'] === 'rejected' ? '#991b1b' : '#92400e') ?>;
        }
    </style>
</head>
<body>
    <?php include '../inc/header.php'; ?>

    <div class="container">
        <?php include '../inc/sidebar.php'; ?>

        <main class="main-content">
            <!-- Hero Section -->
            <div class="property-hero">
                <div class="hero-content">
                    <div style="display:flex; gap:1rem; margin-bottom:1rem;">
                        <span class="badge badge-<?= $property['type'] ?>">
                            <?= $property['type'] === 'sale' ? 'FOR SALE' : 'FOR RENT' ?>
                        </span>
                        <span class="badge badge-status">
                            <?= strtoupper($property['status']) ?>
                        </span>
                    </div>
                    <h1 class="property-title"><?= htmlspecialchars($property['title']) ?></h1>
                    <p class="property-price">₦<?= number_format($property['price']) ?></p>
                    <p class="property-location">
                        <svg width="24" height="24" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5s1.12-2.5 2.5-2.5 2.5 1.12 2.5 2.5-1.12 2.5-2.5 2.5z"/>
                        </svg>
                        <?= htmlspecialchars($property['location']) ?>
                    </p>
                </div>
            </div>

            <div class="details-grid">
                <div>
                    <!-- Gallery -->
                    <?php if (!empty($images)): ?>
                        <h2 style="margin:2rem 0 1rem; font-size:2rem;">Gallery</h2>
                        <div class="gallery">
                            <?php foreach ($images as $img): ?>
                                <img src="../assets/uploads/properties/<?= $img['image_path'] ?>" 
                                     alt="<?= htmlspecialchars($property['title']) ?>" 
                                     onclick="openLightbox(this.src)">
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>

                    <!-- Features -->
                    <h2 style="margin:2rem 0 1rem; font-size:2rem;">Key Features</h2>
                    <div class="features">
                        <div class="feature-item">
                            <div class="feature-value"><?= $property['bedrooms'] ?></div>
                            <div>Bedrooms</div>
                        </div>
                        <div class="feature-item">
                            <div class="feature-value"><?= $property['bathrooms'] ?></div>
                            <div>Bathrooms</div>
                        </div>
                        <?php if (!empty($property['land_size'])): ?>
                        <div class="feature-item">
                            <div class="feature-value"><?= htmlspecialchars($property['land_size']) ?></div>
                            <div>Size (sqm)</div>
                        </div>
                        <?php endif; ?>
                        <div class="feature-item">
                            <div class="feature-value"><?= ucfirst($property['type']) ?></div>
                            <div>Purpose</div>
                        </div>
                    </div>

                    <!-- Description -->
                    <h2 style="margin:2rem 0 1rem; font-size:2rem;">Description</h2>
                    <div class="description">
                        <?= nl2br(htmlspecialchars($property['description'])) ?>
                    </div>

                    <div style="margin:3rem 0; text-align:center;">
                        <a href="https://wa.me/234<?= preg_replace('/\D/', '', $property['agent_phone'] ?? '8030000000') ?>?text=Hi,%20I'm%20interested%20in%20*<?= urlencode($property['title']) ?>*%20in%20<?= urlencode($property['location']) ?>%20(₦<?= number_format($property['price']) ?>)%0A%0ACan%20we%20schedule%20a%20viewing?"
                           target="_blank" class="whatsapp-btn">
                            <img src="../assets/img/whatsapp.png" alt="WhatsApp"> Chat Agent on WhatsApp
                        </a>
                    </div>
                </div>

                <!-- Agent Sidebar -->
                <div>
                    <div class="agent-card">
                        <h3 style="margin:0 0 1.5rem; font-size:1.8rem;">Listing Agent</h3>
                        <img src="../assets/uploads/avatars/<?= $property['agent_photo'] ?: 'default.png' ?>" 
                             class="agent-photo" alt="<?= htmlspecialchars($property['agent_name']) ?>">
                        <h3><?= htmlspecialchars($property['agent_name']) ?></h3>
                        <p style="color:#64748b; margin:0.5rem 0;">Verified Agent</p>
                        <p><strong>Phone:</strong> <?= $property['agent_phone'] ?: 'Not provided' ?></p>
                        <p><strong>Email:</strong> <?= $property['agent_email'] ?: 'Not provided' ?></p>

                        <a href="mailto:<?= $property['agent_email'] ?>" class="btn btn-primary" style="margin-top:1rem; width:100%;">
                            Send Email
                        </a>
                    </div>

                    <?php if (in_array($_SESSION['user']['role'], ['admin', 'agent']) && $_SESSION['user']['id'] == $property['agent_id']): ?>
                        <div class="agent-card" style="margin-top:2rem;">
                            <h3>Agent Actions</h3>
                            <a href="edit_property.php?id=<?= $id ?>" class="btn btn-primary" style="width:100%; margin:0.5rem 0;">
                                Edit Property
                            </a>
                            <form method="POST" action="delete_property.php" onsubmit="return confirm('Delete this property permanently?')">
                                <input type="hidden" name="id" value="<?= $id ?>">
                                <button type="submit" class="btn btn-danger" style="width:100%; margin:0.5rem 0;">
                                    Delete Property
                                </button>
                            </form>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>

    <!-- Lightbox -->
    <div id="lightbox" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.9); z-index:9999; justify-content:center; align-items:center; cursor:pointer;" onclick="this.style.display='none'">
        <img id="lightboxImg" style="max-width:90%; max-height:90%; border-radius:16px;">
    </div>

    <script>
        function openLightbox(src) {
            document.getElementById('lightbox').style.display = 'flex';
            document.getElementById('lightboxImg').src = src;
        }

        // Auto format price
        document.querySelectorAll('.property-price').forEach(el => {
            const num = parseInt(el.textContent.replace(/[^0-9]/g, ''));
            el.textContent = '₦' + num.toLocaleString();
        });
    </script>
</body>
</html>