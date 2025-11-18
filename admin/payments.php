<?php
// admin/payments.php - Admin Payment Dashboard
require '../inc/config.php';
require '../inc/auth.php';

// Only admin can access
if ($_SESSION['user']['role'] !== 'admin') {
    header('Location: ../dashboard/index.php');
    exit;
}

$page = max(1, intval($_GET['page'] ?? 1));
$limit = 20;
$offset = ($page - 1) * $limit;

$where = ["1=1"];
$params = [];
$types = '';

// Filters
$search = trim($_GET['search'] ?? '');
$status = $_GET['status'] ?? '';
$date_from = $_GET['date_from'] ?? '';
$date_to = $_GET['date_to'] ?? '';

if ($search !== '') {
    $where[] = "(p.reference LIKE ? OR u.name LIKE ? OR u.email LIKE ? OR prop.title LIKE ?)";
    $like = "%$search%";
    $params[] = $like; $params[] = $like; $params[] = $like; $params[] = $like;
    $types .= 'ssss';
}
if ($status !== '' && in_array($status, ['success', 'failed', 'pending'])) {
    $where[] = "p.status = ?";
    $params[] = $status;
    $types .= 's';
}
if ($date_from !== '') {
    $where[] = "p.created_at >= ?";
    $params[] = $date_from . ' 00:00:00';
    $types .= 's';
}
if ($date_to !== '') {
    $where[] = "p.created_at <= ?";
    $params[] = $date_to . ' 23:59:59';
    $types .= 's';
}

$whereClause = implode(' AND ', $where);

// Main query
$sql = "SELECT 
            p.*, 
            u.name as client_name, 
            u.email as client_email,
            prop.title as property_title,
            a.name as agent_name
        FROM payments p
        LEFT JOIN users u ON p.user_id = u.id
        LEFT JOIN properties prop ON p.property_id = prop.id
        LEFT JOIN users a ON prop.agent_id = a.id
        WHERE $whereClause
        ORDER BY p.created_at DESC
        LIMIT ? OFFSET ?";
$types .= 'ii';
$params[] = $limit;
$params[] = $offset;

$stmt = $db->prepare($sql);
if (!empty($types)) $stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();
$payments = $result->fetch_all(MYSQLI_ASSOC);

