<?php
session_start();

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

$email = $_POST["email"];
$mot_de_passe = $_POST["mot_de_passe"];

$requete = $bdd->prepare("SELECT * FROM utilisateurs WHERE email = ?");
$requete->execute([$email]);

$utilisateur = $requete->fetch(PDO::FETCH_ASSOC);

if ($utilisateur && password_verify($mot_de_passe, $utilisateur["mot_de_passe"])) {

    $_SESSION["utilisateur_id"] = $utilisateur["id"];
    $_SESSION["prenom"] = $utilisateur["prenom"];
    $_SESSION["nom"] = $utilisateur["nom"];
    $_SESSION["email"] = $utilisateur["email"];
    $_SESSION["role"] = $utilisateur["role"];

    header("Location: sommaire.php");
    exit();

} else {

    header("Location: index.php?erreur=1");
    exit();
}
?>