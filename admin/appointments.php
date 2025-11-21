<?php
// admin/appointments.php — FIXED + LUXURY 2025 ADMIN DESIGN
require '../inc/config.php';
require '../inc/auth.php';

if ($_SESSION['user']['role'] !== 'admin') {
    header('Location: ../dashboard/');
    exit;
}

// Handle status update
if ($_POST['action'] ?? '' && $_POST['appt_id'] ?? '') {
    $appt_id = intval($_POST['appt_id']);
    $action = $_POST['action'];

    $status_map = ['confirm' => 'confirmed', 'complete' => 'completed', 'cancel' => 'cancelled'];
    if (isset($status_map[$action])) {
        $status = $status_map[$action];
        $db->query("UPDATE appointments SET status = '$status' WHERE id = $appt_id");
        if ($action === 'complete') {
            log_activity("Client viewed property: 6-Bedroom Fully Detached in Osapa London");
            log_activity("Viewing completed – Client loved the property");
        }
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

// FIXED QUERY — Uses your actual column names
$appointments = $db->query("
    SELECT 
        a.id,
        a.viewing_date,
        a.viewing_time,
        a.message AS notes,
        a.status,
        a.created_at,
        p.title AS property_title,
        p.location AS property_location,
        c.name AS client_name,
        c.phone AS client_phone,
        c.email AS client_email,
        ag.name AS agent_name,
        ag.phone AS agent_phone
    FROM appointments a
    LEFT JOIN properties p ON a.property_id = p.id
    LEFT JOIN users c ON a.user_id = c.id
    LEFT JOIN users ag ON a.agent_id = ag.id
    ORDER BY a.viewing_date DESC, a.viewing_time DESC
")->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Appointments • Admin • House Unlimited</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Playfair+Display:wght@700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
    <style>
        :root {
            --red: #dc2626;
            --green: #10b981;
            --blue: #3b82f6;
            --gray: #64748b;
            --light: #f8fafc;
            --dark: #0f172a;
        }
        body.dark { --light: #1e1e1e; --dark: #f1f5f9; }

        .admin-header {
            background: linear-gradient(135deg, #7c2d12, var(--red));
            color: white;
            padding: 3rem 2rem;
            border-radius: 24px;
            margin-bottom: 2.5rem;
            text-align: center;
            box-shadow: 0 20px 40px rgba(220,38,38,0.3);
        }
        .admin-header h1 {
            font-family: 'Playfair Display', serif;
            font-size: 3.5rem;
            margin: 0 0 0.5rem;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2.5rem;
        }
        .stat-card {
            background: white;
            padding: 1.8rem;
            border-radius: 20px;
            text-align: center;
            box-shadow: 0 10px 30px rgba(0,0,0,0.08);
        }
        body.dark .stat-card { background: #1e1e1e; }
        .stat-value {
            font-size: 2.8rem;
            font-weight: 800;
            color: var(--red);
            margin: 0.5rem 0;
        }
        body.dark .stat-value { color: #fca5a5; }

        .table-container {
            background: white;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 15px 40px rgba(0,0,0,0.1);
            margin-top: 2rem;
        }
        body.dark .table-container { background: #1e1e1e; }

        table {
            width: 100%;
            border-collapse: collapse;
        }
        th {
            background: linear-gradient(135deg, #fee2e2, #fecaca);
            color: #991b1b;
            padding: 1.5rem 1rem;
            text-transform: uppercase;
            font-size: 0.9rem;
            letter-spacing: 1px;
            font-weight: 700;
        }
        body.dark th { background: #450a0a; color: #fca5a5; }

        td {
            padding: 1.4rem 1rem;
            border-bottom: 1px solid #e2e8f0;
        }
        body.dark td { border-color: #334155; }

        tr:hover {
            background: #fef2f2;
        }
        body.dark tr:hover { background: #2d1b1b; }

        .status-badge {
            padding: 0.6rem 1.2rem;
            border-radius: 50px;
            font-weight: 700;
            font-size: 0.85rem;
            text-transform: uppercase;
        }
        .status-pending { background: #fef3c7; color: #92400e; }
        .status-confirmed { background: #d1fae5; color: #065f46; }
        .status-completed { background: #dbeafe; color: #1e40af; }
        .status-cancelled { background: #fee2e2; color: #991b1b; }

        .action-btn {
            padding: 0.7rem 1.2rem;
            margin: 0.3rem;
            border: none;
            border-radius: 12px;
            cursor: pointer;
            font-weight: 600;
            font-size: 0.9rem;
            transition: all 0.3s;
        }
        .btn-confirm { background: var(--green); color: white; }
        .btn-complete { background: var(--blue); color: white; }
        .btn-cancel { background: #f97316; color: white; }
        .btn-delete { background: #991b1b; color: white; }

        .action-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.2);
        }

        .no-appointments {
            text-align: center;
            padding: 6rem 2rem;
            color: var(--gray);
            font-size: 1.5rem;
        }
        .no-appointments i {
            font-size: 4rem;
            margin-bottom: 1rem;
            opacity: 0.3;
        }
    </style>
</head>
<body>
    <?php include '../inc/header.php'; ?>
    <div class="container">
        <?php include '../inc/sidebar.php'; ?>

        <main class="main-content">
            <div class="admin-header">
                <h1>All Appointments</h1>
                <p>Manage property viewings across Nigeria</p>
            </div>

            <!-- Stats -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-value"><?= count($appointments) ?></div>
                    <div>Total</div>
                </div>
                <div class="stat-card">
                    <div class="stat-value"><?= count(array_filter($appointments, fn($a) => $a['status'] === 'pending')) ?></div>
                    <div>Pending</div>
                </div>
                <div class="stat-card">
                    <div class="stat-value"><?= count(array_filter($appointments, fn($a) => $a['status'] === 'confirmed')) ?></div>
                    <div>Confirmed</div>
                FOURTH CARD: Completed
                <div class="stat-card">
                    <div class="stat-value"><?= count(array_filter($appointments, fn($a) => $a['status'] === 'completed')) ?></div>
                    <div>Completed</div>
                </div>
            </div>

            <?php if (isset($_GET['success'])): ?>
                <div style="background:#d1fae5;color:#065f46;padding:1.2rem;border-radius:16px;margin:1.5rem 0;text-align:center;font-weight:600;">
                    Appointment updated successfully!
                </div>
            <?php endif; ?>

            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>Date & Time</th>
                            <th>Client</th>
                            <th>Property</th>
                            <th>Agent</th>
                            <th>Status</th>
                            <th>Message</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($appointments)): ?>
                            <tr>
                                <td colspan="7" class="no-appointments">
                                    <i class="fas fa-calendar-times"></i>
                                    <p>No appointments scheduled yet.</p>
                                </td>
                            </tr>
                        <?php else: foreach ($appointments as $a): ?>
                            <tr>
                                <td>
                                    <strong><?= date('M j, Y', strtotime($a['viewing_date'])) ?></strong><br>
                                    <span style="color:#dc2626;font-weight:700;"><?= date('g:i A', strtotime($a['viewing_time'])) ?></span>
                                </td>
                                <td>
                                    <div style="font-weight:600; color:#1e40af;"><?= htmlspecialchars($a['client_name'] ?? '—') ?></div>
                                    <small><?= $a['client_phone'] ?? '' ?> • <?= $a['client_email'] ?? '' ?></small>
                                </td>
                                <td>
                                    <div style="font-weight:600; color:#1e40af;"><?= htmlspecialchars($a['property_title'] ?? '—') ?></div>
                                    <small><?= htmlspecialchars($a['property_location'] ?? '') ?></small>
                                </td>
                                <td>
                                    <div style="font-weight:600;"><?= htmlspecialchars($a['agent_name'] ?? '—') ?></div>
                                    <small><?= $a['agent_phone'] ?? '' ?></small>
                                </td>
                                <td>
                                    <span class="status-badge status-<?= $a['status'] ?>">
                                        <?= ucfirst($a['status']) ?>
                                    </span>
                                </td>
                                <td>
                                    <?= $a['notes'] ? htmlspecialchars(substr($a['notes'], 0, 80)).'...' : '<em style="color:#94a3b8;">No message</em>' ?>
                                </td>
                                <td>
                                    <form method="POST" style="display:inline;">
                                        <input type="hidden" name="appt_id" value="<?= $a['id'] ?>">
                                        <?php if ($a['status'] === 'pending'): ?>
                                            <button type="submit" name="action" value="confirm" class="action-btn btn-confirm">Confirm</button>
                                        <?php endif; ?>
                                        <?php if (in_array($a['status'], ['pending','confirmed'])): ?>
                                            <button type="submit" name="action" value="complete" class="action-btn btn-complete">Complete</button>
                                            <button type="submit" name="action" value="cancel" class="action-btn btn-cancel">Cancel</button>
                                        <?php endif; ?>
                                    </form>
                                    <a href="?delete=<?= $a['id'] ?>" 
                                       onclick="return confirm('Delete permanently?')"
                                       class="action-btn btn-delete">Delete</a>
                                </td>
                            </tr>
                        <?php endforeach; endif; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>
</body>
</html>