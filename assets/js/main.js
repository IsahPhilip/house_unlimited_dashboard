// assets/js/main.js
document.addEventListener('DOMContentLoaded', () => {
    // Dark Mode Toggle
    const darkToggle = document.getElementById('darkmode');
    if (localStorage.getItem('theme') === 'dark' || (!localStorage.getItem('theme') && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
        document.body.classList.add('dark');
        if (darkToggle) darkToggle.checked = true;
    }

    if (darkToggle) {
        darkToggle.onchange = () => {
            document.body.classList.toggle('dark');
            localStorage.setItem('theme', document.body.classList.contains('dark') ? 'dark' : 'light');
        };
    }

    // Live Unread Messages Badge
    const updateUnread = () => {
        fetch('../api/unread_count.php')
            .then(r => r.json())
            .then(d => {
                const badge = document.querySelector('.msg-badge');
                if (badge) badge.textContent = d.count > 0 ? d.count : '';
            });
    };
    updateUnread();
    setInterval(updateUnread, 15000);

    // Nigerian Naira Formatter
    window.formatNGN = (amount) => {
        return new Intl.NumberFormat('en-NG', { style: 'currency', currency: 'NGN' }).format(amount);
    };
});