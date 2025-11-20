<?php
// dashboard/properties.php — ULTRA PREMIUM 2025 DESIGN
require '../inc/config.php';
require '../inc/auth.php';

$user = $_SESSION['user'];
$user_id = $user['id'];
$role = $user['role'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Properties • House Unlimited</title>
    <link rel="stylesheet" href="../assets/css/style.css" />
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Playfair+Display:wght@700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"/>
    <style>
        :root {
            --primary: #1e40af;
            --primary-light: #3b82f6;
            --success: #10b981;
            --warning: #f59e0b;
            --danger: #ef4444;
            --dark: #0f172a;
            --gray-100: #f8fafc;
            --gray-200: #e2e8f0;
            --gray-600: #64748b;
            --gray-800: #1e293b;
            --radius: 20px;
            --shadow: 0 20px 40px rgba(0,0,0,0.08);
            --transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        }
        body.dark {
            --primary: #60a5fa;
            --primary-light: #93c5fd;
            --gray-100: #1e1e1e;
            --gray-200: #334155;
            --gray-600: #94a3b8;
            --gray-800: #e2e8f0;
        }

        .page-header h1 {
            font-family: 'Playfair Display', serif;
            font-size: 3rem;
            background: linear-gradient(135deg, var(--primary), var(--primary-light));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin: 0;
        }

        /* === ULTRA MODERN PROPERTY CARD === */
        .property-card {
            background: white;
            border-radius: var(--radius);
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0,0,0,0.06);
            transition: var(--transition);
            position: relative;
            border: 1px solid transparent;
        }
        body.dark .property-card { 
            background: #1e1e1e; 
            box-shadow: 0 10px 30px rgba(0,0,0,0.3);
        }
        .property-card::before {
            content: '';
            position: absolute;
            top: 0; left: 0; right: 0; bottom: 0;
            background: linear-gradient(135deg, rgba(59,130,246,0.1), transparent 50%);
            opacity: 0;
            transition: var(--transition);
            z-index: 1;
            border-radius: var(--radius);
        }
        .property-card:hover::before { opacity: 1; }
        .property-card:hover {
            transform: translateY(-12px) scale(1.02);
            box-shadow: var(--shadow);
            border-color: var(--primary-light);
        }

        .property-img-wrapper {
            position: relative;
            overflow: hidden;
            height: 280px;
        }
        .property-img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.8s ease;
        }
        .property-card:hover .property-img {
            transform: scale(1.12);
        }

        .badge {
            position: absolute;
            top: 16px;
            padding: 0.5rem 1rem;
            border-radius: 50px;
            font-size: 0.85rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            z-index: 2;
        }
        .badge-sale { 
            background: var(--success); 
            color: white; 
            left: 16px; 
        }
        .badge-rent { 
            background: var(--warning); 
            color: white; 
            left: 16px; 
        }
        .badge-featured {
            background: linear-gradient(45deg, #ffd700, #ffb800);
            color: #000;
            right: 16px;
            font-size: 0.75rem;
            padding: 0.4rem 0.8rem;
        }

        .property-info {
            padding: 1.8rem;
            position: relative;
            z-index: 2;
        }
        .property-title {
            font-family: 'Playfair Display', serif;
            font-size: 1.6rem;
            margin: 0 0 0.7rem;
            color: #1e293b;
            line-height: 1.3;
        }
        body.dark .property-title { color: #f1f5f9; }

        .property-location {
            color: var(--primary);
            font-weight: 600;
            font-size: 1rem;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        body.dark .property-location { color: #93c5fd; }

        .property-price {
            font-size: 1.9rem;
            font-weight: 800;
            color: var(--primary);
            margin: 0.8rem 0;
            font-family: 'Inter', sans-serif;
        }
        body.dark .property-price { color: #60a5fa; }

        .property-features {
            display: flex;
            gap: 1.2rem;
            margin: 1.2rem 0;
            flex-wrap: wrap;
        }
        .feature-item {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            color: var(--gray-600);
            font-size: 0.95rem;
        }
        .feature-item i { color: var(--primary); }

        .property-actions {
            display: flex;
            gap: 1rem;
            margin-top: 1.5rem;
        }
        .btn {
            padding: 0.9rem 1.5rem;
            border-radius: 50px;
            font-weight: 600;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            transition: var(--transition);
            font-size: 0.95rem;
        }
        .btn-primary {
            background: var(--primary-light);
            color: white;
        }
        .btn-primary:hover {
            background: #2563eb;
            transform: translateY(-2px);
        }
        .btn-whatsapp {
            background: #25d366;
            color: white;
        }
        .btn-whatsapp:hover {
            background: #128c7e;
            transform: translateY(-2px);
        }

        .card-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(360px, 1fr));
            gap: 2rem;
        }

        .no-results {
            grid-column: 1 / -1;
            text-align: center;
            padding: 6rem 2rem;
            color: var(--gray-600);
            font-size: 1.4rem;
        }
        .no-results i {
            font-size: 4rem;
            color: var(--gray-200);
            margin-bottom: 1rem;
        }
    </style>
</head>
<body>
    <?php include '../inc/header.php'; ?>
    <div class="container">
        <?php include '../inc/sidebar.php'; ?>

        <main class="main-content">
            <div class="page-header">
                <h1>Properties</h1>
                <div style="display:flex; gap:1rem; align-items:center;">
                    <?php if (in_array($role, ['agent', 'admin'])): ?>
                        <a href="add_property.php" class="btn btn-primary">
                            <i class="fas fa-plus"></i> Add Property
                        </a>
                    <?php endif; ?>
                </div>
            </div>

            <div id="propertiesContainer" class="card-grid">
                <div class="no-results">
                    <i class="fas fa-home"></i>
                    <p>Loading luxury properties...</p>
                </div>
            </div>
        </main>
    </div>

    <script>
        async function loadProperties() {
            const params = new URLSearchParams({
                my_listings: <?= $role === 'agent' ? '1' : '0' ?>
            });

            const res = await fetch(`../api/properties.php?${params}`);
            const data = await res.json();

            let html = '';
            if (data.properties.length === 0) {
                html = `<div class="no-results">
                    <i class="fas fa-search"></i>
                    <h3>No properties found</h3>
                    <p>Try adjusting your filters or add a new listing.</p>
                </div>`;
            } else {
                data.properties.forEach(p => {
                    const badge = p.type === 'sale' 
                        ? '<span class="badge badge-sale">For Sale</span>' 
                        : '<span class="badge badge-rent">For Rent</span>';
                    const featured = p.featured ? '<span class="badge badge-featured">Featured</span>' : '';

                    html += `
                    <div class="property-card">
                        <div class="property-img-wrapper">
                            <img src="../assets/uploads/properties/${p.featured_image || 'default.jpg'}" 
                                 alt="${p.title}" class="property-img">
                            ${badge}
                            ${featured}
                        </div>
                        <div class="property-info">
                            <h3 class="property-title">${p.title}</h3>
                            <div class="property-location">
                                <i class="fas fa-map-marker-alt"></i> ${p.location}
                            </div>
                            <div class="property-price">₦${Number(p.price).toLocaleString()}</div>
                            
                            <div class="property-features">
                                ${p.bedrooms ? `<div class="feature-item"><i class="fas fa-bed"></i> ${p.bedrooms} ${p.bedrooms > 1 ? 'Beds' : 'Bed'}</div>` : ''}
                                ${p.bathrooms ? `<div class="feature-item"><i class="fas fa-bath"></i> ${p.bathrooms} Bath</div>` : ''}
                                ${p.land_size ? `<div class="feature-item"><i class="fas fa-ruler-combined"></i> ${p.land_size} sqm</div>` : ''}
                            </div>

                            <div class="property-actions">
                                <a href="property_detail.php?id=${p.id}" class="btn btn-primary">
                                    <i class="fas fa-eye"></i> View Details
                                </a>
                                <a href="https://wa.me/2348030000000?text=Hi,%20I'm%20interested%20in%20${encodeURIComponent(p.title + ' in ' + p.location)}" 
                                   target="_blank" class="btn btn-whatsapp">
                                    <i class="fab fa-whatsapp"></i> WhatsApp
                                </a>
                            </div>
                        </div>
                    </div>`;
                });
            }
            document.getElementById('propertiesContainer').innerHTML = html;
        }

        // Load on page load
        loadProperties();
    </script>
</body>
</html>