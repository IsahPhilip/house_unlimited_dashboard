<?php
// admin/withdrawals.php
require '../inc/config.php';
require '../inc/auth.php';

if ($_SESSION['user']['role'] !== 'admin') {
    header('Location: ../dashboard/');
    exit;
}

// Fetch all withdrawal requests
$requests = $db->query("
    SELECT w.*, u.name AS user_name, u.email
    FROM withdrawals w
    JOIN users u ON w.user_id = u.id
    ORDER BY w.created_at DESC
")->fetch_all(MYSQLI_ASSOC);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Withdrawals • Admin</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .page-header h1 { font-size: 2.4rem; color: #1e40af; }
        body.dark .page-header h1 { color: #93c5fd; }
        .withdrawals-table {
            background: white;
            padding: 2rem;
            border-radius: 18px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.08);
        }
        body.dark .withdrawals-table { background: #1e1e1e; }
        .table { width: 100%; border-collapse: collapse; }
        .table th, .table td { padding: 1rem; text-align: left; border-bottom: 1px solid #e2e8f0; }
        body.dark .table th, body.dark .table td { border-color: #334155; }
        .status-badge { padding: 0.3rem 0.8rem; border-radius: 50px; font-size: 0.85rem; font-weight: 600; }
        .status-pending { background: #fefce8; color: #a16207; }
        .status-approved { background: #ecfdf5; color: #065f46; }
        .status-declined { background: #fee2e2; color: #991b1b; }
        .action-btn {
            padding: 0.5rem 1rem;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
        }
        .btn-approve { background: #10b981; color: white; }
        .btn-decline { background: #ef4444; color: white; }
    </style>
</head>
<body>
    <?php include '../inc/header.php'; ?>
    <div class="container">
        <?php include '../inc/sidebar.php'; ?>

        <main class="main-content">
            <div class="page-header">
                <h1>Manage Withdrawals</h1>
            </div>

            <div class="withdrawals-table">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>User</th>
                            <th>Amount</th>
                            <th>Bank Details</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($requests)): ?>
                            <tr><td colspan="6" style="text-align: center;">No withdrawal requests.</td></tr>
                        <?php else: ?>
                            <?php foreach ($requests as $req): ?>
                                <tr id="request-<?= $req['id'] ?>">
                                    <td><?= date('M d, Y', strtotime($req['created_at'])) ?></td>
                                    <td>
                                        <strong><?= htmlspecialchars($req['user_name']) ?></strong><br>
                                        <small><?= htmlspecialchars($req['email']) ?></small>
                                    </td>
                                    <td>₦<?= number_format($req['amount'], 2) ?></td>
                                    <td>
                                        <strong><?= htmlspecialchars($req['bank_name']) ?></strong><br>
                                        <?= htmlspecialchars($req['account_number']) ?><br>
                                        <small><?= htmlspecialchars($req['account_name']) ?></small>
                                    </td>
                                    <td>
                                        <span class="status-badge status-<?= $req['status'] ?>" id="status-<?= $req['id'] ?>">
                                            <?= ucfirst($req['status']) ?>
                                        </span>
                                    </td>
                                    <td id="actions-<?= $req['id'] ?>">
                                        <?php if ($req['status'] === 'pending'): ?>
                                            <button class="action-btn btn-approve" onclick="processWithdrawal(<?= $req['id'] ?>, 'approved')">Approve</button>
                                            <button class="action-btn btn-decline" onclick="processWithdrawal(<?= $req['id'] ?>, 'declined')">Decline</button>
                                        <?php else: ?>
                                            Processed
                                        <?php endif; ?>
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
        async function processWithdrawal(id, status) {
            if (!confirm(`Are you sure you want to ${status} this withdrawal?`)) {
                return;
            }

            const res = await fetch('../api/admin_process_withdrawal.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id, status })
            });
            const result = await res.json();

            if (res.ok) {
                document.getElementById('status-' + id).textContent = status.charAt(0).toUpperCase() + status.slice(1);
                document.getElementById('status-' + id).className = 'status-badge status-' + status;
                document.getElementById('actions-' + id).textContent = 'Processed';
                alert(result.message);
            } else {
                alert(result.error);
            }
        }
    </script>
</body>
</html>
