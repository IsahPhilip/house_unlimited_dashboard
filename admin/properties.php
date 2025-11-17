<?php
// admin/properties.php - Full Admin Property Management
require '../inc/config.php';
require '../inc/auth.php';

if ($_SESSION['user']['role'] !== 'admin') {
    header('Location: ../dashboard/');
    exit;
}

// Handle status update (approve, reject, delete)
if (isset($_POST['action']) && isset($_POST['property_id'])) {
    $id = intval($_POST['property_id']);
    $action = $_POST['action'];

    if ($action === 'approve') {
        $stmt = $db->prepare("UPDATE properties SET status = 'active' WHERE id = ?");
    } elseif ($action === 'reject') {
        $stmt = $db->prepare("UPDATE properties SET status = 'rejected' WHERE id = ?");
    } elseif ($action === 'delete') {
        // Soft delete or hard delete
        $stmt = $db->prepare("DELETE FROM properties WHERE id = ?");
    }
    $stmt->bind_param('i', $id);
    $stmt->execute();

    if ($action === 'delete') {
        // Also delete images
        $db->query("DELETE FROM property_images WHERE property_id = $id");
    }

    log_activity("Admin " . ucfirst($action) . "d property ID #$id");
    header('Location: properties.php?success=1');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Admin • All Properties • House Unlimited</title>
    <link rel="stylesheet" href="../assets/css/style.css" />
    <style>
        .admin-header {
            background: linear-gradient(135deg, #1e40af, #1e3a8a);
            color: white;
            padding: 2rem;
            border-radius: 20px;
            margin-bottom: 2.5rem;
            text-align: center;
        }
        .admin-header h1 { margin: 0 0 0.5rem; font-size: 2.6rem; }
        .admin-header p { margin: 0; opacity: 0.9; }

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

        .table th {
            background: #f8f9fc;
            padding: 1.2rem 1rem;
            font-weight: 600;
            color: #475569;
            text-transform: uppercase;
            font-size: 0.9rem;
        }
        body.dark .table th { background: #334155; color: #cbd5e1; }

        .table td {
            padding: 1.2rem 1rem;
            vertical-align: middle;
            border-bottom: 1px solid #f1f5f9;
        }
        body.dark .table td { border-color: #334155; }

        .property-thumb {
            width: 70px;
            height: 70px;
            object-fit: cover;
            border-radius: 10px;
        }

        .status {
            padding: 0.5rem 1rem;
            border-radius: 50px;
            font-size: 0.85rem;
            font-weight: 600;
        }
        .status.active { background: #d1fae5; color: #065f46; }
        .status.pending { background: #fef3c7; color: #92400e; }
        .status.rejected { background: #fee2e2; color: #991b1b; }

        .action-btn {
            padding: 0.5rem 1rem;
            margin: 0 0.3rem;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 0.9rem;
        }
        .btn-approve { background: #10b981; color: white; }
        .btn-reject { background: #f59e0b; color: white; }
        .btn-delete { background: #ef4444; color: white; }
        .btn-view { background: #3b82f6; color: white; }

        .no-properties {
            text-align: center;
            padding: 4rem 2rem;
            color: #64748b;
        }
    </style>
</head>
<body class="dark">
    <?php include '../inc/header.php'; ?>

    <div class="container">
        <?php include '../inc/sidebar.php'; ?>

        <main class="main-content">
            <div class="admin-header">
                <h1>All Properties</h1>
                <p>Manage listings across Lagos, Abuja, PH & beyond</p>
            </div>

            <?php if (isset($_GET['success'])): ?>
                <div style="background:#d1fae5; color:#065f46; padding:1rem; border-radius:12px; margin-bottom:1.5rem;">
                    Property action completed successfully!
                </div>
            <?php endif; ?>

            <!-- Filters -->
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

            <!-- Properties Table -->
            <div class="table-container">
                <table class="table" id="propertiesTable">
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
                        <!-- Loaded via JS -->
                    </tbody>
                </table>
            </div>
        </main>
    </div>

    <script>
        async function loadProperties() {
            const res = await fetch('../api/admin_properties.php');
            const properties = await res.json();

            const tbody = document.getElementById('propertiesBody');
            if (properties.length === 0) {
                tbody.innerHTML = `<tr><td colspan="9" class="no-properties">No properties found in the system.</td></tr>`;
                return;
            }

            let html = '';
            properties.forEach(p => {
                const date = new Date(p.created_at).toLocaleDateString('en-NG', {
                    day: 'numeric', month: 'short', year: 'numeric'
                });

                html += `
                <tr data-status="${p.status}" data-type="${p.type}">
                    <td><img src="../assets/uploads/properties/${p.featured_image || 'default.jpg'}" class="property-thumb" alt="${p.title}"></td>
                    <td><strong>${p.title}</strong></td>
                    <td>${p.location}</td>
                    <td><strong>₦${Number(p.price).toLocaleString()}</strong></td>
                    <td>${p.agent_name || 'Unknown'}</td>
                    <td><span class="status ${p.type}">${p.type.toUpperCase()}</span></td>
                    <td><span class="status ${p.status}">${p.status.toUpperCase()}</span></td>
                    <td>${date}</td>
                    <td>
                        <a href="../dashboard/property_detail.php?id=${p.id}" class="action-btn btn-view" target="_blank">View</a>
                        ${p.status === 'pending' ? `
                        <form method="POST" style="display:inline;">
                            <input type="hidden" name="property_id" value="${p.id}">
                            <button type="submit" name="action" value="approve" class="action-btn btn-approve">Approve</button>
                            <button type="submit" name="action" value="reject" class="action-btn btn-reject">Reject</button>
                        </form>` : ''}
                        <form method="POST" style="display:inline;" onsubmit="return confirm('Delete this property permanently?')">
                            <input type="hidden" name="property_id" value="${p.id}">
                            <button type="submit" name="action" value="delete" class="action-btn btn-delete">Delete</button>
                        </form>
                    </td>
                </tr>`;
            });
            tbody.innerHTML = html;
        }

        function filterTable() {
            const search = document.getElementById('search').value.toLowerCase();
            const status = document.getElementById('statusFilter').value;
            const type = document.getElementById('typeFilter').value;

            document.querySelectorAll('#propertiesBody tr').forEach(row => {
                const text = row.textContent.toLowerCase();
                const rowStatus = row.dataset.status;
                const rowType = row.dataset.type;

                const matchesSearch = text.includes(search);
                const matchesStatus = !status || rowStatus === status;
                const matchesType = !type || rowType === type;

                row.style.display = (matchesSearch && matchesStatus && matchesType) ? '' : 'none';
            });
        }

        // Initial load
        loadProperties();
        setInterval(loadProperties, 60000); // Refresh every minute
    </script>
</body>
</html>