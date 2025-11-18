<?php
// inc/sidebar.php
require_once 'auth.php';

// Helper function to render SVG icons
function render_icon($name, $class = 'w-6 h-6') {
    $icons = [
        'dashboard' => '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="'.$class.'"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z" /></svg>',
        'properties' => '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="'.$class.'"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 21h19.5m-18-18v18m10.5-18v18m6-13.5V21M6.75 6.75h.75m-.75 3h.75m-.75 3h.75m3-6h.75m-.75 3h.75m-.75 3h.75M9 21v-3.375c0-.621.504-1.125 1.125-1.125h3.75c.621 0 1.125.504 1.125 1.125V21" /></svg>',
        'appointments' => '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="'.$class.'"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0h18M-4.5 12h22.5" /></svg>',
        'messages' => '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="'.$class.'"><path stroke-linecap="round" stroke-linejoin="round" d="M20.25 8.511c.884.284 1.5 1.128 1.5 2.097v4.286c0 1.136-.847 2.1-1.98 2.193l-3.72 3.72a1.125 1.125 0 01-1.59 0l-3.72-3.72A2.123 2.123 0 013 14.894V8.511c0-.97.616-1.813 1.5-2.097l6.75-3.375a1.125 1.125 0 011.5 0l6.75 3.375zM16.5 8.511L12 11.193l-4.5-2.682" /></svg>',
        'documents' => '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="'.$class.'"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" /></svg>',
        'payments' => '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="'.$class.'"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 002.25-2.25V6.75A2.25 2.25 0 0019.5 4.5h-15A2.25 2.25 0 002.25 6.75v10.5A2.25 2.25 0 004.5 19.5z" /></svg>',
        'profile' => '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="'.$class.'"><path stroke-linecap="round" stroke-linejoin="round" d="M17.982 18.725A7.488 7.488 0 0012 15.75a7.488 7.488 0 00-5.982 2.975m11.963 0a9 9 0 10-11.963 0m11.963 0A8.966 8.966 0 0112 21a8.966 8.966 0 01-5.982-2.275M15 9.75a3 3 0 11-6 0 3 3 0 016 0z" /></svg>',
        'admin' => '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="'.$class.'"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.286zm0 13.036h.008v.008h-.008v-.008z" /></svg>',
        'users' => '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="'.$class.'"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-4.67c.12-.318.232-.656.328-1.014a.75.75 0 011.498-.07M15 19.128a9.38 9.38 0 002.625.372M4.5 10.5a7.5 7.5 0 1115 0 7.5 7.5 0 01-15 0z" /></svg>',
    ];
    echo $icons[$name] ?? '';
}

$user = $_SESSION['user'];
?>
<aside class="sidebar">
    <div class="sidebar-header">
        <div class="logo">
            <h2>HU</h2>
        </div>
        <div class="user-info">
            <p><strong><?= escape($user['name']) ?></strong></p>
            <small style="color:#94a3b8; text-transform:capitalize;">
                <?= $user['role'] === 'admin' ? 'Administrator' : ucfirst($user['role']) ?> Panel
            </small>
        </div>
    </div>

    <nav class="sidebar-nav">
        <ul>
            <li>
                <a href="index.php" class="<?= basename($_SERVER['PHP_SELF']) === 'index.php' ? 'active' : '' ?>">
                    <?= render_icon('dashboard') ?>
                    <span>Dashboard</span>
                </a>
            </li>
            <li>
                <a href="properties.php" class="<?= in_array(basename($_SERVER['PHP_SELF']), ['properties.php', 'property_detail.php', 'add_property.php']) ? 'active' : '' ?>">
                    <?= render_icon('properties') ?>
                    <span>Properties</span>
                </a>
            </li>
            <li>
                <a href="appointments.php" class="<?= basename($_SERVER['PHP_SELF']) === 'appointments.php' ? 'active' : '' ?>">
                    <?= render_icon('appointments') ?>
                    <span>Appointments</span>
                </a>
            </li>
            <li>
                <a href="messages.php" class="<?= basename($_SERVER['PHP_SELF']) === 'messages.php' ? 'active' : '' ?>">
                    <?= render_icon('messages') ?>
                    <span>Messages</span>
                    <span class="msg-badge" id="sidebarMsgBadge"></span>
                </a>
            </li>
            <li>
                <a href="documents.php" class="<?= basename($_SERVER['PHP_SELF']) === 'documents.php' ? 'active' : '' ?>">
                    <?= render_icon('documents') ?>
                    <span>Documents</span>
                </a>
            </li>
            <li>
                <a href="transactions.php" class="<?= basename($_SERVER['PHP_SELF']) === 'transactions.php' ? 'active' : '' ?>">
                    <?= render_icon('payments') ?>
                    <span>Payments</span>
                </a>
            </li>
            <li>
                <a href="profile.php" class="<?= basename($_SERVER['PHP_SELF']) === 'profile.php' ? 'active' : '' ?>">
                    <?= render_icon('profile') ?>
                    <span>Profile Settings</span>
                </a>
            </li>
        </ul>

        <?php if (is_admin()): ?>
        <hr style="border-color:#334155; margin:1.5rem 0;">
        <p style="color:#64748b; font-size:0.85rem; padding:0 1.5rem; margin-bottom:0.8rem;">ADMIN ZONE</p>
        <ul>
            <li>
                <a href="../admin/index.php" style="color:#f59e0b;">
                    <?= render_icon('admin') ?>
                    <span>Admin Dashboard</span>
                </a>
            </li>
            <li>
                <a href="../admin/users.php">
                    <?= render_icon('users') ?>
                    <span>Manage Users</span>
                </a>
            </li>
            <!-- You can add icons for the other admin links too if you wish -->
            <!-- <li><a href="../admin/properties.php">All Properties</a></li> -->
            <!-- <li><a href="../admin/payments.php">All Payments</a></li> -->
        </ul>
        <?php endif; ?>
    </nav>

    <div class="sidebar-footer">
        <div class="theme-toggle">
            <input type="checkbox" id="darkmode" <?= isset($_COOKIE['theme']) && $_COOKIE['theme'] === 'dark' ? 'checked' : '' ?>>
            <label for="darkmode">Dark Mode</label>
        </div>
        <div style="margin-top:1rem; text-align:center; color:#64748b; font-size:0.8rem;">
            <p>Lagos, Nigeria<br><?= date('D, d M Y • h:i A') ?></p>
        </div>
    </div>
