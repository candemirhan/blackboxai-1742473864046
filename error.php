<?php
require_once 'includes/functions.php';

$errorCode = $_GET['code'] ?? '404';
$errorMessage = '';

switch ($errorCode) {
    case '403':
        $errorMessage = 'Bu sayfaya erişim izniniz bulunmamaktadır.';
        break;
    case '404':
        $errorMessage = 'Aradığınız sayfa bulunamadı.';
        break;
    case '500':
        $errorMessage = 'Sunucu hatası oluştu.';
        break;
    default:
        $errorMessage = 'Bir hata oluştu.';
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hata <?php echo $errorCode; ?> - <?php echo SITE_TITLE; ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="bg-gray-50 flex items-center justify-center min-h-screen">
    <div class="text-center">
        <div class="text-6xl font-bold text-gray-300 mb-4">
            <?php echo $errorCode; ?>
        </div>
        <h1 class="text-2xl font-semibold text-gray-800 mb-4">
            <?php echo $errorMessage; ?>
        </h1>
        <p class="text-gray-600 mb-8">
            Bir sorun oluştu. Lütfen tekrar deneyin veya ana sayfaya dönün.
        </p>
        <a href="index.php" class="inline-flex items-center px-4 py-2 border border-transparent text-base font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700">
            <i class="fas fa-home mr-2"></i>
            Ana Sayfaya Dön
        </a>
    </div>
</body>
</html>