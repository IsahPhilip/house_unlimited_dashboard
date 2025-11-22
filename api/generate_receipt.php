<?php
// api/generate_receipt.php - FINAL + DYNAMIC LOGS + WORKS 100% (NO TCPDF ERRORS)
require '../inc/config.php';
require '../inc/auth.php';
require '../vendor/autoload.php'; // This loads mPDF

use Mpdf\Mpdf;

$id  = intval($_GET['id'] ?? 0);
$ref = trim($_GET['ref'] ?? '');

if ($id <= 0 && empty($ref)) {
    die('Invalid request.');
}

$user_id = $_SESSION['user']['id'];

// Fetch transaction
if ($id > 0) {
    $stmt = $db->prepare("
        SELECT t.*, u.name AS client_name, u.email, p.title AS property_title, p.location 
        FROM transactions t 
        LEFT JOIN users u ON t.user_id = u.id 
        LEFT JOIN properties p ON t.property_id = p.id 
        WHERE t.id = ? AND t.user_id = ?
    ");
    $stmt->bind_param('ii', $id, $user_id);
} else {
    $stmt = $db->prepare("
        SELECT t.*, u.name AS client_name, u.email, p.title AS property_title, p.location 
        FROM transactions t 
        LEFT JOIN users u ON t.user_id = u.id 
        LEFT JOIN properties p ON t.property_id = p.id 
        WHERE t.payment_ref = ? AND t.user_id = ?
    ");
    $stmt->bind_param('si', $ref, $user_id);
}

$stmt->execute();
$txn = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$txn) {
    die('Transaction not found or access denied.');
}

// DYNAMIC LOG — NO HARDCODING
$amount = '₦' . number_format($txn['amount']);
$property = $txn['property_title'] 
    ? $txn['property_title'] . ' in ' . ucwords($txn['location'])
    : 'General Booking Fee';
$date = date('j M Y \a\t g:i A', strtotime($txn['created_at']));
$client = $txn['client_name'] ?? 'Client';

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
        <td class="value">' . htmlspecialchars($txn['payment_ref']) . '</td>
    </tr>
    <tr>
        <td class="label">Payment Date:</td>
        <td class="value">' . date('l, j F Y \a\t g:i A', strtotime($txn['created_at'])) . '</td>
    </tr>
    <tr>
        <td class="label">Client Name:</td>
        <td class="value">' . htmlspecialchars($client) . '</td>
    </tr>
    <tr>
        <td class="label">Email:</td>
        <td class="value">' . htmlspecialchars($txn['email']) . '</td>
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
        <td class="status">SUCCESSFULLY PAID</td>
    </tr>
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
$mpdf->Output('Receipt_' . $txn['payment_ref'] . '.pdf', 'I');
?>