<?php
/**
 * Bulut Depolama Sistemi Konfigürasyon Dosyası
 * 
 * Bu dosyayı kendi sunucu bilgilerinize göre düzenleyin.
 * Örnek değerler yorum satırlarında belirtilmiştir.
 */

// Veritabanı Bağlantı Bilgileri
define('DB_HOST', 'localhost');     // Örnek: localhost veya 127.0.0.1
define('DB_USER', 'root');         // Örnek: root veya cloud_user
define('DB_PASS', '');             // Veritabanı şifreniz
define('DB_NAME', 'cloud_storage'); // Veritabanı adı

// FTP Sunucu Bilgileri
define('FTP_HOST', 'localhost');    // FTP sunucu adresi
define('FTP_USER', 'ftpuser');      // FTP kullanıcı adı
define('FTP_PASS', 'ftppass');      // FTP şifresi
define('FTP_PORT', 21);             // FTP port (genellikle 21)

// Site Ayarları
define('SITE_TITLE', 'Bulut Depolama Sistemi');
define('SITE_LOGO', 'assets/img/logo.png');     // Logo yolu
define('SITE_FAVICON', 'assets/img/favicon.ico'); // Favicon yolu

// Oturum Güvenliği
define('SESSION_PREFIX', 'cloud_'); // Oturum öneki

// Dosya Yükleme Ayarları
define('MAX_UPLOAD_SIZE', 1024 * 1024 * 100); // 100MB
define('ALLOWED_EXTENSIONS', 'jpg,jpeg,png,gif,pdf,doc,docx,xls,xlsx,zip,rar,txt');

// Hata Raporlama
error_reporting(E_ALL & ~E_NOTICE);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/error.log');
?>