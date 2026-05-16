<?php
try {
    $bdd = new PDO(
        "mysql:host=localhost;dbname=projet2526;charset=utf8",
        "root",
        "root"
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
    <title>Créer un compte - OmnesEvent</title>
</head>
<body>

    <h1>OmnesEvent</h1>
    <h2>Créer un compte</h2>

    <?php
    if ($message != "") {
        echo "<p style='color:red;'>$message</p>";
    }
    ?>

    <form method="post" action="creationLogin.php">
        <label>Nom :</label><br>
        <input type="text" name="nom" required><br><br>

        <label>Prénom :</label><br>
        <input type="text" name="prenom" required><br><br>

        <label>Email :</label><br>
        <input type="email" name="email" required><br><br>

        <label>Confirmer l'email :</label><br>
        <input type="email" name="email_verif" required><br><br>

        <label>Mot de passe :</label><br>
        <input type="password" name="mot_de_passe" required><br><br>

        <label>Confirmer le mot de passe :</label><br>
        <input type="password" name="mot_de_passe_verif" required><br><br>

        <button type="submit">Créer mon compte</button>
    </form>

    <p>
        Déjà un compte ?
        <a href="index.php">Se connecter</a>
    </p>

</body>
</html>