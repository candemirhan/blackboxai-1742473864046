<?php
require_once 'includes/functions.php';
checkSession();

$userId = $_SESSION[SESSION_PREFIX.'user_id'];
$username = $_SESSION[SESSION_PREFIX.'username'];
$isAdmin = $_SESSION[SESSION_PREFIX.'is_admin'];

// Aktif sekme kontrolü
$activeTab = isset($_GET['tab']) ? $_GET['tab'] : 'personal';
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel - <?php echo SITE_TITLE; ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        body {
            font-family: 'Inter', sans-serif;
        }
        .upload-area {
            border: 2px dashed #cbd5e1;
            transition: all 0.3s ease;
        }
        .upload-area.dragover {
            border-color: #3b82f6;
            background-color: #eff6ff;
        }
        @media (max-width: 768px) {
            .sidebar {
                width: 100%;
                position: fixed;
                bottom: 0;
                left: 0;
                z-index: 50;
            }
        }
    </style>
</head>
<body class="bg-gray-50">
    <!-- Üst Bar -->
    <nav class="bg-white shadow-sm">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <img src="<?php echo SITE_LOGO; ?>" alt="Logo" class="h-8 w-auto">
                    <span class="ml-2 text-lg font-semibold text-gray-900"><?php echo SITE_TITLE; ?></span>
                </div>
                <div class="flex items-center">
                    <span class="text-gray-600 mr-4">
                        <i class="fas fa-user mr-2"></i><?php echo htmlspecialchars($username); ?>
                    </span>
                    <a href="logout.php" class="text-gray-600 hover:text-gray-900">
                        <i class="fas fa-sign-out-alt"></i>
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <div class="flex flex-col md:flex-row min-h-screen">
        <!-- Sol Menü -->
        <div class="sidebar bg-white shadow-sm md:w-64 md:min-h-screen">
            <nav class="px-4 py-3 md:py-6">
                <ul class="flex md:flex-col space-x-4 md:space-x-0 md:space-y-2 justify-around md:justify-start">
                    <li>
                        <a href="?tab=personal" 
                           class="flex items-center px-4 py-2 rounded-md <?php echo $activeTab === 'personal' ? 'bg-blue-50 text-blue-700' : 'text-gray-600 hover:bg-gray-50'; ?>">
                            <i class="fas fa-folder mr-3"></i>
                            <span>Kişisel</span>
                        </a>
                    </li>
                    <li>
                        <a href="?tab=common" 
                           class="flex items-center px-4 py-2 rounded-md <?php echo $activeTab === 'common' ? 'bg-blue-50 text-blue-700' : 'text-gray-600 hover:bg-gray-50'; ?>">
                            <i class="fas fa-share-alt mr-3"></i>
                            <span>Ortak</span>
                        </a>
                    </li>
                    <?php if ($isAdmin): ?>
                    <li>
                        <a href="admin.php" target="_blank"
                           class="flex items-center px-4 py-2 rounded-md text-gray-600 hover:bg-gray-50">
                            <i class="fas fa-cog mr-3"></i>
                            <span>Yönetim Paneli</span>
                        </a>
                    </li>
                    <?php endif; ?>
                </ul>
            </nav>
        </div>

        <!-- Ana İçerik -->
        <div class="flex-1 p-4 md:p-6">
            <?php echo showMessages(); ?>

            <!-- Dosya Yükleme Alanı -->
            <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
                <h2 class="text-lg font-semibold mb-4">Dosya Yükle</h2>
                <form id="uploadForm" action="upload.php" method="POST" enctype="multipart/form-data">
                    <?php echo getCSRFTokenField(); ?>
                    <div class="upload-area p-8 rounded-lg text-center cursor-pointer mb-4">
                        <input type="file" id="fileInput" name="file" class="hidden" multiple>
                        <div class="text-gray-500">
                            <i class="fas fa-cloud-upload-alt text-4xl mb-3"></i>
                            <p>Dosyaları sürükleyip bırakın veya seçmek için tıklayın</p>
                        </div>
                    </div>
                    <div class="flex items-center justify-between">
                        <div class="flex items-center">
                            <input type="radio" id="personal" name="visibility" value="personal" checked 
                                   class="h-4 w-4 text-blue-600 focus:ring-blue-500">
                            <label for="personal" class="ml-2 text-sm text-gray-700">Kişisel</label>
                            
                            <input type="radio" id="common" name="visibility" value="common" 
                                   class="ml-4 h-4 w-4 text-blue-600 focus:ring-blue-500">
                            <label for="common" class="ml-2 text-sm text-gray-700">Ortak</label>
                        </div>
                        <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 transition duration-150">
                            <i class="fas fa-upload mr-2"></i>Yüklemeyi Başlat
                        </button>
                    </div>
                </form>
            </div>

            <!-- Dosya Listesi -->
            <div class="bg-white rounded-lg shadow-sm p-6">
                <h2 class="text-lg font-semibold mb-4">
                    <?php echo $activeTab === 'personal' ? 'Kişisel Dosyalarım' : 'Ortak Dosyalar'; ?>
                </h2>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    <?php
                    $files = getUserFiles($userId, $activeTab === 'common');
                    foreach ($files as $file):
                        $isFolder = pathinfo($file['filename'], PATHINFO_EXTENSION) === '';
                    ?>
                    <div class="border rounded-lg p-4 hover:shadow-md transition duration-150 relative group file-item" 
                         data-file-id="<?php echo $file['id']; ?>">
                        <div class="flex items-start justify-between">
                            <div class="flex items-center">
                                <i class="<?php echo $isFolder ? 'fas fa-folder text-yellow-400' : 'fas fa-file text-blue-400'; ?> text-2xl mr-3"></i>
                                <div>
                                    <h3 class="font-medium text-gray-900"><?php echo htmlspecialchars($file['filename']); ?></h3>
                                    <p class="text-sm text-gray-500"><?php echo formatFileSize($file['filesize']); ?></p>
                                </div>
                            </div>
                        </div>

                        <?php if ($activeTab === 'common'): ?>
                        <div class="hidden group-hover:block absolute top-0 right-0 mt-2 mr-2 bg-white p-2 rounded shadow-lg text-sm">
                            <p><strong>Yükleyen:</strong> <?php echo htmlspecialchars($file['uploader']); ?></p>
                            <p><strong>Tarih:</strong> <?php echo date('d.m.Y H:i', strtotime($file['upload_date'])); ?></p>
                        </div>
                        <?php endif; ?>

                        <div class="mt-4 flex justify-end space-x-2">
                            <a href="download.php?id=<?php echo $file['id']; ?>" 
                               class="text-blue-600 hover:text-blue-800">
                                <i class="fas fa-download"></i>
                            </a>
                            <?php if ($isFolder): ?>
                            <a href="browse.php?id=<?php echo $file['id']; ?>" 
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
    </div>

    <!-- Mobil Dosya Detay Modal -->
    <div id="fileDetailModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50 md:hidden">
        <div class="bg-white rounded-lg p-6 m-4 max-w-sm w-full">
            <div id="fileDetailContent"></div>
            <div class="mt-4 flex justify-end">
                <button onclick="closeModal()" class="text-gray-600 hover:text-gray-800">
                    <i class="fas fa-times"></i> Kapat
                </button>
            </div>
        </div>
    </div>

    <script>
        // Dosya yükleme alanı için drag & drop
        const uploadArea = document.querySelector('.upload-area');
        const fileInput = document.getElementById('fileInput');

        uploadArea.addEventListener('click', () => fileInput.click());

        ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
            uploadArea.addEventListener(eventName, preventDefaults, false);
        });

        function preventDefaults(e) {
            e.preventDefault();
            e.stopPropagation();
        }

        ['dragenter', 'dragover'].forEach(eventName => {
            uploadArea.addEventListener(eventName, highlight, false);
        });

        ['dragleave', 'drop'].forEach(eventName => {
            uploadArea.addEventListener(eventName, unhighlight, false);
        });

        function highlight(e) {
            uploadArea.classList.add('dragover');
        }

        function unhighlight(e) {
            uploadArea.classList.remove('dragover');
        }

        uploadArea.addEventListener('drop', handleDrop, false);

        function handleDrop(e) {
            const dt = e.dataTransfer;
            const files = dt.files;
            fileInput.files = files;
        }

        // Mobil için dosya detay modalı
        function showFileDetail(fileId) {
            const modal = document.getElementById('fileDetailModal');
            const content = document.getElementById('fileDetailContent');
            
            // AJAX ile dosya detaylarını getir
            fetch(`file_detail.php?id=${fileId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.error) {
                        content.innerHTML = `
                            <div class="text-red-600">
                                <i class="fas fa-exclamation-circle mr-2"></i>${data.error}
                            </div>
                        `;
                    } else {
                        const downloadBtn = `
                            <a href="download.php?id=${data.id}" class="bg-blue-600 text-white px-4 py-2 rounded-md block text-center mb-2">
                                <i class="fas fa-download mr-2"></i>İndir
                            </a>
                        `;

                        const browseBtn = data.is_folder ? `
                            <a href="browse.php?path=${encodeURIComponent(data.filepath)}" class="bg-green-600 text-white px-4 py-2 rounded-md block text-center">
                                <i class="fas fa-folder-open mr-2"></i>Aç
                            </a>
                        ` : '';

                        content.innerHTML = `
                            <h3 class="text-lg font-semibold mb-2">${data.filename}</h3>
                            <div class="space-y-2 mb-4">
                                <p><strong>Boyut:</strong> ${data.filesize}</p>
                                <p><strong>Yükleyen:</strong> ${data.uploader}</p>
                                <p><strong>Tarih:</strong> ${data.upload_date}</p>
                                <p><strong>Tür:</strong> ${data.is_common ? 'Ortak' : 'Kişisel'}</p>
                            </div>
                            <div class="mt-4">
                                ${downloadBtn}
                                ${browseBtn}
                            </div>
                        `;
                    }
                    modal.style.display = 'flex';
                })
                .catch(error => {
                    content.innerHTML = `
                        <div class="text-red-600">
                            <i class="fas fa-exclamation-circle mr-2"></i>Dosya bilgileri alınamadı
                        </div>
                    `;
                    modal.style.display = 'flex';
                });
        }

        function closeModal() {
            document.getElementById('fileDetailModal').style.display = 'none';
        }

        // Mobil cihaz için dokunma olayı
        document.querySelectorAll('.file-item').forEach(item => {
            if ('ontouchstart' in window) {
                item.addEventListener('click', (e) => {
                    if (!e.target.closest('a')) { // Eğer tıklanan yer bir link değilse
                        e.preventDefault();
                        const fileId = item.dataset.fileId;
                        showFileDetail(fileId);
                    }
                });
            }
        });
    </script>
</body>
</html>