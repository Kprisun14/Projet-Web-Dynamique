<?php
session_start();

try {
    $bdd = new PDO("mysql:host=localhost;port=3306;dbname=projet2526;charset=utf8", "root", "");
    $bdd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (Exception $e) {
    die("Erreur de connexion : " . $e->getMessage());
}

// On force le filtre sur la catégorie "Soirée"
$requete = $bdd->prepare("SELECT * FROM evenements WHERE categorie = 'Soirée' ORDER BY date_evenement ASC");
$requete->execute();
$evenements = $requete->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Événements & Soirées - OmnesEvent</title>
    <link rel="stylesheet" href="categories.css">
</head>
<body>

<header>
    <a href="sommaire.php">⬅ Retour à l'accueil</a>
    <a href="reglages.php">👤 Mon Profil</a>
</header>

<nav class="menu-navigation">
    <a href="evenements.php" class="active">Événements & Soirées</a>
    <a href="sport.php">Équipes sportives</a>
    <a href="culture.php">Asso's culturelles</a>
</nav>

<div class="page-header">
    <h1>🎉 Événements & Soirées</h1>
    <p>Ne manquez aucune soirée étudiante, gala ou afterwork.</p>
</div>

<section class="liste-evenements">
    <?php if (count($evenements) === 0): ?>
        <p style="text-align: center; width: 100%; color: #5a5f8a;">Aucune soirée de prévue pour le moment.</p>
    <?php endif; ?>

    <?php foreach ($evenements as $evenement) : ?>
        <div class="card-evenement">
            <?php if (!empty($evenement["image_evenement"])) : ?>
                <img src="<?= htmlspecialchars($evenement["image_evenement"]) ?>" alt="Image" class="image-evenement">
            <?php else : ?>
                <div class="image-placeholder">Aucun visuel</div>
            <?php endif; ?>

            <div class="infos-evenement">
                <span class="badge-categorie cat-soiree"><?= htmlspecialchars($evenement["categorie"]) ?></span>
                <h2><?= htmlspecialchars($evenement["titre"]) ?></h2>
                <p>📅 <strong>Date :</strong> <?= date('d/m/Y', strtotime($evenement["date_evenement"])) ?></p>
                <p>📍 <strong>Lieu :</strong> <?= htmlspecialchars($evenement["lieu"]) ?></p>
                <p>🤝 <strong>Asso :</strong> <?= htmlspecialchars($evenement["association"]) ?></p>
                <p>💳 <strong>Prix :</strong> <?= htmlspecialchars($evenement["prix"]) ?> €</p>
                <p class="description"><?= htmlspecialchars($evenement["description"]) ?></p>
                
                <a href="reservation.php?id=<?= $evenement["id"] ?>" class="btn-profil-action">🎟️ Réserver</a>
            </div>
        </div>
    <?php endforeach; ?>
</section>

</body>
</html>