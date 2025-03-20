<?php
require_once 'includes/functions.php';
checkSession();

header('Content-Type: application/json');

try {
    if (!isset($_GET['id'])) {
        throw new Exception("Geçersiz dosya.");
    }

    $fileId = (int)$_GET['id'];
    $userId = $_SESSION[SESSION_PREFIX.'user_id'];

    // Dosya bilgilerini veritabanından al
    $conn = connectDB();
    $stmt = $conn->prepare("
        SELECT f.*, u.username as uploader 
        FROM files f 
        JOIN users u ON f.user_id = u.id 
        WHERE f.id = ? AND (f.user_id = ? OR f.is_common = 1)
    ");
    
    $stmt->bind_param("ii", $fileId, $userId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        throw new Exception("Dosya bulunamadı veya erişim izniniz yok.");
    }

    $file = $result->fetch_assoc();
    
    // Klasör kontrolü
    $isFolder = pathinfo($file['filename'], PATHINFO_EXTENSION) === '';
    
    // JSON yanıtı hazırla
    $response = [
        'id' => $file['id'],
        'filename' => $file['filename'],
        'filesize' => formatFileSize($file['filesize']),
        'uploader' => $file['uploader'],
        'upload_date' => date('d.m.Y H:i', strtotime($file['upload_date'])),
        'is_common' => (bool)$file['is_common'],
        'is_folder' => $isFolder
    ];
    
    echo json_encode($response);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['error' => $e->getMessage()]);
}
?>