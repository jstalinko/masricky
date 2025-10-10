<?php

require 'config.php';
use Tripay\Main;

// Inisialisasi class client Tripay
$main = new Main(
    TRIPAY_API_KEY,
    TRIPAY_PRIVATE_KEY,
    TRIPAY_MERCHANT_CODE,
    'sandbox'
);

// Generate nomor referensi/invoice unik dari sisi Anda
$merchantRef = 'BSTORE' . time().rand(100,999);
try {
    $init = $main->initTransaction($merchantRef);
$init->setAmount(199999);
    // Data transaksi
$data = [
    'method'         => 'QRISC', // Contoh menggunakan BRI Virtual Account
    'merchant_ref'   => $merchantRef,
    'amount'         => $init->getAmount(),
    'customer_name'  => 'Nama Pelanggan',
    'customer_email' => 'emailpelanggan@example.com',
    'customer_phone' => '081234567890',
    'order_items'    => [
        [
            'sku'       => 'PRODUK-01',
            'name'      => 'Contoh Produk Satu',
            'price'     => $init->getAmount(),
            'quantity'  => 1
        ],
    ],
    // 'callback_url'   => 'https://domainanda.com/payment_callback.php', // URL untuk menerima callback
    // 'return_url'     => 'https://domainanda.com/payment_success.php',
    'expired_time'   => (time() + (24 * 60 * 60)), // Expired dalam 24 jam
    'signature'      => $init->createSignature()
];


    // Kirim permintaan transaksi
    $transaction = $init->closeTransaction();
    $transaction->setPayload($data);

    // Tampilkan hasil response
    header('Content-Type: application/json');
    
    echo json_encode($transaction->getJson(), JSON_PRETTY_PRINT);

} catch (\Exception $e) {
    // Tangani jika terjadi error
    header('Content-Type: application/json');
    http_response_code(500); // Internal Server Error
    echo json_encode([
        'success' => false,
        'message' => 'Terjadi kesalahan: ' . $e->getMessage()
    ]);
}