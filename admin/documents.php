<?php
// admin/documents.php — FINAL FIXED + LUXURY DESIGN (100% WORKING)
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

    if ($file['error'] === 0 && $file['size'] <= 25*1024*1024) { // 25MB
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $allowed = ['pdf','doc','docx','xls','xlsx','ppt','pptx','jpg','jpeg','png','zip'];
        
        if (in_array($ext, $allowed)) {
            $filename = 'doc_' . time() . '_' . bin2hex(random_bytes(6)) . '.' . $ext;
            $path = '../assets/uploads/documents/' . $filename;

            if (move_uploaded_file($file['tmp_name'], $path)) {
                $stmt = $db->prepare("INSERT INTO documents (user_id, title, file_path, category) VALUES (?, ?, ?, ?)");
                $stmt->bind_param('isss', $admin_id, $title, $filename, $category);
                $stmt->execute();
                $success = "Document uploaded successfully!";
            } else {
                $error = "Failed to save file on server.";
            }
        } else {
            $error = "Invalid file type. Only PDF, Word, Excel, Images & ZIP allowed.";
        }
    } else {
        $error = "File too large (max 25MB) or upload error.";
    }
}

// Handle delete
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $doc = $db->query("SELECT file_path FROM documents WHERE id = $id AND user_id = $admin_id")->fetch_assoc();
    if ($doc) {
        @unlink('../assets/uploads/documents/' . $doc['file_path']);
        $db->query("DELETE FROM documents WHERE id = $id");
        header('Location: documents.php?deleted=1');
        exit;
    }
}

