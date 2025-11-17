<?php
// dashboard/appointments.php
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
    <title>Appointments • House Unlimited</title>
    <link rel="stylesheet" href="../assets/css/style.css" />
    <style>
        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
            flex-wrap: wrap;
            gap: 1rem;
        }
        .page-header h1 {
            margin: 0;
            font-size: 2.2rem;
            color: #1e40af;
        }
        body.dark .page-header h1 { color: #93c5fd; }

        .calendar-controls {
            display: flex;
            gap: 1rem;
            margin-bottom: 1.5rem;
            flex-wrap: wrap;
        }
        .calendar-controls select, .calendar-controls button {
            padding: 0.8rem 1.2rem;
            border-radius: 12px;
            border: 2px solid #e2e8f0;
        }

        .appointments-container {
            background: white;
            border-radius: 16px;
            box-shadow: 0 8px 30px rgba(0,0,0,0.08);
            overflow: hidden;
        }
        body.dark .appointments-container { background: #1e1e1e; }

        .table {
            width: 100%;
            border-collapse: collapse;
        }
        .table th {
            background: #f8f9fc;
            padding: 1.2rem 1rem;
            text-align: left;
            font-weight: 600;
            color: #475569;
            font-size: 0.95rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        body.dark .table th { background: #334155; color: #cbd5e1; }

        .table td {
            padding: 1.2rem 1rem;
            border-bottom: 1px solid #f1f5f9;
            vertical-align: middle;
        }
        body.dark .table td { border-color: #334155; }

        .property-thumb {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: 10px;
        }

        .status {
            padding: 0.5rem 1rem;
            border-radius: 50px;
            font-size: 0.85rem;
            font-weight: 600;
            text-transform: uppercase;
        }
        .status.pending { background: #fef3c7; color: #92400e; }
        .status.confirmed { background: #d1fae5; color: #065f46; }
        .status.completed { background: #dbeafe; color: #1e40af; }
        .status.cancelled { background: #fee2e2; color: #991b1b; }

        .action-btn {
            padding: 0.5rem 1rem;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 0.9rem;
            margin-right: 0.5rem;
        }
        .btn-confirm { background: #10b981; color: white; }
        .btn-cancel { background: #ef4444; color: white; }

        .no-appointments {
            text-align: center;
            padding: 4rem 2rem;
            color: #64748b;
            font-size: 1.2rem;
        }
        .no-appointments img {
            width: 120px;
            opacity: 0.5;
            margin-bottom: 1rem;
        }

        .whatsapp-reminder {
            background: #25d366;
            color: white;
            padding: 0.6rem 1rem;
            border-radius: 50px;
            text-decoration: none;
            font-size: 0.9rem;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }
        .whatsapp-reminder img { width: 18px; }
    </style>
</head>
<body>
    <?php include '../inc/header.php'; ?>

    <div class="container">
        <?php include '../inc/sidebar.php'; ?>

        <main class="main-content">
            <div class="page-header">
                <h1>My Appointments</h1>
                <div class="calendar-controls">
                    <select id="filterStatus">
                        <option value="">All Status</option>
                        <option value="pending">Pending</option>
                        <option value="confirmed">Confirmed</option>
                        <option value="completed">Completed</option>
                        <option value="cancelled">Cancelled</option>
                    </select>
                    <button onclick="loadAppointments()" class="btn btn-primary">Refresh</button>
                </div>
            </div>

            <div class="appointments-container">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Date & Time</th>
                            <th>Property</th>
                            <th><?= $role === 'client' ? 'Agent' : 'Client' ?></th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="appointmentsBody">
                        <tr>
                            <td colspan="5" class="no-appointments">
                                <img src="../assets/img/calendar.svg" alt="Calendar">
                                <p>Loading your appointments...</p>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </main>
    </div>

    <script src="../assets/js/main.js"></script>
    <script>
        async function loadAppointments() {
            const status = document.getElementById('filterStatus').value;
            const params = status ? `?status=${status}` : '';
            
            const res = await fetch(`../api/appointments.php${params}`);
            const appointments = await res.json();

            let html = '';
            if (appointments.length === 0) {
                html = `
                <tr>
                    <td colspan="5" class="no-appointments">
                        <img src="../assets/img/no-appointments.svg" alt="No appointments">
                        <p>No appointments found.</p>
                        ${'<?= $role === "client" ? "<p>Browse properties and book a viewing today!</p>" : "" ?>'}
                    </td>
                </tr>`;
            } else {
                appointments.forEach(app => {
                    const date = new Date(app.viewing_date);
                    const formattedDate = date.toLocaleDateString('en-NG', { 
                        weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' 
                    }) + ' at ' + date.toLocaleTimeString('en-NG', { 
                        hour: '2-digit', minute: '2-digit' 
                    });

                    const clientPhone = app.client_phone || '2348000000000';
                    const agentPhone = app.agent_phone || '2348030000000';

                    html += `
                    <tr>
                        <td>
                            <strong>${formattedDate}</strong><br>
                            <small style="color:#64748b">Scheduled ${new Date(app.created_at).toLocaleDateString()}</small>
                        </td>
                        <td>
                            <div style="display:flex; align-items:center; gap:1rem;">
                                <img src="../assets/uploads/properties/${app.property_image || 'default.jpg'}" 
                                     alt="${app.property_title}" class="property-thumb">
                                <div>
                                    <strong>${app.property_title}</strong><br>
                                    <small>${app.property_location}</small>
                                </div>
                            </div>
                        </td>
                        <td>
                            <strong>${app.counterparty_name}</strong><br>
                            <small>${app.counterparty_phone}</small>
                        </td>
                        <td><span class="status ${app.status}">${app.status.replace('_', ' ')}</span></td>
                        <td>
                            ${app.status === 'pending' && '<?= $role !== "client" ? true : false ?>' ? 
                                `<button class="action-btn btn-confirm" onclick="updateStatus(${app.id}, 'confirmed')">Confirm</button>
                                 <button class="action-btn btn-cancel" onclick="updateStatus(${app.id}, 'cancelled')">Cancel</button>` : ''
                            }
                            ${app.status === 'confirmed' ? 
                                `<a href="https://wa.me/${<?= $role === 'client' ? 'agentPhone' : 'clientPhone' ?>}?text=Hi!%20Regarding%20our%20appointment%20for%20${encodeURIComponent(app.property_title)}%20on%20${encodeURIComponent(formattedDate)}" 
                                   target="_blank" class="whatsapp-reminder">
                                    <img src="../assets/img/whatsapp-white.png" alt="WhatsApp"> Remind via WhatsApp
                                </a>` : ''
                            }
                            ${app.status === 'completed' ? '<em>Done</em>' : ''}
                        </td>
                    </tr>`;
                });
            }
            document.getElementById('appointmentsBody').innerHTML = html;
        }

        async function updateStatus(id, status) {
            if (!confirm(`Are you sure you want to ${status} this appointment?`)) return;

            const res = await fetch('../api/update_appointment.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id, status })
            });
            const data = await res.json();
            if (data.success) {
                alert('Appointment updated successfully!');
                loadAppointments();
            }
        }

        // Auto-refresh every 30 seconds
        loadAppointments();
        setInterval(loadAppointments, 30000);
    </script>
</body>
</html>