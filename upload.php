<?php
require_once 'includes/functions.php';
require_once 'includes/ftp.php';
checkSession();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: dashboard.php");
    exit();
}

try {
    // CSRF kontrolü
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION[SESSION_PREFIX.'csrf_token']) {
        throw new Exception("Güvenlik doğrulaması başarısız.");
    }

    // Dosya kontrolü
    if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
        throw new Exception("Dosya yükleme hatası oluştu.");
    }

    $file = $_FILES['file'];
    $fileName = $file['name'];
    $fileTmpPath = $file['tmp_name'];
    $fileSize = $file['size'];
    $visibility = $_POST['visibility'] ?? 'personal';

    // Dosya boyutu kontrolü
    if ($fileSize > MAX_UPLOAD_SIZE) {
        throw new Exception("Dosya boyutu çok büyük. Maksimum " . formatFileSize(MAX_UPLOAD_SIZE) . " olabilir.");
    }

    // Dosya uzantısı kontrolü
    $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
    $allowedExtensions = explode(',', ALLOWED_EXTENSIONS);
    if (!in_array($fileExtension, $allowedExtensions)) {
        throw new Exception("Bu dosya türüne izin verilmiyor. İzin verilen türler: " . ALLOWED_EXTENSIONS);
    }

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