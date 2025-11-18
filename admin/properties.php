<?php
// admin/properties.php - FULLY FIXED & BEAUTIFUL
require '../inc/config.php';
require '../inc/auth.php';

if ($_SESSION['user']['role'] !== 'admin') {
    header('Location: ../dashboard/');
    exit;
}

// Handle actions: approve, reject, delete
if ($_POST['action'] ?? '' && $_POST['property_id'] ?? 0) {
    $id = (int)$_POST['property_id'];
    $action = $_POST['action'];

    if (in_array($action, ['approve', 'reject', 'delete'])) {
        if ($action === 'delete') {
            $db->query("DELETE FROM property_images WHERE property_id = $id");
            $db->query("DELETE FROM properties WHERE id = $id");
        } else {
            $status = $action === 'approve' ? 'active' : 'rejected';
            $db->query("UPDATE properties SET status = '$status' WHERE id = $id");
        }
        log_activity("Admin $action" . "d property ID #$id");
        header('Location: properties.php?success=1');
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin • All Properties • House Unlimited</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .admin-header {
            background: linear-gradient(135deg, #1e40af, #1e3a8a);
            color: white;
            padding: 2.5rem;
            border-radius: 20px;
            margin-bottom: 2.5rem;
            text-align: center;
        }
        .admin-header h1 { margin: 0 0 0.5rem; font-size: 2.8rem; font-weight: 800; }
        .admin-header p { margin: 0; opacity: 0.9; font-size: 1.2rem; }

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

        .table-container {
            background: white;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 10px 35px rgba(0,0,0,0.1);
        }
        body.dark .table-container { background: #1e1e1e; }

        table { width: 100%; border-collapse: collapse; }
        th {
            background: #f8f9fc;
            padding: 1.3rem 1rem;
            font-weight: 600;
            color: #475569;
            text-transform: uppercase;
            font-size: 0.9rem;
            text-align: left;
        }
        body.dark th { background: #334155; color: #cbd5e1; }

        td {
            padding: 1.2rem 1rem;
            vertical-align: middle;
            border-bottom: 1px solid #f1f5f9;
        }
        body.dark td { border-color: #334155; }

        .property-thumb {
            width: 70px;
            height: 70px;
            object-fit: cover;
            border-radius: 12px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
        }

        .status {
            padding: 0.5rem 1rem;
            border-radius: 50px;
            font-size: 0.85rem;
            font-weight: 600;
            text-transform: uppercase;
        }
        .status.active { background: #d1fae5; color: #065f46; }
        .status.pending { background: #fef3c7; color: #92400e; }
        .status.rejected { background: #fee2e2; color: #991b1b; }
        .status.sale { background: #dbeafe; color: #1e40af; }
        .status.rent { background: #f0fdfa; color: #0d9488; }

        .action-btn {
            padding: 0.6rem 1rem;
            margin: 0 0.3rem;
            border: none;
            border-radius: 10px;
            cursor: pointer;
            font-size: 0.9rem;
            font-weight: 600;
            transition: all 0.2s;
        }
        .btn-view { background: #3b82f6; color: white; }
        .btn-approve { background: #10b981; color: white; }
        .btn-reject { background: #f59e0b; color: white; }
        .btn-delete { background: #ef4444; color: white; }
        .action-btn:hover { transform: translateY(-2px); box-shadow: 0 5px 15px rgba(0,0,0,0.2); }

        .no-properties {
            text-align: center;
            padding: 5rem 2rem;
            color: #64748b;
            font-size: 1.3rem;
        }
    </style>
</head>
<body>
    <?php include '../inc/header.php'; ?>

    <div class="container">
        <?php include '../inc/sidebar.php'; ?>

        <main class="main-content">
            <div class="admin-header">
                <h1>All Properties</h1>
                <p>Full control over every listing in House Unlimited Nigeria</p>
            </div>

            <?php if (isset($_GET['success'])): ?>
                <div style="background:#d1fae5; color:#065f46; padding:1.2rem; border-radius:12px; margin-bottom:1.5rem; text-align:center; font-weight:600;">
                    Property action completed successfully!
                </div>
            <?php endif; ?>

            <div class="filters">
                <input type="text" id="search" placeholder="Search title, location, agent..." oninput="filterTable()" />
                <select id="statusFilter" onchange="filterTable()">
                    <option value="">All Status</option>
                    <option value="active">Active</option>
                    <option value="pending">Pending</option>
                    <option value="rejected">Rejected</option>
                </select>
                <select id="typeFilter" onchange="filterTable()">
                    <option value="">All Types</option>
                    <option value="sale">For Sale</option>
                    <option value="rent">For Rent</option>
                </select>
            </div>

            <div class="table-container">
                <table id="propertiesTable">
                    <thead>
                        <tr>
                            <th>Image</th>
                            <th>Title</th>
                            <th>Location</th>
                            <th>Price</th>
                            <th>Agent</th>
                            <th>Type</th>
                            <th>Status</th>
                            <th>Added</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="propertiesBody">
                        <tr><td colspan="9" class="no-properties">Loading properties...</td></tr>
                    </tbody>
                </table>
            </div>
        </main>
    </div>

    <script>
        async function loadProperties() {
            try {
                const res = await fetch('../api/admin_properties.php');
                if (!res.ok) throw new Error('Failed to load');
                const properties = await res.json();

                const tbody = document.getElementById('propertiesBody');
                if (!Array.isArray(properties) || properties.length === 0) {
                    tbody.innerHTML = `<tr><td colspan="9" class="no-properties">No properties found.</td></tr>`;
                    return;
                }

                let html = '';
                properties.forEach(p => {
                    const date = new Date(p.created_at).toLocaleDateString('en-NG', {
                        day: 'numeric', month: 'short', year: 'numeric'
                    });

                    html += `
                    <tr data-status="${p.status}" data-type="${p.type}">
                        <td><img src="../assets/uploads/properties/${p.featured_image}" class="property-thumb" alt="${p.title}"></td>
                        <td><strong>${p.title}</strong></td>
                        <td>${p.location}</td>
                        <td><strong>₦${Number(p.price).toLocaleString()}</strong></td>
                        <td>${p.agent_name || '—'}</td>
                        <td><span class="status ${p.type}">${p.type === 'sale' ? 'FOR SALE' : 'FOR RENT'}</span></td>
                        <td><span class="status ${p.status}">${p.status.toUpperCase()}</span></td>
                        <td>${date}</td>
                        <td>
                            <a href="../dashboard/property_detail.php?id=${p.id}" target="_blank" class="action-btn btn-view">View</a>
                            ${p.status === 'pending' ? `
                            <form method="POST" style="display:inline;">
                                <input type="hidden" name="property_id" value="${p.id}">
                                <button type="submit" name="action" value="approve" class="action-btn btn-approve">Approve</button>
                                <button type="submit" name="action" value="reject" class="action-btn btn-reject">Reject</button>
                            </form>` : ''}
                            <form method="POST" style="display:inline;" onsubmit="return confirm('Permanently delete this property?')">
                                <input type="hidden" name="property_id" value="${p.id}">
                                <button type="submit" name="action" value="delete" class="action-btn btn-delete">Delete</button>
                            </form>
                        </td>
                    </tr>`;
                });
                tbody.innerHTML = html;
            } catch (err) {
                document.getElementById('propertiesBody').innerHTML = 
                    `<tr><td colspan="9" class="no-properties">Error loading properties. Please refresh.</td></tr>`;
            }
        }

        function filterTable() {
            const search = document.getElementById('search').value.toLowerCase();
            const status = document.getElementById('statusFilter').value;
            const type = document.getElementById('typeFilter').value;

            document.querySelectorAll('#propertiesBody tr').forEach(row => {
                const text = row.textContent.toLowerCase();
                const rowStatus = row.dataset.status;
                const rowType = row.dataset.type;

                const matches = 
                    text.includes(search) &&
                    (!status || rowStatus === status) &&
                    (!type || rowType === type);

                row.style.display = matches ? '' : 'none';
            });
        }

        // Load on start + refresh every 60 seconds
        loadProperties();
        setInterval(loadProperties, 60000);
    </script>
</body>
</html>