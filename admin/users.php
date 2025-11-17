<?php
// admin/users.php - Full Admin User Management
require '../inc/config.php';
require '../inc/auth.php';

if ($_SESSION['user']['role'] !== 'admin') {
    header('Location: ../dashboard/');
    exit;
}

// Handle actions: ban, unban, delete, change role
if (isset($_POST['action']) && isset($_POST['user_id'])) {
    $user_id = intval($_POST['user_id']);
    $action = $_POST['action'];

    if ($action === 'ban') {
        $stmt = $db->prepare("UPDATE users SET status = 'banned' WHERE id = ? AND role != 'admin'");
    } elseif ($action === 'unban') {
        $stmt = $db->prepare("UPDATE users SET status = 'active' WHERE id = ?");
    } elseif ($action === 'make_agent') {
        $stmt = $db->prepare("UPDATE users SET role = 'agent' WHERE id = ? AND role != 'admin'");
    } elseif ($action === 'make_client') {
        $stmt = $db->prepare("UPDATE users SET role = 'client' WHERE id = ?");
    } elseif ($action === 'delete' && $_POST['confirm'] === 'yes') {
        $stmt = $db->prepare("DELETE FROM users WHERE id = ? AND role != 'admin'");
    }

    if (isset($stmt)) {
        $stmt->bind_param('i', $user_id);
        $stmt->execute();
        log_activity("Admin $action user ID #$user_id");
    }

    header('Location: users.php?success=1');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Admin • Manage Users • House Unlimited</title>
    <link rel="stylesheet" href="../assets/css/style.css" />
    <style>
        .admin-header {
            background: linear-gradient(135deg, #f59e0b, #d97706);
            color: white;
            padding: 2.5rem;
            border-radius: 20px;
            margin-bottom: 2.5rem;
            text-align: center;
        }
        .admin-header h1 { margin: 0 0 0.5rem; font-size: 2.8rem; font-weight: 800; }
        .admin-header p { margin: 0; opacity: 0.95; }

        .filters {
            background: white;
            padding: 1.5rem;
            border-radius: 16px;
            box-shadow: 0 8px 30px rgba(0,0,0,0.08);
            margin-bottom: 2rem;
            display: flex;
            gap: 1rem;
            flex-wrap: wrap;
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
            background: #fffbeb;
            padding: 1.2rem 1rem;
            font-weight: 600;
            color: #92400e;
            text-transform: uppercase;
            font-size: 0.9rem;
        }
        body.dark .table th { background: #451a03; color: #fcd34d; }

        .table td {
            padding: 1.2rem 1rem;
            vertical-align: middle;
        }

        .user-avatar {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid #f59e0b;
        }

        .role-badge {
            padding: 0.4rem 1rem;
            border-radius: 50px;
            font-size: 0.8rem;
            font-weight: 700;
        }
        .role-admin { background: #fee2e2; color: #991b1b; }
        .role-agent { background: #dbeafe; color: #1e40af; }
        .role-client { background: #f0fdf4; color: #166534; }

        .status-badge {
            padding: 0.4rem 1rem;
            border-radius: 50px;
            font-size: 0.8rem;
            font-weight: 700;
        }
        .status-active { background: #d1fae5; color: #065f46; }
        .status-banned { background: #fee2e2; color: #991b1b; }

        .action-btn {
            padding: 0.5rem 1rem;
            margin: 0 0.3rem;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 0.85rem;
        }
        .btn-ban { background: #ef4444; color: white; }
        .btn-unban { background: #10b981; color: white; }
        .btn-agent { background: #3b82f6; color: white; }
        .btn-client { background: #64748b; color: white; }
        .btn-delete { background: #991b1b; color: white; }

        .no-users {
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
                <h1>Manage Users</h1>
                <p>Total control over all users across Nigeria</p>
            </div>

            <?php if (isset($_GET['success'])): ?>
                <div style="background:#d1fae5; color:#065f46; padding:1rem; border-radius:12px; margin-bottom:1.5rem;">
                    User action completed successfully!
                </div>
            <?php endif; ?>

            <!-- Filters -->
            <div class="filters">
                <input type="text" id="search" placeholder="Search name, email, phone..." oninput="filterTable()" />
                <select id="roleFilter" onchange="filterTable()">
                    <option value="">All Roles</option>
                    <option value="admin">Admin</option>
                    <option value="agent">Agent</option>
                    <option value="client">Client</option>
                </select>
                <select id="statusFilter" onchange="filterTable()">
                    <option value="">All Status</option>
                    <option value="active">Active</option>
                    <option value="banned">Banned</option>
                </select>
            </div>

            <!-- Users Table -->
            <div class="table-container">
                <table class="table" id="usersTable">
                    <thead>
                        <tr>
                            <th>User</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Role</th>
                            <th>Status</th>
                            <th>Joined</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="usersBody">
                        <!-- Loaded via JS -->
                    </tbody>
                </table>
            </div>
        </main>
    </div>

    <script>
        async function loadUsers() {
            const res = await fetch('../api/admin_users.php');
            const users = await res.json();

            const tbody = document.getElementById('usersBody');
            if (users.length === 0) {
                tbody.innerHTML = `<tr><td colspan="7" class="no-users">No users found in the system.</td></tr>`;
                return;
            }

            let html = '';
            users.forEach(u => {
                const joined = new Date(u.created_at).toLocaleDateString('en-NG', {
                    day: 'numeric', month: 'short', year: 'numeric'
                });

                const isAdmin = u.role === 'admin';
                const isBanned = u.status === 'banned';

                html += `
                <tr data-role="${u.role}" data-status="${u.status}">
                    <td>
                        <div style="display:flex; align-items:center; gap:1rem;">
                            <img src="../assets/uploads/avatars/${u.photo || 'default.png'}" class="user-avatar" alt="${u.name}">
                            <div>
                                <strong>${u.name}</strong><br>
                                <small>#${String(u.id).padStart(6, '0')}</small>
                            </div>
                        </div>
                    </td>
                    <td>${u.email}</td>
                    <td>${u.phone || '—'}</td>
                    <td><span class="role-badge role-${u.role}">${u.role.toUpperCase()}</span></td>
                    <td><span class="status-badge status-${u.status}">${u.status.toUpperCase()}</span></td>
                    <td>${joined}</td>
                    <td>
                        ${!isAdmin ? `
                        <form method="POST" style="display:inline;">
                            <input type="hidden" name="user_id" value="${u.id}">
                            ${isBanned ? `
                            <button type="submit" name="action" value="unban" class="action-btn btn-unban">Unban</button>
                            ` : `
                            <button type="submit" name="action" value="ban" class="action-btn btn-ban">Ban</button>
                            `}
                            ${u.role === 'client' ? `
                            <button type="submit" name="action" value="make_agent" class="action-btn btn-agent">Make Agent</button>
                            ` : u.role === 'agent' ? `
                            <button type="submit" name="action" value="make_client" class="action-btn btn-client">Make Client</button>
                            ` : ''}
                            <button type="submit" name="action" value="delete" 
                                    onclick="return confirm('Permanently delete this user? This cannot be undone.')" 
                                    class="action-btn btn-delete">Delete</button>
                        </form>
                        ` : '<em>Protected</em>'}
                    </td>
                </tr>`;
            });
            tbody.innerHTML = html;
        }

        function filterTable() {
            const search = document.getElementById('search').value.toLowerCase();
            const role = document.getElementById('roleFilter').value;
            const status = document.getElementById('statusFilter').value;

            document.querySelectorAll('#usersBody tr').forEach(row => {
                const text = row.textContent.toLowerCase();
                const rowRole = row.dataset.role;
                const rowStatus = row.dataset.status;

                const matchesSearch = text.includes(search);
                const matchesRole = !role || rowRole === role;
                const matchesStatus = !status || rowStatus === status;

                row.style.display = (matchesSearch && matchesRole && matchesStatus) ? '' : 'none';
            });
        }

        // Initial load
        loadUsers();
        setInterval(loadUsers, 60000);
    </script>
</body>
</html>