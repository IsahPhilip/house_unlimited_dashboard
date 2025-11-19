<?php
// admin/documents.php - Admin Document Management Center
require '../inc/config.php';
require '../inc/auth.php';

if ($_SESSION['user']['role'] !== 'admin') {
    header('Location: ../dashboard/');
    exit;
}

$admin_id = $_SESSION['user']['id'];

// Handle document upload
if ($_POST['upload'] ?? false) {
    $title = trim($_POST['title']);
    $category = $_POST['category'];
    $file = $_FILES['document'];

    if ($file['error'] === 0 && $file['size'] <= 20*1024*1024) { // 20MB max
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $allowed = ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'jpg', 'jpeg', 'png', 'zip'];
        
        if (in_array($ext, $allowed)) {
            $filename = 'doc_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
            $path = '../assets/uploads/documents/' . $filename;

            if (move_uploaded_file($file['tmp_name'], $path)) {
                $stmt = $db->prepare("INSERT INTO documents (title, filename, category, size, uploaded_by) VALUES (?, ?, ?, ?, ?)");
                $stmt->bind_param('sssii', $title, $filename, $category, $file['size'], $admin_id);
                $stmt->execute();
                $success = "Document uploaded successfully!";
            } else {
                $error = "Failed to save file.";
            }
        } else {
            $error = "File type not allowed.";
        }
    } else {
        $error = "File too large or upload error.";
    }
}

// Handle delete
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $doc = $db->query("SELECT filename FROM documents WHERE id = $id")->fetch_assoc();
    if ($doc) {
        unlink('../assets/uploads/documents/' . $doc['filename']);
        $db->query("DELETE FROM documents WHERE id = $id");
        header('Location: documents.php?deleted=1');
        exit;
    }
}

