<?php
defined('BASEPATH') OR exit('No direct script access allowed');

// HAPUS semua define() untuk konstanta yang sudah didefinisikan
// Hanya tambahkan konstanta custom jika diperlukan

// Contoh konstanta custom untuk aplikasi Anda:
define('SITE_NAME', 'MKS Sports Quiz');
define('UPLOAD_PATH', FCPATH . 'uploads/');
define('MAX_FILE_SIZE', 5242880); // 5MB

// JANGAN definisikan ulang: APPPATH, BASEPATH, ENVIRONMENT, CI_VERSION
// Karena sudah didefinisikan oleh CodeIgniter