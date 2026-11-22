<?php
session_start();

$host    = '127.0.0.1';
$db      = 'freelance_db';
$user    = 'root';
$pass    = '';
$charset = 'utf8';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8 COLLATE utf8_vietnamese_ci"
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
    die("Lỗi kết nối cơ sở dữ liệu: " . $e->getMessage());
}

// Tự động ghi nhật ký mọi thao tác vào bảng NHAT_KY_HE_THONG
function logAudit(PDO $pdo, string $action, string $entity, string $desc): void {
    $nd_ma = $_SESSION['user']['ND_ma'] ?? null;
    $ip    = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
    $agent = substr($_SERVER['HTTP_USER_AGENT'] ?? 'Trình duyệt Web', 0, 180);

    $stmt = $pdo->prepare("
        INSERT INTO NHAT_KY_HE_THONG (ND_ma, NKHT_hanhdong, NKHT_doituong, NKHT_mota, NKHT_diachiIP, NKHT_thietbi)
        VALUES (?, ?, ?, ?, ?, ?)
    ");
    $stmt->execute([$nd_ma, $action, $entity, $desc, $ip, $agent]);
}

function e(?string $str): string {
    return htmlspecialchars($str ?? '', ENT_QUOTES, 'UTF-8');
}