<?php
require_once 'config/database.php';

try {
    // 1. Ajouter la table chat_messages si elle n'existe pas
    $pdo->exec("CREATE TABLE IF NOT EXISTS chat_messages (
        id INT AUTO_INCREMENT PRIMARY KEY,
        expediteur_id INT NOT NULL,
        destinataire_id INT NOT NULL,
        message TEXT NOT NULL,
        lu TINYINT(1) NOT NULL DEFAULT 0,
        date_envoi DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (expediteur_id) REFERENCES utilisateurs(id) ON DELETE CASCADE,
        FOREIGN KEY (destinataire_id) REFERENCES utilisateurs(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");
    echo "Table 'chat_messages' vérifiée/créée avec succès.<br>";

    // 2. Ajouter les colonnes manquantes dans la table vehicules
    $vehiculesCols = [
        'image2' => "VARCHAR(255) DEFAULT NULL",
        'image3' => "VARCHAR(255) DEFAULT NULL",
        'image4' => "VARCHAR(255) DEFAULT NULL",
        'immatriculation' => "VARCHAR(20) DEFAULT NULL"
    ];
    
    foreach ($vehiculesCols as $col => $def) {
        try {
            $pdo->exec("ALTER TABLE vehicules ADD COLUMN $col $def");
            echo "Colonne '$col' ajoutée à la table 'vehicules'.<br>";
        } catch (PDOException $e) {
            // Ignore error if column already exists
            if ($e->getCode() != '42S21') { // 42S21 = Duplicate column name
                echo "Erreur lors de l'ajout de '$col' (vehicules) : " . $e->getMessage() . "<br>";
            }
        }
    }

    // 3. Ajouter les colonnes manquantes dans la table reservations
    $reservationsCols = [
        'mode_paiement' => "ENUM('sur_place', 'ligne') DEFAULT NULL",
        'statut_paiement' => "ENUM('non_paye', 'paye') NOT NULL DEFAULT 'non_paye'"
    ];
    
    foreach ($reservationsCols as $col => $def) {
        try {
            $pdo->exec("ALTER TABLE reservations ADD COLUMN $col $def");
            echo "Colonne '$col' ajoutée à la table 'reservations'.<br>";
        } catch (PDOException $e) {
            // Ignore error if column already exists
            if ($e->getCode() != '42S21') {
                echo "Erreur lors de l'ajout de '$col' (reservations) : " . $e->getMessage() . "<br>";
            }
        }
    }

    // 4. Ajouter la table favoris si elle n'existe pas
    $pdo->exec("CREATE TABLE IF NOT EXISTS favoris (
        id INT AUTO_INCREMENT PRIMARY KEY,
        utilisateur_id INT NOT NULL,
        vehicule_id INT NOT NULL,
        date_creation DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        UNIQUE KEY unique_favori (utilisateur_id, vehicule_id),
        FOREIGN KEY (utilisateur_id) REFERENCES utilisateurs(id) ON DELETE CASCADE,
        FOREIGN KEY (vehicule_id) REFERENCES vehicules(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");
    echo "Table 'favoris' vérifiée/créée avec succès.<br>";

    echo "<br><b>✅ Mise à jour de la base de données terminée. Les erreurs devraient être résolues. Vous pouvez supprimer ce fichier.</b>";
} catch (Exception $e) {
    echo "Erreur globale : " . $e->getMessage();
}
