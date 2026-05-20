<?php
session_start();

// 1. Sécurité : Seuls les organisateurs et admins peuvent valider un billet
if (!isset($_SESSION['utilisateur_id']) || ($_SESSION['role'] !== 'organisateur' && $_SESSION['role'] !== 'administrateur')) {
    header("Location: index.php");
    exit();
}

if (isset($_GET['code'])) {
    $code_billet = $_GET['code'];
    $id_connecte = $_SESSION['utilisateur_id'];
    $role = $_SESSION['role'];

    try {
        $bdd = new PDO("mysql:host=localhost;port=3306;dbname=projet2526;charset=utf8", "root", "");
        $bdd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // 2. On met à jour le statut du billet. 
        // Sécurité : On vérifie que le billet appartient bien à un événement créé par cet organisateur (ou qu'il est admin)
        $req = $bdd->prepare("
            UPDATE billets b 
            JOIN evenements e ON b.evenement_id = e.id 
            SET b.statut = 'Utilisé' 
            WHERE b.code_billet = ? AND (e.organisateur_id = ? OR ? = 'administrateur')
        ");
        
        $req->execute([$code_billet, $id_connecte, $role]);

        // 3. Retour au profil avec un message de succès
        header("Location: reglages.php?succes=presence");
        exit();

    } catch (Exception $e) {
        die("Erreur de validation : " . $e->getMessage());
    }
} else {
    header("Location: reglages.php");
    exit();
}
?>