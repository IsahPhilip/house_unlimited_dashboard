<?php
// dashboard/transactions.php
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
    <title>Payments & Transactions • House Unlimited</title>
    <link rel="stylesheet" href="../assets/css/style.css" />
    <script src="https://js.paystack.co/v1/inline.js"></script>
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

        .summary-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2.5rem;
        }
        .summary-card {
            background: white;
            padding: 1.8rem;
            border-radius: 16px;
            box-shadow: 0 8px 30px rgba(0,0,0,0.08);
            text-align: center;
        }
        body.dark .summary-card { background: #1e1e1e; }
        .summary-card h3 {
            font-size: 1rem;
            color: #64748b;
            margin-bottom: 0.8rem;
            text-transform: uppercase;
            letter-spacing: 0.8px;
        }
        .summary-card .amount {
            font-size: 2.4rem;
            font-weight: 700;
            color: #10b981;
        }
        .summary-card.pending .amount { color: #f59e0b; }
        .summary-card.failed .amount { color: #ef4444; }

        .transactions-table {
            background: white;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 8px 30px rgba(0,0,0,0.08);
        }
        body.dark .transactions-table { background: #1e1e1e; }

        .table th {
            background: #f8f9fc;
            padding: 1.2rem 1rem;
            text-align: left;
            font-weight: 600;
            color: #475569;
            font-size: 0.95rem;
        }
        body.dark .table th { background: #334155; color: #cbd5e1; }

        .table td {
            padding: 1.2rem 1rem;
            border-bottom: 1px solid #f1f5f9;
        }
        body.dark .table td { border-color: #334155; }

        .status {
            padding: 0.5rem 1rem;
            border-radius: 50px;
            font-size: 0.85rem;
            font-weight: 600;
            text-transform: uppercase;
        }
        .status.success { background: #d1fae5; color: #065f46; }
        .status.pending { background: #fef3c7; color: #92400e; }
        .status.failed { background: #fee2e2; color: #991b1b; }

        .property-thumb {
            width: 50px;
            height: 50px;
            object-fit: cover;
            border-radius: 8px;
        }

        .pay-now-btn {
            background: #10b981;
            color: white;
            padding: 1rem 2rem;
            border-radius: 12px;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            border: none;
            box-shadow: 0 8px 20px rgba(16, 185, 129, 0.3);
            transition: all 0.3s;
        }
        .pay-now-btn:hover {
            background: #059669;
            transform: translateY(-3px);
        }

        .no-transactions {
            text-align: center;
            padding: 5rem 2rem;
            color: #64748b;
        }
        .no-transactions img {
            width: 120px;
            opacity: 0.4;
            margin-bottom: 1.5rem;
        }

        .receipt-btn {
            background: #3b82f6;
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 8px;
            font-size: 0.9rem;
            text-decoration: none;
        }
    </style>
</head>
<body>
    <?php include '../inc/header.php'; ?>

    <div class="container">
        <?php include '../inc/sidebar.php'; ?>

        <main class="main-content">
            <div class="page-header">
                <h1>Payments & Transactions</h1>
                <button class="pay-now-btn" onclick="initiatePayment()">
                    Pay Booking Fee
                </button>
            </div>

            <!-- Summary Cards -->
            <div class="summary-cards">
                <div class="summary-card">
                    <h3>Total Paid</h3>
                    <div class="amount" id="totalPaid">₦0</div>
                </div>
                <div class="summary-card pending">
                    <h3>Pending</h3>
                    <div class="amount" id="totalPending">₦0</div>
                </div>
                <div class="summary-card">
                    <h3>Successful</h3>
                    <div class="amount success" id="successfulCount">0</div>
                </div>
                <div class="summary-card failed">
                    <h3>Failed</h3>
                    <div class="amount" id="failedCount">0</div>
                </div>
            </div>

            <!-- Transactions Table -->
            <div class="transactions-table">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Property</th>
                            <th>Amount</th>
                            <th>Reference</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody id="transactionsBody">
                        <tr>
                            <td colspan="6" class="no-transactions">
                                <img src="../assets/img/wallet.svg" alt="Wallet">
                                <h3>No transactions yet</h3>
                                <p>All your payments and receipts will appear here.</p>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </main>
    </div>

    <script src="../assets/js/main.js"></script>
    <script>
        async function loadTransactions() {
            const res = await fetch('../api/get_transactions.php');
            const transactions = await res.json();

            // Update Summary
            const paid = transactions.filter(t => t.status === 'success').reduce((a, t) => a + parseInt(t.amount), 0);
            const pending = transactions.filter(t => t.status === 'pending').reduce((a, t) => a + parseInt(t.amount), 0);
            document.getElementById('totalPaid').textContent = '₦' + paid.toLocaleString();
            document.getElementById('totalPending').textContent = '₦' + pending.toLocaleString();
            document.getElementById('successfulCount').textContent = transactions.filter(t => t.status === 'success').length;
            document.getElementById('failedCount').textContent = transactions.filter(t => t.status === 'failed').length;

            // Update Table
            const tbody = document.getElementById('transactionsBody');
            if (transactions.length === 0) {
                tbody.innerHTML = `<tr><td colspan="6" class="no-transactions">
                    <img src="../assets/img/no-transactions.svg" alt="No transactions">
                    <h3>No payment history</h3>
                    <p>Once you make a payment, it will appear here with receipt.</p>
                </td></tr>`;
                return;
            }

            let html = '';
            transactions.forEach(t => {
                const date = new Date(t.created_at).toLocaleDateString('en-NG', {
                    day: 'numeric', month: 'long', year: 'numeric', hour: '2-digit', minute: '2-digit'
                });

                html += `
                <tr>
                    <td><strong>${date}</strong></td>
                    <td>
                        <div style="display:flex; align-items:center; gap:1rem;">
                            <img src="../assets/uploads/properties/${t.property_image || 'default.jpg'}" 
                                 class="property-thumb" alt="${t.property_title}">
                            <div>
                                <strong>${t.property_title}</strong><br>
                                <small>${t.property_location}</small>
                            </div>
                        </div>
                    </td>
                    <td><strong>₦${Number(t.amount).toLocaleString()}</strong></td>
                    <td><code>${t.payment_ref}</code></td>
                    <td><span class="status ${t.status}">${t.status}</span></td>
                    <td>
                        ${t.status === 'success' 
                            ? `<a href="../api/generate_receipt.php?id=${t.id}" target="_blank" class="receipt-btn">Receipt</a>`
                            : '<em>Awaiting confirmation</em>'
                        }
                    </td>
                </tr>`;
            });
            tbody.innerHTML = html;
        }

        function initiatePayment() {
            // Example: Pay ₦50,000,000 booking fee
            const handler = PaystackPop.setup({
                key: 'pk_live_YOUR_REAL_KEY', // Replace with your Paystack public key
                email: '<?= $user['email'] ?>',
                amount: 5000000000, // ₦50,000,000 in kobo
                currency: 'NGN',
                ref: 'HUL-' + Math.floor((Math.random() * 1000000000) + 1),
                metadata: {
                    user_id: <?= $user_id ?>,
                    property_id: 45 // Dynamic in real app
                },
                callback: function(response) {
                    verifyPayment(response.reference);
                },
                onClose: function() {
                    alert('Payment cancelled');
                }
            });
            handler.openIframe();
        }

        async function verifyPayment(reference) {
            const res = await fetch('../api/verify_payment.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ reference })
            });
            const data = await res.json();

            if (data.success) {
                alert('Payment successful! Receipt generating...');
                loadTransactions();
            } else {
                alert('Payment failed or already processed.');
            }
        }

        // Load on start
        loadTransactions();
        setInterval(loadTransactions, 30000);
    </script>
</body>
</html>