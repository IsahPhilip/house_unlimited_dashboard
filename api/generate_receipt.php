<?php
// api/generate_receipt.php - Generates beautiful PDF receipt
require '../inc/config.php';
require '../inc/auth.php';
require '../vendor/autoload.php'; // TCPDF or FPDF

$id = intval($_GET['id'] ?? 0);
$ref = $_GET['ref'] ?? '';

if ($id > 0) {
    $stmt = $db->prepare("SELECT t.*, u.name, u.email, p.title as property 
                          FROM transactions t 
                          LEFT JOIN users u ON t.user_id = u.id 
                          LEFT JOIN properties p ON t.property_id = p.id 
                          WHERE t.id = ? AND t.user_id = ?");
    $stmt->bind_param('ii', $id, $_SESSION['user']['id']);
} else {
    $stmt = $db->prepare("SELECT t.*, u.name, u.email, p.title as property 
                          FROM transactions t 
                          LEFT JOIN users u ON t.user_id = u.id 
                          LEFT JOIN properties p ON t.property_id = p.id 
                          WHERE t.payment_ref = ?");
    $stmt->bind_param('s', $ref);
}
$stmt->execute();
$result = $stmt->get_result();
$txn = $result->fetch_assoc();

if (!$txn) die('Transaction not found');

$pdf->SetCreator('House Unlimited');
$pdf->SetAuthor('House Unlimited & Land Services');
$pdf->SetTitle('Payment Receipt - ' . $txn['payment_ref']);
$pdf->SetMargins(15, 15, 15);
$pdf->AddPage();

$html = '
<h1 style="text-align:center; color:#1e40af;">Payment Receipt</h1>
<p style="text-align:center; font-size:18px;"><strong>HOUSE UNLIMITED & LAND SERVICES NIGERIA</strong></p>
<p style="text-align:center;">Lagos • Abuja • Port Harcourt | +234 803 000 0000</p>
<hr>

<table cellpadding="8">
    <tr><td><strong>Receipt No:</strong></td><td>' . $txn['payment_ref'] . '</td></tr>
    <tr><td><strong>Date:</strong></td><td>' . date('d F Y \a\t h:i A', strtotime($txn['created_at'])) . '</td></tr>
    <tr><td><strong>Client Name:</strong></td><td>' . htmlspecialchars($txn['name']) . '</td></tr>
    <tr><td><strong>Email:</strong></td><td>' . $txn['email'] . '</td></tr>
    <tr><td><strong>Property:</strong></td><td>' . ($txn['property'] ?? 'General Booking') . '</td></tr>
    <tr><td><strong>Amount Paid:</strong></td><td style="font-size:20px; color:#10b981;"><strong>₦' . number_format($txn['amount'], 0, '.', ',') . '</strong></td></tr>
    <tr><td><strong>Status:</strong></td><td><span style="color:#10b981; font-weight:bold;">PAID</span></td></tr>
</table>

<br><br>
<p style="text-align:center; color:#64748b;">
    Thank you for choosing House Unlimited.<br>
    This is an electronically generated receipt.
</p>
';

$pdf->writeHTML($html);
log_activity("Generated invoice #INV-2025-0487 for client");
$pdf->Output('Receipt_' . $txn['payment_ref'] . '.pdf', 'I');
?>