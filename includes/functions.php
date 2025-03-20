<?php
session_start();
require_once 'db.php';

// Kullanıcı girişi kontrolü
function loginUser($username, $password) {
    $conn = connectDB();
    $password = md5($password);
    
    $stmt = $conn->prepare("SELECT id, username, is_admin FROM users WHERE username = ? AND password = ?");
    $stmt->bind_param("ss", $username, $password);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        $_SESSION[SESSION_PREFIX.'user_id'] = $user['id'];
        $_SESSION[SESSION_PREFIX.'username'] = $user['username'];
        $_SESSION[SESSION_PREFIX.'is_admin'] = $user['is_admin'];
        return true;
    }
    return false;
}

// Oturum kontrolü
function checkSession() {
    if (!isset($_SESSION[SESSION_PREFIX.'user_id'])) {
        header("Location: index.php");
        exit();
    }
}

// Admin kontrolü
function checkAdmin() {
    if (!isset($_SESSION[SESSION_PREFIX.'is_admin']) || $_SESSION[SESSION_PREFIX.'is_admin'] != 1) {
        header("Location: dashboard.php");
        exit();
    }
}

// Çıkış yap
function logout() {
    session_unset();
    session_destroy();
    header("Location: index.php");
    exit();
}

// Dosya boyutunu formatla
function formatFileSize($bytes) {
    if ($bytes >= 1073741824) {
        return number_format($bytes / 1073741824, 2) . ' GB';
    } elseif ($bytes >= 1048576) {
        return number_format($bytes / 1048576, 2) . ' MB';
    } elseif ($bytes >= 1024) {
        return number_format($bytes / 1024, 2) . ' KB';
    } else {
        return $bytes . ' B';
    }
}

// Kullanıcının dosyalarını getir
function getUserFiles($userId, $isCommon = false) {
    $conn = connectDB();
    $sql = "SELECT f.*, u.username as uploader FROM files f 
            JOIN users u ON f.user_id = u.id 
            WHERE " . ($isCommon ? "f.is_common = 1" : "f.user_id = ?") . 
            " ORDER BY f.upload_date DESC";
    
    if (!$isCommon) {
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $userId);
    } else {
        $stmt = $conn->prepare($sql);
    }
    
    $stmt->execute();
    $result = $stmt->get_result();
    $files = [];
    
    while ($row = $result->fetch_assoc()) {
        $files[] = $row;
    }
    
    return $files;
}

// Güvenli string temizleme
function cleanInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// Dosya uzantısından MIME type belirleme
function getMimeType($extension) {
    $mimes = [
        'txt' => 'text/plain',
        'pdf' => 'application/pdf',
        'doc' => 'application/msword',
        'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'xls' => 'application/vnd.ms-excel',
        'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        'jpg' => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'png' => 'image/png',
        'gif' => 'image/gif',
        'zip' => 'application/zip',
        'rar' => 'application/x-rar-compressed'
    ];
    
    return isset($mimes[strtolower($extension)]) ? $mimes[strtolower($extension)] : 'application/octet-stream';
}

// Dosya adından uzantı çıkarma
function getFileExtension($filename) {
    return strtolower(pathinfo($filename, PATHINFO_EXTENSION));
}

// Rastgele benzersiz dosya adı oluşturma
function generateUniqueFilename($originalName) {
    $extension = getFileExtension($originalName);
    return uniqid() . '_' . time() . '.' . $extension;
}

// Hata mesajı oluşturma
function setError($message) {
    $_SESSION[SESSION_PREFIX.'error'] = $message;
}

// Başarı mesajı oluşturma
function setSuccess($message) {
    $_SESSION[SESSION_PREFIX.'success'] = $message;
}

// Mesajları göster ve temizle
function showMessages() {
    $output = '';
    if (isset($_SESSION[SESSION_PREFIX.'error'])) {
        $output .= '<div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4">' . $_SESSION[SESSION_PREFIX.'error'] . '</div>';
        unset($_SESSION[SESSION_PREFIX.'error']);
    }
    if (isset($_SESSION[SESSION_PREFIX.'success'])) {
        $output .= '<div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-4">' . $_SESSION[SESSION_PREFIX.'success'] . '</div>';
        unset($_SESSION[SESSION_PREFIX.'success']);
    }
    return $output;
}
?>