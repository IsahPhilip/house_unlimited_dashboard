<?php
// dashboard/index.php - Main Role-Based Dashboard
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
    <title>Dashboard • House Unlimited & Land Services</title>
    <link rel="stylesheet" href="../assets/css/style.css" />
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        .welcome-header h1 { font-size: 2.2rem; color: #1e40af; margin-bottom: 0.5rem; }
        body.dark .welcome-header h1 { color: #93c5fd; }
        .welcome-header p { color: #64748b; font-size: 1.1rem; }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
            gap: 1.8rem;
            margin: 2.5rem 0;
        }
        .stat-card {
            background: white;
            padding: 1.8rem;
            border-radius: 16px;
            box-shadow: 0 8px 30px rgba(0,0,0,0.08);
            text-align: center;
            transition: all 0.3s;
        }
        body.dark .stat-card { background: #1e1e1e; box-shadow: 0 8px 30px rgba(0,0,0,0.5); }
        .stat-card:hover { transform: translateY(-6px); }
        .stat-card h3 { font-size: 1rem; color: #64748b; margin-bottom: 0.8rem; text-transform: uppercase; letter-spacing: 0.5px; }
        .stat-card span {
            font-size: 2.8rem;
            font-weight: 700;
            color: #1e40af;
        }
        body.dark .stat-card span { color: #60a5fa; }

        .recent-activity {
            margin-top: 3rem;
            background: white;
            padding: 2rem;
            border-radius: 16px;
            box-shadow: 0 8px 30px rgba(0,0,0,0.08);
        }
        body.dark .recent-activity { background: #1e1e1e; }
        .recent-activity h2 { margin-bottom: 1.5rem; color: #1e293b; }
        body.dark .recent-activity h2 { color: #e2e8f0; }

        .activity-list {
            list-style: none;
        }
        .activity-item {
            padding: 1rem 0;
            border-bottom: 1px solid #f1f5f9;
            display: flex;
            justify-content: space-between;
            font-size: 1rem;
        }
        body.dark .activity-item { border-color: #334155; }
        .activity-item:last-child { border: none; }
        .activity-time { color: #94a3b8; font-size: 0.9rem; }

        .quick-actions {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1.2rem;
            margin-top: 2rem;
        }
        .quick-btn {
            padding: 1.5rem;
            background: #f8f9fc;
            border-radius: 12px;
            text-align: center;
            font-weight: 600;
            color: #1e40af;
            text-decoration: none;
            transition: 0.3s;
        }
        body.dark .quick-btn { background: #1e293b; color: #93c5fd; }
        .quick-btn:hover { background: #3b82f6; color: white; transform: translateY(-4px); }
    </style>
</head>
<body>
    <?php include '../inc/header.php'; ?>

    <div class="container">
        <?php include '../inc/sidebar.php'; ?>

        <main class="main-content">
            <div class="welcome-header">
                <h1>Hello, <?= htmlspecialchars($user['name']) ?>!</h1>
                <p>
                    <?php
                    $hour = date('H');
                    if ($hour < 12) echo "Good morning";
                    elseif ($hour < 17) echo "Good afternoon";
                    else echo "Good evening";
                    ?>, welcome back to your <?= ucfirst($role) ?> dashboard.
                </p>
            </div>

            <!-- Stats Grid -->
            <div class="stats-grid">
                <?php if ($role === 'client'): ?>
                    <div class="stat-card">
                        <h3>My Appointments</h3>
                        <span id="clientAppointments">0</span>
                    </div>
                    <div class="stat-card">
                        <h3>Owned Properties</h3>
                        <span id="clientProperties">0</span>
                    </div>
                <?php else: ?>
                    <div class="stat-card">
                        <h3>My Listings</h3>
                        <span id="agentListings">0</span>
                    </div>
                    <div class="stat-card">
                        <h3>Pending Viewings</h3>
                        <span id="agentAppointments">0</span>
                    </div>
                <?php endif; ?>

                <div class="stat-card">
                    <h3>Unread Messages</h3>
                    <span id="unreadMessages">0</span>
                </div>
                <div class="stat-card">
                    <h3>Total Transactions</h3>
                    <span id="totalTransactions">0</span>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="quick-actions">
                <a href="properties.php" class="quick-btn">Browse Properties</a>
                <a href="appointments.php" class="quick-btn">View Appointments</a>
                <a href="messages.php" class="quick-btn">Check Messages</a>
                <?php if ($role !== 'client'): ?>
                    <a href="add_property.php" class="quick-btn">+ Add New Property</a>
                <?php endif; ?>
            </div>

            <!-- Recent Activity -->
            <div class="recent-activity">
                <h2>Recent Activity</h2>
                <ul class="activity-list" id="activityFeed">
                    <li class="activity-item">
                        <div>Loading your recent actions...</div>
                    </li>
                </ul>
            </div>
        </main>
    </div>

    <script src="../assets/js/main.js"></script>
    <script>
        // Load Dashboard Stats
        async function loadStats() {
            const res = await fetch('../api/dashboard_stats.php');
            const data = await res.json();

            <?php if ($role === 'client'): ?>
                document.getElementById('clientAppointments').textContent = data.appointments || 0;
                document.getElementById('clientProperties').textContent = data.properties || 0;
            <?php else: ?>
                document.getElementById('agentListings').textContent = data.properties || 0;
                document.getElementById('agentAppointments').textContent = data.appointments || 0;
            <?php endif; ?>

            document.getElementById('totalTransactions').textContent = data.transactions || 0;
        }

        // Load Unread Messages
        async function updateUnread() {
            const res = await fetch('../api/unread_count.php');
            const d = await res.json();
            const badge = document.querySelector('.msg-badge');
            const countSpan = document.getElementById('unreadMessages');
            const count = d.count || 0;
            if (countSpan) countSpan.textContent = count;
            if (badge) badge.textContent = count > 0 ? count : '';
        }

        // Recent Activity (Sample + Real from DB later)
        function loadActivity() {
            const activities = [
                "You scheduled a viewing for Guzape 5-Bedroom Duplex",
                "New message from Client: Mr. Adebayo",
                "Payment received: ₦50,000,000 for Victoria Island Plot",
                "New property added: 4-Bedroom Terrace in Lekki Phase 1",
                "Appointment confirmed for tomorrow at 2:00 PM"
            ];
            const feed = document.getElementById('activityFeed');
            feed.innerHTML = '';
            activities.forEach(act => {
                const li = document.createElement('li');
                li.className = 'activity-item';
                li.innerHTML = `
                    <div>${act}</div>
                    <div class="activity-time">${new Date().toLocaleString('en-NG', {hour: '2-digit', minute: '2-digit'})}</div>
                `;
                feed.appendChild(li);
            });
        }

        // Initialize
        loadStats();
        updateUnread();
        loadActivity();
        setInterval(updateUnread, 15000);
    </script>
</body>
</html>