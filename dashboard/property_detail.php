<?php
// dashboard/property_detail.php — ULTRA LUXURY 2025 EDITION
require '../inc/config.php';
require '../inc/auth.php';

$id = intval($_GET['id'] ?? 0);
if ($id <= 0) { header('Location: properties.php'); exit; }

// Fetch property + agent
$stmt = $db->prepare("
    SELECT p.*, u.name as agent_name, u.phone as agent_phone, u.email as agent_email, u.photo as agent_photo
    FROM properties p
    LEFT JOIN users u ON p.agent_id = u.id
    WHERE p.id = ?
");
$stmt->bind_param('i', $id);
$stmt->execute();
$property = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$property) {
    die("<div style='text-align:center;padding:8rem;font-size:2rem;color:#ef4444;'>Property not found.</div>");
}

// Fetch all images
$images = $db->query("SELECT image_path FROM property_images WHERE property_id = $id ORDER BY is_featured DESC, id ASC")
    ->fetch_all(MYSQLI_ASSOC);
$featured = $images[0]['image_path'] ?? 'default.jpg';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($property['title']) ?> • House Unlimited</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Playfair+Display:wght@700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css"/>
    <style>
        :root {
            --primary: #1e40af;
            --primary-light: #3b82f6;
            --accent: #10b981;
            --gold: #fbbf24;
            --dark: #0f172a;
            --light: #f8fafc;
            --radius: 24px;
            --shadow: 0 25px 50px rgba(0,0,0,0.12);
            --transition: all 0.5s cubic-bezier(0.4, 0, 0.2, 1);
        }
        body.dark {
            --light: #1e1e1e;
            --primary: #60a5fa;
            --primary-light: #93c5fd;
        }

        /* HERO */
        .hero {
            position: relative;
            height: 90vh;
            min-height: 600px;
            background: linear-gradient(135deg, rgba(15,23,42,0.85), rgba(30,41,59,0.7)), 
                        url('../assets/uploads/properties/<?= $featured ?>') center/cover no-repeat;
            color: white;
            display: flex;
            align-items: flex-end;
            border-radius: var(--radius);
            overflow: hidden;
            margin: 2rem 0;
        }
        .hero::before {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(transparent 40%, rgba(0,0,0,0.9));
        }
        .hero-content {
            position: relative;
            z-index: 2;
            max-width: 1400px;
            margin: 0 auto;
            width: 100%;
            padding: 4rem 2rem;
        }
        .hero h1 {
            font-family: 'Playfair Display', serif;
            font-size: clamp(3rem, 8vw, 6rem);
            font-weight: 900;
            margin: 0 0 1rem;
            line-height: 1.1;
            text-shadow: 0 10px 30px rgba(0,0,0,0.6);
        }
        .hero .price {
            font-size: clamp(2.5rem, 6vw, 4.5rem);
            font-weight: 800;
            color: var(--gold);
            margin: 1rem 0;
            text-shadow: 0 4px 20px rgba(0,0,0,0.5);
        }
        .hero .location {
            font-size: 1.6rem;
            display: flex;
            align-items: center;
            gap: 1rem;
            opacity: 0.95;
        }

        .badges {
            display: flex;
            gap: 1rem;
            flex-wrap: wrap;
            margin-bottom: 1.5rem;
        }
        .badge {
            padding: 0.8rem 1.8rem;
            border-radius: 50px;
            font-weight: 700;
            font-size: 1rem;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .badge-sale { background: #10b981; color: white; }
        .badge-rent { background: #f59e0b; color: white; }
        .badge-status { background: #1e293b; color: #93c5fd; }

        /* MAIN GRID */
        .main-grid {
            display: grid;
            grid-template-columns: 1fr 380px;
            gap: 3rem;
            max-width: 1400px;
            margin: 0 auto;
            padding: 2rem;
        }
        @media (max-width: 1024px) {
            .main-grid { grid-template-columns: 1fr; }
        }

        /* GALLERY */
        .gallery {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 1.5rem;
            margin: 3rem 0;
        }
        .gallery img {
            width: 100%;
            height: 240px;
            object-fit: cover;
            border-radius: 20px;
            cursor: zoom-in;
            transition: var(--transition);
            box-shadow: 0 15px 35px rgba(0,0,0,0.15);
        }
        .gallery img:hover {
            transform: translateY(-8px) scale(1.02);
            box-shadow: 0 25px 60px rgba(0,0,0,0.25);
        }

        /* FEATURES */
        .features-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
            gap: 1.8rem;
            margin: 3rem 0;
        }
        .feature-card {
            background: white;
            padding: 2rem;
            border-radius: 20px;
            text-align: center;
            box-shadow: 0 15px 40px rgba(0,0,0,0.08);
            transition: var(--transition);
        }
        body.dark .feature-card { background: #1e1e1e; }
        .feature-card:hover { transform: translateY(-10px); }
        .feature-icon {
            font-size: 3rem;
            color: var(--primary-light);
            margin-bottom: 1rem;
        }
        .feature-value {
            font-size: 2.2rem;
            font-weight: 800;
            color: var(--primary);
            margin: 0.5rem 0;
        }
        body.dark .feature-value { color: #93c5fd; }

        /* DESCRIPTION */
        .description-box {
            background: white;
            padding: 3rem;
            border-radius: 24px;
            box-shadow: var(--shadow);
            line-height: 2;
            font-size: 1.15rem;
            color: #374151;
        }
        body.dark .description-box { background: #1e1e1e; color: #e2e8f0; }

        /* AGENT CARD */
        .agent-card {
            background: white;
            border-radius: 24px;
            padding: 2.5rem;
            box-shadow: var(--shadow);
            text-align: center;
            position: sticky;
            top: 2rem;
        }
        body.dark .agent-card { background: #1e1e1e; }
        .agent-photo {
            width: 140px;
            height: 140px;
            border-radius: 50%;
            object-fit: cover;
            border: 6px solid var(--primary-light);
            margin: 0 auto 1.5rem;
            box-shadow: 0 15px 40px rgba(59,130,246,0.3);
        }
        .agent-name {
            font-family: 'Playfair Display', serif;
            font-size: 1.8rem;
            margin: 0 0 0.5rem;
        }
        .btn-whatsapp {
            background: linear-gradient(135deg, #25d366, #128c7e);
            color: white;
            padding: 1.2rem 2rem;
            border-radius: 50px;
            font-weight: 700;
            font-size: 1.1rem;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 1rem;
            margin: 1.5rem auto 0;
            width: 100%;
            box-shadow: 0 10px 30px rgba(37,211,102,0.4);
            transition: var(--transition);
        }
        .btn-whatsapp:hover {
            transform: translateY(-4px);
            box-shadow: 0 20px 40px rgba(37,211,102,0.5);
        }

        /* LIGHTBOX */
        #lightbox {
            display: none;
            position: fixed;
            top: 0; left: 0; right: 0; bottom: 0;
            background: rgba(0,0,0,0.95);
            z-index: 9999;
            justify-content: center;
            align-items: center;
            cursor: zoom-out;
            backdrop-filter: blur(10px);
        }
        #lightbox img {
            max-width: 95%;
            max-height: 95%;
            border-radius: 20px;
            box-shadow: 0 30px 80px rgba(0,0,0,0.6);
        }
    </style>
</head>
<body>
    <?php include '../inc/header.php'; ?>
    <div class="container">
        <?php include '../inc/sidebar.php'; ?>

        <main class="main-content">

            <!-- HERO -->
            <div class="hero">
                <div class="hero-content">
                    <div class="badges">
                        <span class="badge badge-<?= $property['type'] ?>">
                            <?= $property['type'] === 'sale' ? 'FOR SALE' : 'FOR RENT' ?>
                        </span>
                        <span class="badge badge-status">
                            <?= strtoupper($property['status']) ?>
                        </span>
                    </div>
                    <h1><?= htmlspecialchars($property['title']) ?></h1>
                    <div class="price">₦<?= number_format($property['price']) ?></div>
                    <div class="location">
                        <i class="fas fa-map-marker-alt"></i>
                        <?= htmlspecialchars($property['location']) ?>, <?= htmlspecialchars($property['state']) ?>
                    </div>
                </div>
            </div>

            <div class="main-grid">

                <!-- LEFT COLUMN -->
                <div>
                    <!-- GALLERY -->
                    <?php if ($images): ?>
                        <h2 style="font-size:2.5rem; margin:3rem 0 2rem; font-family:'Playfair Display',serif;">
                            Gallery
                        </h2>
                        <div class="gallery">
                            <?php foreach ($images as $img): ?>
                                <img src="../assets/uploads/properties/<?= $img['image_path'] ?>" 
                                     alt="Property Image" 
                                     onclick="document.getElementById('lightbox').style.display='flex'; 
                                              document.getElementById('lightboxImg').src=this.src">
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>

                    <!-- FEATURES -->
                    <h2 style="font-size:2.5rem; margin:4rem 0 2rem; font-family:'Playfair Display',serif;">
                        Key Features
                    </h2>
                    <div class="features-grid">
                        <?php if ($property['bedrooms']): ?>
                            <div class="feature-card"><div class="feature-icon">Bed</div><div class="feature-value"><?= $property['bedrooms'] ?></div><div>Bedrooms</div></div>
                        <?php endif; ?>
                        <?php if ($property['bathrooms']): ?>
                            <div class="feature-card"><div class="feature-icon">Bath</div><div class="feature-value"><?= $property['bathrooms'] ?></div><div>Bathrooms</div></div>
                        <?php endif; ?>
                        <?php if ($property['toilets']): ?>
                            <div class="feature-card"><div class="feature-icon">Toilet</div><div class="feature-value"><?= $property['toilets'] ?></div><div>Toilets</div></div>
                        <?php endif; ?>
                        <?php if ($property['land_size']): ?>
                            <div class="feature-card"><div class="feature-icon">Ruler</div><div class="feature-value"><?= $property['land_size'] ?></div><div>Land Size (sqm)</div></div>
                        <?php endif; ?>
                    </div>

                    <!-- DESCRIPTION -->
                    <h2 style="font-size:2.5rem; margin:4rem 0 2rem; font-family:'Playfair Display',serif;">
                        Description
                    </h2>
                    <div class="description-box">
                        <?= nl2br(htmlspecialchars($property['description'])) ?>
                    </div>

                    <div style="text-align:center; margin:4rem 0;">
                        <a href="https://wa.me/234<?= preg_replace('/\D/', '', $property['agent_phone'] ?? '8030000000') ?>?text=Hi%20<?= urlencode($property['agent_name']) ?>,%20I'm%20interested%20in%20*<?= urlencode($property['title']) ?>*%20in%20<?= urlencode($property['location']) ?>%20(₦<?= number_format($property['price']) ?>)%0A%0ACan%20we%20schedule%2086a%20viewing?"
                           target="_blank" class="btn-whatsapp">
                            <i class="fab fa-whatsapp fa-2x"></i>
                            Chat Agent on WhatsApp
                        </a>
                    </div>
                </div>

                <!-- AGENT SIDEBAR -->
                <div class="agent-card">
                    <h3 style="font-size:1.8rem; margin-bottom:2rem;">Listing Agent</h3>
                    <img src="../assets/uploads/avatars/<?= $property['agent_photo'] ?: 'default.png' ?>" 
                         class="agent-photo" alt="<?= htmlspecialchars($property['agent_name']) ?>">
                    <h3 class="agent-name"><?= htmlspecialchars($property['agent_name']) ?></h3>
                    <p style="color:#64748b; margin:1rem 0;">Verified Real Estate Professional</p>
                    <p><strong>Phone:</strong> <?= $property['agent_phone'] ?: 'Not listed' ?></p>
                    <p><strong>Email:</strong> <?= $property['agent_email'] ?: 'Not listed' ?></p>

                    <?php if ($_SESSION['user']['role'] === 'client'): ?>
                        <div id="paymentContainer" style="margin-top: 2rem; border-top: 1px solid #e2e8f0; padding-top: 2rem;">
                            <h3 style="font-size: 1.5rem; margin-bottom: 1rem;">Ready to Own?</h3>
                            <p>Secure this property by making a down payment or full payment today.</p>
                            <form id="paymentForm">
                                <input type="hidden" name="property_id" value="<?= $id ?>">
                                <input type="hidden" name="amount" value="<?= $property['price'] ?>">
                                <button type="submit" class="btn-whatsapp" style="background: var(--primary); box-shadow: 0 10px 30px rgba(30,64,175,0.4);">
                                    <i class="fas fa-credit-card"></i>
                                    Make Payment (₦<?= number_format($property['price']) ?>)
                                </button>
                            </form>
                            <div id="payment-message" style="margin-top: 1rem; color: red;"></div>
                        </div>
                    <?php endif; ?>

                    <?php if (in_array($_SESSION['user']['role'], ['admin','agent']) && $_SESSION['user']['id'] == $property['agent_id']): ?>
                        <div style="margin-top:2rem; padding-top:2rem; border-top:2px solid #e2e8f0;">
                            <a href="edit_property.php?id=<?= $id ?>" class="btn btn-primary" style="width:100%; margin:0.5rem 0; padding:1rem;">
                                Edit Property
                            </a>
                            <form method="POST" action="delete_property.php" onsubmit="return confirm('Permanently delete this property?')">
                                <input type="hidden" name="id" value="<?= $id ?>">
                                <button type="submit" style="width:100%; padding:1rem; background:#ef4444; color:white; border:none; border-radius:50px; font-weight:700; cursor:pointer;">
                                    Delete Property
                                </button>
                            </form>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>

    <!-- LIGHTBOX -->
    <div id="lightbox" onclick="this.style.display='none'">
        <img id="lightboxImg" src="" alt="Full View">
    </div>

</body>
</html>