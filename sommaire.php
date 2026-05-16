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
    <title>Sommaire</title>
</head>

<body>
    <header>
    <a href="reglages.php">Réglages</a>
    </header>
    <h1>Bienvenue sur OmnesEvent</h1>

    <p>
        Bonjour <?php echo isset($_SESSION["prenom"]) ? $_SESSION["prenom"]. " " . $_SESSION["nom"] : ""; ?> !
    </p>


</body>

</html>