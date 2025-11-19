<?php
// admin/appointments.php - Admin Appointment & Property Inspection Manager
require '../inc/config.php';
require '../inc/auth.php';

if ($_SESSION['user']['role'] !== 'admin') {
    header('Location: ../dashboard/');
    exit;
}

$admin_id = $_SESSION['user']['id'];

// Handle status update
if (isset($_POST['action']) && isset($_POST['appt_id'])) {
    $appt_id = intval($_POST['appt_id']);
    $action = $_POST['action'];

    if (in_array($action, ['confirm', 'complete', 'cancel'])) {
        $status = $action === 'confirm' ? 'confirmed' : ($action === 'complete' ? 'completed' : 'cancelled');
        $db->query("UPDATE appointments SET status = '$status' WHERE id = $appt_id");
        log_activity("Admin $action appointment ID #$appt_id");
    }
    header('Location: appointments.php?success=1');
    exit;
}

// Handle delete
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $db->query("DELETE FROM appointments WHERE id = $id");
    header('Location: appointments.php?deleted=1');
    exit;
}

// Fetch all appointments with property & client details
$appointments = $db->query("
    SELECT a.*, 
           p.title AS property_title,
           p.location AS property_location,
           u.name AS client_name,
           u.phone AS client_phone,
           u.email AS client_email,
           agent.name AS agent_name
    FROM appointments a
    LEFT JOIN properties p ON a.property_id = p.id
    LEFT JOIN users u ON a.client_id = u.id
    LEFT JOIN users agent ON p.agent_id = agent.id
    ORDER BY a.appointment_date DESC, a.appointment_time DESC
")->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Appointments • Admin • House Unlimited</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .admin-header {
            background: linear-gradient(135deg, #dc2626, #b91c1c);
            color: white;
            padding: 2.5rem;
            border-radius: 20px;
            margin-bottom: 2.5rem;
            text-align: center;
        }
        .admin-header h1 { margin: 0 0 0.5rem; font-size: 2.8rem; font-weight: 800; }

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

        th {
            background: #fee2e2;
            padding: 1.2rem 1rem;
            font-weight: 600;
            color: #991b1b;
            text-transform: uppercase;
            font-size: 0.9rem;
        }
        body.dark th { background: #450a0a; color: #fca5a5; }

        td { padding: 1.2rem 1rem; vertical-align: middle; }

        .property-info {
            font-weight: 600;
            color: #1e40af;
        }
        .client-info {
            font-size: 1.1rem;
            font-weight: 600;
        }

        .status-badge {
            padding: 0.5rem 1rem;
            border-radius: 50px;
            font-size: 0.85rem;
            font-weight: 700;
        }
        .status-pending { background: #fef3c7; color: #92400e; }
        .status-confirmed { background: #d1fae5; color: #065f46; }
        .status-completed { background: #dbeafe; color: #1e40af; }
        .status-cancelled { background: #fee2e2; color: #991b1b; }

        .action-btn {
            padding: 0.6rem 1rem;
            margin: 0 0.3rem;
            border: none;
            border-radius: 10px;
            cursor: pointer;
            font-size: 0.9rem;
            font-weight: 600;
        }
        .btn-confirm { background: #10b981; color: white; }
        .btn-complete { background: #3b82f6; color: white; }
        .btn-cancel { background: #ef4444; color: white; }
        .btn-delete { background: #991b1b; color: white; }

        .no-appointments {
            text-align: center;
            padding: 5rem 2rem;
            color: #64748b;
            font-size: 1.3rem;
        }
    </style>
</head>
<body>
    <?php include '../inc/header.php'; ?>

    <div class="container">
        <?php include '../inc/sidebar.php'; ?>

        <main class="main-content">
            <div class="admin-header">
                <h1>Property Inspections & Appointments</h1>
                <p>Full control over all client viewing schedules in Nigeria</p>
            </div>

            <?php if (isset($_GET['success'])): ?>
                <div style="background:#d1fae5; color:#065f46; padding:1.2rem; border-radius:12px; margin-bottom:1.5rem; text-align:center; font-weight:600;">
                    Appointment updated successfully!
                </div>
            <?php endif; ?>
            <?php if (isset($_GET['deleted'])): ?>
                <div style="background:#fee2e2; color:#991b1b; padding:1.2rem; border-radius:12px; margin-bottom:1.5rem; text-align:center;">
                    Appointment deleted permanently.
                </div>
            <?php endif; ?>

            <div class="filters">
                <input type="text" id="search" placeholder="Search client, property, agent..." oninput="filterTable()" />
                <select id="statusFilter" onchange="filterTable()">
                    <option value="">All Status</option>
                    <option value="pending">Pending</option>
                    <option value="confirmed">Confirmed</option>
                    <option value="completed">Completed</option>
                    <option value="cancelled">Cancelled</option>
                </select>
                <select id="dateFilter" onchange="filterTable()">
                    <option value="">All Dates</option>
                    <option value="today">Today</option>
                    <option value="tomorrow">Tomorrow</option>
                    <option value="this-week">This Week</option>
                    <option value="past">Past</option>
                </select>
            </div>

            <div class="table-container">
                <table id="appointmentsTable">
                    <thead>
                        <tr>
                            <th>Date & Time</th>
                            <th>Client</th>
                            <th>Property</th>
                            <th>Agent</th>
                            <th>Status</th>
                            <th>Notes</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="appointmentsBody">
                        <?php if (empty($appointments)): ?>
                            <tr><td colspan="7" class="no-appointments">No appointments scheduled yet.</td></tr>
                        <?php else: ?>
                            <?php foreach ($appointments as $a): ?>
                                <tr data-status="<?= $a['status'] ?>">
                                    <td>
                                        <strong><?= date('M j, Y', strtotime($a['appointment_date'])) ?></strong><br>
                                        <span style="color:#dc2626; font-weight:600;"><?= date('g:ia', strtotime($a['appointment_time'])) ?></span>
                                    </td>
                                    <td>
                                        <div class="client-info"><?= htmlspecialchars($a['client_name']) ?></div>
                                        <small><?= $a['client_phone'] ?> • <?= $a['client_email'] ?></small>
                                    </td>
                                    <td>
                                        <div class="property-info"><?= htmlspecialchars($a['property_title']) ?></div>
                                        <small><?= htmlspecialchars($a['property_location']) ?></small>
                                    </td>
                                    <td><?= htmlspecialchars($a['agent_name'] ?? '—') ?></td>
                                    <td>
                                        <span class="status-badge status-<?= $a['status'] ?>">
                                            <?= ucfirst($a['status']) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?= $a['notes'] ? htmlspecialchars(substr($a['notes'], 0, 60)).'...' : '<em>No notes</em>' ?>
                                    </td>
                                    <td>
                                        <form method="POST" style="display:inline;">
                                            <input type="hidden" name="appt_id" value="<?= $a['id'] ?>">
                                            <?php if ($a['status'] === 'pending'): ?>
                                                <button type="submit" name="action" value="confirm" class="action-btn btn-confirm">Confirm</button>
                                            <?php endif; ?>
                                            <?php if (in_array($a['status'], ['pending', 'confirmed'])): ?>
                                                <button type="submit" name="action" value="complete" class="action-btn btn-complete">Complete</button>
                                                <button type="submit" name="action" value="cancel" class="action-btn btn-cancel">Cancel</button>
                                            <?php endif; ?>
                                        </form>
                                        <a href="appointments.php?delete=<?= $a['id'] ?>" 
                                           onclick="return confirm('Delete this appointment permanently?')"
                                           class="action-btn btn-delete">Delete</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>

    <script>
        function filterTable() {
            const search = document.getElementById('search').value.toLowerCase();
            const status = document.getElementById('statusFilter').value;
            const dateFilter = document.getElementById('dateFilter').value;

            document.querySelectorAll('#appointmentsBody tr').forEach(row => {
                const text = row.textContent.toLowerCase();
                const rowStatus = row.dataset.status;

                let show = true;
                if (search && !text.includes(search)) show = false;
                if (status && rowStatus !== status) show = false;

                row.style.display = show ? '' : 'none';
            });
        }
    </script>
</body>
</html>