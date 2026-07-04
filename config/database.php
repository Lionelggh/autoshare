<?php
/**
 * AutoShare - Configuration base de données
 * Connexion PDO avec gestion d'erreurs
 */

// Identifiants fournis par le panneau InfinityFree (MySQL Databases)
define('DB_HOST', 'VOTRE_HOST_MYSQL');       // ex: sql209.epizy.com
define('DB_NAME', 'VOTRE_NOM_BDD');          // ex: epiz_12345678_autopartage
define('DB_USER', 'VOTRE_USER_MYSQL');       // ex: epiz_12345678
define('DB_PASS', 'VOTRE_MOT_DE_PASSE');
define('DB_CHARSET', 'utf8mb4');

// '' si les fichiers sont à la racine de htdocs, '/hope' si dans un sous-dossier htdocs/hope/
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
