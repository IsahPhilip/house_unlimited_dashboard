<?php
// inc/sidebar.php — HOUSE UNLIMITED NIGERIA | FINAL LUXURY EDITION 2025
require_once 'auth.php';

function icon($name) {
    $icons = [
        'dashboard'     => '<i class="fa-solid fa-gauge-high"></i>',
        'properties'    => '<i class="fa-solid fa-building"></i>',
        'my-properties' => '<i class="fa-solid fa-house-user"></i>',
        'appointments'  => '<i class="fa-solid fa-calendar-check"></i>',
        'messages'      => '<i class="fa-solid fa-comments"></i>',
        'documents'     => '<i class="fa-solid fa-file-lines"></i>',
        'payments'      => '<i class="fa-solid fa-credit-card"></i>',
        'progress'      => '<i class="fa-solid fa-spinner"></i>',
        'profile'       => '<i class="fa-solid fa-user"></i>',
        'admin'         => '<i class="fa-solid fa-shield-halved"></i>',
        'users'         => '<i class="fa-solid fa-users"></i>',
        'referrals'     => '<i class="fa-solid fa-users-line"></i>',
    ];
    return $icons[$name] ?? '';
}

$user = $_SESSION['user'];
$page = basename($_SERVER['PHP_SELF']);
$is_active = fn($pages) => in_array($page, (array)$pages) ? 'active' : '';
?>

<aside class="sidebar">
    <div class="sidebar-header">
        <div class="logo">
            <h2>HU</h2>
        </div>
        <div class="user-info">
            <p class="name"><?= htmlspecialchars($user['name']) ?></p>
            <p class="role"><?= $user['role'] === 'admin' ? 'Administrator' : ucfirst($user['role']) ?></p>
        </div>
    </div>

    <nav class="sidebar-nav">
        <ul class="nav-list">
            <li><a href="index.php" class="<?= $is_active('index.php') ?>">
                <?= icon('dashboard') ?><span>Dashboard</span>
            </a></li>

            <li><a href="properties.php" class="<?= $is_active(['properties.php', 'property_detail.php', 'add_property.php', 'edit_property.php']) ?>">
                <?= icon('properties') ?><span>Properties</span>
            </a></li>

            <?php if ($user['role'] === 'client'): ?>
            <li><a href="my_properties.php" class="<?= $is_active('my_properties.php') ?>">
                <?= icon('my-properties') ?><span>My Properties</span>
            </a></li>
            <?php endif; ?>

            <li><a href="appointments.php" class="<?= $is_active('appointments.php') ?>">
                <?= icon('appointments') ?><span>Appointments</span>
            </a></li>

            <li><a href="messages.php" class="<?= $is_active('messages.php') ?>">
                <?= icon('messages') ?><span>Messages</span>
                <span class="msg-badge" id="sidebarMsgBadge"></span>
            </a></li>

            <li><a href="documents.php" class="<?= $is_active('documents.php') ?>">
                <?= icon('documents') ?><span>Documents</span>
            </a></li>

            <li><a href="transactions.php" class="<?= $is_active('transactions.php') ?>">
                <?= icon('payments') ?><span>Payments</span>
            </a></li>

            <li><a href="profile.php" class="<?= $is_active('profile.php') ?>">
                <?= icon('profile') ?><span>Profile</span>
            </a></li>

            <li><a href="referrals.php" class="<?= $is_active('referrals.php') ?>">
                <?= icon('referrals') ?><span>Referrals</span>
            </a></li>
        </ul>

        <?php if (in_array($user['role'], ['admin', 'agent'])): ?>
        <div class="nav-section">
            <p class="section-title">
                <?= $user['role'] === 'admin' ? 'ADMIN ZONE' : 'AGENT TOOLS' ?>
            </p>
            <ul class="nav-list">
                <?php if ($user['role'] === 'admin'): ?>
                <li><a href="../admin/index.php">
                    <?= icon('admin') ?><span>Admin Dashboard</span>
                </a></li>
                <li><a href="../admin/users.php">
                    <?= icon('users') ?><span>Manage Users</span>
                </a></li>
                <li><a href="../admin/properties.php">
                    <?= icon('properties') ?><span>All Properties</span>
                </a></li>
                <li><a href="../admin/appointments.php">
                    <?= icon('appointments') ?><span>All Appointments</span>
                </a></li>
                <?php endif; ?>

                <?php if ($user['role'] === 'agent'): ?>
                <li><a href="my_listings_progress.php">
                    <?= icon('progress') ?><span>Project Progress</span>
                </a></li>
                <li><a href="referrals.php">
                    <?= icon('referrals') ?><span>Referrals</span>
                </a></li>
                <?php endif; ?>
            </ul>
        </div>
        <?php endif; ?>
    </nav>

    <div class="sidebar-footer">
        <label class="theme-toggle">
            <input type="checkbox" id="darkmode" <?= $_COOKIE['theme'] ?? '' === 'dark' ? 'checked' : '' ?>>
            <span class="slider"></span>
            <span>Dark Mode</span>
        </label>
        <div class="location">
            <small>Lagos, Nigeria</small><br>
            <span><?= date('D, M j • h:i A') ?></span>
        </div>
    </div>
