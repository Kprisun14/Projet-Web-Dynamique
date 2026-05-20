<?php
try {
    $bdd = new PDO(
        "mysql:host=localhost;port=3306;dbname=projet2526;charset=utf8",
        "root",
        ""
    );
    $bdd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

} catch (Exception $e) {
    die("Erreur de connexion : " . $e->getMessage());
}

$message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $nom = $_POST["nom"];
    $prenom = $_POST["prenom"];
    $email = $_POST["email"];
    $email_verif = $_POST["email_verif"];
    $mot_de_passe = $_POST["mot_de_passe"];
    $mot_de_passe_verif = $_POST["mot_de_passe_verif"];

    if ($email !== $email_verif) {
        $message = "Les emails ne correspondent pas.";
    } elseif ($mot_de_passe !== $mot_de_passe_verif) {
        $message = "Les mots de passe ne correspondent pas.";
    } else {
        $requete = $bdd->prepare("SELECT id FROM utilisateurs WHERE email = ?");
        $requete->execute([$email]);

        if ($requete->rowCount() > 0) {
            $message = "Cet email est déjà utilisé.";
        } else {
            $mot_de_passe_hash = password_hash($mot_de_passe, PASSWORD_DEFAULT);

            $insert = $bdd->prepare("
                INSERT INTO utilisateurs (nom, prenom, email, mot_de_passe, role)
                VALUES (?, ?, ?, ?, 'participant')
            ");
            
            $insert->execute([$nom, $prenom, $email, $mot_de_passe_hash]);
            header("Location: index.php?creation=1");
            exit();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="creationLogin.css">
    <title>Créer un compte - OmnesEvent</title>
</head>
<body>

    <div class="auth-box">
        <h1>OmnesEvent</h1>
        <h2>Créer un compte</h2>

        <?php
        if ($message != "") {
            echo "<p class='alerte-erreur'>$message</p>";
        }
        ?>

        <form method="post" action="creationLogin.php">
            <label>Nom :</label>
            <input type="text" name="nom" required>

            <label>Prénom :</label>
            <input type="text" name="prenom" required>

            <label>Email :</label>
            <input type="email" name="email" required>

            <label>Confirmer l'email :</label>
            <input type="email" name="email_verif" required>

            <label>Mot de passe :</label>
            <input type="password" name="mot_de_passe" required>

            <label>Confirmer le mot de passe :</label>
            <input type="password" name="mot_de_passe_verif" required>

            <button type="submit">Créer mon compte</button>
        </form>

        <p class="lien-bas">
            Déjà un compte ? <a href="index.php">Se connecter</a>
        </p>
    </div>

</body>
</html>