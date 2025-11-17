<?php
// admin/index.php - Admin Dashboard
require '../inc/config.php';
require '../inc/auth.php';

if ($_SESSION['user']['role'] !== 'admin') {
    header('Location: ../dashboard/');
    exit;
}

$user = $_SESSION['user'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Admin Dashboard • House Unlimited</title>
    <link rel="stylesheet" href="../assets/css/style.css" />
    <style>
        .admin-header {
            background: linear-gradient(135deg, #1e40af, #1e3a8a);
            color: white;
            padding: 2rem;
            border-radius: 20px;
            margin-bottom: 2.5rem;
            text-align: center;
            box-shadow: 0 15px 40px rgba(30,64,175,0.3);
        }
        .admin-header h1 {
            margin: 0 0 0.5rem;
            font-size: 2.8rem;
            font-weight: 800;
        }
        .admin-header p {
            margin: 0;
            opacity: 0.95;
            font-size: 1.2rem;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
            gap: 1.8rem;
            margin: 2.5rem 0;
        }
        .stat-card {
            background: white;
            padding: 2rem;
            border-radius: 20px;
            box-shadow: 0 10px 35px rgba(0,0,0,0.1);
            text-align: center;
            transition: all 0.3s;
            border: 1px solid #e2e8f0;
        }
        body.dark .stat-card { background: #1e1e1e; border-color: #334155; }
        .stat-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 20px 50px rgba(0,0,0,0.15);
        }
        .stat-card .icon {
            width: 70px;
            height: 70px;
            background: #3b82f6;
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
            margin: 0 auto 1rem;
        }
        .stat-card h3 {
            font-size: 1.1rem;
            color: #64748b;
            margin: 0 0 1rem;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .stat-card .value {
            font-size: 3rem;
            font-weight: 800;
            color: #1e40af;
        }
        body.dark .stat-card .value { color: #60a5fa; }

        .revenue { background: linear-gradient(135deg, #10b981, #059669); color: white; }
        .revenue .value { color: white; }

        .quick-actions {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 1.5rem;
            margin: 3rem 0;
        }
        .action-card {
            background: white;
            padding: 2rem;
            border-radius: 16px;
            text-align: center;
            box-shadow: 0 8px 30px rgba(0,0,0,0.08);
            transition: all 0.3s;
        }
        body.dark .action-card { background: #1e1e1e; }
        .action-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 20px 40px rgba(0,0,0,0.15);
        }
        .action-card a {
            color: #1e40af;
            font-weight: 600;
            text-decoration: none;
            font-size: 1.1rem;
        }
        body.dark .action-card a { color: #93c5fd; }

        .recent-activity {
            background: white;
            padding: 2rem;
            border-radius: 20px;
            box-shadow: 0 10px 35px rgba(0,0,0,0.1);
            margin-top: 2rem;
        }
        body.dark .recent-activity { background: #1e1e1e; }
        .activity-item {
            padding: 1rem 0;
            border-bottom: 1px solid #f1f5f9;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        body.dark .activity-item { border-color: #334155; }
        .activity-item:last-child { border: none; }
    </style>
</head>
<body class="dark">
    <?php include '../inc/header.php'; ?>

    <div class="container">
        <?php include '../inc/sidebar.php'; ?>

        <main class="main-content">
            <div class="admin-header">
                <h1>Admin Control Center</h1>
                <p>House Unlimited & Land Services Nigeria • Lagos Headquarters</p>
            </div>

            <!-- Stats Grid -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="icon">Users</div>
                    <h3>Total Users</h3>
                    <div class="value" id="totalUsers">0</div>
                </div>
                <div class="stat-card">
                    <div class="icon">Properties</div>
                    <h3>Active Listings</h3>
                    <div class="value" id="totalProperties">0</div>
                </div>
                <div class="stat-card revenue">
                    <div class="icon">Currency</div>
                    <h3>Total Revenue</h3>
                    <div class="value" id="totalRevenue">₦0</div>
                </div>
                <div class="stat-card">
                    <div class="icon">Calendar</div>
                    <h3>Appointments Today</h3>
                    <div class="value" id="todayAppointments">0</div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="quick-actions">
                <div class="action-card">
                    <h3>Users</h3>
                    <p><a href="users.php">Manage Users →</a></p>
                </div>
                <div class="action-card">
                    <h3>Properties</h3>
                    <p><a href="properties.php">All Properties →</a></p>
                </div>
                <div class="action-card">
                    <h3>Payments</h3>
                    <p><a href="payments.php">View All Transactions →</a></p>
                </div>
                <div class="action-card">
                    <h3>Documents</h3>
                    <p><a href="documents.php">System Documents →</a></p>
                </div>
            </div>

            <!-- Recent Activity -->
            <div class="recent-activity">
                <h2>Recent System Activity</h2>
                <div id="activityLog">
                    <p style="text-align:center; color:#64748b; padding:2rem;">Loading admin activity...</p>
                </div>
            </div>
        </main>
    </div>

    <script>
        async function loadAdminStats() {
            const [usersRes, propsRes, revenueRes, apptsRes] = await Promise.all([
                fetch('../api/admin_stats.php?type=users'),
                fetch('../api/admin_stats.php?type=properties'),
                fetch('../api/admin_stats.php?type=revenue'),
                fetch('../api/admin_stats.php?type=appointments_today')
            ]);

            const users = await usersRes.json();
            const props = await propsRes.json();
            const revenue = await revenueRes.json();
            const appts = await apptsRes.json();

            document.getElementById('totalUsers').textContent = users.count.toLocaleString();
            document.getElementById('totalProperties').textContent = props.count.toLocaleString();
            document.getElementById('totalRevenue').textContent = '₦' + Number(revenue.total).toLocaleString();
            document.getElementById('todayAppointments').textContent = appts.count;
        }

        async function loadActivityLog() {
            const res = await fetch('../api/admin_activity.php');
            const logs = await res.json();

            const container = document.getElementById('activityLog');
            if (logs.length === 0) {
                container.innerHTML = '<p style="text-align:center; color:#94a3b8;">No recent activity</p>';
                return;
            }

            let html = '';
            logs.slice(0, 10).forEach(log => {
                const time = new Date(log.timestamp).toLocaleString('en-NG', {
                    weekday: 'short', hour: '2-digit', minute: '2-digit'
                });
                html += `
                <div class="activity-item">
                    <div>
                        <strong>${log.action}</strong><br>
                        <small style="color:#64748b;">by ${log.user_name || 'System'}</small>
                    </div>
                    <div style="color:#94a3b8; font-size:0.9rem;">${time}</div>
                </div>`;
            });
            container.innerHTML = html;
        }

        // Load everything
        loadAdminStats();
        loadActivityLog();
        setInterval(() => {
            loadAdminStats();
            loadActivityLog();
        }, 30000);
    </script>
</body>
</html>