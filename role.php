<?php
session_start();

if (!isset($_SESSION["utilisateur_id"])) {
    header("Location: index.php");
    exit();
}

try {
    // Ajout du port 3306 pour correspondre aux autres fichiers
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

    $role_demande = $_POST["role_demande"];
    $code = $_POST["code"] ?? "";

    if ($role_demande == "participant") {

        $requete = $bdd->prepare("
            UPDATE utilisateurs
            SET role = 'participant'
            WHERE id = ?
        ");

        $requete->execute([$_SESSION["utilisateur_id"]]);

        $_SESSION["role"] = "participant";

        $message = "Vous êtes maintenant participant.";

    } elseif ($role_demande == "organisateur" && $code == "1234") {

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

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="role.css">
    <title>Gestion du Rôle - OmnesEvent</title>
</head>

<body>

    <div class="role-container">

        <header style="margin-bottom: 25px;">
            <a href="reglages.php" style="color: var(--c-blue); font-weight: 700; text-decoration: none; font-size: 14px;">
                ← Retour aux réglages
            </a>
        </header>

        <h1>Gestion du rôle</h1>

        <p>
            Votre rôle actuel :
            <strong><?php echo htmlspecialchars($_SESSION["role"]); ?></strong>
        </p>

        <?php
        if ($message != "") {
            // Le CSS va automatiquement repérer ce paragraphe et en faire un bel encart d'information
            echo "<p>" . htmlspecialchars($message) . "</p>";
        }
        ?>

        <h2>Redevenir participant</h2>

        <form method="post" action="role.php">
            <input type="hidden" name="role_demande" value="participant">

            <button type="submit">
                Redevenir participant
            </button>
        </form>

        <h2>Devenir organisateur</h2>

        <form method="post" action="role.php">
            <input type="hidden" name="role_demande" value="organisateur">

            <label>Code organisateur :</label>
            <input type="password" name="code" required>

            <button type="submit">
                Devenir organisateur
            </button>
        </form>

        <h2>Devenir administrateur</h2>

        <form method="post" action="role.php">
            <input type="hidden" name="role_demande" value="administrateur">

            <label>Code administrateur :</label>
            <input type="password" name="code" required>

            <button type="submit">
                Devenir administrateur
            </button>
        </form>

    </div>

</body>
</html>