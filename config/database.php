<?php
/**
 * AutoShare - Configuration base de données
 * Connexion PDO avec gestion d'erreurs
 */

define('DB_HOST', 'sql211.infinityfree.com');
define('DB_NAME', 'if0_42336905_autopartage');
define('DB_USER', 'if0_42336905');
define('DB_PASS', '2X4T3BjFgCDhvSw');
define('DB_CHARSET', 'utf8mb4');

define('BASE_URL', '');

try {
    $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
        PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci"
    ];
    $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
} catch (PDOException $e) {
    die("Erreur de connexion à la base de données: " . $e->getMessage());
}

// Démarrer la session si pas déjà active
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
