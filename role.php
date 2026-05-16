<?php
session_start();

if (!isset($_SESSION["utilisateur_id"])) {
    header("Location: index.php");
    exit();
}

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

    $role_demande = $_POST["role_demande"];
    $code = $_POST["code"];

    if ($role_demande == "organisateur" && $code == "1234") {

        $requete = $bdd->prepare("
            UPDATE utilisateurs
            SET role = 'organisateur'
            WHERE id = ?
        ");

        $requete->execute([$_SESSION["utilisateur_id"]]);

        $_SESSION["role"] = "organisateur";

        $message = "Vous êtes maintenant organisateur.";

    } elseif ($role_demande == "administrateur" && $code == "12345") {

        $requete = $bdd->prepare("
            UPDATE utilisateurs
            SET role = 'administrateur'
            WHERE id = ?
        ");

        $requete->execute([$_SESSION["utilisateur_id"]]);

        $_SESSION["role"] = "administrateur";

        $message = "Vous êtes maintenant administrateur.";

    } else {

        $message = "Code incorrect.";
    }
}
?>

<h1>Gestion du rôle</h1>

<p>
    Votre rôle actuel :
    <strong><?php echo $_SESSION["role"]; ?></strong>
</p>

<?php
if ($message != "") {
    echo "<p>$message</p>";
}
?>

<h2>Devenir organisateur</h2>

<form method="post" action="role.php">

    <input type="hidden" name="role_demande" value="organisateur">

    <label>Code organisateur :</label><br>
    <input type="password" name="code" required><br><br>

    <button type="submit">
        Devenir organisateur
    </button>

</form>

<br><br>

<h2>Devenir administrateur</h2>

<form method="post" action="role.php">

    <input type="hidden" name="role_demande" value="administrateur">

    <label>Code administrateur :</label><br>
    <input type="password" name="code" required><br><br>

    <button type="submit">
        Devenir administrateur
    </button>
</form>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <title>Rôle</title>
</head>

<body>
    <a href="reglages.php">Réglages</a>
</body>

</html>