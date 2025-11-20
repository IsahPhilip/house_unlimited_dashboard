<?php
// admin/properties.php — PERSONAL AGENT REVENUE DASHBOARD (2025 LUXURY EDITION)
require '../inc/config.php';
require '../inc/auth.php';

if ($_SESSION['user']['role'] !== 'admin') {
    header('Location: ../dashboard/');
    exit;
}

$admin_id = $_SESSION['user']['id'];
$admin_name = $_SESSION['user']['name'];

$period = $_GET['period'] ?? '7days';
$start_date = $_GET['start'] ?? '';
$end_date = $_GET['end'] ?? '';

// Build date filter (personal to this agent)
if ($period === 'today') {
    $where = "DATE(t.created_at) = CURDATE() AND p.agent_id = ?";
    $params = [$admin_id];
    $types = 'i';
} elseif ($period === '7days') {
    $where = "t.created_at >= DATE_SUB(CURDATE(), INTERVAL 7 DAY) AND p.agent_id = ?";
    $params = [$admin_id];
    $types = 'i';
} elseif ($period === '30days') {
    $where = "t.created_at >= DATE_SUB(CURDATE(), INTERVAL 30 DAY) AND p.agent_id = ?";
    $params = [$admin_id];
    $types = 'i';
} elseif ($period === 'custom' && $start_date && $end_date) {
    $where = "DATE(t.created_at) BETWEEN ? AND ? AND p.agent_id = ?";
    $params = [$start_date, $end_date, $admin_id];
    $types = 'ssi';
} else {
    $where = "t.created_at >= DATE_SUB(CURDATE(), INTERVAL 30 DAY) AND p.agent_id = ?";
    $params = [$admin_id];
    $types = 'i';
}

// === PERSONAL REVENUE STATS ===
$statsQuery = "
    SELECT 
        COUNT(*) as total_txns,
        COALESCE(SUM(CASE WHEN t.status = 'success' THEN t.amount ELSE 0 END), 0) as revenue,
        COUNT(CASE WHEN t.status = 'success' THEN 1 END) as successful,
        COUNT(CASE WHEN t.status = 'failed' THEN 1 END) as failed,
        COUNT(CASE WHEN t.status = 'pending' THEN 1 END) as pending
FROM transactions t
JOIN properties p ON t.property_id = p.id
WHERE $where
";

$stmt = $db->prepare($statsQuery);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$stats = $stmt->get_result()->fetch_assoc();

// === PERSONAL REVENUE CHART (Last 30 Days) ===
$chartQuery = "
    SELECT 
        DATE(t.created_at) as date,
        COALESCE(SUM(CASE WHEN t.status = 'success' THEN t.amount ELSE 0 END), 0) as daily_revenue
    FROM transactions t
    JOIN properties p ON t.property_id = p.id
    WHERE t.created_at >= DATE_SUB(CURDATE(), INTERVAL 29 DAY)
      AND p.agent_id = ?
    GROUP BY DATE(t.created_at)
    ORDER BY date ASC
";

$stmt = $db->prepare($chartQuery);
$stmt->bind_param('i', $admin_id);
$stmt->execute();
$chartResult = $stmt->get_result();

$chartData = [];
$labels = [];

for ($i = 29; $i >= 0; $i--) {
    $date = date('Y-m-d', strtotime("-$i days"));
    $labels[] = date('M j', strtotime($date));
    $chartData[$date] = 0;
}

while ($row = $chartResult->fetch_assoc()) {
    $chartData[$row['date']] = (int)$row['daily_revenue'];
}
$revenueValues = array_values($chartData);