// Fetch all documents
$documents = $db->query("
    SELECT d.*, u.name as uploader_name 
    FROM documents d 
    LEFT JOIN users u ON d.uploaded_by = u.id 
    ORDER BY d.uploaded_at DESC
")->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Documents • Admin • House Unlimited</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .admin-header {
            background: linear-gradient(135deg, #7c3aed, #5b21b6);
            color: white;
            padding: 2.5rem;
            border-radius: 20px;
            margin-bottom: 2.5rem;
            text-align: center;
        }
        .admin-header h1 { margin: 0 0 0.5rem; font-size: 2.8rem; font-weight: 800; }

        .upload-box {
            background: white;
            padding: 2rem;
            border-radius: 20px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.1);
            margin-bottom: 2.5rem;
            border: 3px dashed #7c3aed;
        }
        body.dark .upload-box { background: #1e1e1e; border-color: #a78bfa; }

        .docs-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 1.5rem;
        }

        .doc-card {
            background: white;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 8px 30px rgba(0,0,0,0.1);
            transition: all 0.3s;
        }
        body.dark .doc-card { background: #1e1e1e; }
        .doc-card:hover { transform: translateY(-8px); box-shadow: 0 20px 50px rgba(0,0,0,0.2); }

        .doc-header {
            padding: 1.5rem;
            background: linear-gradient(135deg, #7c3aed, #5b21b6);
            color: white;
        }
        .doc-title { margin: 0; font-size: 1.2rem; font-weight: 600; }
        .doc-category { font-size: 0.9rem; opacity: 0.9; margin-top: 0.3rem; }

        .doc-body {
            padding: 1.5rem;
        }
        .doc-info { 
            display: flex; 
            justify-content: space-between; 
            align-items: center; 
            margin-bottom: 1rem; 
            color: #64748b;
            font-size: 0.9rem;
        }
        .doc-size { font-weight: 600; }

        .doc-actions {
            display: flex;
            gap: 0.8rem;
        }
        .btn-download {
            background: #10b981; color: white;
            padding: 0.7rem 1.2rem; border-radius: 12px; text-decoration: none;
            font-weight: 600; font-size: 0.9rem;
        }
        .btn-delete {
            background: #ef4444; color: white;
            padding: 0.7rem 1.2rem; border-radius: 12px; text-decoration: none;
            font-weight: 600; font-size: 0.9rem;
        }

        .no-docs {
            text-align: center;
            padding: 4rem 2rem;
            color: #64748b;
            grid-column: 1 / -1;
        }
    </style>
</head>
<body>
    <?php include '../inc/header.php'; ?>

    <div class="container">
        <?php include '../inc/sidebar.php'; ?>

        <main class="main-content">
            <div class="admin-header">
                <h1>Document Vault</h1>
                <p>Secure storage for contracts, agreements, certificates & more</p>
            </div>

            <?php if (isset($success)): ?>
                <div style="background:#d1fae5; color:#065f46; padding:1.2rem; border-radius:12px; margin-bottom:1.5rem; text-align:center;">
                    <?= $success ?>
                </div>
            <?php endif; ?>
            <?php if (isset($_GET['deleted'])): ?>
                <div style="background:#fee2e2; color:#991b1b; padding:1.2rem; border-radius:12px; margin-bottom:1.5rem; text-align:center;">
                    Document deleted permanently.
                </div>
            <?php endif; ?>
            <?php if (isset($error)): ?>
                <div style="background:#fee2e2; color:#991b1b; padding:1.2rem; border-radius:12px; margin-bottom:1.5rem; text-align:center;">
                    <?= $error ?>
                </div>
            <?php endif; ?>

            <!-- Upload Form -->
            <div class="upload-box">
                <h3 style="margin-top:0; color:#5b21b6;">Upload New Document</h3>
                <form method="POST" enctype="multipart/form-data">
                    <div style="display:grid; grid-template-columns:1fr 1fr 200px auto; gap:1rem; align-items:end;">
                        <div>
                            <label style="display:block; margin-bottom:0.5rem; font-weight:600;">Title</label>
                            <input type="text" name="title" required style="width:100%; padding:0.8rem; border:2px solid #e2e8f0; border-radius:12px;">
                        </div>
                        <div>
                            <label style="display:block; margin-bottom:0.5rem; font-weight:600;">Category</label>
                            <select name="category" style="width:100%; padding:0.8rem; border:2px solid #e2e8f0; border-radius:12px;">
                                <option value="contract">Contract</option>
                                <option value="agreement">Agreement</option>
                                <option value="certificate">Certificate</option>
                                <option value="invoice">Invoice</option>
                                <option value="report">Report</option>
                                <option value="legal">Legal</option>
                                <option value="other">Other</option>
                            </select>
                        </div>
                        <div>
                            <label style="display:block; margin-bottom:0.5rem; font-weight:600;">File (Max 20MB)</label>
                            <input type="file" name="document" required accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.jpg,.jpeg,.png,.zip" 
                                   style="width:100%; padding:0.6rem;">
                        </div>
                        <button type="submit" name="upload" 
                                style="background:#7c3aed; color:white; border:none; padding:0.9rem 2rem; border-radius:12px; font-weight:600; cursor:pointer;">
                            Upload Document
                        </button>
                    </div>
                </form>
            </div>

            <!-- Documents Grid -->
            <div class="docs-grid">
                <?php if (empty($documents)): ?>
                    <div class="no-docs">
                        <h2>No documents uploaded yet</h2>
                        <p>Start by uploading your first contract or certificate above.</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($documents as $doc): ?>
                        <div class="doc-card">
                            <div class="doc-header">
                                <h3 class="doc-title"><?= htmlspecialchars($doc['title']) ?></h3>
                                <div class="doc-category">
                                    <?= ucfirst($doc['category']) ?>
                                </div>
                            </div>
                            <div class="doc-body">
                                <div class="doc-info">
                                    <span>Uploaded by <strong><?= htmlspecialchars($doc['uploader_name']) ?></strong></span>
                                    <span class="doc-size"><?= number_format($doc['size']/1024, 1) ?> KB</span>
                                </div>
                                <div class="doc-info">
                                    <small><?= date('M j, Y \a\t g:ia', strtotime($doc['uploaded_at'])) ?></small>
                                </div>
                                <div class="doc-actions">
                                    <a href="../assets/uploads/documents/<?= $doc['filename'] ?>" 
                                       target="_blank" class="btn-download">
                                       Download
                                    </a>
                                    <a href="documents.php?delete=<?= $doc['id'] ?>" 
                                       onclick="return confirm('Permanently delete this document?')" 
                                       class="btn-delete">
                                       Delete
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </main>
    </div>
</body>
</html>