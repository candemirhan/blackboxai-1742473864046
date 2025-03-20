<?php
require_once 'config.php';

class FTPManager {
    private $conn;
    private static $instance = null;

    private function __construct() {
        $this->connect();
    }

    // Singleton pattern
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    // FTP bağlantısı
    private function connect() {
        $this->conn = ftp_connect(FTP_HOST, FTP_PORT);
        if ($this->conn === false) {
            throw new Exception("FTP sunucusuna bağlanılamadı.");
        }

        $login = ftp_login($this->conn, FTP_USER, FTP_PASS);
        if ($login === false) {
            throw new Exception("FTP girişi başarısız.");
        }

        ftp_pasv($this->conn, true);
    }

    // Dosya yükleme
    public function uploadFile($localFile, $remoteFile) {
        try {
            if (!file_exists($localFile)) {
                throw new Exception("Yerel dosya bulunamadı.");
            }

            $upload = ftp_put($this->conn, $remoteFile, $localFile, FTP_BINARY);
            if ($upload === false) {
                throw new Exception("Dosya yükleme başarısız.");
            }

            return true;
        } catch (Exception $e) {
            throw new Exception("Dosya yükleme hatası: " . $e->getMessage());
        }
    }

    // Dosya indirme
    public function downloadFile($remoteFile, $localFile) {
        try {
            $download = ftp_get($this->conn, $localFile, $remoteFile, FTP_BINARY);
            if ($download === false) {
                throw new Exception("Dosya indirme başarısız.");
            }

            return true;
        } catch (Exception $e) {
            throw new Exception("Dosya indirme hatası: " . $e->getMessage());
        }
    }

    // Dosya silme
    public function deleteFile($remoteFile) {
        try {
            $delete = ftp_delete($this->conn, $remoteFile);
            if ($delete === false) {
                throw new Exception("Dosya silme başarısız.");
            }

            return true;
        } catch (Exception $e) {
            throw new Exception("Dosya silme hatası: " . $e->getMessage());
        }
    }

    // Klasör oluşturma
    public function createDirectory($directory) {
        try {
            $create = ftp_mkdir($this->conn, $directory);
            if ($create === false) {
                throw new Exception("Klasör oluşturma başarısız.");
            }

            return true;
        } catch (Exception $e) {
            throw new Exception("Klasör oluşturma hatası: " . $e->getMessage());
        }
    }

    // Klasör silme
    public function removeDirectory($directory) {
        try {
            $delete = ftp_rmdir($this->conn, $directory);
            if ($delete === false) {
                throw new Exception("Klasör silme başarısız.");
            }

            return true;
        } catch (Exception $e) {
            throw new Exception("Klasör silme hatası: " . $e->getMessage());
        }
    }

    // Dosya/Klasör listesi
    public function listDirectory($directory = '/') {
        try {
            $list = ftp_nlist($this->conn, $directory);
            if ($list === false) {
                throw new Exception("Dizin listesi alınamadı.");
            }

            return $list;
        } catch (Exception $e) {
            throw new Exception("Dizin listeleme hatası: " . $e->getMessage());
        }
    }

    // Dosya/Klasör var mı kontrolü
    public function fileExists($remoteFile) {
        $list = $this->listDirectory(dirname($remoteFile));
        return in_array(basename($remoteFile), array_map('basename', $list));
    }

    // Bağlantıyı kapat
    public function close() {
        if ($this->conn) {
            ftp_close($this->conn);
        }
    }

    // Destructor
    public function __destruct() {
        $this->close();
    }
}

// Kullanım örneği:
/*
try {
    $ftp = FTPManager::getInstance();
    $ftp->uploadFile('local.txt', 'remote.txt');
} catch (Exception $e) {
    echo $e->getMessage();
}
*/
?>