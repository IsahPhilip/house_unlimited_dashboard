<?php
require '../inc/config.php';
require '../inc/auth.php';

$user_id = $_SESSION['user']['id'];

// Fetch wallet balance
$stmt = $db->prepare("SELECT wallet_balance FROM users WHERE id = ?");
$stmt->bind_param('i', $user_id);
$stmt->execute();
$wallet_balance = $stmt->get_result()->fetch_assoc()['wallet_balance'] ?? 0;
$stmt->close();

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Wallet • Dashboard</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .page-header h1 {
            font-size: 2.4rem;
            color: #1e40af;
        }
        body.dark .page-header h1 { color: #93c5fd; }
        .wallet-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 2rem;
            align-items: flex-start;
        }
        .wallet-card {
            background: white;
            padding: 2rem;
            border-radius: 18px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.08);
        }
        body.dark .wallet-card { background: #1e1e1e; }
        .wallet-card h2 {
            margin-top: 0;
            font-size: 1.5rem;
            color: #1e293b;
        }
        body.dark .wallet-card h2 { color: #e2e8f0; }

        .balance-display {
            text-align: center;
            margin-bottom: 2rem;
        }
        .balance-amount {
            font-size: 4rem;
            font-weight: 800;
            color: #1e40af;
            line-height: 1.2;
        }
        body.dark .balance-amount { color: #60a5fa; }
        .balance-label {
            font-size: 1rem;
            color: #64748b;
        }

        .withdraw-btn {
            display: block;
            width: 100%;
            padding: 1rem;
            font-size: 1.2rem;
            font-weight: 600;
            text-align: center;
            background: #10b981;
            color: white;
            border: none;
            border-radius: 12px;
            cursor: pointer;
            transition: background 0.3s;
        }
        .withdraw-btn:hover { background: #059669; }

        .history-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 2rem;
        }
        .history-table th, .history-table td {
            padding: 1rem;
            text-align: left;
            border-bottom: 1px solid #e2e8f0;
        }
        body.dark .history-table th, body.dark .history-table td { border-color: #334155; }
        .status-badge {
            padding: 0.3rem 0.8rem;
            border-radius: 50px;
            font-size: 0.85rem;
            font-weight: 600;
        }
        .status-pending { background: #fefce8; color: #a16207; }
        .status-approved { background: #ecfdf5; color: #065f46; }
        .status-declined { background: #fee2e2; color: #991b1b; }

    </style>
</head>
<body>
    <?php include '../inc/header.php'; ?>
    <div class="container">
        <?php include '../inc/sidebar.php'; ?>

        <main class="main-content">
            <div class="page-header">
                <h1>My Wallet</h1>
            </div>

            <div class="wallet-grid">
                <div class="wallet-card">
                    <h2>Available Balance</h2>
                    <div class="balance-display">
                        <div class="balance-amount">₦<?= number_format($wallet_balance, 2) ?></div>
                        <div class="balance-label">Total funds available for withdrawal</div>
                    </div>
                    <button class="withdraw-btn" onclick="openWithdrawModal()">Request Withdrawal</button>
                </div>

                <div class="wallet-card">
                    <h2>Bank Details</h2>
                    <div id="bankDetails">
                        <p>Please add your bank details to enable withdrawals.</p>
                        <button onclick="openBankDetailsModal()">Add Bank Details</button>
                    </div>
                </div>
            </div>

            <div class="wallet-card" style="margin-top: 2rem;">
                <h2>Withdrawal History</h2>
                <table class="history-table">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Amount</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody id="withdrawalHistory">
                        <tr><td colspan="3" style="text-align: center;">No withdrawal history.</td></tr>
                    </tbody>
                </table>
            </div>
        </main>
    </div>

    <div class="modal-overlay" id="withdrawModal" style="display:none;">
        <div class="modal-content">
            <h2>Request Withdrawal</h2>
            <form id="withdrawForm">
                <div class="form-group">
                    <label for="withdrawAmount">Amount</label>
                    <input type="number" id="withdrawAmount" name="amount" step="0.01" required>
                </div>
                <div class="form-actions">
                    <button type="button" class="btn-secondary" onclick="closeModal('withdrawModal')">Cancel</button>
                    <button type="submit" class="btn-primary">Submit Request</button>
                </div>
            </form>
        </div>
    </div>

    <div class="modal-overlay" id="bankDetailsModal" style="display:none;">
        <div class="modal-content">
            <h2>Bank Details</h2>
            <form id="bankDetailsForm">
                <div class="form-group">
                    <label for="bankName">Bank Name</label>
                    <input type="text" id="bankName" name="bank_name" required>
                </div>
                <div class="form-group">
                    <label for="accountNumber">Account Number</label>
                    <input type="text" id="accountNumber" name="account_number" required>
                </div>
                <div class="form-group">
                    <label for="accountName">Account Name</label>
                    <input type="text" id="accountName" name="account_name" required>
                </div>
                <div class="form-actions">
                    <button type="button" class="btn-secondary" onclick="closeModal('bankDetailsModal')">Cancel</button>
                    <button type="submit" class="btn-primary">Save Details</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        async function loadWalletData() {
            const res = await fetch('../api/get_wallet_data.php');
            const data = await res.json();

            // Update balance
            document.querySelector('.balance-amount').textContent = '₦' + Number(data.wallet_balance).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });

            // Update bank details
            const bankDetailsContainer = document.getElementById('bankDetails');
            if (data.bank_details) {
                bankDetailsContainer.innerHTML = `
                    <p><strong>Bank:</strong> ${data.bank_details.bank_name}</p>
                    <p><strong>Account Number:</strong> ${data.bank_details.account_number}</p>
                    <p><strong>Account Name:</strong> ${data.bank_details.account_name}</p>
                    <button onclick="openBankDetailsModal()">Edit Details</button>
                `;
                document.getElementById('bankName').value = data.bank_details.bank_name;
                document.getElementById('accountNumber').value = data.bank_details.account_number;
                document.getElementById('accountName').value = data.bank_details.account_name;
            }

            // Update withdrawal history
            const historyBody = document.getElementById('withdrawalHistory');
            if (data.withdrawals.length > 0) {
                let historyHtml = '';
                data.withdrawals.forEach(w => {
                    historyHtml += `
                        <tr>
                            <td>${new Date(w.created_at).toLocaleDateString()}</td>
                            <td>₦${Number(w.amount).toLocaleString()}</td>
                            <td><span class="status-badge status-${w.status}">${w.status}</span></td>
                        </tr>
                    `;
                });
                historyBody.innerHTML = historyHtml;
            }
        }

        function openWithdrawModal() {
            document.getElementById('withdrawModal').style.display = 'flex';
        }

        function openBankDetailsModal() {
            document.getElementById('bankDetailsModal').style.display = 'flex';
        }

        function closeModal(modalId) {
            document.getElementById(modalId).style.display = 'none';
        }
        
        document.getElementById('bankDetailsForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            const data = Object.fromEntries(formData.entries());

            const res = await fetch('../api/save_bank_details.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(data)
            });
            
            if(res.ok) {
                closeModal('bankDetailsModal');
                loadWalletData();
                alert('Bank details saved successfully!');
            } else {
                alert('Failed to save bank details.');
            }
        });

        document.getElementById('withdrawForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            const data = Object.fromEntries(formData.entries());

            const res = await fetch('../api/request_withdrawal.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(data)
            });
            const result = await res.json();

            if (res.ok) {
                closeModal('withdrawModal');
                loadWalletData();
                alert(result.message || 'Withdrawal request submitted successfully!');
            } else {
                alert(result.error || 'Failed to submit withdrawal request.');
            }
        });

        loadWalletData();
    </script>
</body>
</html>
