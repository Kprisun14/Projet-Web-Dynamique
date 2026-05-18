<?php
session_start();

// 1. Sécurité : Si l'utilisateur n'est pas connecté, on le renvoie à l'accueil
if (!isset($_SESSION['utilisateur_id'])) {
    header("Location: index.php");
    exit();
}

// 2. Connexion à la base de données
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

$id_connecte = $_SESSION['utilisateur_id'];
$role = $_SESSION['role']; // 'participant' ou 'organisateur'
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Mon Espace - OmnesEvent</title>
    <link rel="stylesheet" href="sommaire.css">
    <link rel="stylesheet" href="profil.css">
</head>
<body>

    <div class="menu-categories">
        <h1 style="margin: 0; font-size: 24px;">OmnesEvent</h1>
        <div class="dropdown">
            <a href="sommaire.php" class="dropbtn" style="text-decoration: none; color: black;">Accueil</a>
        </div>
        <div class="dropdown">
            <a href="deconnexion.php" class="dropbtn" style="text-decoration: none; color: red;">Déconnexion</a>
        </div>
    </div>

    <div class="profil-container">
        <h1 class="titre-profil">Bonjour, <?php echo htmlspecialchars($_SESSION['prenom'] . " " . $_SESSION['nom']); ?> 💻</h1>
        <p style="margin-left: 5px; color: #666;">Espace Personnel — Rôle : <strong><?php echo ucfirst($role); ?></strong></p>

        <?php if ($role === 'participant'):
            // Requête pour les événements À VENIR (date_evenement >= aujourd'hui)
            $req_avenir = $bdd->prepare("
                SELECT e.*, b.date_achat, b.code_billet, b.statut
                FROM billets b
                JOIN evenements e ON b.evenement_id = e.id
                WHERE b.utilisateur_id = ? AND e.date_evenement >= CURDATE()
                ORDER BY e.date_evenement ASC
            ");
            $req_avenir->execute([$id_connecte]);
            $billets_avenir = $req_avenir->fetchAll(PDO::FETCH_ASSOC);

            // Requête pour les événements PASSÉS (date_evenement < aujourd'hui)
            $req_passes = $bdd->prepare("
                SELECT e.*, b.date_achat, b.statut
                FROM billets b
                JOIN evenements e ON b.evenement_id = e.id
                WHERE b.utilisateur_id = ? AND e.date_evenement < CURDATE()
                ORDER BY e.date_evenement DESC
            ");
            $req_passes->execute([$id_connecte]);
            $billets_passes = $req_passes->fetchAll(PDO::FETCH_ASSOC);
        ?>

            <div class="profil-section">
                <h2>🎟️ Mes billets pour les événements à venir</h2>
                <?php if (count($billets_avenir) > 0): ?>
                    <?php foreach ($billets_avenir as $billet): ?>
                        <div class="billet-carte">
                            <div class="billet-info">
                                <h3><?php echo htmlspecialchars($billet['titre']); ?></h3>
                                <p>📅 Date : <?php echo date('d/m/Y', strtotime($billet['date_evenement'])); ?> à <?php echo htmlspecialchars($billet['heure_evenement']); ?></p>
                                <p>📍 Lieu : <?php echo htmlspecialchars($billet['lieu']); ?> — 💳 Prix : <?php echo htmlspecialchars($billet['prix']); ?>€</p>
                                <span class="date-achat">Acheté le : <?php echo date('d/m/Y', strtotime($billet['date_achat'])); ?> | Code : <strong><?php echo htmlspecialchars($billet['code_billet']); ?></strong></span>
                            </div>
                            <button class="btn-profil-action" onclick="alert('Code Billet : <?php echo htmlspecialchars($billet['code_billet']); ?>\nStatut : <?php echo htmlspecialchars($billet['statut']); ?>')">Télécharger le Billet</button>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p class="aucun-resultat">Vous n'avez aucun événement de prévu pour le moment.</p>
                <?php endif; ?>
            </div>

            <div class="profil-section">
                <h2>📜 Historique de mes événements passés</h2>
                <?php if (count($billets_passes) > 0): ?>
                    <?php foreach ($billets_passes as $billet): ?>
                        <div class="billet-carte billet-passe">
                            <div class="billet-info">
                                <h3><?php echo htmlspecialchars($billet['titre']); ?></h3>
                                <p>📅 Date : <?php echo date('d/m/Y', strtotime($billet['date_evenement'])); ?> — 📍 Lieu : <?php echo htmlspecialchars($billet['lieu']); ?></p>
                            </div>
                            <span class="badge-termine">Événement terminé</span>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p class="aucun-resultat">Aucun historique d'événement passé.</p>
                <?php endif; ?>
            </div>

        <?php elseif ($role === 'organisateur'):
            // Requête pour voir tous les billets achetés pour les événements créés par cet organisateur
            $req_inscrits = $bdd->prepare("
                SELECT e.titre AS evenement_titre, e.date_evenement, u.nom, u.prenom, u.email, b.date_achat, b.statut
                FROM billets b
                JOIN evenements e ON b.evenement_id = e.id
                JOIN utilisateurs u ON b.utilisateur_id = u.id
                WHERE e.organisateur_id = ?
                ORDER BY e.date_evenement DESC, b.date_achat DESC
            ");
            $req_inscrits->execute([$id_connecte]);
            $inscrits = $req_inscrits->fetchAll(PDO::FETCH_ASSOC);
        ?>

            <div class="profil-section">
                <h2>📊 Tableau de bord de vos événements</h2>
                <p class="aucun-resultat">Liste en temps réel des participants inscrits à vos activités :</p>

                <?php if (count($inscrits) > 0): ?>
                    <table class="table-inscrits">
                        <thead>
                            <tr>
                                <th>Événement</th>
                                <th>Date Événement</th>
                                <th>Nom du Participant</th>
                                <th>Email</th>
                                <th>Date d'Achat</th>
                                <th>Statut du billet</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($inscrits as $inscrit): ?>
                                <tr>
                                    <td><strong><?php echo htmlspecialchars($inscrit['evenement_titre']); ?></strong></td>
                                    <td><?php echo date('d/m/Y', strtotime($inscrit['date_evenement'])); ?></td>
                                    <td><?php echo htmlspecialchars($inscrit['nom'] . ' ' . $inscrit['prenom']); ?></td>
                                    <td><?php echo htmlspecialchars($inscrit['email']); ?></td>
                                    <td><?php echo date('d/m/Y', strtotime($inscrit['date_achat'])); ?></td>
                                    <td><i><?php echo htmlspecialchars($inscrit['statut']); ?></i></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p class="aucun-resultat" style="color: #ff9800; font-weight: bold;">Aucun participant ne s'est encore inscrit à vos événements pour le moment.</p>
                <?php endif; ?>
            </div>

        <?php endif; ?>
    </div>
 <a href="role.php">Modifier mon rôle</a>
</body>
</html>
