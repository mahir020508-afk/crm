<?php
$host     = 'localhost';
$dbname   = 'crm_db';
$username = 'root';
$password = '';

try {
    $db = new PDO(
        "mysql:host=$host;dbname=$dbname;charset=utf8mb4", 
        $username, 
        $password,
        [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false 
        ]
    );
} catch (PDOException $e) {
    error_log("DB Bağlantı Hatası: " . $e->getMessage());
    die("Sistem Güvenliği: Veritabanı bağlantısı kurulamadı. Lütfen sistem yöneticisine başvurun.");
}
?>