<?php
require_once 'includes/functions.php';

// Kullanıcı zaten giriş yapmışsa dashboard'a yönlendir
if (isset($_SESSION[SESSION_PREFIX.'user_id'])) {
    header("Location: dashboard.php");
    exit();
}

// Giriş formu gönderildiğinde
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = cleanInput($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (empty($username) || empty($password)) {
        setError("Kullanıcı adı ve şifre gereklidir.");
    } else {
        if (loginUser($username, $password)) {
            header("Location: dashboard.php");
            exit();
        } else {
            setError("Geçersiz kullanıcı adı veya şifre.");
        }
    }
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Giriş - <?php echo SITE_TITLE; ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #0061f2 0%, #00c6f2 100%);
            min-height: 100vh;
        }
    </style>
</head>
<body class="flex items-center justify-center min-h-screen p-4">
    <div class="bg-white rounded-lg shadow-xl p-8 w-full max-w-md">
        <div class="text-center mb-8">
            <img src="<?php echo SITE_LOGO; ?>" alt="Logo" class="mx-auto h-16 mb-4">
            <h1 class="text-2xl font-bold text-gray-800"><?php echo SITE_TITLE; ?></h1>
            <p class="text-gray-600 mt-2">Lütfen giriş yapın</p>
        </div>

        <?php echo showMessages(); ?>

        <form method="POST" class="space-y-6">
            <?php echo getCSRFTokenField(); ?>
            <div>
                <label for="username" class="block text-sm font-medium text-gray-700 mb-2">
                    Kullanıcı Adı
                </label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-500">
                        <i class="fas fa-user"></i>
                    </span>
                    <input type="text" id="username" name="username" 
                           class="pl-10 w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500"
                           placeholder="Kullanıcı adınız">
                </div>
            </div>

            <div>
                <label for="password" class="block text-sm font-medium text-gray-700 mb-2">
                    Şifre
                </label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-500">
                        <i class="fas fa-lock"></i>
                    </span>
                    <input type="password" id="password" name="password" 
                           class="pl-10 w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500"
                           placeholder="Şifreniz">
                </div>
            </div>

            <button type="submit" 
                    class="w-full flex justify-center py-3 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition duration-150">
                <i class="fas fa-sign-in-alt mr-2"></i> Giriş Yap
            </button>
        </form>

        <div class="mt-6 text-center text-sm">
            <p class="text-gray-600">
                Demo Hesap: <span class="font-semibold">can</span> / <span class="font-semibold">123456</span>
            </p>
        </div>
    </div>

    <div class="fixed bottom-4 text-center w-full text-white text-sm">
        &copy; <?php echo date('Y'); ?> <?php echo SITE_TITLE; ?>. Tüm hakları saklıdır.
    </div>
</body>
</html>