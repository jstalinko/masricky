<?php

// Aktifkan mode error reporting untuk development
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Muat autoloader Composer
require 'vendor/autoload.php';

// Konfigurasi Kredensial Tripay Anda
define('TRIPAY_API_KEY', 'DEV-h9WRz6gl4Ttcgq3eAnIoypdXGHOLEBxyxkcwfjWM');
define('TRIPAY_PRIVATE_KEY', 'ko1mj-wbDMD-dTL9G-384gd-vNtrY');
define('TRIPAY_MERCHANT_CODE', 'T17010');
define('TRIPAY_SANDBOX_MODE', true); // Ganti menjadi false untuk mode produksi