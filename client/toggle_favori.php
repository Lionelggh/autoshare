<?php
require_once '../config/database.php';
require_once '../includes/functions.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Non connecté']);
    exit;
}

$userId = $_SESSION['user_id'];
$vehiculeId = isset($_POST['vehicule_id']) ? (int)$_POST['vehicule_id'] : 0;

if ($vehiculeId <= 0) {
    echo json_encode(['success' => false, 'message' => 'Véhicule invalide']);
    exit;
}

try {
    // Vérifier si déjà en favori
    $stmt = $pdo->prepare("SELECT id FROM favoris WHERE utilisateur_id = :uid AND vehicule_id = :vid");
    $stmt->execute([':uid' => $userId, ':vid' => $vehiculeId]);
    $exists = $stmt->fetch();

    if ($exists) {
        // Retirer des favoris
        $stmt = $pdo->prepare("DELETE FROM favoris WHERE utilisateur_id = :uid AND vehicule_id = :vid");
        $stmt->execute([':uid' => $userId, ':vid' => $vehiculeId]);
        echo json_encode(['success' => true, 'liked' => false, 'message' => 'Retiré des favoris']);
    } else {
        // Ajouter aux favoris
        $stmt = $pdo->prepare("INSERT INTO favoris (utilisateur_id, vehicule_id) VALUES (:uid, :vid)");
        $stmt->execute([':uid' => $userId, ':vid' => $vehiculeId]);
        echo json_encode(['success' => true, 'liked' => true, 'message' => 'Ajouté aux favoris']);
    }
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Erreur base de données']);
}
