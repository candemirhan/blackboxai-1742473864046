<?php
require_once 'includes/functions.php';
checkSession();
checkAdmin();

// İşlem mesajları
$messages = [];

// POST işlemlerinde CSRF kontrolü
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    checkCSRFToken();
}

// Site ayarlarını güncelle
if (isset($_POST['update_site_settings'])) {
    $siteTitle = cleanInput($_POST['site_title']);
    
    // Config dosyasını güncelle
    $configFile = file_get_contents('config.php');
    
    // Site başlığını güncelle
    $configFile = preg_replace(
        "/define\('SITE_TITLE',\s*'.*?'\);/",
        "define('SITE_TITLE', '$siteTitle');",
        $configFile
    );
    
    // Logo yükleme
    if (isset($_FILES['site_logo']) && $_FILES['site_logo']['error'] === UPLOAD_ERR_OK) {
        $logoPath = 'assets/img/' . basename($_FILES['site_logo']['name']);
        move_uploaded_file($_FILES['site_logo']['tmp_name'], $logoPath);
        $configFile = preg_replace(
            "/define\('SITE_LOGO',\s*'.*?'\);/",
            "define('SITE_LOGO', '$logoPath');",
            $configFile
        );
    }
    
    // Favicon yükleme
    if (isset($_FILES['site_favicon']) && $_FILES['site_favicon']['error'] === UPLOAD_ERR_OK) {
        $faviconPath = 'assets/img/' . basename($_FILES['site_favicon']['name']);
        move_uploaded_file($_FILES['site_favicon']['tmp_name'], $faviconPath);
        $configFile = preg_replace(
            "/define\('SITE_FAVICON',\s*'.*?'\);/",
            "define('SITE_FAVICON', '$faviconPath');",
            $configFile
        );
    }
    
    file_put_contents('config.php', $configFile);
    $messages[] = ['type' => 'success', 'text' => 'Site ayarları güncellendi.'];
}

// FTP ayarlarını güncelle
if (isset($_POST['update_ftp_settings'])) {
    $ftpHost = cleanInput($_POST['ftp_host']);
    $ftpUser = cleanInput($_POST['ftp_user']);
    $ftpPass = cleanInput($_POST['ftp_pass']);
    $ftpPort = (int)$_POST['ftp_port'];
    
    $configFile = file_get_contents('config.php');
    
    $configFile = preg_replace(
        "/define\('FTP_HOST',\s*'.*?'\);/",
        "define('FTP_HOST', '$ftpHost');",
        $configFile
    );
    $configFile = preg_replace(
        "/define\('FTP_USER',\s*'.*?'\);/",
        "define('FTP_USER', '$ftpUser');",
        $configFile
    );
    $configFile = preg_replace(
        "/define\('FTP_PASS',\s*'.*?'\);/",
        "define('FTP_PASS', '$ftpPass');",
        $configFile
    );
    $configFile = preg_replace(
        "/define\('FTP_PORT',\s*\d+\);/",
        "define('FTP_PORT', $ftpPort);",
        $configFile
    );
    
    file_put_contents('config.php', $configFile);
    $messages[] = ['type' => 'success', 'text' => 'FTP ayarları güncellendi.'];
}

// Yeni kullanıcı ekle
if (isset($_POST['add_user'])) {
    $username = cleanInput($_POST['username']);
    $password = md5($_POST['password']); // MD5 hash
    
    $conn = connectDB();
    $stmt = $conn->prepare("INSERT INTO users (username, password) VALUES (?, ?)");
    $stmt->bind_param("ss", $username, $password);
    
    if ($stmt->execute()) {
        $messages[] = ['type' => 'success', 'text' => 'Kullanıcı başarıyla eklendi.'];
    } else {
        $messages[] = ['type' => 'error', 'text' => 'Kullanıcı eklenirken hata oluştu.'];
    }
}

