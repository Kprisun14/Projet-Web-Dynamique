<?php
session_start();

// Connexion à la base de données
try {
    $bdd = new PDO("mysql:host=localhost;port=3306;dbname=projet2526;charset=utf8", "root", "");
    $bdd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (Exception $e) {
    die("Erreur de connexion : " . $e->getMessage());
}

// 1. GESTION DU MOIS ET DE L'ANNÉE AFFICHÉS
// S'il n'y a rien dans l'URL, on prend le mois et l'année actuels
$m = isset($_GET['m']) ? intval($_GET['m']) : date('n');
$y = isset($_GET['y']) ? intval($_GET['y']) : date('Y');

// Boutons Précédent / Suivant
$prevM = $m - 1; $prevY = $y;
if ($prevM == 0) { $prevM = 12; $prevY--; }

$nextM = $m + 1; $nextY = $y;
if ($nextM == 13) { $nextM = 1; $nextY++; }

// Tableau des mois en français
$mois_francais = [
    1 => 'Janvier', 2 => 'Février', 3 => 'Mars', 4 => 'Avril',
    5 => 'Mai', 6 => 'Juin', 7 => 'Juillet', 8 => 'Août',
    9 => 'Septembre', 10 => 'Octobre', 11 => 'Novembre', 12 => 'Décembre'
];

// 2. RÉCUPÉRATION DES ÉVÉNEMENTS DU MOIS
$req = $bdd->prepare("
    SELECT id, titre, date_evenement, categorie 
    FROM evenements 
    WHERE MONTH(date_evenement) = ? AND YEAR(date_evenement) = ?
");
$req->execute([$m, $y]);
$evenements_bruts = $req->fetchAll(PDO::FETCH_ASSOC);

// On réorganise les événements dans un tableau avec le 'jour' comme clé
// Exemple : $evenements_du_mois[15] contiendra tous les événements du 15 du mois.
$evenements_du_mois = [];
foreach ($evenements_bruts as $event) {
    $jour = intval(date('d', strtotime($event['date_evenement'])));
    $evenements_du_mois[$jour][] = $event;
}

// 3. MATHÉMATIQUES DU CALENDRIER
$premier_jour_timestamp = mktime(0, 0, 0, $m, 1, $y);
$nombre_de_jours = date('t', $premier_jour_timestamp);

// On cherche quel jour de la semaine tombe le 1er du mois (0=Dimanche, 1=Lundi...)
$date_components = getdate($premier_jour_timestamp);
$jour_semaine = $date_components['wday'] - 1; 
if ($jour_semaine < 0) $jour_semaine = 6; // On force le Lundi à être la case 0 et Dimanche la case 6
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Calendrier - OmnesEvent</title>
    <link rel="stylesheet" href="calendrier.css">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;600;700&family=Plus+Jakarta+Sans:wght@400;500;600&display=swap" rel="stylesheet">
</head>
<body>

    <div class="menu-categories">
        <h1 style="margin: 0; font-size: 24px; font-family: 'Outfit'; background: linear-gradient(90deg, #2d3adb, #6c2bd9); -webkit-background-clip: text; color: transparent;">OmnesEvent</h1>
        <div class="dropdown"><a href="sommaire.php" class="dropbtn">Accueil</a></div>
        <?php if (isset($_SESSION['utilisateur_id'])): ?>
            <div class="dropdown"><a href="reglages.php" class="dropbtn">Mon Profil</a></div>
        <?php endif; ?>
    </div>

    <div class="calendrier-container">
        
        <div class="cal-header">
            <a href="?m=<?= $prevM ?>&y=<?= $prevY ?>" class="cal-btn">← Mois précédent</a>
            <h2><?= $mois_francais[$m] . " " . $y ?></h2>
            <a href="?m=<?= $nextM ?>&y=<?= $nextY ?>" class="cal-btn">Mois suivant →</a>
        </div>

        <div class="cal-grid">
            <div class="cal-day-name">Lun</div>
            <div class="cal-day-name">Mar</div>
            <div class="cal-day-name">Mer</div>
            <div class="cal-day-name">Jeu</div>
            <div class="cal-day-name">Ven</div>
            <div class="cal-day-name">Sam</div>
            <div class="cal-day-name">Dim</div>

            <?php
            // 1. Cases vides avant le 1er du mois
            for ($i = 0; $i < $jour_semaine; $i++) {
                echo "<div class='cal-cell empty'></div>";
            }

            // 2. Les jours du mois
            for ($jour = 1; $jour <= $nombre_de_jours; $jour++) {
                
                // Mettre en surbrillance le jour d'aujourd'hui
                $est_aujourd_hui = ($jour == date('j') && $m == date('n') && $y == date('Y')) ? 'today' : '';
                
                echo "<div class='cal-cell $est_aujourd_hui'>";
                echo "<span class='cal-date'>$jour</span>";

                // S'il y a des événements ce jour-là, on les affiche
                if (isset($evenements_du_mois[$jour])) {
                    echo "<div class='cal-events-list'>";
                    foreach ($evenements_du_mois[$jour] as $event) {
                        // Petite couleur différente selon la catégorie
                        $cat_class = strtolower($event['categorie']) == 'sport' ? 'cat-sport' : (strtolower($event['categorie']) == 'culture' ? 'cat-culture' : 'cat-soiree');
                        
                        echo "<a href='reserver.php?id=" . $event['id'] . "' class='cal-badge $cat_class' title='" . htmlspecialchars($event['titre']) . "'>";
                        echo htmlspecialchars($event['titre']);
                        echo "</a>";
                    }
                    echo "</div>";
                }

                echo "</div>";
            }

            // 3. Cases vides pour finir la dernière semaine
            $jours_restants = (7 - (($jour_semaine + $nombre_de_jours) % 7)) % 7;
            for ($i = 0; $i < $jours_restants; $i++) {
                echo "<div class='cal-cell empty'></div>";
            }
            ?>
        </div>

    </div>

</body>
</html>