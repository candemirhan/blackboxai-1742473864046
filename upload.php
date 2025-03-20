<?php
require_once 'includes/functions.php';
require_once 'includes/ftp.php';
checkSession();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: dashboard.php");
    exit();
}

try {
    // Dosya kontrolü
    if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
        throw new Exception("Dosya yükleme hatası oluştu.");
    }

    $file = $_FILES['file'];
    $fileName = $file['name'];
    $fileTmpPath = $file['tmp_name'];
    $fileSize = $file['size'];
    $visibility = $_POST['visibility'] ?? 'personal';

    // Güvenli dosya adı oluştur
    $safeFileName = generateUniqueFilename($fileName);
    
    // FTP yöneticisini başlat
    $ftp = FTPManager::getInstance();
    
    // Kullanıcı klasörünü oluştur (yoksa)
    $userDir = $_SESSION[SESSION_PREFIX.'username'];
    if (!$ftp->fileExists($userDir)) {
        $ftp->createDirectory($userDir);
    }
    
    // Dosyayı FTP'ye yükle
    $remotePath = $userDir . '/' . $safeFileName;
    $ftp->uploadFile($fileTmpPath, $remotePath);
    
    // Veritabanına kaydet
    $conn = connectDB();
    $stmt = $conn->prepare("INSERT INTO files (filename, filepath, filesize, filetype, is_common, user_id) VALUES (?, ?, ?, ?, ?, ?)");
    
    $isCommon = $visibility === 'common' ? 1 : 0;
    $userId = $_SESSION[SESSION_PREFIX.'user_id'];
    $fileType = mime_content_type($fileTmpPath);
    
    $stmt->bind_param("ssisii", 
        $fileName,
        $remotePath,
        $fileSize,
        $fileType,
        $isCommon,
        $userId
    );
    
    if (!$stmt->execute()) {
        throw new Exception("Veritabanı kaydı oluşturulamadı.");
    }
    
    setSuccess("Dosya başarıyla yüklendi.");
    
} catch (Exception $e) {
    setError("Hata: " . $e->getMessage());
}

header("Location: dashboard.php");
exit();
?>