// FIXED QUERY — Matches your actual table structure
$documents = $db->query("
    SELECT d.*, u.name AS uploader_name
    FROM documents d
    LEFT JOIN users u ON d.user_id = u.id
    ORDER BY d.created_at DESC
")->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document Vault • Admin • House Unlimited</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Playfair+Display:wght@700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">
    <style>
        :root {
            --purple: #7c3aed;
            --purple-dark: #5b21b6;
            --gold: #fbbf24;
            --gray: #64748b;
        }
        .admin-header {
            background: linear-gradient(135deg, var(--purple-dark), var(--purple));
            color: white;
            padding: 4rem 2rem;
            border-radius: 28px;
            text-align: center;
            margin-bottom: 3rem;
            box-shadow: 0 25px 60px rgba(124, 58, 237, 0.3);
        }
        .admin-header h1 {
            font-family: 'Playfair Display', serif;
            font-size: 4rem;
            margin: 0 0 1rem;
            background: linear-gradient(90deg, #fff, #ddd);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .upload-box {
            background: white;
            border: 3px dashed var(--purple);
            border-radius: 24px;
            padding: 2.5rem;
            text-align: center;
            box-shadow: 0 15px 40px rgba(124,58,237,0.15);
            transition: all 0.4s;
        }
        body.dark .upload-box { background: #1e1e1e; border-color: #a78bfa; }
        .upload-box:hover { transform: translateY(-8px); box-shadow: 0 25px 60px rgba(124,58,237,0.25); }

        .docs-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(340px, 1fr));
            gap: 2rem;
            margin-top: 2rem;
        }

        .doc-card {
            background: white;
            border-radius: 24px;
            overflow: hidden;
            box-shadow: 0 15px 40px rgba(0,0,0,0.1);
            transition: all 0.4s ease;
        }
        body.dark .doc-card { background: #1e1e1e; }
        .doc-card:hover {
            transform: translateY(-12px);
            box-shadow: 0 30px 70px rgba(124,58,237,0.3);
        }

        .doc-header {
            background: linear-gradient(135deg, var(--purple-dark), var(--purple));
            color: white;
            padding: 1.8rem;
            position: relative;
            overflow: hidden;
        }
        .doc-header::after {
            content: '';
            position: absolute;
            top: -50%; right: -50%;
            width: 100%; height: 100%;
            background: radial-gradient(circle, rgba(255,255,255,0.15), transparent);
            animation: pulse 4s infinite;
        }
        @keyframes pulse { 0%,100% { opacity: 0; } 50% { opacity: 0.3; } }

        .doc-title {
            font-size: 1.4rem;
            font-weight: 700;
            margin: 0;
            line-height: 1.3;
        }
        .doc-meta {
            margin-top: 0.8rem;
            opacity: 0.95;
            font-size: 0.95rem;
        }

        .doc-body {
            padding: 2rem;
        }
        .doc-info {
            display: flex;
            justify-content: space-between;
            color: var(--gray);
            font-size: 0.95rem;
            margin-bottom: 1.2rem;
        }
        .doc-size {
            background: #f3f4f6;
            padding: 0.4rem 0.8rem;
            border-radius: 50px;
            font-weight: 600;
            font-size: 0.9rem;
        }
        body.dark .doc-size { background: #334155; }

        .doc-actions {
            display: flex;
            gap: 1rem;
            margin-top: 1.5rem;
        }
        .btn {
            flex: 1;
            padding: 1rem;
            border-radius: 16px;
            text-align: center;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.3s;
        }
        .btn-view {
            background: var(--purple);
            color: white;
        }
        .btn-download {
            background: #10b981;
            color: white;
        }
        .btn-delete {
            background: #ef4444;
            color: white;
        }
        .btn:hover { transform: translateY(-4px); }

        .no-docs {
            grid-column: 1 / -1;
            text-align: center;
            padding: 6rem 2rem;
            color: var(--gray);
            font-size: 1.6rem;
        }
        .no-docs i {
            font-size: 5rem;
            margin-bottom: 1.5rem;
            opacity: 0.3;
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
                <p>Secure. Organized. Always Accessible.</p>
            </div>

            <?php if (isset($success)): ?>
                <div style="background:#ecfdf5;color:#065f46;padding:1.5rem;border-radius:16px;margin-bottom:2rem;text-align:center;font-weight:600;box-shadow:0 10px 30px rgba(16,185,129,0.2);">
                    <?= $success ?>
                </div>
            <?php endif; ?>

            <!-- Upload Box -->
            <div class="upload-box">
                <i class="fas fa-cloud-upload-alt" style="font-size:4rem;color:var(--purple);margin-bottom:1rem;"></i>
                <h3>Upload New Document</h3>
                <form method="POST" enctype="multipart/form-data">
                    <div style="display:grid;grid-template-columns:1fr 1fr 1fr auto;gap:1.5rem;align-items:end;margin-top:1.5rem;">
                        <div>
                            <input type="text" name="title" placeholder="Document Title (e.g. Deed of Assignment)" required 
                                   style="width:100%;padding:1rem;border:2px solid #e2e8f0;border-radius:16px;font-size:1rem;">
                        </div>
                        <div>
                            <select name="category" style="width:100%;padding:1rem;border:2px solid #e2e8f0;border-radius:16px;font-size:1rem;">
                                <option value="contract">Contract</option>
                                <option value="deed">Deed of Assignment</option>
                                <option value="survey">Survey Plan</option>
                                <option value="c-of-o">C of O</option>
                                <option value="receipt">Payment Receipt</option>
                                <option value="invoice">Invoice</option>
                                <option value="other">Other</option>
                            </select>
                        </div>
                        <div>
                            <input type="file" name="document" required 
                                   accept=".pdf,.doc,.docx,.xls,.xlsx,.jpg,.png,.zip"
                                   style="padding:0.8rem;">
                        </div>
                        <button type="submit" name="upload" 
                                style="background:var(--purple);color:white;border:none;padding:1rem 2.5rem;border-radius:16px;font-weight:700;font-size:1.1rem;cursor:pointer;">
                            Upload
                        </button>
                    </div>
                </form>
            </div>

            <!-- Documents Grid -->
            <div class="docs-grid">
                <?php if (empty($documents)): ?>
                    <div class="no-docs">
                        <i class="fas fa-folder-open"></i>
                        <h2>No Documents Yet</h2>
                        <p>Upload your first legal document, contract, or certificate to get started.</p>
                    </div>
                <?php else: foreach ($documents as $doc): ?>
                    <div class="doc-card">
                        <div class="doc-header">
                            <h3 class="doc-title"><?= htmlspecialchars($doc['title']) ?></h3>
                            <div class="doc-meta">
                                <strong><?= ucfirst(str_replace('-', ' ', $doc['category'])) ?></strong>
                            </div>
                        </div>
                        <div class="doc-body">
                            <div class="doc-info">
                                <span>Uploaded by <strong><?= htmlspecialchars($doc['uploader_name'] ?? 'Admin') ?></strong></span>
                                <span class="doc-size">
                                    <?= round(strlen(file_get_contents('../assets/uploads/documents/'.$doc['file_path'])) / 1024) ?> KB
                                </span>
                            </div>
                            <div class="doc-info">
                                <small><?= date('M j, Y \a\t g:i A', strtotime($doc['created_at'])) ?></small>
                            </div>
                            <div class="doc-actions">
                                <a href="../assets/uploads/documents/<?= $doc['file_path'] ?>" target="_blank" class="btn btn-view">
                                    View
                                </a>
                                <a href="../assets/uploads/documents/<?= $doc['file_path'] ?>" download class="btn btn-download">
                                    Download
                                </a>
                                <a href="?delete=<?= $doc['id'] ?>" 
                                   onclick="return confirm('Delete this document permanently?')" 
                                   class="btn btn-delete">
                                    Delete
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; endif; ?>
            </div>
        </main>
    </div>
</body>
</html>