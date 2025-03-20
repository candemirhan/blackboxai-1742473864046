<?php
require_once 'includes/functions.php';
require_once 'includes/ftp.php';
checkSession();

try {
if (!isset($_GET['path'])) {
        throw new Exception("Geçersiz klasör.");
    }

    $folderPath = $_GET['path'];
    $userId = $_SESSION[SESSION_PREFIX.'user_id'];

    // FTP'den klasör içeriğini al
    $ftp = FTPManager::getInstance();
    try {
        $files = $ftp->listDirectory($folderPath);
    } catch (Exception $e) {
        throw new Exception("Klasör içeriği alınamadı: " . $e->getMessage());
    }

    // Klasör adını al
    $folderName = basename($folderPath);

} catch (Exception $e) {
    setError($e->getMessage());
    header("Location: dashboard.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($folder['filename']); ?> - <?php echo SITE_TITLE; ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="bg-gray-50">
    <nav class="bg-white shadow-sm">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <a href="dashboard.php" class="text-gray-500 hover:text-gray-700">
                        <i class="fas fa-arrow-left mr-2"></i>Geri Dön
                    </a>
                </div>
                <div class="flex items-center">
            <span class="text-gray-600"><?php echo htmlspecialchars($folderName); ?></span>
                </div>
            </div>
        </div>
    </nav>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <?php echo showMessages(); ?>

        <div class="bg-white rounded-lg shadow-sm p-6">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <?php foreach ($files as $file): ?>
                    <?php
                    $isDir = !strpos($file, '.');
                    $fileName = basename($file);
                    ?>
                    <div class="border rounded-lg p-4 hover:shadow-md transition duration-150">
                        <div class="flex items-center">
                            <i class="<?php echo $isDir ? 'fas fa-folder text-yellow-400' : 'fas fa-file text-blue-400'; ?> text-2xl mr-3"></i>
                            <div class="flex-1">
                                <h3 class="font-medium text-gray-900"><?php echo htmlspecialchars($fileName); ?></h3>
                            </div>
                        </div>
                        <div class="mt-4 flex justify-end space-x-2">
                            <?php if (!$isDir): ?>
                            <a href="download.php?path=<?php echo urlencode($folder['filepath'] . '/' . $fileName); ?>" 
                               class="text-blue-600 hover:text-blue-800">
                                <i class="fas fa-download"></i>
                            </a>
                            <?php else: ?>
                            <a href="browse.php?path=<?php echo urlencode($folder['filepath'] . '/' . $fileName); ?>" 
                               class="text-green-600 hover:text-green-800">
                                <i class="fas fa-folder-open"></i>
                            </a>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</body>
</html>