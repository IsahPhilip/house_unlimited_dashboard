<?php
require '../inc/config.php';
require '../inc/auth.php';

$user_id = $_SESSION['user']['id'];

// Fetch the referral_code from the database for the current user
$stmt_code = $db->prepare("SELECT referral_code FROM users WHERE id = ?");
$stmt_code->bind_param('i', $user_id);
$stmt_code->execute();
$result_code = $stmt_code->get_result();
$user_data = $result_code->fetch_assoc();
$referral_code = $user_data['referral_code'] ?? ''; // Fallback if no code found
$stmt_code->close();
$referral_link = 'http://' . $_SERVER['HTTP_HOST'] . '/register.php?ref=' . $referral_code;

// Fetch referral data
$stmt = $db->prepare("
    SELECT 
        u.name AS referee_name,
        u.created_at AS signup_date,
        r.status
    FROM referrals r
    JOIN users u ON r.referee_id = u.id
    WHERE r.referrer_id = ?
    ORDER BY u.created_at DESC
");
$stmt->bind_param('i', $user_id);
$stmt->execute();
$referrals = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Calculate stats
$total_referrals = count($referrals);
$pending_referrals = count(array_filter($referrals, fn($r) => $r['status'] === 'pending'));
$completed_referrals = $total_referrals - $pending_referrals;

// Fetch total bonus earned
$stmt = $db->prepare("SELECT SUM(bonus_earned) AS total_bonus FROM referrals WHERE referrer_id = ?");
$stmt->bind_param('i', $user_id);
$stmt->execute();
$total_bonus = $stmt->get_result()->fetch_assoc()['total_bonus'] ?? 0;
$stmt->close();

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Referral Program • Dashboard</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .page-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 2.5rem; }
        .page-header h1 { margin: 0; font-size: 2.4rem; color: #1e40af; }
        body.dark .page-header h1 { color: #93c5fd; }

        .referral-card { background: white; padding: 2rem; border-radius: 18px; box-shadow: 0 10px 30px rgba(0,0,0,0.08); margin-bottom: 2rem; }
        body.dark .referral-card { background: #1e1e1e; }
        .referral-card h2 { margin-top: 0; font-size: 1.5rem; color: #1e293b; }
        body.dark .referral-card h2 { color: #e2e8f0; }

        .referral-code-box { background: #f1f5f9; border: 2px dashed #cbd5e1; padding: 1.5rem; border-radius: 12px; text-align: center; margin: 1.5rem 0; }
        body.dark .referral-code-box { background: #334155; border-color: #475569; }
        .referral-code { font-size: 2rem; font-weight: 700; color: #1e40af; letter-spacing: 2px; }
        body.dark .referral-code { color: #60a5fa; }
        .referral-link { margin-top: 1rem; }
        .referral-link input { width: 100%; padding: 0.8rem; border-radius: 8px; border: 1px solid #cbd5e1; background: #fff; text-align: center; }
        body.dark .referral-link input { background: #475569; border-color: #64748b; color: #e2e8f0; }

        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1.5rem; margin-bottom: 2rem; }
        .stat-card { background: #f8fafc; padding: 1.5rem; border-radius: 14px; text-align: center; }
        body.dark .stat-card { background: #334155; }
        .stat-value { font-size: 2.5rem; font-weight: 800; color: #1e40af; }
        body.dark .stat-value { color: #60a5fa; }
        .stat-label { color: #64748b; font-size: 1rem; }

        .referrals-table { width: 100%; border-collapse: collapse; }
        .referrals-table th, .referrals-table td { padding: 1rem 1.2rem; text-align: left; border-bottom: 1px solid #e2e8f0; }
        body.dark .referrals-table th, body.dark .referrals-table td { border-bottom-color: #334155; }
        .referrals-table th { background: #f8fafc; font-weight: 600; color: #475569; }
        body.dark .referrals-table th { background: #334155; color: #94a3b8; }
        .status-badge { padding: 0.3rem 0.8rem; border-radius: 50px; font-size: 0.85rem; font-weight: 600; }
        .status-pending { background: #fefce8; color: #a16207; }
        .status-completed { background: #ecfdf5; color: #065f46; }
    </style>
</head>
<body>
    <?php include '../inc/header.php'; ?>
    <div class="container">
        <?php include '../inc/sidebar.php'; ?>

        <main class="main-content">
            <div class="page-header">
                <h1>Referral Program</h1>
            </div>

            <div class="referral-card">
                <h2>Your Referral Code</h2>
                <p>Share your code with friends. When they sign up and meet the requirements, you'll both earn a bonus!</p>
                <div class="referral-code-box">
                    <div class="referral-code" id="referralCode"><?= htmlspecialchars($referral_code) ?></div>
                </div>
                <div class="referral-link">
                    <label>Your Unique Referral Link</label>
                    <input type="text" value="<?= htmlspecialchars($referral_link) ?>" readonly onclick="this.select()">
                </div>
                <button onclick="copyToClipboard()" style="background:#3b82f6;color:white;border:none;padding:1rem;border-radius:12px;cursor:pointer;font-weight:600;margin-top:1.5rem;width:100%;font-size:1rem;">Copy Code & Link</button>
            </div>

            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-value"><?= $total_referrals ?></div>
                    <div class="stat-label">Total Referrals</div>
                </div>
                <div class="stat-card">
                    <div class="stat-value"><?= $completed_referrals ?></div>
                    <div class="stat-label">Completed</div>
                </div>
                <div class="stat-card">
                    <div class="stat-value">$<?= number_format($total_bonus, 2) ?></div>
                    <div class="stat-label">Total Bonus Earned</div>
                </div>
            </div>

            <div class="referral-card">
                <h2>Your Referred Users</h2>
                <?php if (empty($referrals)): ?>
                    <p>You haven't referred anyone yet. Share your code to get started!</p>
                <?php else: ?>
                    <table class="referrals-table">
                        <thead>
                            <tr>
                                <th>User</th>
                                <th>Date Joined</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($referrals as $ref): ?>
                                <tr>
                                    <td><?= htmlspecialchars($ref['referee_name']) ?></td>
                                    <td><?= date('M d, Y', strtotime($ref['signup_date'])) ?></td>
                                    <td>
                                        <span class="status-badge status-<?= $ref['status'] ?>">
                                            <?= ucfirst($ref['status']) ?>
                                        </span>
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
        function copyToClipboard() {
            const code = document.getElementById('referralCode').innerText;
            const link = document.querySelector('.referral-link input').value;
            const textToCopy = `My referral code: ${code}\nMy referral link: ${link}`;
            navigator.clipboard.writeText(textToCopy).then(() => {
                alert('Referral code and link copied to clipboard!');
            }, (err) => {
                alert('Failed to copy.');
            });
        }
    </script>
</body>
</html>
