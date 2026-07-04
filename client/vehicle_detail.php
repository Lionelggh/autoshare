<?php
require_once '../config/database.php';
require_once '../includes/functions.php';

if (!isset($_GET['id']) || empty($_GET['id'])) {
    redirect('/client/vehicles.php');
}

$id = (int)$_GET['id'];
$stmt = $pdo->prepare("SELECT * FROM vehicules WHERE id = :id");
$stmt->execute([':id' => $id]);
$vehicle = $stmt->fetch();

if (!$vehicle) {
    setFlash('error', "Véhicule non trouvé.");
    redirect('/client/vehicles.php');
}

// Vérifier si le véhicule est en favori
$isFavori = false;
if (isLoggedIn()) {
    $stmtFav = $pdo->prepare("SELECT id FROM favoris WHERE utilisateur_id = :uid AND vehicule_id = :vid");
    $stmtFav->execute([':uid' => $_SESSION['user_id'], ':vid' => $id]);
    $isFavori = (bool)$stmtFav->fetch();
}

$pageTitle = $vehicle['marque'] . ' ' . $vehicle['modele'];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?> - AutoShare</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="dashboard">
    <?php include '../includes/sidebar.php'; ?>
    
    <main class="dashboard-content">
        <header class="dashboard-header">
            <a href="vehicles.php" class="back-link">&larr; Retour aux véhicules</a>
            <div class="user-info">
                 <button id="likeBtn" class="btn btn-outline btn-sm" onclick="toggleFavori(<?= $vehicle['id'] ?>)" title="<?= $isFavori ? 'Retirer des favoris' : 'Ajouter aux favoris' ?>" style="<?= $isFavori ? 'color: #e74c3c; border-color: #e74c3c;' : '' ?>">
                     <i class="<?= $isFavori ? 'fas' : 'far' ?> fa-heart"></i>
                 </button>
            </div>
        </header>

        <div class="detail-page">
            <div class="detail-grid">
                <div class="detail-image">
                    <img id="mainImage" src="<?= getVehiculeImage($vehicle['image']) ?>" alt="<?= clean($vehicle['marque'] . ' ' . $vehicle['modele']) ?>" style="width: 100%; height: 400px; object-fit: cover; border-radius: 12px; box-shadow: var(--shadow);">
                    <div class="grid-4 mt-2">
                        <img src="<?= getVehiculeImage($vehicle['image']) ?>" class="thumbnail active" onclick="changeImage(this)" style="height: 80px; width: 100%; object-fit: cover; border-radius: 8px; cursor: pointer; border: 2px solid var(--primary);">
                        <?php if ($vehicle['image2']): ?>
                            <img src="<?= getVehiculeImage($vehicle['image2']) ?>" class="thumbnail" onclick="changeImage(this)" style="height: 80px; width: 100%; object-fit: cover; border-radius: 8px; cursor: pointer; border: 2px solid transparent;">
                        <?php endif; ?>
                        <?php if ($vehicle['image3']): ?>
                            <img src="<?= getVehiculeImage($vehicle['image3']) ?>" class="thumbnail" onclick="changeImage(this)" style="height: 80px; width: 100%; object-fit: cover; border-radius: 8px; cursor: pointer; border: 2px solid transparent;">
                        <?php endif; ?>
                        <?php if ($vehicle['image4']): ?>
                            <img src="<?= getVehiculeImage($vehicle['image4']) ?>" class="thumbnail" onclick="changeImage(this)" style="height: 80px; width: 100%; object-fit: cover; border-radius: 8px; cursor: pointer; border: 2px solid transparent;">
                        <?php endif; ?>
                    </div>
                </div>
                <div class="detail-info">
                    <div class="flex-between mb-2">
                        <h1><?= clean($vehicle['marque'] . ' ' . $vehicle['modele']) ?></h1>
                        <?= getVehiculeStatutBadge($vehicle['statut']) ?>
                    </div>
                    
                    <div class="detail-specs">
                        <div class="spec-tag"><i class="fas fa-bolt"></i> <?= clean($vehicle['type_carburant']) ?></div>
                        <div class="spec-tag"><i class="fas fa-calendar-alt"></i> <?= $vehicle['annee'] ?></div>
                        <div class="spec-tag"><i class="fas fa-cogs"></i> <?= clean($vehicle['transmission']) ?></div>
                        <div class="spec-tag"><i class="fas fa-users"></i> <?= $vehicle['nombre_places'] ?> places</div>
                        <div class="spec-tag"><i class="fas fa-snowflake"></i> Climatisation</div>
                    </div>

                    <div class="detail-price">
                        <?= formatPrix($vehicle['prix_jour']) ?> <span>/ jour</span>
                    </div>



                    <?php if (isAdmin()): ?>
                        <div class="flash flash-info" style="margin-bottom: 0;">
                            <i class="fas fa-info-circle"></i> En tant qu'administrateur, vous ne pouvez pas effectuer de réservation client.
                        </div>
                    <?php elseif ($vehicle['statut'] === 'disponible'): ?>
                        <a href="reserve.php?id=<?= $vehicle['id'] ?>" class="btn btn-primary btn-lg btn-block">Réserver maintenant</a>
                    <?php else: ?>
                        <button class="btn btn-primary btn-lg btn-block" disabled>Indisponible actuellement</button>
                    <?php endif; ?>

                    <div class="detail-tabs">
                        <div class="tabs-header">
                            <button class="tab-btn active" onclick="openTab(event, 'tab-desc')">Description</button>
                            <button class="tab-btn" onclick="openTab(event, 'tab-carac')">Caractéristiques</button>
                            <button class="tab-btn" onclick="openTab(event, 'tab-cond')">Conditions</button>
                        </div>
                        <div id="tab-desc" class="tab-content" style="display: block; color: var(--secondary); line-height: 1.6;">
                            <?= nl2br(clean($vehicle['description'])) ?>
                        </div>
                        <div id="tab-carac" class="tab-content" style="display: none; color: var(--secondary); line-height: 1.6;">
                            <?= nl2br(clean($vehicle['caracteristiques'])) ?>
                        </div>
                        <div id="tab-cond" class="tab-content" style="display: none; color: var(--secondary); line-height: 1.6;">
                            <ul style="padding-left: 20px;">
                                <li>Permis de conduire valide depuis au moins 2 ans.</li>
                                <li>Pièce d'identité en cours de validité.</li>
                                <li>Caution exigée au moment de la réservation.</li>
                                <li>Véhicule strictement non-fumeur.</li>
                                <li>Le véhicule doit être restitué avec le même niveau de carburant.</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
    <script>
        function openTab(evt, tabName) {
            var i, tabcontent, tablinks;
            tabcontent = document.getElementsByClassName("tab-content");
            for (i = 0; i < tabcontent.length; i++) {
                tabcontent[i].style.display = "none";
            }
            tablinks = document.getElementsByClassName("tab-btn");
            for (i = 0; i < tablinks.length; i++) {
                tablinks[i].className = tablinks[i].className.replace(" active", "");
            }
            document.getElementById(tabName).style.display = "block";
            evt.currentTarget.className += " active";
        }

        function changeImage(el) {
            document.getElementById('mainImage').src = el.src;
            // Update active state
            document.querySelectorAll('.thumbnail').forEach(thumb => {
                thumb.style.borderColor = 'transparent';
            });
            el.style.borderColor = 'var(--primary)';
        }

        function toggleFavori(vehiculeId) {
            const btn = document.getElementById('likeBtn');
            const icon = btn.querySelector('i');
            
            fetch('toggle_favori.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'vehicule_id=' + vehiculeId
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    if (data.liked) {
                        icon.className = 'fas fa-heart';
                        btn.style.color = '#e74c3c';
                        btn.style.borderColor = '#e74c3c';
                        btn.title = 'Retirer des favoris';
                        // Micro-animation
                        btn.style.transform = 'scale(1.3)';
                        setTimeout(() => btn.style.transform = 'scale(1)', 200);
                    } else {
                        icon.className = 'far fa-heart';
                        btn.style.color = '';
                        btn.style.borderColor = '';
                        btn.title = 'Ajouter aux favoris';
                    }
                }
            })
            .catch(err => console.error('Erreur:', err));
        }
    </script>
</body>
</html>
