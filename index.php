<?php
session_start();

if (isset($_SESSION["utilisateur_id"])) {
    header("Location: sommaire.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <title>Connexion - OmnesEvent</title>
</head>

<body>

    <h1>OmnesEvent</h1>
    <h2>Connexion</h2>

    <?php
    if (isset($_GET["erreur"])) {
        echo "<p style='color:red;'>Email ou mot de passe incorrect.</p>";
    }

    if (isset($_GET["creation"])) {
        echo "<p style='color:green;'>Compte créé avec succès.</p>";
    }
    ?>

    <form method="post" action="verifLogin.php">

        <label>Email :</label><br>
        <input type="email" name="email" required><br><br>

        <label>Mot de passe :</label><br>
        <input type="password" name="mot_de_passe" required><br><br>

        <button type="submit">Se connecter</button>

    </form>

    <p>
        Pas encore de compte ?
        <a href="creationLogin.php">Créer un compte</a>
    </p>

</body>

</html>