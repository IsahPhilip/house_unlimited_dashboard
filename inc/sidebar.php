<?php
// inc/sidebar.php — HOUSE UNLIMITED NIGERIA | FINAL LUXURY EDITION 2025
require_once 'auth.php';

function icon($name) {
    $icons = [
        'dashboard'     => '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.7" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z"/></svg>',
        'properties'    => '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.7" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 21v-4.875c0-.621.504-1.125 1.125-1.125h5.25c.621 0 1.125.504 1.125 1.125V21m-7.5-12.75h7.5m-7.5 3.75h7.5m-9-9h13.5m-13.5 0v13.5m13.5-13.5L12 10.5m0 0L4.5 4.5"/></svg>',
        'my-properties' => '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.7" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>',
        'appointments'  => '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.7" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.75 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0h18M9 12h6m-6 4h6"/></svg>',
        'messages'      => '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.7" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M8.625 12a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0H8.25m4.125 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0H12m4.125 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0h-.375M21 12c0 4.556-4.03 8.25-9 8.25a9.764 9.764 0 01-2.555-.337A5.972 5.972 0 015.41 20.97a5.97 5.97 0 01-.474-.065 4.48 4.48 0 00.978-2.025c.09-.457-.133-.902-.467-.882C3.267 17.943 2.25 16.425 2.25 15c0-4.556 4.03-8.25 9-8.25s9 3.694 9 8.25z"/></svg>',
        'documents'     => '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.7" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m6 10.5h-6m6-4.5h-6m3 9v-6m-3 6h6"/></svg>',
        'payments'      => '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.7" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 4.5h3m-3.75 3h15a2.25 2.25 0 002.25-2.25V6.75A2.25 2.25 0 0019.5 4.5h-15A2.25 2.25 0 002.25 6.75v10.5A2.25 2.25 0 004.5 19.5z"/></svg>',
        'progress'      => '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.7" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3 7.5L7.5 3m0 0L12 7.5M7.5 3v13.5m9-13.5L21 7.5m-4.5-4.5V16.5"/></svg>',
        'profile'       => '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.7" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M17.982 18.725A7.488 7.488 0 0012 15.75a7.488 7.488 0 00-5.982 2.975m11.963 0a9 9 0 10-11.963 0m11.963 0A8.966 8.966 0 0112 21a8.966 8.966 0 01-5.982-2.275M15 9.75a3 3 0 11-6 0 3 3 0 016 0z"/></svg>',
        'admin'         => '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.7" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z"/></svg>',
        'users'         => '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.7" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-4.67c.12-.318.232-.656.328-1.014a.75.75 0 011.498-.07M15 19.128a9.38 9.38 0 002.625.372M4.5 10.5a7.5 7.5 0 1115 0 7.5 7.5 0 01-15 0z"/></svg>',
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
    padding: 2rem 1.sidebar-header .logo h2 {
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

.user-info { margin-top: 1.2rem; }
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
    margin: 4px 12px;
    transition: all 0.25s ease;
    font-weight: 500;
}
.nav-list a svg { width: 10px; height: 10px; flex-shrink: 0; }
.nav-list a:hover { background: rgba(59,130,246,0.15); color: white; transform: translateX(6px); }
.nav-list a.active { 
    background: linear-gradient(90deg, #3b82f6, transparent);
    color: white; 
    font-weight: 600;
    border-left: 4px solid #60a5fa;
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