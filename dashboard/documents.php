<?php
// dashboard/documents.php
require '../inc/config.php';
require '../inc/auth.php';

$user = $_SESSION['user'];
$user_id = $user['id'];
$role = $user['role'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Documents • House Unlimited</title>
    <link rel="stylesheet" href="../assets/css/style.css" />
    <style>
        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
            flex-wrap: wrap;
            gap: 1rem;
        }
        .page-header h1 {
            margin: 0;
            font-size: 2.2rem;
            color: #1e40af;
        }
        body.dark .page-header h1 { color: #93c5fd; }

        .doc-filters {
            display: flex;
            gap: 1rem;
            margin-bottom: 1.5rem;
            flex-wrap: wrap;
        }
        .doc-filters select, .doc-filters input {
            padding: 0.8rem 1rem;
            border: 2px solid #e2e8f0;
            border-radius: 12px;
            font-size: 1rem;
        }

        .documents-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 1.8rem;
        }

        .doc-card {
            background: white;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 8px 30px rgba(0,0,0,0.1);
            transition: all 0.3s;
        }
        body.dark .doc-card { background: #1e1e1e; }

        .doc-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 20px 40px rgba(0,0,0,0.15);
        }

        .doc-preview {
            height: 200px;
            background: #f8f9fc;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            overflow: hidden;
        }
        body.dark .doc-preview { background: #0f172a; }

        .doc-preview iframe {
            width: 100%;
            height: 100%;
            border: none;
        }

        .doc-watermark {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-30deg);
            font-size: 3rem;
            font-weight: 900;
            color: rgba(239, 68, 68, 0.15);
            pointer-events: none;
            z-index: 1;
            text-transform: uppercase;
            letter-spacing: 8px;
        }

        .doc-icon {
            width: 80px;
            opacity: 0.7;
        }

        .doc-info {
            padding: 1.5rem;
        }

        .doc-title {
            font-size: 1.3rem;
            margin: 0 0 0.5rem;
            color: #1e293b;
        }
        body.dark .doc-title { color: #e2e8f0; }

        .doc-meta {
            color: #64748b;
            font-size: 0.95rem;
            margin: 0.5rem 0;
        }

        .doc-actions {
            display: flex;
            gap: 0.8rem;
            margin-top: 1rem;
        }

        .btn-download {
            background: #10b981;
            color: white;
        }
        .btn-view {
            background: #3b82f6;
            color: white;
        }

        .doc-category {
            display: inline-block;
            padding: 0.4rem 0.9rem;
            background: #e0e7ff;
            color: #1e40af;
            border-radius: 50px;
            font-size: 0.85rem;
            font-weight: 600;
            margin-bottom: 0.8rem;
        }
        body.dark .doc-category {
            background: #1e40af;
            color: white;
        }

        .no-documents {
            text-align: center;
            padding: 5rem 2rem;
            color: #64748b;
            grid-column: 1 / -1;
        }
        .no-documents img {
            width: 140px;
            opacity: 0.4;
            margin-bottom: 1.5rem;
        }
    </style>
</head>
<body>
    <?php include '../inc/header.php'; ?>

    <div class="container">
        <?php include '../inc/sidebar.php'; ?>

        <main class="main-content">
            <div class="page-header">
                <h1>My Documents</h1>
                <div class="doc-filters">
                    <select id="filterCategory">
                        <option value="">All Categories</option>
                        <option value="receipt">Payment Receipts</option>
                        <option value="contract">Sale Contracts</option>
                        <option value="deed">Deed of Assignment</option>
                        <option value="survey">Survey Plans</option>
                        <option value="c-of-o">C of O</option>
                        <option value="invoice">Invoices</option>
                    </select>
                    <input type="text" id="searchDoc" placeholder="Search documents..." />
                </div>
            </div>

            <div class="documents-grid" id="documentsContainer">
                <div class="no-documents">
                    <img src="../assets/img/documents.svg" alt="Documents">
                    <h3>No documents yet</h3>
                    <p>Documents like receipts, contracts, and titles will appear here once generated.</p>
                </div>
            </div>
        </main>
    </div>

    <script src="../assets/js/main.js"></script>
    <script>
        async function loadDocuments() {
            const category = document.getElementById('filterCategory').value;
            const search = document.getElementById('searchDoc').value.toLowerCase();

            const res = await fetch('../api/get_documents.php');
            const docs = await res.json();

            let filtered = docs.filter(doc => {
                const matchesCategory = !category || doc.category === category;
                const matchesSearch = !search || doc.title.toLowerCase().includes(search) || 
                                    (doc.property_title && doc.property_title.toLowerCase().includes(search));
                return matchesCategory && matchesSearch;
            });

            const container = document.getElementById('documentsContainer');
            if (filtered.length === 0) {
                container.innerHTML = `
                    <div class="no-documents">
                        <img src="../assets/img/no-results.svg" alt="No results">
                        <h3>No documents found</h3>
                        <p>Try adjusting your filters or search term.</p>
                    </div>`;
                return;
            }

            let html = '';
            filtered.forEach(doc => {
                const isPDF = doc.file_path.toLowerCase().endsWith('.pdf');
                const previewHTML = isPDF 
                    ? `<iframe src="../assets/uploads/documents/${doc.file_path}#view=FitH&toolbar=0&navpanes=0" loading="lazy"></iframe>`
                    : `<img src="../assets/img/pdf-icon.png" class="doc-icon" alt="PDF">`;

                const categoryText = {
                    'receipt': 'Payment Receipt',
                    'contract': 'Sale Contract',
                    'deed': 'Deed of Assignment',
                    'survey': 'Survey Plan',
                    'c-of-o': 'Certificate of Occupancy',
                    'invoice': 'Invoice'
                }[doc.category] || doc.category;

                html += `
                <div class="doc-card">
                    <div class="doc-preview">
                        ${previewHTML}
                        <div class="doc-watermark">HOUSE UNLIMITED</div>
                    </div>
                    <div class="doc-info">
                        <span class="doc-category">${categoryText}</span>
                        <h3 class="doc-title">${doc.title}</h3>
                        <p class="doc-meta">
                            ${doc.property_title ? 'Property: ' + doc.property_title + '<br>' : ''}
                            Uploaded: ${new Date(doc.created_at).toLocaleDateString('en-NG', { 
                                day: 'numeric', month: 'long', year: 'numeric' 
                            })}
                        </p>
                        <div class="doc-actions">
                            <a href="../assets/uploads/documents/${doc.file_path}" 
                               target="_blank" class="btn btn-view btn-sm">View</a>
                            <a href="../api/download_document.php?id=${doc.id}" 
                               class="btn btn-download btn-sm">Download</a>
                        </div>
                    </div>
                </div>`;
            });

            container.innerHTML = html;
        }

        // Auto-refresh on filter change
        let searchTimeout;
        document.getElementById('searchDoc').addEventListener('input', () => {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(loadDocuments, 400);
        });
        document.getElementById('filterCategory').addEventListener('change', loadDocuments);

        // Initial load
        loadDocuments();
        setInterval(loadDocuments, 30000); // Refresh every 30 seconds
    </script>
</body>
</html>