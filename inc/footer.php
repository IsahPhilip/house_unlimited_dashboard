<footer style="background:#1e293b; color:#94a3b8; text-align:center; padding:2rem; margin-top:4rem; font-size:0.95rem;">
    <p>
        &copy; <?= date('Y') ?> <strong>House Unlimited & Land Services Nigeria</strong><br>
        <small>Lagos • Abuja • Port Harcourt | Powered by Nigerian Innovation</small><br>
        <small>Secure • Fast • Trusted</small>
    </p>
</footer>

<script src="<?= BASE_URL ?>/assets/js/main.js"></script>
<?php if (isset($extra_scripts)): ?>
    <?= $extra_scripts ?>
<?php endif; ?>
</body>
</html>