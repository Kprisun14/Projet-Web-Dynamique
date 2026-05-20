<?php
session_start();

if (!isset($_SESSION["role"]) || ($_SESSION["role"] !== "organisateur" && $_SESSION["role"] !== "administrateur")) {
    header("Location: sommaire.php");
    exit();
}

try {
    $bdd = new PDO("mysql:host=localhost;dbname=projet2526;charset=utf8", "root", "");
    $bdd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (Exception $e) {
    die("Erreur de connexion : " . $e->getMessage());
}

$message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $titre = $_POST["titre"];
    $description = $_POST["description"];
    $date_evenement = $_POST["date_evenement"];
    $heure_evenement = $_POST["heure_evenement"];
    $lieu = $_POST["lieu"];
    $categorie = $_POST["categorie"];
    $association = $_POST["association"];
    $prix = $_POST["prix"];
    $places_max = $_POST["places_max"];

    $imagePath = null;

    if (isset($_FILES["image_evenement"]) && $_FILES["image_evenement"]["error"] === 0) {
        $dossier = "uploads/evenements/";

        if (!is_dir($dossier)) {
            mkdir($dossier, 0777, true);
        }

        $nomImage = time() . "_" . basename($_FILES["image_evenement"]["name"]);
        $cheminImage = $dossier . $nomImage;

        move_uploaded_file($_FILES["image_evenement"]["tmp_name"], $cheminImage);

        $imagePath = $cheminImage;
    }

    // ON AJOUTE 'organisateur_id' à la fin de la liste des colonnes
    $requete = $bdd->prepare("
        INSERT INTO evenements 
        (titre, description, date_evenement, heure_evenement, lieu, categorie, association, prix, places_max, image_evenement, organisateur_id)
        VALUES
        (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");

    // ON AJOUTE '$_SESSION['utilisateur_id']' à la fin du tableau d'exécution
    $requete->execute([
        $titre,
        $description,
        $date_evenement,
        $heure_evenement,
        $lieu,
        $categorie,
        $association,
        $prix,
        $places_max,
        $imagePath,
        $_SESSION['utilisateur_id'] // <-- C'est cette ligne magique qui fait le lien !
    ]);

    $message = "Événement créé avec succès.";
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Créer un événement</title>
    <link rel="stylesheet" href="creation_evenement.css">
</head>

<body>

<header>
    <a href="sommaire.php">OMNES EVENT</a>
</header>

<h1>Créer un événement</h1>

<?php if ($message !== "") : ?>
    <p class="message"><?= htmlspecialchars($message) ?></p>
<?php endif; ?>

<form method="post" enctype="multipart/form-data" class="form-evenement">

    <input type="text" name="titre" placeholder="Titre de l'événement" required>

    <textarea name="description" placeholder="Description de l'événement"></textarea>

    <input type="date" name="date_evenement" required>

    <input type="time" name="heure_evenement">

    <input type="text" name="lieu" placeholder="Lieu">

    <select name="categorie" required>
        <option value="">Choisir une catégorie</option>
        <option value="Soirée">Soirée</option>
        <option value="Sport">Sport</option>
        <option value="Culture">Culture</option>
    </select>

    <input type="text" name="association" placeholder="Association organisatrice">

    <input type="number" step="0.01" name="prix" placeholder="Prix">

    <input type="number" name="places_max" placeholder="Nombre de places maximum">

    <label>Image de décoration :</label>
    <input type="file" name="image_evenement" accept="image/*">

    <button type="submit">Créer l'événement</button>

</form>

</body>
</html>