<?php
session_start();

// 1. Sécurité : Seuls les organisateurs et administrateurs connectés peuvent supprimer
if (!isset($_SESSION['utilisateur_id']) || ($_SESSION['role'] !== 'organisateur' && $_SESSION['role'] !== 'administrateur')) {
    header("Location: index.php");
    exit();
}

// 2. Vérification de l'ID de l'événement à supprimer
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: reglages.php?erreur=id_manquant");
    exit();
}

$evenement_id = intval($_GET['id']);
$id_connecte = $_SESSION['utilisateur_id'];
$role = $_SESSION['role'];

// 3. Connexion à la base de données
try {
    $bdd = new PDO("mysql:host=localhost;port=3306;dbname=projet2526;charset=utf8", "root", "");
    $bdd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // 4. Suppression sécurisée
    if ($role === 'administrateur') {
        // Un admin peut supprimer n'veporte quel événement
        $req_delete = $bdd->prepare("DELETE FROM evenements WHERE id = ?");
        $req_delete->execute([$evenement_id]);
    } else {
        // Un organisateur ne peut supprimer QUE ses propres événements (sécurité anti-triche)
        $req_delete = $bdd->prepare("DELETE FROM evenements WHERE id = ? AND organisateur_id = ?");
        $req_delete->execute([$evenement_id, $id_connecte]);
    }

    // Note : Grâce à la contrainte ON DELETE CASCADE présente dans votre fichier .sql,
    // tous les billets associés à cet événement seront automatiquement supprimés de la table billets !

    header("Location: reglages.php?succes=suppression");
    exit();

} catch (Exception $e) {
    die("Erreur lors de la suppression : " . $e->getMessage());
}
?>