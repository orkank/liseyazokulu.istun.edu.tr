<?php
// DenizBank 3D Secure Payment Request - Simple Example
$userCode =  'istunedu_adm';
$userPass = '2024Spos';
$shopCode = '9488';
$merchantPass = '9zbHT';

// Merchant (store) details - Replace with your credentials
$merchant_id = "9488";
$terminal_id = "01645391";
$store_key   = "9488"; // Secret store key provided by DenizBank
$amount      = "1000"; // Payment amount in smallest currency unit (e.g., 10.00 TL -> 1000)
$order_id    = uniqid(); // Unique order ID
$currency    = "949"; // Currency code (949 for TRY)
$success_url = "https://dev.lisekisokulu.istun.edu.tr/pos.php"; // Your success return URL
$fail_url    = "https://dev.lisekisokulu.istun.edu.tr/pos.php"; // Your fail return URL
$installment = "0"; // Installment count (0 means no installment)

// Generate Hash
$hash_str = $terminal_id . $order_id . $amount . $success_url . $fail_url . $store_key;
$hash = base64_encode(pack('H*', hash('sha512', $hash_str)));

// Bank URL for 3D Secure payment (replace with the real test/production URL)
$bank_url = "https://inter-vpos.com.tr/mpi/Default.aspx"; // Test URL
print_r($_POST);
?>

<!-- Payment Form -->
<form method="POST" action="<?= htmlspecialchars($bank_url); ?>">
    <input type="hidden" name="MerchantID" value="<?= $merchant_id; ?>">
    <input type="hidden" name="TerminalID" value="<?= $terminal_id; ?>">
    <input type="hidden" name="Amount" value="<?= $amount; ?>">
    <input type="hidden" name="OrderID" value="<?= $order_id; ?>">
    <input type="hidden" name="Currency" value="<?= $currency; ?>">
    <input type="hidden" name="SuccessURL" value="<?= $success_url; ?>">
    <input type="hidden" name="FailURL" value="<?= $fail_url; ?>">
    <input type="hidden" name="Installment" value="<?= $installment; ?>">
    <input type="hidden" name="Hash" value="<?= $hash; ?>">
    <button type="submit">Proceed to Payment</button>
</form>