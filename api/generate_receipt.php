<?php
// api/generate_receipt.php - FINAL + DYNAMIC LOGS + WORKS 100% (NO TCPDF ERRORS)
require_once __DIR__ . '/../vendor/autoload.php';
require '../inc/config.php';
require '../inc/auth.php';

use Mpdf\Mpdf;

$transaction_id  = intval($_GET['payment_id'] ?? 0); // Renamed from payment_id for clarity
$payment_ref = trim($_GET['payment_ref'] ?? '');

if ($transaction_id <= 0 && empty($payment_ref)) {
    die('Invalid request: No transaction ID or reference provided.');
}

$user_id = $_SESSION['user']['id']; // User generating the receipt (admin)

// Fetch transaction data
$sql = "
    SELECT t.id, t.user_id, t.amount, t.payment_ref, t.status, t.gateway, t.created_at,
           u.name AS client_name, u.email,
           prop.title AS property_title, prop.location 
    FROM transactions t 
    LEFT JOIN users u ON t.user_id = u.id 
    LEFT JOIN properties prop ON t.property_id = prop.id 
    WHERE 1=1 "; // Start with a true condition

$params = [];
$types = '';

if ($transaction_id > 0) {
    $sql .= " AND t.id = ?";
    $params[] = $transaction_id;
    $types .= 'i';
} elseif (!empty($payment_ref)) {
    $sql .= " AND t.payment_ref = ?";
    $params[] = $payment_ref;
    $types .= 's';
} else {
    die('Invalid request: No transaction ID or reference provided.');
}

