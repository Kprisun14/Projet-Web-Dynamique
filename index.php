<?php
session_start();

// Redirection automatique si l'utilisateur est déjà connecté
if (isset($_SESSION["utilisateur_id"])) {
    header("Location: sommaire.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="connexion.css">
    <title>Connexion - OmnesEvent</title>
</head>
<body>

    <div class="auth-box">
        
        <h1>OmnesEvent</h1>
        <h2>Connexion</h2>

        <?php
        // Affichage des messages d'erreur ou de succès
        // Le CSS est configuré pour intercepter ces styles et en faire de beaux encarts
        if (isset($_GET["erreur"])) {
            echo "<p style='color:red;'>Email ou mot de passe incorrect.</p>";
        }

        if (isset($_GET["creation"])) {
            echo "<p style='color:green;'>Compte créé avec succès. Connectez-vous !</p>";
        }
        ?>

        <form method="post" action="verifLogin.php">
            
            <label>Email :</label>
            <input type="email" name="email" required>

            <label>Mot de passe :</label>
            <input type="password" name="mot_de_passe" required>

            <button type="submit">Se connecter</button>

        </form>

        <p>
            Pas encore de compte ? 
            <a href="creationLogin.php">Créer un compte</a>
        </p>

    </div>

</body>
</html>