<?php
// admin/reports.php - Advanced Business Reports & Analytics
require '../inc/config.php';
require '../inc/auth.php';

if ($_SESSION['user']['role'] !== 'admin') {
    header('Location: ../dashboard/');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Admin • Reports & Analytics • House Unlimited</title>
    <link rel="stylesheet" href="../assets/css/style.css" />
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .admin-header {
            background: linear-gradient(135deg, #7c3aed, #5b21b6);
            color: white;
            padding: 2.5rem;
            border-radius: 20px;
            margin-bottom: 2.5rem;
            text-align: center;
        }
        .admin-header h1 { margin: 0 0 0.5rem; font-size: 2.8rem; font-weight: 800; }
        .admin-header p { margin: 0; opacity: 0.95; font-size: 1.3rem; }

        .report-period {
            display: flex;
            justify-content: center;
            gap: 1rem;
            margin-bottom: 2rem;
            flex-wrap: wrap;
        }
        .period-btn {
            padding: 0.8rem 1.8rem;
            background: #1e293b;
            color: white;
            border: none;
            border-radius: 50px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s;
        }
        .period-btn.active, .period-btn:hover {
            background: #7c3aed;
            transform: translateY(-3px);
            box-shadow: 0 10px 25px rgba(124,58,237,0.4);
        }

        .grid-2 {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(500px, 1fr));
            gap: 2rem;
            margin: 2.5rem 0;
        }
        .chart-card {
            background: white;
            padding: 2rem;
            border-radius: 20px;
            box-shadow: 0 15px 40px rgba(0,0,0,0.1);
        }
        body.dark .chart-card { background: #1e1e1e; }

        .kpi-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 1.5rem;
            margin: 2.5rem 0;
        }
        .kpi-card {
            background: white;
            padding: 1.8rem;
            border-radius: 16px;
            text-align: center;
            box-shadow: 0 10px 30px rgba(0,0,0,0.08);
            border-left: 5px solid #7c3aed;
        }
        body.dark .kpi-card { background: #1e1e1e; }
        .kpi-card h3 {
            font-size: 1rem;
            color: #64748b;
            margin: 0 0 1rem;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .kpi-card .value {
            font-size: 2.6rem;
            font-weight: 800;
            color: #7c3aed;
        }

        .top-locations, .top-agents {
            background: white;
            padding: 2rem;
            border-radius: 20px;
            box-shadow: 0 15px 40px rgba(0,0,0,0.1);
            margin-top: 2rem;
        }
        body.dark .top-locations, body.dark .top-agents { background: #1e1e1e; }

        .ranking-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        .ranking-item {
            display: flex;
            justify-content: space-between;
            padding: 1rem 0;
            border-bottom: 1px solid #f1f5f9;
        }
        body.dark .ranking-item { border-color: #334155; }
        .ranking-item:last-child { border: none; }
        .rank-badge {
            width: 32px;
            height: 32px;
            background: #7c3aed;
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 0.9rem;
        }
    </style>
</head>
<body class="dark">
    <?php include '../inc/header.php'; ?>

    <div class="container">
        <?php include '../inc/sidebar.php'; ?>

        <main class="main-content">
            <div class="admin-header">
                <h1>Business Reports & Analytics</h1>
                <p>Real-time insights powering Nigeria's #1 real estate platform</p>
            </div>

            <!-- Period Selector -->
            <div class="report-period">
                <button class="period-btn active" data-period="7">Last 7 Days</button>
                <button class="period-btn" data-period="30">Last 30 Days</button>
                <button class="period-btn" data-period="90">Last 90 Days</button>
                <button class="period-btn" data-period="365">Past Year</button>
            </div>

            <!-- KPI Cards -->
            <div class="kpi-grid">
                <div class="kpi-card">
                    <h3>Total Revenue</h3>
                    <div class="value" id="totalRevenue">₦0</div>
                </div>
                <div class="kpi-card">
                    <h3>Bookings Made</h3>
                    <div class="value" id="totalBookings">0</div>
                </div>
                <div class="kpi-card">
                    <h3>New Users</h3>
                    <div class="value" id="newUsers">0</div>
                </div>
                <div class="kpi-card">
                    <h3>Conversion Rate</h3>
                    <div class="value" id="conversionRate">0%</div>
                </div>
            </div>

            <!-- Charts -->
            <div class="grid-2">
                <div class="chart-card">
                    <h3>Revenue Trend (₦)</h3>
                    <canvas id="revenueChart"></canvas>
                </div>
                <div class="chart-card">
                    <h3>Property Views & Bookings</h3>
                    <canvas id="activityChart"></canvas>
                </div>
            </div>

            <!-- Rankings -->
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:2rem; margin-top:2rem;">
                <div class="top-locations">
                    <h3>Top Locations by Bookings</h3>
                    <ol class="ranking-list" id="topLocations">
                        <li style="text-align:center; color:#64748b; padding:2rem;">Loading...</li>
                    </ol>
                </div>
                <div class="top-agents">
                    <h3>Top Performing Agents</h3>
                    <ol class="ranking-list" id="topAgents">
                        <li style="text-align:center; color:#64748b; padding:2rem;">Loading...</li>
                    </ol>
                </div>
            </div>
        </main>
    </div>

    <script>
        let revenueChart, activityChart;

        async function loadReports(period = 30) {
            const res = await fetch(`../api/admin_reports.php?period=${period}`);
            const data = await res.json();

            // Update KPIs
            document.getElementById('totalRevenue').textContent = '₦' + Number(data.revenue.total).toLocaleString();
            document.getElementById('totalBookings').textContent = data.bookings.total;
            document.getElementById('newUsers').textContent = data.users.new;
            document.getElementById('conversionRate').textContent = data.conversion + '%';

            // Revenue Chart
            const revCtx = document.getElementById('revenueChart').getContext('2d');
            if (revenueChart) revenueChart.destroy();
            revenueChart = new Chart(revCtx, {
                type: 'line',
                data: {
                    labels: data.revenue.dates,
                    datasets: [{
                        label: 'Revenue (₦)',
                        data: data.revenue.amounts,
                        borderColor: '#7c3aed',
                        backgroundColor: 'rgba(124,58,237,0.1)',
                        tension: 0.4,
                        fill: true
                    }]
                },
                options: { responsive: true, plugins: { legend: { display: false } } }
            });

            // Activity Chart
            const actCtx = document.getElementById('activityChart').getContext('2d');
            if (activityChart) activityChart.destroy();
            activityChart = new Chart(actCtx, {
                type: 'bar',
                data: {
                    labels: data.activity.dates,
                    datasets: [
                        {
                            label: 'Property Views',
                            data: data.activity.views,
                            backgroundColor: '#3b82f6'
                        },
                        {
                            label: 'Bookings',
                            data: data.activity.bookings,
                            backgroundColor: '#10b981'
                        }
                    ]
                },
                options: { responsive: true, scales: { y: { beginAtZero: true } } }
            });

            // Rankings
            const locList = document.getElementById('topLocations');
            locList.innerHTML = data.top_locations.map((loc, i) => `
                <li class="ranking-item">
                    <span><span class="rank-badge">${i+1}</span> ${loc.location}</span>
                    <strong>${loc.count} bookings</strong>
                </li>
            `).join('');

            const agentList = document.getElementById('topAgents');
            agentList.innerHTML = data.top_agents.map((agent, i) => `
                <li class="ranking-item">
                    <span><span class="rank-badge">${i+1}</span> ${agent.name}</span>
                    <strong>₦${Number(agent.revenue).toLocaleString()}</strong>
                </li>
            `).join('');
        }

        // Period buttons
        document.querySelectorAll('.period-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                document.querySelectorAll('.period-btn').forEach(b => b.classList.remove('active'));
                this.classList.add('active');
                loadReports(this.dataset.period);
            });
        });

        // Initial load
        loadReports(30);
        setInterval(() => loadReports(document.querySelector('.period-btn.active').dataset.period), 300000);
    </script>
</body>
</html>