// Total revenue
$totalRevenue = $db->query("SELECT COALESCE(SUM(amount), 0) as total FROM payments WHERE status = 'success'")->fetch_assoc()['total'];
$totalToday = $db->query("SELECT COALESCE(SUM(amount), 0) as total FROM payments WHERE status = 'success' AND DATE(created_at) = CURDATE()")->fetch_assoc()['total'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payments • Admin • House Unlimited</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }
        .stat-card {
            background: white;
            padding: 2rem;
            border-radius: 16px;
            box-shadow: 0 8px 30px rgba(0,0,0,0.08);
            text-align: center;
        }
        body.dark .stat-card { background: #1e1e1e; }
        .stat-value {
            font-size: 2.2rem;
            font-weight: 700;
            color: #1e40af;
            margin: 0.5rem 0;
        }
        body.dark .stat-value { color: #60a5fa; }
        .stat-label { color: #64748b; font-size: 1rem; }

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

        table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 8px 30px rgba(0,0,0,0.08);
        }
        body.dark table { background: #1e1e1e; }
        th, td {
            padding: 1rem;
            text-align: left;
            border-bottom: 1px solid #e2e8f0;
        }
        body.dark th, body.dark td { border-color: #334155; }
        th { background: #f8fafc; font-weight: 600; color: #1e293b; }
        body.dark th { background: #334155; color: #e2e8f0; }

        .status-success { background: #d1fae5; color: #065f46; padding: 0.4rem 0.8rem; border-radius: 20px; font-size: 0.85rem; }
        .status-failed { background: #fee2e2; color: #991b1b; }
        .status-pending { background: #fef3c7; color: #92400e; }

        .pagination {
            display: flex;
            justify-content: center;
            gap: 0.5rem;
            margin: 2rem 0;
        }
        .pagination a {
            padding: 0.6rem 1rem;
            background: #f1f5f9;
            border-radius: 8px;
            text-decoration: none;
            color: #1e293b;
        }
        body.dark .pagination a { background: #334155; color: #e2e8f0; }
        .pagination a.active, .pagination a:hover { background: #3b82f6; color: white; }
    </style>
</head>
<body>
    <?php include '../inc/header.php'; ?>

    <div class="container">
        <?php include '../inc/sidebar.php'; ?>

        <main class="main-content">
            <div class="page-header">
                <h1>Payment Dashboard</h1>
                <button onclick="exportPayments()" class="btn btn-success">Export to CSV</button>
            </div>

            <!-- Stats -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-value">₦<?= number_format($totalRevenue) ?></div>
                    <div class="stat-label">Total Revenue (All Time)</div>
                </div>
                <div class="stat-card">
                    <div class="stat-value">₦<?= number_format($totalToday) ?></div>
                    <div class="stat-label">Revenue Today</div>
                </div>
                <div class="stat-card">
                    <div class="stat-value"><?= count($payments) ?></div>
                    <div class="stat-label">Transactions Shown</div>
                </div>
            </div>

            <!-- Filters -->
            <div class="filters">
                <input type="text" id="search" placeholder="Search client, reference, property..." value="<?= htmlspecialchars($search) ?>">
                <select id="status">
                    <option value="">All Status</option>
                    <option value="success" <?= $status==='success'?'selected':'' ?>>Success</option>
                    <option value="pending" <?= $status==='pending'?'selected':'' ?>>Pending</option>
                    <option value="failed" <?= $status==='failed'?'selected':'' ?>>Failed</option>
                </select>
                <input type="date" id="date_from" value="<?= $date_from ?>">
                <input type="date" id="date_to" value="<?= $date_to ?>">
                <button onclick="applyFilters()" class="btn btn-primary">Apply Filters</button>
            </div>

            <!-- Payments Table -->
            <table>
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Reference</th>
                        <th>Client</th>
                        <th>Property</th>
                        <th>Agent</th>
                        <th>Amount</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($payments)): ?>
                        <tr><td colspan="7" style="text-align:center; padding:3rem; color:#64748b;">No payments found</td></tr>
                    <?php else: foreach ($payments as $p): ?>
                        <tr>
                            <td><?= date('j M Y, g:ia', strtotime($p['created_at'])) ?></td>
                            <td><code><?= htmlspecialchars($p['reference']) ?></code></td>
                            <td>
                                <?= htmlspecialchars($p['client_name'] ?? 'N/A') ?><br>
                                <small><?= htmlspecialchars($p['client_email'] ?? '') ?></small>
                            </td>
                            <td><?= htmlspecialchars($p['property_title'] ?? 'N/A') ?></td>
                            <td><?= htmlspecialchars($p['agent_name'] ?? 'N/A') ?></td>
                            <td><strong>₦<?= number_format($p['amount']) ?></strong></td>
                            <td>
                                <span class="status-<?= $p['status'] ?>">
                                    <?= ucfirst($p['status']) ?>
                                </span>
                            </td>
                        </tr>
                    <?php endforeach; endif; ?>
                </tbody>
            </table>

            <!-- Pagination -->
            <div class="pagination">
                <?php for($i = 1; $i <= 10; $i++): // Show 10 pages max ?>
                    <a href="?page=<?= $i ?>&search=<?= urlencode($search) ?>&status=<?= $status ?>&date_from=<?= $date_from ?>&date_to=<?= $date_to ?>"
                       class="<?= $i == $page ? 'active' : '' ?>">
                        <?= $i ?>
                    </a>
                <?php endfor; ?>
            </div>
        </main>
    </div>

    <script>
        function applyFilters() {
            const params = new URLSearchParams({
                search: document.getElementById('search').value,
                status: document.getElementById('status').value,
                date_from: document.getElementById('date_from').value,
                date_to: document.getElementById('date_to').value
            });
            location.href = 'payments.php?' + params;
        }

        function exportPayments() {
            const params = new URLSearchParams(window.location.search);
            location.href = '../api/export_payments.php?' + params;
        }
    </script>
</body>
</html>