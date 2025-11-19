<!-- Add this section inside dashboard/property_detail.php after description -->
<?php if (in_array($_SESSION['user']['role'], ['admin', 'agent'])): ?>
<div style="margin:3rem 0; background:#f8fafc; padding:2rem; border-radius:16px;">
    <h2>Update Construction Progress</h2>
    <form method="POST" action="../admin/update_progress.php">
        <input type="hidden" name="property_id" value="<?= $property['id'] ?>">
        <div style="display:grid; grid-template-columns:1fr 1fr 1fr auto; gap:1rem; margin-top:1rem;">
            <select name="phase" required style="padding:1rem; border-radius:12px; border:2px solid #e2e8f0;">
                <option>Foundation</option>
                <option>Structure</option>
                <option>Roofing</option>
                <option>Plumbing & Electrical</option>
                <option>Finishing</option>
                <option>Handover</option>
            </select>
            <input type="number" name="percentage" min="0" max="100" placeholder="%" required style="padding:1rem; border-radius:12px;">
            <input type="text" name="description" placeholder="Update note..." style="padding:1rem; border-radius:12px;">
            <button type="submit" style="background:#10b981; color:white; border:none; padding:1rem 2rem; border-radius:12px; font-weight:600;">
                Update Progress
            </button>
        </div>
    </form>
</div>
<?php endif; ?>