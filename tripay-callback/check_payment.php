<?php

require 'config.php';

// Inisialisasi class client Tripay
$tripay = new \Tripay\Client(
    TRIPAY_API_KEY,
    TRIPAY_PRIVATE_KEY,
    TRIPAY_MERCHANT_CODE,
    TRIPAY_SANDBOX_MODE
);

// Ambil reference code dari URL (contoh: check_payment.php?reference=T123XXX)
$referenceCode = isset($_GET['reference']) ? $_GET['reference'] : '';

if (empty($referenceCode)) {
    http_response_code(400); // Bad Request
    die(json_encode(['success' => false, 'message' => 'Error: reference code tidak ditemukan.']));
}

try {
    // Panggil method untuk mendapatkan detail transaksi
    $detail = $tripay->getDetailTransaction($referenceCode);

    // Tampilkan hasil response
    header('Content-Type: application/json');
    echo json_encode($detail->getData(), JSON_PRETTY_PRINT);

} catch (\Exception $e) {
    // Tangani jika terjadi error (misal: transaksi tidak ditemukan)
    header('Content-Type: application/json');
    http_response_code(500); // Internal Server Error
    echo json_encode([
        'success' => false,
        'message' => 'Terjadi kesalahan: ' . $e->getMessage()
    ]);
}