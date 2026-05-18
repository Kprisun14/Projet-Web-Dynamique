<?php
session_start();

try {
    $bdd = new PDO("mysql:host=localhost;dbname=projet2526;charset=utf8", "root", "root");
    $bdd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (Exception $e) {
    die("Erreur de connexion : " . $e->getMessage());
}

/* =========================
   FILTRES DE RECHERCHE
========================= */

$date = isset($_GET["date"]) ? $_GET["date"] : "";
$categorie = isset($_GET["categorie"]) ? $_GET["categorie"] : "";
$association = isset($_GET["association"]) ? $_GET["association"] : "";

$sql = "SELECT * FROM evenements WHERE 1=1";
$params = [];

if (!empty($date)) {
    $sql .= " AND date_evenement = ?";
    $params[] = $date;
}

if (!empty($categorie)) {
    $sql .= " AND categorie = ?";
    $params[] = $categorie;
}

if (!empty($association)) {
    $sql .= " AND association LIKE ?";
    $params[] = "%" . $association . "%";
}

$sql .= " ORDER BY date_creation DESC";

$requete = $bdd->prepare($sql);
$requete->execute($params);

$evenements = $requete->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">

    <title>Sommaire</title>

    <link rel="stylesheet" href="sommaire.css">

    <link href="https://fonts.googleapis.com/css2?family=Raleway:wght@400;700&display=swap" rel="stylesheet">

</head>

<body>

<header>
    <a href="reglages.php">
       Profil
    </a>
</header>

<!-- =========================
     MENU
========================= -->

<nav class="menu-navigation">

    <a href="#">Événements</a>

    <a href="#">Équipes sportives</a>

    <a href="#">Asso's culturelles</a>

</nav>

<!-- =========================
     RECHERCHE
========================= -->

<div class="barre-recherche">

    <form method="get" action="">

        <input type="date" name="date">

        <select name="categorie">

            <option value="">Toutes les catégories</option>

            <option value="Soirée">Soirée</option>

            <option value="Sport">Sport</option>

            <option value="Culture">Culture</option>

        </select>

        <input 
            type="text" 
            name="association" 
            placeholder="Association"
        >

        <button type="submit">
            Rechercher
        </button>

        <?php if (
            isset($_SESSION["role"]) &&
            (
                $_SESSION["role"] === "organisateur" ||
                $_SESSION["role"] === "administrateur"
            )
        ) : ?>

            <a href="creation_evenement.php" class="btn-plus">
                +
            </a>

        <?php endif; ?>

    </form>

</div>

<!-- =========================
     EVENEMENTS
========================= -->

<section class="liste-evenements">

    <?php foreach ($evenements as $evenement) : ?>

        <div class="card-evenement">

            <?php if (!empty($evenement["image_evenement"])) : ?>

                <img
                    src="<?= htmlspecialchars($evenement["image_evenement"]) ?>"
                    alt="Image événement"
                    class="image-evenement"
                >

            <?php else : ?>

                <div class="image-placeholder">
                    Aucun visuel
                </div>

            <?php endif; ?>

            <div class="infos-evenement">

                <h2>
                    <?= htmlspecialchars($evenement["titre"]) ?>
                </h2>

                <p class="categorie">

                    <?= htmlspecialchars($evenement["categorie"]) ?>

                </p>

                <p>

                    <strong>Date :</strong>

                    <?= htmlspecialchars($evenement["date_evenement"]) ?>

                    <?php if (!empty($evenement["heure_evenement"])) : ?>

                        à <?= htmlspecialchars($evenement["heure_evenement"]) ?>

                    <?php endif; ?>

                </p>

                <p>

                    <strong>Lieu :</strong>

                    <?= htmlspecialchars($evenement["lieu"]) ?>

                </p>

                <p>

                    <strong>Association :</strong>

                    <?= htmlspecialchars($evenement["association"]) ?>

                </p>

                <p>

                    <strong>Prix :</strong>

                    <?= htmlspecialchars($evenement["prix"]) ?> €

                </p>

                <p>

                    <strong>Places :</strong>

                    <?= htmlspecialchars($evenement["places_max"]) ?>

                </p>

                <p class="description">

                    <?= htmlspecialchars($evenement["description"]) ?>

                </p>

            </div>

        </div>

    <?php endforeach; ?>

</section>

</body>

</html>