// Dosya sil
if (isset($_POST['delete_file'])) {
    $fileId = (int)$_POST['file_id'];
    
    try {
        $conn = connectDB();
        
        // Dosya bilgilerini al
        $stmt = $conn->prepare("SELECT filepath FROM files WHERE id = ?");
        $stmt->bind_param("i", $fileId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($file = $result->fetch_assoc()) {
            // FTP'den dosyayı sil
            $ftp = FTPManager::getInstance();
            $ftp->deleteFile($file['filepath']);
            
            // Veritabanından kaydı sil
            $stmt = $conn->prepare("DELETE FROM files WHERE id = ?");
            $stmt->bind_param("i", $fileId);
            $stmt->execute();
            
            $messages[] = ['type' => 'success', 'text' => 'Dosya başarıyla silindi.'];
        }
    } catch (Exception $e) {
        $messages[] = ['type' => 'error', 'text' => 'Dosya silinirken hata: ' . $e->getMessage()];
    }
}

// Tüm kullanıcıları getir
$conn = connectDB();
$users = $conn->query("SELECT * FROM users ORDER BY created_at DESC")->fetch_all(MYSQLI_ASSOC);

// Tüm dosyaları getir
$files = $conn->query("
    SELECT f.*, u.username as uploader 
    FROM files f 
    JOIN users u ON f.user_id = u.id 
    ORDER BY f.upload_date DESC
")->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Yönetim Paneli - <?php echo SITE_TITLE; ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        body {
            font-family: 'Inter', sans-serif;
        }
    </style>
</head>
<body class="bg-gray-50">
    <nav class="bg-white shadow-sm">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <a href="dashboard.php" class="text-gray-500 hover:text-gray-700">
                        <i class="fas fa-arrow-left mr-2"></i>Panele Dön
                    </a>
                </div>
                <div class="flex items-center">
                    <span class="text-gray-600">Yönetici Paneli</span>
                </div>
            </div>
        </div>
    </nav>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <?php foreach ($messages as $message): ?>
        <div class="mb-4 p-4 rounded-lg <?php echo $message['type'] === 'success' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'; ?>">
            <?php echo $message['text']; ?>
        </div>
        <?php endforeach; ?>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Site Ayarları -->
            <div class="bg-white rounded-lg shadow-sm p-6">
                <h2 class="text-lg font-semibold mb-4">Site Ayarları</h2>
                <form method="POST" enctype="multipart/form-data">
                    <?php echo getCSRFTokenField(); ?>
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Site Başlığı</label>
                            <input type="text" name="site_title" value="<?php echo SITE_TITLE; ?>" 
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Site Logo</label>
                            <input type="file" name="site_logo" accept="image/*" 
                                   class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Favicon</label>
                            <input type="file" name="site_favicon" accept="image/x-icon,image/png" 
                                   class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                        </div>
                        <button type="submit" name="update_site_settings" 
                                class="w-full bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 transition duration-150">
                            Ayarları Güncelle
                        </button>
                    </div>
                </form>
            </div>

            <!-- FTP Ayarları -->
            <div class="bg-white rounded-lg shadow-sm p-6">
                <h2 class="text-lg font-semibold mb-4">FTP Ayarları</h2>
                <form method="POST">
                    <?php echo getCSRFTokenField(); ?>
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">FTP Sunucu</label>
                            <input type="text" name="ftp_host" value="<?php echo FTP_HOST; ?>" 
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">FTP Kullanıcı</label>
                            <input type="text" name="ftp_user" value="<?php echo FTP_USER; ?>" 
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">FTP Şifre</label>
                            <input type="password" name="ftp_pass" value="<?php echo FTP_PASS; ?>" 
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">FTP Port</label>
                            <input type="number" name="ftp_port" value="<?php echo FTP_PORT; ?>" 
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        </div>
                        <button type="submit" name="update_ftp_settings" 
                                class="w-full bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 transition duration-150">
                            FTP Ayarlarını Güncelle
                        </button>
                    </div>
                </form>
            </div>

            <!-- Kullanıcı Yönetimi -->
            <div class="bg-white rounded-lg shadow-sm p-6">
                <h2 class="text-lg font-semibold mb-4">Kullanıcı Yönetimi</h2>
                
                <!-- Yeni Kullanıcı Ekle -->
                <form method="POST" class="mb-6">
                    <?php echo getCSRFTokenField(); ?>
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Kullanıcı Adı</label>
                            <input type="text" name="username" required 
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Şifre</label>
                            <input type="password" name="password" required 
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        </div>
                        <button type="submit" name="add_user" 
                                class="w-full bg-green-600 text-white px-4 py-2 rounded-md hover:bg-green-700 transition duration-150">
                            Yeni Kullanıcı Ekle
                        </button>
                    </div>
                </form>

                <!-- Kullanıcı Listesi -->
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Kullanıcı Adı</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Kayıt Tarihi</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tip</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php foreach ($users as $user): ?>
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap"><?php echo htmlspecialchars($user['username']); ?></td>
                                <td class="px-6 py-4 whitespace-nowrap"><?php echo date('d.m.Y H:i', strtotime($user['created_at'])); ?></td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full <?php echo $user['is_admin'] ? 'bg-purple-100 text-purple-800' : 'bg-green-100 text-green-800'; ?>">
                                        <?php echo $user['is_admin'] ? 'Admin' : 'Kullanıcı'; ?>
                                    </span>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Dosya Yönetimi -->
            <div class="bg-white rounded-lg shadow-sm p-6">
                <h2 class="text-lg font-semibold mb-4">Dosya Yönetimi</h2>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Dosya Adı</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Yükleyen</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Boyut</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tip</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">İşlem</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php foreach ($files as $file): ?>
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap"><?php echo htmlspecialchars($file['filename']); ?></td>
                                <td class="px-6 py-4 whitespace-nowrap"><?php echo htmlspecialchars($file['uploader']); ?></td>
                                <td class="px-6 py-4 whitespace-nowrap"><?php echo formatFileSize($file['filesize']); ?></td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full <?php echo $file['is_common'] ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-800'; ?>">
                                        <?php echo $file['is_common'] ? 'Ortak' : 'Kişisel'; ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <form method="POST" class="inline-block">
                                        <?php echo getCSRFTokenField(); ?>
                                        <input type="hidden" name="file_id" value="<?php echo $file['id']; ?>">
                                        <button type="submit" name="delete_file" 
                                                class="text-red-600 hover:text-red-900"
                                                onclick="return confirm('Bu dosyayı silmek istediğinize emin misiniz?');">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</body>
</html>