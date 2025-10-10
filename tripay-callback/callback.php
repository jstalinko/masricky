<?php

require 'config.php'; // Hanya butuh Private Key dari config

// Ambil callback signature dari header
$callbackSignature = isset($_SERVER['HTTP_X_CALLBACK_SIGNATURE']) ? $_SERVER['HTTP_X_CALLBACK_SIGNATURE'] : '';

// Ambil data JSON dari body request
$json = file_get_contents("php://input");

// Pastikan signature dan data callback tidak kosong
if (!$callbackSignature || !$json) {
    http_response_code(400);
    die(json_encode(['success' => false, 'message' => 'Invalid request']));
}

// Generate signature lokal untuk validasi
$signature = hash_hmac('sha256', $json, TRIPAY_PRIVATE_KEY);

// Validasi signature
if ($callbackSignature !== $signature) {
    http_response_code(401); // Unauthorized
    die(json_encode(['success' => false, 'message' => 'Invalid signature']));
}

// Decode data JSON
$data = json_decode($json);
$event = $_SERVER['HTTP_X_CALLBACK_EVENT'];

// Pastikan proses callback berhasil
if (json_last_error() !== JSON_ERROR_NONE) {
    http_response_code(400);
    die(json_encode(['success' => false, 'message' => 'Invalid JSON format']));
}

// Handle event sesuai dengan status pembayaran
if ($event == 'payment_status') {
    // Lakukan pengecekan status
    if (isset($data->status) && $data->status == 'PAID') {
        $merchantRef = $data->merchant_ref;
        
        // LOGIKA ANDA DI SINI
        // 1. Cek ke database, apakah invoice/merchant_ref tersebut ada dan statusnya belum lunas.
        // 2. Jika ada dan belum lunas, update statusnya menjadi lunas.
        // 3. Berikan respon sukses ke Tripay.
        // Contoh: file_put_contents('callback_log.txt', "[SUCCESS] Invoice " . $merchantRef . " dibayar.\n", FILE_APPEND);
    }
}

// Beri respon ke Tripay bahwa callback berhasil diterima
header('Content-Type: application/json');
echo json_encode(['success' => true]);
exit;