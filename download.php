<?php
require_once 'includes/functions.php';
require_once 'includes/ftp.php';
checkSession();

try {
    if (!isset($_GET['id'])) {
        throw new Exception("Geçersiz dosya.");
    }

    $fileId = (int)$_GET['id'];
    $userId = $_SESSION[SESSION_PREFIX.'user_id'];

    // Dosya bilgilerini veritabanından al
    $conn = connectDB();
    $stmt = $conn->prepare("SELECT * FROM files WHERE id = ? AND (user_id = ? OR is_common = 1)");
    $stmt->bind_param("ii", $fileId, $userId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        throw new Exception("Dosya bulunamadı veya erişim izniniz yok.");
    }

    $file = $result->fetch_assoc();
    
    // FTP yöneticisini başlat
    $ftp = FTPManager::getInstance();
    
    // Geçici dosya adı oluştur
    $tempFile = tempnam(sys_get_temp_dir(), 'DWN');
    
    // Dosyayı FTP'den indir
    $ftp->downloadFile($file['filepath'], $tempFile);
    
    // Dosya tipini belirle
    $mimeType = getMimeType(pathinfo($file['filename'], PATHINFO_EXTENSION));
    
    // Header'ları ayarla
    header('Content-Type: ' . $mimeType);
    header('Content-Disposition: attachment; filename="' . $file['filename'] . '"');
    header('Content-Length: ' . filesize($tempFile));
    header('Cache-Control: no-cache, must-revalidate');
    header('Pragma: no-cache');
    header('Expires: 0');
    
    // Dosyayı gönder
    readfile($tempFile);
    
    // Geçici dosyayı sil
    unlink($tempFile);
    
    exit();

} catch (Exception $e) {
    setError($e->getMessage());
    header("Location: dashboard.php");
    exit();
}
?>