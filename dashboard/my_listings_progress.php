<?php
// dashboard/my_listings_progress.php — FINAL VERSION (Works with your real schema)
require '../inc/config.php';
require '../inc/auth.php';

if ($_SESSION['user']['role'] !== 'agent') {
    header('Location: index.php');
    exit;
}

$agent_id = $_SESSION['user']['id'];

// GET PROPERTIES + FEATURED IMAGE FROM property_images TABLE
$stmt = $db->prepare("
    SELECT 
        p.id,
        p.title,
        p.location,
        p.created_at,
        pi.image_path AS featured_image,
        COUNT(pr.id) AS total_phases,
        SUM(CASE WHEN pr.percentage = 100 THEN 1 ELSE 0 END) AS completed_phases
    FROM properties p
    LEFT JOIN property_images pi ON p.id = pi.property_id AND pi.is_featured = 1
    LEFT JOIN property_progress pr ON p.id = pr.property_id
    WHERE p.agent_id = ? AND p.status = 'active'
    GROUP BY p.id
    ORDER BY p.created_at DESC
");

$stmt->bind_param('i', $agent_id);
$stmt->execute();
$properties = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Project Progress • Agent Dashboard</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .page-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 2.5rem; flex-wrap: wrap; gap: 1rem; }
        .page-header h1 { margin: 0; font-size: 2.4rem; color: #1e40af; }
        body.dark .page-header h1 { color: #93c5fd; }

        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 1.5rem; margin-bottom: 3rem; }
        .stat-card { background: white; padding: 1.8rem; border-radius: 18px; box-shadow: 0 10px 30px rgba(0,0,0,0.08); text-align: center; }
        body.dark .stat-card { background: #1e1e1e; }
        .stat-value { font-size: 2.8rem; font-weight: 800; color: #1e40af; margin: 0.5rem 0; }
        body.dark .stat-value { color: #60a5fa; }
        .stat-label { color: #64748b; font-size: 1rem; }

        .property-card { background: white; border-radius: 20px; overflow: hidden; box-shadow: 0 12px 35px rgba(0,0,0,0.1); margin-bottom: 2rem; transition: transform 0.3s; }
        body.dark .property-card { background: #1e1e1e; }
        .property-card:hover { transform: translateY(-8px); box-shadow: 0 20px 50px rgba(0,0,0,0.18); }

        .property-header { height: 220px; background: #e2e8f0; position: relative; overflow: hidden; }
        .property-header img { width: 100%; height: 100%; object-fit: cover; transition: transform 0.4s; }
        .property-card:hover .property-header img { transform: scale(1.08); }
        .property-badge { position: absolute; top: 15px; right: 15px; background: #10b981; color: white; padding: 0.5rem 1rem; border-radius: 50px; font-size: 0.85rem; font-weight: 700; }

        .property-body { padding: 1.8rem; }
        .property-title { font-size: 1.5rem; margin: 0 0 0.5rem; color: #1e293b; }
        body.dark .property-title { color: #e2e8f0; }
        .property-location { color: #64748b; margin-bottom: 1.5rem; font-size: 0.95rem; }

        .progress-phases { display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 1rem; margin-top: 1.5rem; }
        .phase { background: #f8fafc; padding: 1rem; border-radius: 14px; border: 2px solid #e2e8f0; text-align: center; }
        body.dark .phase { background: #334155; border-color: #475569; }
        .phase.done { border-color: #10b981; background: #ecfdf5; }
        body.dark .phase.done { background: #166534; }
        .phase-name { font-weight: 600; font-size: 0.95rem; margin-bottom: 0.5rem; }
        .phase-bar { height: 10px; background: #e2e8f0; border-radius: 50px; overflow: hidden; margin: 0.5rem 0; }
        body.dark .phase-bar { background: #475569; }
        .phase-fill { height: 100%; background: #3b82f6; width: 0%; border-radius: 50px; transition: width 1.2s ease; }
        .phase.done .phase-fill { background: #10b981; }
        .phase-percentage { font-weight: 600; font-size: 0.9rem; color: #1e40af; }
        body.dark .phase-percentage { color: #93c5fd; }

        .update-btn { background: #3b82f6; color: white; border: none; padding: 1rem; border-radius: 12px; cursor: pointer; font-weight: 600; margin-top: 1.5rem; width: 100%; font-size: 1rem; }
        .update-btn:hover { background: #2563eb; }

        .no-properties { text-align: center; padding: 6rem 2rem; color: #64748b; font-size: 1.4rem; background: white; border-radius: 20px; box-shadow: 0 10px 30px rgba(0,0,0,0.05); }
        body.dark .no-properties { background: #1e1e1e; }
    </style>
</head>
<body>
    <?php include '../inc/header.php'; ?>
    <div class="container">
        <?php include '../inc/sidebar.php'; ?>

        <main class="main-content">
            <div class="page-header">
                <h1>Project Progress Updates</h1>
                <div>Real-time construction tracking for your listings</div>
            </div>

            <!-- Stats -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-value"><?= count($properties) ?></div>
                    <div class="stat-label">Active Listings</div>
                </div>
                <div class="stat-card">
                    <div class="stat-value"><?= array_sum(array_column($properties, 'completed_phases')) ?></div>
                    <div class="stat-label">Completed Phases</div>
                </div>
                <div class="stat-card">
                    <div class="stat-value">
                        <?php 
                        $total = array_sum(array_column($properties, 'total_phases'));
                        echo $total > 0 ? round(array_sum(array_column($properties, 'completed_phases')) / $total * 100) : 0 
                        ?>%
                    </div>
                    <div class="stat-label">Overall Progress</div>
                </div>
                <div class="stat-card">
                    <div class="stat-value">
                        <?= count(array_filter($properties, fn($p) => $p['completed_phases'] == 6)) ?>
                    </div>
                    <div class="stat-label">Fully Delivered</div>
                </div>
            </div>

            <?php if (empty($properties)): ?>
                <div class="no-properties">
                    <h3>No active listings yet</h3>
                    <p>Your construction projects will appear here once added.</p>
                    <a href="add_property.php" style="background:#3b82f6;color:white;padding:1rem 2rem;border-radius:12px;text-decoration:none;display:inline-block;margin-top:1rem;font-weight:600;">+ Add New Property</a>
                </div>
            <?php else: ?>
                <?php foreach ($properties as $prop): ?>
                    <?php
                    // Get all progress phases
                    $stmt2 = $db->prepare("SELECT phase, percentage FROM property_progress WHERE property_id = ? ORDER BY FIELD(phase, 'Foundation','Structure','Roofing','Plumbing & Electrical','Finishing','Handover')");
                    $stmt2->bind_param('i', $prop['id']);
                    $stmt2->execute();
                    $progress = $stmt2->get_result()->fetch_all(MYSQLI_ASSOC);
                    $stmt2->close();

                    $overall = $prop['total_phases'] > 0 ? round($prop['completed_phases'] / $prop['total_phases'] * 100) : 0;
                    $image = $prop['featured_image'] ?? 'default.jpg';
                    $image_path = file_exists("../assets/uploads/properties/$image") ? $image : 'default.jpg';
                    ?>
                    <div class="property-card">
                        <div class="property-header">
                            <img src="../assets/uploads/properties/<?= htmlspecialchars($image_path) ?>" alt="<?= htmlspecialchars($prop['title']) ?>">
                            <div class="property-badge"><?= $overall ?>% Complete</div>
                        </div>
                        <div class="property-body">
                            <h3 class="property-title"><?= htmlspecialchars($prop['title']) ?></h3>
                            <p class="property-location"><?= htmlspecialchars($prop['location']) ?></p>

                            <div class="progress-phases">
                                <?php 
                                $phases = ['Foundation','Structure','Roofing','Plumbing & Electrical','Finishing','Handover'];
                                foreach ($phases as $phase_name):
                                    $pct = 0;
                                    foreach ($progress as $pg) {
                                        if ($pg['phase'] === $phase_name) {
                                            $pct = (int)$pg['percentage'];
                                            break;
                                        }
                                    }
                                ?>
                                    <div class="phase <?= $pct == 100 ? 'done' : '' ?>">
                                        <div class="phase-name"><?= $phase_name ?></div>
                                        <div class="phase-bar">
                                            <div class="phase-fill" style="width: <?= $pct ?>%"></div>
                                        </div>
                                        <div class="phase-percentage"><?= $pct ?>%</div>
                                    </div>
                                <?php endforeach; ?>
                            </div>

                            <button onclick="openUpdateModal(<?= $prop['id'] ?>, '<?= addslashes(htmlspecialchars($prop['title'])) ?>')" class="update-btn">
                                Update Progress
                            </button>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </main>
    </div>

    <!-- Update Modal -->
    <div id="updateModal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.85);z-index:9999;display:flex;justify-content:center;align-items:center;">
        <div style="background:white;width:90%;max-width:520px;border-radius:20px;padding:2rem;box-shadow:0 20px 60px rgba(0,0,0,0.3);">
            <h2 style="margin:0 0 1rem;color:#1e293b;">Update Construction Progress</h2>
            <p style="margin:0 0 1.5rem;color:#475569;"><strong id="modalTitle"></strong></p>
            <form method="POST" action="../admin/update_progress.php">
                <input type="hidden" name="property_id" id="modalPropertyId">
                <div style="margin-bottom:1rem;">
                    <label style="display:block;margin-bottom:0.5rem;font-weight:600;">Phase</label>
                    <select name="phase" required style="width:100%;padding:1rem;border-radius:12px;border:2px solid #e2e8f0;font-size:1rem;">
                        <option>Foundation</option>
                        <option>Structure</option>
                        <option>Roofing</option>
                        <option>Plumbing & Electrical</option>
                        <option>Finishing</option>
                        <option>Handover</option>
                    </select>
                </div>
                <div style="margin-bottom:1rem;">
                    <label style="display:block;margin-bottom:0.5rem;font-weight:600;">Completion %</label>
                    <input type="number" name="percentage" min="0" max="100" value="100" required style="width:100%;padding:1rem;border-radius:12px;border:2px solid #e2e8f0;font-size:1rem;">
                </div>
                <div style="margin-bottom:1.5rem;">
                    <label style="display:block;margin-bottom:0.5rem;font-weight:600;">Notes (optional)</label>
                    <textarea name="description" rows="3" placeholder="e.g. Foundation completed and certified by structural engineer..." style="width:100%;padding:1rem;border-radius:12px;border:2px solid #e2e8f0;font-size:1rem;"></textarea>
                </div>
                <div style="display:flex;gap:1rem;">
                    <button type="submit" style="flex:1;background:#10b981;color:white;border:none;padding:1rem;border-radius:12px;font-weight:600;font-size:1rem;">Save Update</button>
                    <button type="button" onclick="document.getElementById('updateModal').style.display='none'" style="flex:1;background:#64748b;color:white;border:none;padding:1rem;border-radius:12px;font-weight:600;">Cancel</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openUpdateModal(id, title) {
            document.getElementById('modalPropertyId').value = id;
            document.getElementById('modalTitle').textContent = title;
            document.getElementById('updateModal').style.display = 'flex';
        }

        // Animate progress bars on load
        setTimeout(() => {
            document.querySelectorAll('.phase-fill').forEach(bar => {
                bar.style.width = bar.style.width;
            });
        }, 400);
    </script>
</body>
</html>