<?php
session_start();

// 1. SÉCURITÉ ABSOLUE : Seul l'administrateur peut accéder à cette page
if (!isset($_SESSION['utilisateur_id']) || $_SESSION['role'] !== 'administrateur') {
    header("Location: sommaire.php");
    exit();
}

// 2. Connexion à la base de données
try {
    $bdd = new PDO("mysql:host=localhost;port=3306;dbname=projet2526;charset=utf8", "root", "");
    $bdd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (Exception $e) {
    die("Erreur de connexion : " . $e->getMessage());
}

$message = "";

// =========================================================
// 3. GESTION DES ACTIONS RAPIDES (Bannir, Promouvoir, etc.)
// =========================================================
if (isset($_GET['action']) && isset($_GET['id'])) {
    $id_cible = intval($_GET['id']);
    $action = $_GET['action'];

    // On s'empêche de s'auto-supprimer ou s'auto-rétrograder par erreur
    if ($id_cible === $_SESSION['utilisateur_id']) {
        $message = "<div class='alerte error'>Vous ne pouvez pas modifier votre propre compte administrateur ici.</div>";
    } else {
        if ($action === 'bannir') {
            $req = $bdd->prepare("DELETE FROM utilisateurs WHERE id = ?");
            $req->execute([$id_cible]);
            $message = "<div class='alerte success'>L'utilisateur a été banni et supprimé.</div>";
        } elseif ($action === 'promouvoir') {
            $req = $bdd->prepare("UPDATE utilisateurs SET role = 'organisateur' WHERE id = ?");
            $req->execute([$id_cible]);
            $message = "<div class='alerte success'>Le compte a été validé en tant qu'Organisateur.</div>";
        } elseif ($action === 'retrograder') {
            $req = $bdd->prepare("UPDATE utilisateurs SET role = 'participant' WHERE id = ?");
            $req->execute([$id_cible]);
            $message = "<div class='alerte success'>L'organisateur a été rétrogradé au rang de participant.</div>";
        }
    }
}

// =========================================================
// 4. RÉCUPÉRATION DES DONNÉES POUR L'AFFICHAGE
// =========================================================

// A. Récupérer tous les utilisateurs (triés par rôle)
$req_users = $bdd->query("SELECT * FROM utilisateurs ORDER BY role ASC, nom ASC");
$utilisateurs = $req_users->fetchAll(PDO::FETCH_ASSOC);

// B. Récupérer TOUS les événements avec le nom de leur créateur (pour la modération)
$req_events = $bdd->query("
    SELECT e.*, u.nom, u.prenom 
    FROM evenements e 
    LEFT JOIN utilisateurs u ON e.organisateur_id = u.id 
    ORDER BY e.date_creation DESC
");
$evenements = $req_events->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Panel Administration - OmnesEvent</title>
    <link rel="stylesheet" href="admin.css">
    <style>
        .alerte { padding: 15px; border-radius: 8px; margin-bottom: 20px; font-weight: bold; }
        .alerte.success { background-color: #def7ec; color: #03543f; border: 1px solid #bcf0da; }
        .alerte.error { background-color: #fde8e8; color: #9b1c1c; border: 1px solid #f8b4b4; }
        
        .btn-admin { padding: 6px 12px; border-radius: 6px; text-decoration: none; font-size: 12px; font-weight: bold; color: white; display: inline-block; margin: 2px; }
        .btn-admin.green { background-color: #059669; }
        .btn-admin.orange { background-color: #ea580c; }
        .btn-admin.red { background-color: #e11d48; }
        .btn-admin:hover { filter: brightness(1.1); transform: translateY(-1px); }
        
        .role-badge { padding: 4px 8px; border-radius: 4px; font-size: 11px; font-weight: bold; text-transform: uppercase; }
        .role-admin { background: #1e1b4b; color: #c7d2fe; }
        .role-orga { background: #4c1d95; color: #ddd6fe; }
        .role-parti { background: #f1f5f9; color: #64748b; border: 1px solid #cbd5e1; }
    </style>
</head>
<body>

    <div class="menu-categories">
        <h1 style="margin: 0; font-size: 24px;">OmnesEvent <span style="color:red; font-size: 14px;">[ADMIN]</span></h1>
        <div class="dropdown"><a href="sommaire.php" class="dropbtn" style="text-decoration: none; color: black;">Retour au site</a></div>
    </div>

    <div class="profil-container" style="max-width: 1200px;">
        <h1 class="titre-profil">Centre de Contrôle & Modération 🛡️</h1>
        <p>Espace réservé à l'administrateur système.</p>

        <?php echo $message; ?>

        <div class="profil-section">
            <h2>👥 Gestion des Utilisateurs & Validations</h2>
            <p class="aucun-resultat">Validez les demandes pour devenir Organisateur, gérez les litiges ou bannissez des comptes.</p>
            
            <table class="table-inscrits">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nom Complet</th>
                        <th>Email</th>
                        <th>Rôle actuel</th>
                        <th>Actions Administrateur</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($utilisateurs as $u): ?>
                        <tr>
                            <td>#<?= $u['id'] ?></td>
                            <td><strong><?= htmlspecialchars($u['nom'] . ' ' . $u['prenom']) ?></strong></td>
                            <td><?= htmlspecialchars($u['email']) ?></td>
                            <td>
                                <?php 
                                    if ($u['role'] == 'administrateur') echo "<span class='role-badge role-admin'>Admin</span>";
                                    elseif ($u['role'] == 'organisateur') echo "<span class='role-badge role-orga'>Organisateur</span>";
                                    else echo "<span class='role-badge role-parti'>Participant</span>";
                                ?>
                            </td>
                            <td>
                                <?php if ($u['id'] !== $_SESSION['utilisateur_id']): ?>
                                    
                                    <?php if ($u['role'] === 'participant'): ?>
                                        <a href="?action=promouvoir&id=<?= $u['id'] ?>" class="btn-admin green" onclick="return confirm('Valider ce compte en tant qu\'Organisateur ?');">⬆️ Passer Orga</a>
                                    <?php elseif ($u['role'] === 'organisateur'): ?>
                                        <a href="?action=retrograder&id=<?= $u['id'] ?>" class="btn-admin orange" onclick="return confirm('Retirer les droits d\'organisation à cet utilisateur ?');">⬇️ Rétrograder</a>
                                    <?php endif; ?>
                                    
                                    <a href="?action=bannir&id=<?= $u['id'] ?>" class="btn-admin red" onclick="return confirm('BANNIR DÉFINITIVEMENT ce compte ? Cela supprimera tous ses billets et événements !');">❌ Bannir</a>
                                
                                <?php else: ?>
                                    <span style="color: #aaa; font-style: italic; font-size: 12px;">C'est vous</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <div class="profil-section">
            <h2>🚨 Modération des Événements</h2>
            <p class="aucun-resultat">Vue sur la totalité des événements de la plateforme. En cas de contenu inapproprié, vous pouvez supprimer l'événement de force.</p>

            <table class="table-inscrits">
                <thead>
                    <tr>
                        <th>Événement</th>
                        <th>Créé par</th>
                        <th>Date & Lieu</th>
                        <th>Catégorie</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($evenements as $ev): ?>
                        <tr>
                            <td><strong><?= htmlspecialchars($ev['titre']) ?></strong></td>
                            <td><?= htmlspecialchars($ev['nom'] . ' ' . $ev['prenom']) ?: '<i style="color:red">Auteur supprimé</i>' ?></td>
                            <td><?= date('d/m/Y', strtotime($ev['date_evenement'])) ?><br><small><?= htmlspecialchars($ev['lieu']) ?></small></td>
                            <td><?= htmlspecialchars($ev['categorie']) ?></td>
                            <td>
                                <a href="supprimer_evenement.php?id=<?= $ev['id'] ?>" class="btn-admin red" onclick="return confirm('Supprimer DE FORCE cet événement de la plateforme ?');">❌ Censure / Supprimer</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

    </div>

</body>
</html>