</aside>

<style>
.sidebar {
    width: 280px;
    background: #1e293b;
    color: #e2e8f0;
    position: fixed;
    top: 0;
    left: 0;
    height: 100vh;
    z-index: 999;
    transition: all 0.3s;
    box-shadow: 4px 0 20px rgba(0,0,0,0.2);
    display: flex;
    flex-direction: column;
}
.sidebar-header {
    padding: 0 1.5rem 1.5rem;
    border-bottom: 1px solid #334155;
}
.logo h2 {
    width: 60px;
    height: 60px;
    background: #3b82f6;
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 16px;
    font-size: 1.8rem;
    font-weight: 800;
    margin-bottom: 1rem;
}
.sidebar-nav {
    flex-grow: 1;
    overflow-y: auto;
}
.sidebar-nav ul {
    list-style: none;
    padding: 0;
    margin: 0;
}
.sidebar-nav a {
    display: flex;
    align-items: center;
    gap: 0.9rem;
    padding: 1rem 1.5rem;
    color: #cbd5e1;
    text-decoration: none;
    transition: all 0.2s;
    border-left: 4px solid transparent;
    font-weight: 500;
}
.sidebar-nav a svg {
    width: 22px;
    height: 22px;
    stroke-width: 1.5;
}
.sidebar-nav a:hover, .sidebar-nav a.active {
    background: #334155;
    color: white;
    border-left-color: #3b82f6;
}
.msg-badge {
    margin-left: auto;
    background: #ef4444;
    color: white;
    font-size: 0.75rem;
    padding: 0.2rem 0.6rem;
    border-radius: 50px;
    min-width: 20px;
    text-align: center;
}
.sidebar-footer {
    margin-top: auto; /* Pushes footer to the bottom */
    padding: 1.5rem;
    background: #0f172a;
    border-top: 1px solid #334155;
}
.theme-toggle {
    display: flex;
    align-items: center;
    gap: 0.8rem;
    cursor: pointer;
}
.theme-toggle input { 
    width: 48px; 
    height: 24px; 
    appearance: none; 
    background: #334155; 
    border-radius: 50px; 
    position: relative; 
    cursor: pointer;
}
.theme-toggle input::after {
    content: '';
    position: absolute;
    top: 3px;
    left: 4px;
    width: 18px;
    height: 18px;
    background: white;
    border-radius: 50%;
    transition: 0.3s;
}
.theme-toggle input:checked { background: #3b82f6; }
.theme-toggle input:checked::after { left: 26px; }

@media (max-width: 992px) {
    .sidebar { 
        transform: translateX(-100%);
        position: fixed;
    }
    .sidebar.active { transform: translateX(0); }
    main.main-content { margin-left: 0 !important; }
}
</style>

<script>
// Live unread message badge update
function updateSidebarBadge() {
    fetch('../api/unread_count.php')
        .then(r => r.json())
        .then(data => {
            const badge = document.getElementById('sidebarMsgBadge');
            if (badge) {
                badge.textContent = data.count > 0 ? data.count : '';
                badge.style.display = data.count > 0 ? 'inline' : 'none';
            }
        });
}

// Dark mode toggle
document.getElementById('darkmode')?.addEventListener('change', function() {
    if (this.checked) {
        document.body.classList.add('dark');
        document.cookie = "theme=dark;path=/;max-age=31536000";
    } else {
        document.body.classList.remove('dark');
        document.cookie = "theme=light;path=/;max-age=31536000";
    }
});

// Initialize
updateSidebarBadge();
setInterval(updateSidebarBadge, 15000);
</script>