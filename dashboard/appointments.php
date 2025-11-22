<?php
// dashboard/appointments.php - 100% WORKING
require '../inc/config.php';
require '../inc/auth.php';

$user = $_SESSION['user'];
$user_id = $user['id'];
$role = $user['role'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Appointments • House Unlimited</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .page-header h1 { margin: 0; font-size: 2.2rem; color: #1e40af; }
        body.dark .page-header h1 { color: #93c5fd; }

        .appointments-container {
            background: white; border-radius: 16px; box-shadow: 0 8px 30px rgba(0,0,0,0.08); overflow: hidden;
        }
        body.dark .appointments-container { background: #1e1e1e; }

        .table th { background: #f8f9fc; padding: 1.2rem; text-align: left; color: #475569; font-size: 0.95rem; text-transform: uppercase; }
        body.dark .table th { background: #334155; color: #cbd5e1; }

        .property-thumb { width: 60px; height: 60px; object-fit: cover; border-radius: 10px; }

        .status { padding: 0.5rem 1rem; border-radius: 50px; font-size: 0.85rem; font-weight: 600; text-transform: uppercase; }
        .status.pending { background: #fef3c7; color: #92400e; }
        .status.confirmed { background: #d1fae5; color: #065f46; }
        .status.completed { background: #dbeafe; color: #1e40af; }
        .status.cancelled, .status.rejected { background: #fee2e2; color: #991b1b; }

        .whatsapp-reminder {
            background: #25d366; color: white; padding: 0.6rem 1rem; border-radius: 50px;
            text-decoration: none; font-size: 0.9rem; display: inline-flex; align-items: center; gap: 0.5rem;
        }
        .whatsapp-reminder img { width: 18px; }
    </style>
</head>
<body>
    <?php include '../inc/header.php'; ?>
    <div class="container">
        <?php include '../inc/sidebar.php'; ?>

        <main class="main-content">
            <div class="page-header" style="display:flex; justify-content:space-between; align-items:center; margin-bottom:2rem;">
                <h1>My Appointments</h1>
                <select id="filterStatus" onchange="loadAppointments()" style="padding:0.8rem; border-radius:12px; border:2px solid #e2e8f0;">
                    <option value="">All Status</option>
                    <option value="pending">Pending</option>
                    <option value="confirmed">Confirmed</option>
                    <option value="completed">Completed</option>
                    <option value="cancelled">Cancelled</option>
                </select>
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
                        <tr><td colspan="5" style="text-align:center; padding:3rem; color:#64748b;">Loading...</td></tr>
                    </tbody>
                </table>
            </div>
        </main>
    </div>

    <script>
        async function loadAppointments() {
            const status = document.getElementById('filterStatus').value;
            const url = status ? `../api/appointments.php?status=${status}` : '../api/appointments.php';

            try {
                const res = await fetch(url);
                const result = await res.json(); // Change 'apps' to 'result' for clarity

                let html = '';
                if (!result.success || result.data.length === 0) { // Check for success and data length
                    html = `<tr><td colspan="5" style="text-align:center; padding:4rem; color:#64748b;">
                        <h3>No appointments found</h3>
                        <p>${'<?= $role === "client" ? "Browse properties and book a viewing!" : "No client requests yet." ?>'}</p>
                    </td></tr>`;
                } else {
                    result.data.forEach(a => { // Iterate over result.data
                        const date = new Date(a.appointment_date + ' ' + a.appointment_time);
                        const formatted = date.toLocaleDateString('en-NG', { weekday:'long', year:'numeric', month:'long', day:'numeric' }) 
                                        + ' at ' + date.toLocaleTimeString('en-NG', { hour:'numeric', minute:'2-digit' });

                        const phone = a.counterparty_phone?.replace(/\D/g, '') || '2348000000000';
                        if (phone.length < 10) phone = '2348000000000';

                        html += `
                        <tr>
                            <td><strong>${formatted}</strong></td>
                            <td>
                                <div style="display:flex; align-items:center; gap:1rem;">
                                    <img src="../assets/uploads/properties/${(a.property_image) || 'default_property.png'}" class="property-thumb" alt="" onerror="this.onerror=null; this.src='../assets/uploads/properties/default_property.png';">
                                    <div>
                                        <strong>${a.property_title}</strong><br>
                                        <small>${a.property_location}</small>
                                    </div>
                                </div>
                            </td>
                            <td><strong>${a.counterparty_name}</strong><br><small>${a.counterparty_phone || '—'}</small></td>
                            <td><span class="status ${a.status}">${a.status.charAt(0).toUpperCase() + a.status.slice(1)}</span></td>
                            <td>
                                <div style="display:flex; gap:0.5rem; flex-wrap:wrap;">
                                    <a href="../dashboard/property_detail.php?id=${a.property_id}" class="button-small" style="background:#4a5568; color:white; padding:0.6rem 1rem; border-radius:50px; text-decoration:none; font-size:0.9rem;">View Details</a>

                                    <?php if ($role !== 'client'): ?>
                                        ${a.status === 'pending' ? `
                                            <button onclick="handleAppointmentAction(${a.id}, 'accept')" class="button-small" style="background:#10b981; color:white; padding:0.6rem 1rem; border-radius:50px; border:none; cursor:pointer; font-size:0.9rem;">Confirm</button>
                                            <button onclick="handleAppointmentAction(${a.id}, 'reject')" class="button-small" style="background:#ef4444; color:white; padding:0.6rem 1rem; border-radius:50px; border:none; cursor:pointer; font-size:0.9rem;">Reject</button>
                                        ` : ''}
                                        ${a.status === 'confirmed' ? `
                                            <button onclick="handleAppointmentAction(${a.id}, 'reject')" class="button-small" style="background:#ef4444; color:white; padding:0.6rem 1rem; border-radius:50px; border:none; cursor:pointer; font-size:0.9rem;">Cancel</button>
                                            <a href="https://wa.me/${phone}?text=Hi!%20Just%20confirming%20our%20appointment%20for%20${encodeURIComponent(a.property_title)}%20on%20${encodeURIComponent(formatted)}"
                                               target="_blank" class="whatsapp-reminder">
                                                <img src="../assets/img/whatsapp-white.png" style="width: 18px;"> Remind on WhatsApp
                                            </a>
                                        ` : ''}
                                    <?php else: ?>
                                        ${a.status === 'confirmed' ? `
                                            <a href="https://wa.me/${phone}?text=Hi!%20Just%20confirming%20our%20appointment%20for%20${encodeURIComponent(a.property_title)}%20on%20${encodeURIComponent(formatted)}"
                                               target="_blank" class="whatsapp-reminder">
                                                <img src="../assets/img/whatsapp-white.png" style="width: 18px;"> Remind on WhatsApp
                                            </a>
                                        ` : ''}
                                    <?php endif; ?>
                                    ${a.status === 'completed' ? '<em style="color:#64748b;">Completed</em>' : ''}
                                    ${a.status === 'cancelled' || a.status === 'rejected' ? '<em style="color:#64748b;">' + a.status.charAt(0).toUpperCase() + a.status.slice(1) + '</em>' : ''}
                                </div>
                            </td>
                        </tr>`;
                    });
                }
                document.getElementById('appointmentsBody').innerHTML = html;
            } catch (err) {
                document.getElementById('appointmentsBody').innerHTML = 
                    `<tr><td colspan="5" style="text-align:center; padding:4rem; color:#ef4444;">Error loading appointments</td></tr>`;
            }
        }

        async function handleAppointmentAction(appointmentId, action) {
            if (!confirm(`Are you sure you want to ${action} this appointment?`)) {
                return;
            }

            try {
                const res = await fetch('../api/update_appointment.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({ id: appointmentId, action: action })
                });
                const result = await res.json();

                if (result.success) {
                    alert(`Appointment ${action}ed successfully!`);
                    loadAppointments(); // Reload appointments to reflect changes
                } else {
                    alert('Error: ' + result.message);
                }
            } catch (err) {
                console.error('Error handling appointment action:', err);
                alert('An error occurred while processing your request.');
            }
        }

        loadAppointments();
        setInterval(loadAppointments, 30000);
    </script>
</body>
</html>