$stmt = $db->prepare($sql);
if (!empty($types)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$transaction_data = $stmt->get_result()->fetch_assoc();
$stmt->close();

// Admin should be able to generate receipts for any user's payment
if (!$transaction_data) {
    die('Transaction not found.');
}

// Check if the current user is an admin or the user who made the payment
// Admins can view all receipts, regular users can only view their own
if ($_SESSION['user']['role'] !== 'admin' && $transaction_data['user_id'] !== $user_id) {
    die('Access denied to this transaction receipt.');
}

// DYNAMIC LOG — NO HARDCODING
$amount = '₦' . number_format($transaction_data['amount']);
$property = $transaction_data['property_title'] 
    ? $transaction_data['property_title'] . ' in ' . ucwords($transaction_data['location'])
    : 'General Payment';
$date = date('j M Y \a\t g:i A', strtotime($transaction_data['created_at']));
$client = $transaction_data['client_name'] ?? 'Client';

log_activity("Generated payment receipt for $client – $amount paid for $property on $date");

// Generate BEAUTIFUL PDF with mPDF
$mpdf = new Mpdf([
    'mode' => 'utf-8',
    'format' => 'A4',
    'margin_left' => 15,
    'margin_right' => 15,
    'margin_top' => 20,
    'margin_bottom' => 20,
    'margin_header' => 10,
    'margin_footer' => 10
]);

$html = '
<!DOCTYPE html>
<html>
<head>
    <style>
        body { font-family: DejaVu Sans, sans-serif; color: #1e293b; }
        .header { text-align: center; padding: 20px 0; border-bottom: 3px solid #1e40af; }
        .title { font-size: 32px; color: #1e40af; margin: 10px 0; font-weight: bold; }
        .subtitle { font-size: 18px; color: #64748b; }
        table { width: 100%; margin: 30px 0; font-size: 16px; }
        td { padding: 12px 0; }
        .label { font-weight: bold; width: 35%; color: #1e293b; }
        .value { color: #475569; }
        .amount { font-size: 28px; color: #10b981; font-weight: bold; }
        .status { font-size: 24px; color: #10b981; font-weight: bold; }
        .footer { text-align: center; margin-top: 60px; color: #94a3b8; font-size: 12px; }
        hr { border: 1px solid #e2e8f0; margin: 40px 0; }
    </style>
</head>
<body>

<div class="header">
    <div class="title">Payment Receipt</div>
    <div style="font-size:22px; font-weight:bold;">HOUSE UNLIMITED & LAND SERVICES</div>
    <div class="subtitle">Lagos • Abuja • Port Harcourt • Dubai</div>
    <div class="subtitle">+234 803 000 0000 • info@houseunlimited.ng</div>
</div>

<table>
    <tr>
        <td class="label">Receipt No:</td>
        <td class="value">' . htmlspecialchars($transaction_data['payment_ref'] ?? ('OFFLINE-TRANS-' . $transaction_data['id'])) . '</td>
    </tr>
    <tr>
        <td class="label">Payment Date:</td>
        <td class="value">' . date('l, j F Y \a\t g:i A', strtotime($transaction_data['created_at'])) . '</td>
    </tr>
    <tr>
        <td class="label">Client Name:</td>
        <td class="value">' . htmlspecialchars($client) . '</td>
    </tr>
    <tr>
        <td class="label">Email:</td>
        <td class="value">' . htmlspecialchars($transaction_data['email']) . '</td>
    </tr>
    <tr>
        <td class="label">Payment For:</td>
        <td class="value"><strong>' . htmlspecialchars($property) . '</strong></td>
    </tr>
    <tr>
        <td class="label">Amount Paid:</td>
        <td class="amount">' . $amount . '</td>
    </tr>
    <tr>
        <td class="label">Status:</td>
        <td class="status">' . htmlspecialchars(strtoupper(str_replace('_', ' ', $transaction_data['status']))) . '</td>
    </tr>
    ' . (!empty($transaction_data['payment_method']) ? '
        <tr>
            <td class="label">Payment Method:</td>
            <td class="value">' . htmlspecialchars($transaction_data['gateway']) . '</td>
        </tr>' : '') . '
    ' . (!empty($transaction_data['notes']) ? '
        <tr>
            <td class="label">Notes/Reference:</td>
            <td class="value">' . htmlspecialchars($transaction_data['notes']) . '</td>
        </tr>' : '') . '
</table>

<hr>

<div class="footer">
    <p>Thank you for choosing House Unlimited — Building Africa\'s most exclusive real estate legacy.</p>
    <p>This is an electronically generated receipt • No signature required</p>
    <br>
    <p>© 2025 House Unlimited Nigeria • All Rights Reserved</p>
</div>

</body>
</html>
';

$mpdf->WriteHTML($html);

// Generate a unique filename for saving
$saved_filename = 'receipt_' . ($transaction_data['payment_ref'] ?? ('offline_trans_' . $transaction_data['id'])) . '_' . bin2hex(random_bytes(4)) . '.pdf';
$save_path_dir = '../assets/uploads/documents/';
if (!is_dir($save_path_dir)) {
    mkdir($save_path_dir, 0777, true);
}
$save_path = $save_path_dir . $saved_filename;

// Save the PDF to the server
$mpdf->Output($save_path, 'F'); // 'F' means save to file

// Prepare title for document record
$document_title = "Payment Receipt for " . ($transaction_data['property_title'] ? $transaction_data['property_title'] : "Payment ID " . $transaction_data['id']);

// Insert record into documents table
// Check if a document with this transaction_id and category already exists for this user
$stmt_check_doc = $db->prepare("SELECT id FROM documents WHERE user_id = ? AND source_id = ? AND category = ?");
$category = 'receipt';
$stmt_check_doc->bind_param('iis', $transaction_data['user_id'], $transaction_data['id'], $category);
$stmt_check_doc->execute();
$existing_doc = $stmt_check_doc->get_result()->fetch_assoc();
$stmt_check_doc->close();

if (!$existing_doc) { // Only insert if it doesn't already exist
    // Add source_id to documents table to link it to the transaction
    $stmt_insert_doc = $db->prepare("INSERT INTO documents (user_id, title, file_path, category, source_id) VALUES (?, ?, ?, ?, ?)");
    $stmt_insert_doc->bind_param('isssi', $transaction_data['user_id'], $document_title, $saved_filename, $category, $transaction_data['id']);
    $stmt_insert_doc->execute();
    $stmt_insert_doc->close();
}

$mpdf->Output('Receipt_' . ($transaction_data['payment_ref'] ?? ('OFFLINE_TRANS_' . $transaction_data['id'])) . '.pdf', 'I');
?>