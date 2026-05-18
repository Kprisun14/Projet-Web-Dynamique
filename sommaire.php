<?php
session_start();

if (!isset($_SESSION["utilisateur_id"])) {
    header("Location: index.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="sommaire.css">
    <title>Sommaire</title>
</head>

<body>

    <header>
        <a href="reglages.php">Réglages</a>
    </header>

    <h1 class="titre">OmnesEvent</h1>

<div class="menu-categories">

    <div class="dropdown">
        <button class="dropbtn">Événements</button>

        <div class="dropdown-content">
            <a href="#">Concerts</a>
            <a href="#">Conférences</a>
            <a href="#">Tournois</a>
        </div>
    </div>

    <div class="dropdown">
        <button class="dropbtn">Équipes sportives</button>

        <div class="dropdown-content">
            <a href="#">Football</a>
            <a href="#">Basket</a>
            <a href="#">Volleyball</a>
        </div>
    </div>

    <div class="dropdown">
        <button class="dropbtn">Asso's culturelles</button>

        <div class="dropdown-content">
            <a href="#">Musique</a>
            <a href="#">Cinéma</a>
            <a href="#">Art</a>
        </div>
    </div>

</div>
<form method="get" action="evenements.php" class="form-recherche">

    <label for="date">Date :</label>
    <input type="date" name="date" id="date">

    <label for="categorie">Catégorie :</label>
    <select name="categorie" id="categorie">
        <option value="">Toutes</option>
        <option value="Soirée">Soirée</option>
        <option value="Sport">Sport</option>
        <option value="Culture">Culture</option>
    </select>

    <label for="association">Association :</label>
    <input type="text" name="association" id="association" placeholder="Nom de l'association">

    <button type="submit">Rechercher</button>

</form>

</body>

</html>