</aside>

<style>
.sidebar {
    width: 280px;
    background: linear-gradient(180deg, #0f172a 0%, #1e293b 100%);
    color: #e2e8f0;
    position: fixed;
    top: 0; left: 0; bottom: 0;
    z-index: 999;
    display: flex;
    flex-direction: column;
    box-shadow: 8px 0 30px rgba(0,0,0,0.3);
    font-family: 'Inter', sans-serif;
}

.sidebar-header {
    padding: 2rem 1.5rem;
    text-align: center;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 1.2rem;
}

.logo {
    width: 64px; height: 64px;
    background: linear-gradient(135deg, #3b82f6, #1d4ed8);
    color: white;
    border-radius: 18px;
    font-size: 2rem;
    font-weight: 900;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 8px 25px rgba(59,130,246,0.4);
}
.logo h2{
    width: 64px; height: 64px;
    background: linear-gradient(135deg, #3b82f6, #1d4ed8);
    color: white;
    border-radius: 18px;
    font-size: 2rem;
    font-weight: 900;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 8px 25px rgba(59,130,246,0.4);
}
.user-info {
    text-align: center;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 0.5rem;
}
.user-info .name { font-size: 1.1rem; font-weight: 600; margin: 0; }
.user-info .role { font-size: 0.875rem; color: #94a3b8; margin: 0.3rem 0 0; }
.sidebar-nav { flex: 1; overflow-y: auto; padding: 1rem 0; }
.nav-list { list-style: none; padding: 0; margin: 0; }
.nav-list a {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 0.9rem 1.5rem;
    color: #cbd5e1;
    text-decoration: none;
    border-radius: 0 12px 12px 0;
    margin-right: 12px;
    transition: all 0.25s ease;
    font-weight: 500;
    border-left: 4px solid transparent;
}
.nav-list a i { width: 22px; flex-shrink: 0; text-align: center; }
.nav-list a:hover { background: rgba(59,130,246,0.15); color: white; border-left-color: #60a5fa; }
.nav-list a.active { 
    background: linear-gradient(90deg, #3b82f6, transparent);
    color: white; 
    font-weight: 600;
    border-left-color: #60a5fa;
}

.msg-badge {
    margin-left: auto;
    background: #ef4444;
    color: white;
    font-size: 0.7rem;
    padding: 2px 8px;
    border-radius: 50px;
    min-width: 20px;
    font-weight: 700;
}

.nav-section {
    margin-top: 2rem;
    border-top: 1px solid #334155;
}
.section-title {
    color: #f59e0b;
    font-size: 0.75rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 1.2px;
    padding: 1rem 1.5rem;
    margin: 1rem 0 0.5rem;
}

.sidebar-footer {
    padding: 1.5rem;
    background: rgba(15,23,42,0.8);
    backdrop-filter: blur(10px);
    border-top: 1px solid #334155;
}
.theme-toggle {
    display: flex;
    align-items: center;
    gap: 12px;
    cursor: pointer;
    font-size: 0.95rem;
}
.theme-toggle input { display: none; }
.slider {
    position: relative;
    width: 52px; height: 28px;
    background: #334155;
    border-radius: 50px;
    transition: 0.3s;
}
.slider::before {
    content: '';
    position: absolute;
    width: 22px; height: 22px;
    left: 4px; top: 3px;
    background: white;
    border-radius: 50%;
    transition: 0.3s;
}
input:checked + .slider { background: #3b82f6; }
input:checked + .slider::before { transform: translateX(24px); }

.location { 
    text-align: center; 
    margin-top: 1rem; 
    font-size: 0.8rem; 
    color: #94a3b8;
}
.location span { font-weight: 600; color: #cbd5e1; }

@media (max-width: 992px) {
    .sidebar { transform: translateX(-100%); }
    .sidebar.active { transform: translateX(0); }
}
</style>

<script>
document.getElementById('darkmode')?.addEventListener('change', function() {
    document.body.classList.toggle('dark', this.checked);
    document.cookie = `theme=${this.checked ? 'dark' : 'light'};path=/;max-age=31536000`;
});

function updateMsgBadge() {
    fetch('../api/unread_count.php')
        .then(r => r.json())
        .then(d => {
            const b = document.getElementById('sidebarMsgBadge');
            if (b) {
                b.textContent = d.count > 0 ? d.count : '';
                b.style.display = d.count > 0 ? 'block' : 'none';
            }
        });
}
updateMsgBadge();
setInterval(updateMsgBadge, 20000);
</script>