<!DOCTYPE html>
<html lang="fr">
<?php
session_start();
?>
<head>
    <meta charset="UTF-8">
    <title> Réglages </title>
</head>

<body>
    <header>
    <a href="sommaire.php">Omnes Lyon</a>
    </header>
    <h1>Réglages</h1>

    <p>
    <?php echo $_SESSION["nom"] . " " . $_SESSION["prenom"]; ?> !
    </p>

    <p>
        Rôle : <?php echo $_SESSION["role"]; ?>
    </p>

    <form method="post" action="deconnexion.php">
    <button type="submit">Se déconnecter</button>
    </form>

    <a href="role.php">Modifier mon rôle</a>

</body>

</html>