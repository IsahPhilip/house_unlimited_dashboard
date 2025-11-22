<?php
require '../inc/config.php';
require '../inc/auth.php';

if ($_SESSION['user']['role'] !== 'admin') {
    header('Location: ../dashboard/');
    exit;
}

$user_id = $_GET['user_id'] ?? 0;
if (!$user_id) {
    header('Location: users.php');
    exit;
}

// Fetch user details
$stmt = $db->prepare("SELECT id, name, email FROM users WHERE id = ?");
$stmt->bind_param('i', $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$user) {
    header('Location: users.php');
    exit;
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Record Offline Payment • Admin</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .record-payment-card {
            background: white;
            padding: 2.5rem;
            border-radius: 18px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.08);
            margin-bottom: 2rem;
            max-width: 600px;
            margin: 2rem auto;
        }
        body.dark .record-payment-card { background: #1e1e1e; }
        .record-payment-card h1 {
            font-size: 2.4rem;
            color: #1e40af;
            margin-bottom: 1.5rem;
            text-align: center;
        }
        body.dark .record-payment-card h1 { color: #93c5fd; }
        .record-payment-card h2 {
            font-size: 1.8rem;
            margin-top: 0;
            color: #334155;
            text-align: center;
            margin-bottom: 2rem;
        }
        body.dark .record-payment-card h2 { color: #e2e8f0; }

        .form-group {
            margin-bottom: 1.5rem;
        }
        .form-group label {
            display: block;
            margin-bottom: 0.7rem;
            font-weight: 600;
            color: #334155;
        }
        body.dark .form-group label { color: #e2e8f0; }
        .form-group input[type="text"],
        .form-group input[type="number"],
        .form-group input[type="date"],
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 1rem 1.2rem;
            border: 1px solid #cbd5e1;
            border-radius: 10px;
            font-size: 1rem;
            background: #f8fafc;
            box-sizing: border-box;
        }
        body.dark .form-group input,
        body.dark .form-group select,
        body.dark .form-group textarea {
            background: #334155;
            border-color: #475569;
            color: #e2e8f0;
        }
        .form-group input[type="submit"] {
            background: #3b82f6;
            color: white;
            border: none;
            padding: 1.2rem 2rem;
            border-radius: 12px;
            cursor: pointer;
            font-weight: 700;
            font-size: 1.1rem;
            width: 100%;
            transition: background 0.3s ease;
        }
        .form-group input[type="submit"]:hover {
            background: #2563eb;
        }
    </style>
</head>
<body>
    <?php include '../inc/header.php'; ?>
    <div class="container">
        <?php include '../inc/sidebar.php'; ?>

        <main class="main-content">
            <div class="record-payment-card">
                <h1>Record Offline Payment</h1>
                <h2>For: <?= htmlspecialchars($user['name']) ?> (<?= htmlspecialchars($user['email']) ?>)</h2>

                <form id="recordPaymentForm">
                    <input type="hidden" name="user_id" value="<?= htmlspecialchars($user['id']) ?>">

                    <div class="form-group">
                        <label for="property_id">Property</label>
                        <select id="property_id" name="property_id" required>
                            <option value="">Select Property</option>
                            <!-- Properties will be loaded here by JavaScript -->
                        </select>
                        <div id="property-loading-error" style="color: #EF4444; margin-top: 0.5rem; display: none;"></div>
                    </div>
                    
                    <div class="form-group">
                        <label for="amount">Amount (₦)</label>
                        <input type="number" id="amount" name="amount" step="0.01" min="0" required>
                    </div>

                    <div class="form-group">
                        <label for="payment_date">Payment Date</label>
                        <input type="date" id="payment_date" name="payment_date" value="<?= date('Y-m-d') ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="payment_method">Payment Method</label>
                        <select id="payment_method" name="payment_method" required>
                            <option value="">Select Method</option>
                            <option value="Cash">Cash</option>
                            <option value="Bank Transfer">Bank Transfer</option>
                            <option value="Cheque">Cheque</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="status">Payment Status</label>
                        <select id="status" name="status" required>
                            <option value="pending_offline">Pending Offline</option>
                            <option value="completed_offline">Completed Offline</option>
                            <!-- Add other statuses as needed, e.g., 'refunded', 'failed_offline' -->
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="notes">Notes/Reference (Optional)</label>
                        <textarea id="notes" name="notes" rows="4"></textarea>
                    </div>

                    <div class="form-group">
                        <input type="submit" value="Record Payment">
                    </div>
                </form>
                <div id="message" style="margin-top: 1.5rem; text-align: center; font-weight: bold;"></div>
            </div>
        </main>
    </div>

    <script>
        document.getElementById('recordPaymentForm').addEventListener('submit', async function(e) {
            e.preventDefault();

            const form = e.target;
            const formData = new FormData(form);
            const messageDiv = document.getElementById('message');

            try {
                const response = await fetch('../api/admin_record_payment.php', {
                    method: 'POST',
                    body: formData
                });

                const result = await response.json();

                if (result.success) {
                    messageDiv.style.color = '#10B981'; // Green for success
                    messageDiv.innerHTML = result.message + '<br><a href="../api/generate_receipt.php?payment_id=' + result.payment_id + '" target="_blank" class="action-btn btn-payment" style="margin-top: 1rem;">Generate Receipt</a>';
                    form.reset(); // Clear the form
                    // Optional: redirect or update UI after success
                    // setTimeout(() => { window.location.href = 'users.php'; }, 2000); 
                } else {
                    messageDiv.style.color = '#EF4444'; // Red for error
                    messageDiv.textContent = result.message;
                }
            } catch (error) {
                messageDiv.style.color = '#EF4444'; // Red for error
                messageDiv.textContent = 'An unexpected error occurred.';
                console.error('Error:', error);
            }
        });

        async function loadProperties() {
            const propertySelect = document.getElementById('property_id');
            const errorDiv = document.getElementById('property-loading-error');
            try {
                const response = await fetch('../api/get_all_properties.php');
                const result = await response.json();

                if (result.success) {
                    result.properties.forEach(property => {
                        const option = document.createElement('option');
                        option.value = property.id;
                        option.textContent = property.title;
                        propertySelect.appendChild(option);
                    });
                } else {
                    errorDiv.textContent = result.message || 'Failed to load properties.';
                    errorDiv.style.display = 'block';
                }
            } catch (error) {
                errorDiv.textContent = 'Could not load properties. Please try again.';
                errorDiv.style.display = 'block';
                console.error('Error loading properties:', error);
            }
        }

        loadProperties(); // Call on page load
    </script>
</body>
</html>
