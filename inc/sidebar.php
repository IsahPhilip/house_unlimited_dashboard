<?php
// inc/sidebar.php
require_once 'auth.php';
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
                    Dashboard
                </a>
            </li>
            <li>
                <a href="properties.php" class="<?= in_array(basename($_SERVER['PHP_SELF']), ['properties.php', 'property_detail.php']) ? 'active' : '' ?>">
                     Properties
                 </a>
                <ul class="sub-menu">
                    <li><a href="properties.php" class="<?= basename($_SERVER['PHP_SELF']) === 'properties.php' ? 'active' : '' ?>">All Properties</a></li>
                    <li><a href="add_property.php" class="<?= basename($_SERVER['PHP_SELF']) === 'add_property.php' ? 'active' : '' ?>">Add New</a></li>
                </ul>
            </li>
            <li>
                <a href="appointments.php" class="<?= basename($_SERVER['PHP_SELF']) === 'appointments.php' ? 'active' : '' ?>">
                    Appointments
                </a>
            </li>
            <li>
                <a href="messages.php" class="<?= basename($_SERVER['PHP_SELF']) === 'messages.php' ? 'active' : '' ?>">
                    Messages 
                    <span class="msg-badge" id="sidebarMsgBadge"></span>
                </a>
            </li>
            <li>
                <a href="documents.php" class="<?= basename($_SERVER['PHP_SELF']) === 'documents.php' ? 'active' : '' ?>">
                    Documents
                </a>
            </li>
            <li>
                <a href="transactions.php" class="<?= basename($_SERVER['PHP_SELF']) === 'transactions.php' ? 'active' : '' ?>">
                    Payments
                </a>
            </li>
            <li>
                <a href="profile.php" class="<?= basename($_SERVER['PHP_SELF']) === 'profile.php' ? 'active' : '' ?>">
                    Profile Settings
                </a>
            </li>
        </ul>

        <?php if (is_admin()): ?>
        <hr style="border-color:#334155; margin:1.5rem 0;">
        <p style="color:#64748b; font-size:0.85rem; padding:0 1.5rem; margin-bottom:0.8rem;">ADMIN ZONE</p>
        <ul>
            <li><a href="../admin/index.php" style="color:#f59e0b;">Admin Dashboard</a></li>
            <li><a href="../admin/users.php">Manage Users</a></li>
            <li><a href="../admin/properties.php">All Properties</a></li>
            <li><a href="../admin/payments.php">All Payments</a></li>
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
    bottom: 0;
    overflow-y: auto;
    z-index: 999;
    padding: 1.5rem 0;
    transition: all 0.3s;
    box-shadow: 4px 0 20px rgba(0,0,0,0.2);
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
.sidebar-nav ul {
    list-style: none;
    padding: 0;
    margin: 0;
}
.sidebar-nav a {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 1rem 1.5rem;
    color: #cbd5e1;
    text-decoration: none;
    transition: all 0.2s;
    border-left: 4px solid transparent;
}
.sidebar-nav a:hover, .sidebar-nav a.active {
    background: #334155;
    color: white;
    border-left-color: #3b82f6;
}
.msg-badge {
    background: #ef4444;
    color: white;
    font-size: 0.75rem;
    padding: 0.2rem 0.6rem;
    border-radius: 50px;
    min-width: 20px;
    text-align: center;
}
.sidebar-footer {
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
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