// === MY PROPERTIES ONLY (with earnings) ===
$myProperties = $db->prepare("
    SELECT p.id, p.title, p.location, p.price,
           COUNT(t.id) as total_bookings,
           COALESCE(SUM(CASE WHEN t.status = 'success' THEN t.amount ELSE 0 END), 0) as earned
    FROM properties p
    LEFT JOIN transactions t ON p.id = t.property_id
    WHERE p.agent_id = ?
    GROUP BY p.id
    ORDER BY earned DESC, p.created_at DESC
");
$myProperties->bind_param('i', $admin_id);
$myProperties->execute();
$properties = $myProperties->get_result()->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Properties & Earnings • Agent • House Unlimited</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Playfair+Display:wght@700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        :root { --primary: #1e40af; --gold: #fbbf24; --success: #10b981; }
        .hero {
            background: linear-gradient(135deg, #1e293b, #0f172a);
            color: white;
            padding: 4rem 2rem;
            border-radius: 28px;
            text-align: center;
            margin-bottom: 3rem;
        }
        .hero h1 {
            font-family: 'Playfair Display', serif;
            font-size: 3.8rem;
            margin: 0 0 1rem;
        }
        .hero p { font-size: 1.4rem; opacity: 0.9; }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
            gap: 2rem;
            margin: 2rem 0;
        }
        .stat-card {
            background: white;
            padding: 2.5rem;
            border-radius: 24px;
            text-align: center;
            box-shadow: 0 20px 50px rgba(0,0,0,0.1);
            transition: all 0.4s;
        }
        .stat-card:hover { transform: translateY(-12px); }
        body.dark .stat-card { background: #1e1e1e; }
        .stat-value {
            font-size: 3.5rem;
            font-weight: 800;
            color: var(--primary);
            margin: 1rem 0;
        }
        .stat-label { color: #64748b; font-size: 1.2rem; }

        .chart-container {
            background: white;
            padding: 3rem;
            border-radius: 28px;
            box-shadow: 0 25px 60px rgba(0,0,0,0.12);
            margin: 3rem 0;
        }
        body.dark .chart-container { background: #1e1e1e; }

        .properties-table {
            background: white;
            border-radius: 24px;
            overflow: hidden;
            box-shadow: 0 20px 50px rgba(0,0,0,0.1);
        }
        body.dark .properties-table { background: #1e1e1e; }
        table { width: 100%; border-collapse: collapse; }
        th { background: #f8fafc; padding: 1.5rem; font-weight: 700; color: var(--primary); }
        body.dark th { background: #334155; color: #93c5fd; }
        td { padding: 1.5rem; border-bottom: 1px solid #e2e8f0; }
        body.dark td { border-color: #334155; }
        tr:hover { background: #f8fafc; }
        body.dark tr:hover { background: #2d3748; }

        .earned { color: var(--success); font-weight: 700; }
        .btn { padding: 0.8rem 1.5rem; border-radius: 50px; text-decoration: none; font-weight: 600; }
        .btn-primary { background: var(--primary); color: white; }
        .btn-success { background: var(--success); color: white; }
    </style>
</head>
<body>
    <?php include '../inc/header.php'; ?>
    <div class="container">
        <?php include '../inc/sidebar.php'; ?>

        <main class="main-content">
            <div class="hero">
                <h1>My Properties & Earnings</h1>
                <p>Welcome back, <strong><?= htmlspecialchars($admin_name) ?></strong> — Here's your personal performance</p>
            </div>

            <div style="text-align:center; margin-bottom:2rem;">
                <select onchange="location.href='?period='+this.value" style="padding:1rem 2rem; border-radius:16px; border:2px solid var(--primary); font-size:1.1rem;">
                    <option value="today" <?= $period==='today'?'selected':'' ?>>Today</option>
                    <option value="7days" <?= $period==='7days'?'selected':'' ?>>Last 7 Days</option>
                    <option value="30days" <?= $period==='30days'?'selected':'' ?>>Last 30 Days</option>
                </select>
            </div>

            <!-- Personal Stats -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-value">₦<?= number_format($stats['revenue']) ?></div>
                    <div class="stat-label">My Total Earnings</div>
                </div>
                <div class="stat-card">
                    <div class="stat-value"><?= count($properties) ?></div>
                    <div class="stat-label">My Properties</div>
                </div>
                <div class="stat-card">
                    <div class="stat-value"><?= $stats['successful'] ?></div>
                    <div class="stat-label">Successful Sales</div>
                </div>
                <div class="stat-card">
                    <div class="stat-value"><?= $stats['total_txns'] ?></div>
                    <div class="stat-label">Total Bookings</div>
                </div>
            </div>

            <!-- Personal Revenue Chart -->
            <div class="chart-container">
                <h2 style="text-align:center; margin-bottom:2rem; font-size:2.2rem; color:var(--primary);">
                    My Earnings Trend (Last 30 Days)
                </h2>
                <canvas id="myRevenueChart"></canvas>
            </div>

            <!-- My Properties List -->
            <div class="properties-table">
                <h2 style="padding:2rem; margin:0; background:var(--primary); color:white; font-size:1.8rem;">
                    My Listed Properties
                </h2>
                <?php if (empty($properties)): ?>
                    <p style="text-align:center; padding:4rem; color:#64748b; font-size:1.4rem;">
                        You haven't listed any properties yet.<br>
                        <a href="add_property.php" class="btn btn-primary" style="margin-top:1rem; display:inline-block;">
                            List Your First Property
                        </a>
                    </p>
                <?php else: ?>
                    <table>
                        <thead>
                            <tr>
                                <th>Property</th>
                                <th>Location</th>
                                <th>Price</th>
                                <th>Bookings</th>
                                <th>Earned</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($properties as $p): ?>
                                <tr>
                                    <td><strong><?= htmlspecialchars($p['title']) ?></strong></td>
                                    <td><?= htmlspecialchars($p['location']) ?></td>
                                    <td>₦<?= number_format($p['price']) ?></td>
                                    <td><strong><?= $p['total_bookings'] ?></strong></td>
                                    <td class="earned">₦<?= number_format($p['earned']) ?></td>
                                    <td>
                                        <a href="../property_detail.php?id=<?= $p['id'] ?>" class="btn btn-primary" target="_blank">View</a>
                                        <a href="edit_property.php?id=<?= $p['id'] ?>" class="btn btn-success">Edit</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </main>
    </div>

    <script>
        // PERSONAL REVENUE CHART
        const ctx = document.getElementById('myRevenueChart').getContext('2d');
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: <?= json_encode($labels) ?>,
                datasets: [{
                    label: 'My Daily Earnings (₦)',
                    data: <?= json_encode($revenueValues) ?>,
                    borderColor: '#10b981',
                    backgroundColor: 'rgba(16, 185, 129, 0.1)',
                    tension: 0.4,
                    fill: true,
                    pointBackgroundColor: '#10b981',
                    pointRadius: 7,
                    pointHoverRadius: 12
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { position: 'top', labels: { font: { size: 16, weight: 'bold' } } },
                    tooltip: {
                        callbacks: {
                            label: ctx => '₦' + Number(ctx.parsed.y).toLocaleString()
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: { callback: value => '₦' + value.toLocaleString() }
                    }
                }
            }
        });
    </script>
</body>
</html>