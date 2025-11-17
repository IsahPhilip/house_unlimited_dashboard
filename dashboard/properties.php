<?php
// dashboard/properties.php
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
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
            flex-wrap: wrap;
            gap: 1rem;
        }
        .page-header h1 { margin: 0; font-size: 2.2rem; color: #1e40af; }
        body.dark .page-header h1 { color: #93c5fd; }

        .filters {
            background: white;
            padding: 1.5rem;
            border-radius: 16px;
            box-shadow: 0 8px 30px rgba(0,0,0,0.08);
            margin-bottom: 2rem;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
        }
        body.dark .filters { background: #1e1e1e; }

        .filters input, .filters select {
            padding: 0.9rem 1rem;
            border: 2px solid #e2e8f0;
            border-radius: 12px;
            font-size: 1rem;
            width: 100%;
        }
        .filters input:focus, .filters select:focus {
            outline: none;
            border-color: #3b82f6;
            box-shadow: 0 0 0 4px rgba(59,130,246,0.15);
        }

        .view-toggle {
            display: flex;
            gap: 0.5rem;
        }
        .view-btn {
            padding: 0.6rem 1rem;
            background: #f1f5f9;
            border: none;
            border-radius: 8px;
            cursor: pointer;
        }
        .view-btn.active { background: #3b82f6; color: white; }

        .property-card {
            position: relative;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 8px 30px rgba(0,0,0,0.1);
            transition: all 0.3s;
        }
        .property-card:hover { transform: translateY(-10px); box-shadow: 0 20px 40px rgba(0,0,0,0.15); }

        .property-img {
            width: 100%;
            height: 240px;
            object-fit: cover;
        }
        .property-badge {
            position: absolute;
            top: 15px;
            right: 15px;
            background: #ef4444;
            color: white;
            padding: 0.4rem 0.8rem;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
        }
        .for-sale { background: #10b981; }
        .under-offer { background: #f59e0b; }

        .property-info {
            padding: 1.5rem;
            background: white;
        }
        body.dark .property-info { background: #1e1e1e; }

        .property-title {
            font-size: 1.4rem;
            margin: 0 0 0.5rem;
            color: #1e293b;
        }
        body.dark .property-title { color: #e2e8f0; }

        .property-price {
            font-size: 1.6rem;
            font-weight: 700;
            color: #1e40af;
            margin: 0.5rem 0;
        }
        body.dark .property-price { color: #60a5fa; }

        .property-meta {
            display: flex;
            gap: 1rem;
            color: #64748b;
            font-size: 0.95rem;
            margin: 1rem 0;
        }

        .property-actions {
            display: flex;
            gap: 0.8rem;
            margin-top: 1rem;
        }

        .whatsapp-btn {
            background: #25d366;
            color: white;
            padding: 0.7rem 1rem;
            border-radius: 50px;
            text-decoration: none;
            font-size: 0.9rem;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }
        .whatsapp-btn img { width: 18px; }

        .no-results {
            text-align: center;
            padding: 4rem 2rem;
            color: #64748b;
            font-size: 1.2rem;
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
                        <a href="add_property.php" class="btn btn-primary">+ Add Property</a>
                    <?php endif; ?>
                    <div class="view-toggle">
                        <button class="view-btn active" id="gridView">Grid</button>
                        <button class="view-btn" id="listView">List</button>
                    </div>
                </div>
            </div>

            <!-- Filters -->
            <div class="filters">
                <input type="text" id="search" placeholder="Search location, title..." />
                <select id="type">
                    <option value="">All Types</option>
                    <option value="sale">For Sale</option>
                    <option value="rent">For Rent</option>
                </select>
                <input type="number" id="minPrice" placeholder="Min Price (₦)" />
                <input type="number" id="maxPrice" placeholder="Max Price (₦)" />
                <select id="bedrooms">
                    <option value="">Any Bedrooms</option>
                    <option value="1">1 Bedroom</option>
                    <option value="2">2 Bedrooms</option>
                    <option value="3">3 Bedrooms</option>
                    <option value="4">4 Bedrooms</option>
                    <option value="5">5+ Bedrooms</option>
                </select>
                <button onclick="loadProperties(1)" class="btn btn-primary" style="height:50px;">Search</button>
            </div>

            <!-- Properties Grid -->
            <div id="propertiesContainer" class="card-grid">
                <div class="no-results">Loading properties...</div>
            </div>
        </main>
    </div>

    <script src="../assets/js/main.js"></script>
    <script>
        let currentView = 'grid';

        document.getElementById('gridView').onclick = () => {
            document.querySelector('.card-grid').style.gridTemplateColumns = 'repeat(auto-fill, minmax(320px, 1fr))';
            document.getElementById('gridView').classList.add('active');
            document.getElementById('listView').classList.remove('active');
        };
        document.getElementById('listView').onclick = () => {
            document.querySelector('.card-grid').style.gridTemplateColumns = '1fr';
            document.getElementById('listView').classList.add('active');
            document.getElementById('gridView').classList.remove('active');
        };

        async function loadProperties(page = 1) {
            const params = new URLSearchParams({
                page,
                search: document.getElementById('search').value,
                type: document.getElementById('type').value,
                minPrice: document.getElementById('minPrice').value,
                maxPrice: document.getElementById('maxPrice').value,
                bedrooms: document.getElementById('bedrooms').value,
                my_listings: <?= $role === 'agent' ? '1' : '0' ?>
            });

            const res = await fetch(`../api/properties.php?${params}`);
            const data = await res.json();

            let html = '';
            if (data.properties.length === 0) {
                html = '<div class="no-results"><p>No properties found matching your criteria.</p></div>';
            } else {
                data.properties.forEach(p => {
                    const badgeClass = p.type === 'sale' ? 'for-sale' : '';
                    const badgeText = p.type === 'sale' ? 'FOR SALE' : 'FOR RENT';
                    const statusBadge = p.status === 'under_offer' ? '<span class="property-badge under-offer">Under Offer</span>' : '';

                    html += `
                    <div class="card property-card">
                        <img src="../assets/uploads/properties/${p.featured_image || 'default.jpg'}" alt="${p.title}" class="property-img">
                        <span class="property-badge ${badgeClass}">${badgeText}</span>
                        ${statusBadge}
                        <div class="property-info">
                            <h3 class="property-title">${p.title}</h3>
                            <p class="property-price">₦${Number(p.price).toLocaleString()}</p>
                            <div class="property-meta">
                                <span>${p.location}</span> • 
                                <span>${p.bedrooms} ${p.bedrooms > 1 ? 'beds' : 'bed'}</span> • 
                                <span>${p.bathrooms} bath</span>
                            </div>
                            <div class="property-actions">
                                <a href="property_detail.php?id=${p.id}" class="btn btn-primary btn-sm">View Details</a>
                                <a href="https://wa.me/2348030000000?text=Hi,%20I'm%20interested%20in%20${encodeURIComponent(p.title + ' in ' + p.location)}" 
                                   target="_blank" class="whatsapp-btn">
                                    <img src="../assets/img/whatsapp.png" alt="WhatsApp"> Chat Agent
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

        // Auto-refresh search on input change (debounced)
        let searchTimeout;
        document.querySelectorAll('#search, #type, #minPrice, #maxPrice, #bedrooms').forEach(el => {
            el.addEventListener('input', () => {
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(() => loadProperties(1), 600);
            });
        });
    </script>
</body>
</html>