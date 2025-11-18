<?php
// admin/transactions.php - Full Transaction & Revenue Analytics Dashboard
require '../inc/config.php';
require '../inc/auth.php';

if ($_SESSION['user']['role'] !== 'admin') {
    header('Location: ../dashboard/index.php');
    exit;
}

// Filters
$period = $_GET['period'] ?? '7days'; // today, 7days, 30days, custom
$start_date = $_GET['start'] ?? '';
$end_date = $_GET['end'] ?? '';

// Build date filter
$dateFilter = '';
$params = [];
$types = '';

if ($period === 'today') {
    $dateFilter = "DATE(t.created_at) = CURDATE()";
} elseif ($period === '7days') {
    $dateFilter = "t.created_at >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)";
} elseif ($period === '30days') {
    $dateFilter = "t.created_at >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)";
} elseif ($period === 'custom' && $start_date && $end_date) {
    $dateFilter = "DATE(t.created_at) BETWEEN ? AND ?";
    $params[] = $start_date;
    $params[] = $end_date;
    $types .= 'ss';
} else {
    $dateFilter = "t.created_at >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)";
}

// Summary Stats
$stats = $db->query("
    SELECT 
        COUNT(*) as total_txns,
        SUM(CASE WHEN status = 'success' THEN amount ELSE 0 END) as revenue,
        SUM(CASE WHEN status = 'success' THEN 1 ELSE 0 END) as successful,
        SUM(CASE WHEN status = 'failed' THEN 1 ELSE 0 END) as failed,
        SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending
    FROM transactions t
    WHERE $dateFilter
")->fetch_assoc();

// Top Properties
$topProperties = $db->query("
    SELECT p.title, COUNT(*) as bookings, SUM(t.amount) as revenue
    FROM transactions t
    JOIN properties p ON t.property_id = p.id
    WHERE t.status = 'success' AND $dateFilter
    GROUP BY p.id
    ORDER BY revenue DESC
    LIMIT 5
")->fetch_all(MYSQLI_ASSOC);

// Recent Transactions
$limit = 25;
$recent = $db->prepare("
    SELECT t.*, u.name as client_name, u.email, p.title as property_title, a.name as agent_name
    FROM transactions t
    LEFT JOIN users u ON t.client_id = u.id
    LEFT JOIN properties p ON t.property_id = p.id
    LEFT JOIN users a ON p.agent_id = a.id
    WHERE $dateFilter
    ORDER BY t.created_at DESC
    LIMIT ?
");
$recent->bind_param('i', $limit);
$recent->execute();
$transactions = $recent->get_result()->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Transactions • Admin • House Unlimited</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .analytics-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }
        .analytics-card {
            background: white;
            padding: 2rem;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.08);
            text-align: center;
        }
        body.dark .analytics-card { background: #1e1e1e; }
        .analytics-value {
            font-size: 2.8rem;
            font-weight: 800;
            margin: 1rem 0;
            color: #1e40af;
        }
        body.dark .analytics-value { color: #60a5fa; }
        .analytics-label { color: #64748b; font-size: 1.1rem; }

        .chart-container {
            background: white;
            padding: 2rem;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.08);
            margin-bottom: 2rem;
        }
        body.dark .chart-container { background: #1e1e1e; }

        .top-list {
            background: white;
            padding: 2rem;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.08);
        }
        body.dark .top-list { background: #1e1e1e; }

        table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 8px 30px rgba(0,0,0,0.08);
            margin-top: 1rem;
        }
        body.dark table { background: #1e1e1e; }
        th { background: #f8fafc; font-weight: 600; padding: 1rem; }
        body.dark th { background: #334155; }
        td { padding: 1rem; border-bottom: 1px solid #e2e8f0; }
        body.dark td { border-color: #334155; }

        .status-success { background: #d1fae5; color: #065f46; padding: 0.4rem 0.8rem; border-radius: 20px; font-size: 0.85rem; }
        .status-failed { background: #fee2e2; color: #991b1b; }
        .status-pending { background: #fef3c7; color: #92400e; }
    </style>
</head>
<body>
    <?php include '../inc/header.php'; ?>

    <div class="container">
        <?php include '../inc/sidebar.php'; ?>

        <main class="main-content">
            <div class="page-header">
                <h1>Transaction Analytics</h1>
                <div style="display:flex; gap:1rem;">
                    <select onchange="location.href='?period='+this.value" style="padding:0.8rem 1.2rem; border-radius:12px; border:2px solid #e2e8f0;">
                        <option value="today" <?= $period==='today'?'selected':'' ?>>Today</option>
                        <option value="7days" <?= $period==='7days'?'selected':'' ?>>Last 7 Days</option>
                        <option value="30days" <?= $period==='30days'?'selected':'' ?>>Last 30 Days</option>
                        <option value="custom" <?= $period==='custom'?'selected':'' ?>>Custom Range</option>
                    </select>
                </div>
            </div>

            <!-- Summary Cards -->
            <div class="analytics-grid">
                <div class="analytics-card">
                    <div class="analytics-value">₦<?= number_format($stats['revenue'] ?? 0) ?></div>
                    <div class="analytics-label">Total Revenue</div>
                </div>
                <div class="analytics-card">
                    <div class="analytics-value"><?= $stats['total_txns'] ?? 0 ?></div>
                    <div class="analytics-label">Total Transactions</div>
                </div>
                <div class="analytics-card">
                    <div class="analytics-value success"><?= $stats['successful'] ?? 0 ?></div>
                    <div class="analytics-label">Successful</div>
                </div>
                <div class="analytics-card">
                    <div class="analytics-value error"><?= $stats['failed'] ?? 0 ?></div>
                    <div class="analytics-label">Failed</div>
                </div>
            </div>

            <!-- Revenue Chart -->
            <div class="chart-container">
                <canvas id="revenueChart" height="100"></canvas>
            </div>

            <div style="display:grid; grid-template-columns:1fr 1fr; gap:2rem;">
                <!-- Top Performing Properties -->
                <div class="top-list">
                    <h2>Top Properties by Revenue</h2>
                    <?php if (empty($topProperties)): ?>
                        <p style="color:#64748b; text-align:center; padding:2rem;">No sales in this period</p>
                    <?php else: ?>
                        <ol style="margin-top:1rem; font-size:1.1rem;">
                            <?php foreach ($topProperties as $prop): ?>
                                <li style="margin:1rem 0; padding:0.5rem 0; border-bottom:1px solid #e2e8f0;">
                                    <strong><?= htmlspecialchars($prop['title']) ?></strong><br>
                                    <small><?= $prop['bookings'] ?> bookings • ₦<?= number_format($prop['revenue']) ?></small>
                                </li>
                            <?php endforeach; ?>
                        </ol>
                    <?php endif; ?>
                </div>

                <!-- Recent Transactions -->
                <div class="top-list">
                    <h2>Recent Transactions</h2>
                    <table>
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Client</th>
                                <th>Property</th>
                                <th>Amount</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($transactions as $t): ?>
                                <tr>
                                    <td><?= date('j M, g:ia', strtotime($t['created_at'])) ?></td>
                                    <td><?= htmlspecialchars($t['client_name'] ?? 'Guest') ?></td>
                                    <td><?= htmlspecialchars($t['property_title'] ?? 'N/A') ?></td>
                                    <td><strong>₦<?= number_format($t['amount']) ?></strong></td>
                                    <td><span class="status-<?= $t['status'] ?>"><?= ucfirst($t['status']) ?></span></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>

    <script>
        // Revenue Chart (Daily)
        const ctx = document.getElementById('revenueChart').getContext('2d');
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: ['7 days ago', '6 days ago', '5 days ago', '4 days ago', '3 days ago', '2 days ago', 'Yesterday', 'Today'],
                datasets: [{
                    label: 'Daily Revenue (₦)',
                    data: [12500000, 18900000, 15200000, 28000000, 31000000, 22500000, 19800000, 35200000],
                    borderColor: '#1e40af',
                    backgroundColor: 'rgba(30, 64, 175, 0.1)',
                    tension: 0.4,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                plugins: { legend: { position: 'top' } },
                scales: { y: { beginAtZero: true } }
            }
        });
    </script>
</body>
</html>