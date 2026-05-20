<?php
session_start();

// 1. Sécurité : Seuls les utilisateurs connectés peuvent réserver
if (!isset($_SESSION['utilisateur_id'])) {
    header("Location: index.php");
    exit();
}

// 2. Vérification de l'ID de l'événement passé dans l'URL (ex: reservation.php?id=3)
if (!isset($_GET['id']) || empty($_GET['id'])) {
    die("Événement non spécifié.");
}

$evenement_id = intval($_GET['id']);
$utilisateur_id = $_SESSION['utilisateur_id'];
$message = "";
$message_type = ""; // 'success' ou 'error'

// 3. Connexion à la base de données
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

// 4. LOGIQUE DE RÉSERVATION (Si l'utilisateur clique sur le bouton)
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['confirmer_reservation'])) {
    
    // Étape A : On revérifie la jauge en base de données pour éviter la triche
    $req_jauge = $bdd->prepare("SELECT places_max FROM evenements WHERE id = ?");
    $req_jauge->execute([$evenement_id]);
    $event_jauge = $req_jauge->fetch(PDO::FETCH_ASSOC);

    $req_comptage = $bdd->prepare("SELECT COUNT(*) AS total FROM billets WHERE evenement_id = ?");
    $req_comptage->execute([$evenement_id]);
    $deja_vendus = $req_comptage->fetch(PDO::FETCH_ASSOC)['total'];

    // Étape B : On compare
    if ($deja_vendus >= $event_jauge['places_max']) {
        $message = "Désolé, cet événement vient d'afficher complet !";
        $message_type = "error";
    } else {
        // Étape C : Génération d'un code billet unique (ex: OMNES-65F3A...)
        $code_billet = strtoupper(uniqid('OMNES-'));

        // Étape D : Insertion dans votre table billets
        $req_insert = $bdd->prepare("
            INSERT INTO billets (utilisateur_id, evenement_id, code_billet, statut, date_achat)
            VALUES (?, ?, ?, 'Valide', NOW())
        ");
        
        if ($req_insert->execute([$utilisateur_id, $evenement_id, $code_billet])) {
            $message = "Votre réservation a bien été enregistrée ! Retrouvez votre billet sur votre profil.";
            $message_type = "success";
        } else {
            $message = "Une erreur est survenue lors de la réservation.";
            $message_type = "error";
        }
    }
}

// 5. AFFICHAGE DE L'ÉVÉNEMENT ET CALCUL DES PLACES RESTANTES
// Récupération des infos de l'événement
$req_event = $bdd->prepare("SELECT * FROM evenements WHERE id = ?");
$req_event->execute([$evenement_id]);
$evenement = $req_event->fetch(PDO::FETCH_ASSOC);

if (!$evenement) {
    die("Événement introuvable.");
}

// Calcul des places déjà réservées
$req_total_billets = $bdd->prepare("SELECT COUNT(*) AS total FROM billets WHERE evenement_id = ?");
$req_total_billets->execute([$evenement_id]);
$places_prises = $req_total_billets->fetch(PDO::FETCH_ASSOC)['total'];

// Calcul final des places encore disponibles
$places_restantes = $evenement['places_max'] - $places_prises;
$est_complet = ($places_restantes <= 0);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Réserver : <?php echo htmlspecialchars($evenement['titre']); ?></title>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <link rel="stylesheet" href="reservation.css"> <style>
        
        .alerte {
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-weight: bold;
        }
        .alerte.success { background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .alerte.error { background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        
        .jauge-container {
            margin: 20px 0;
            padding: 15px;
            background-color: #f8f9fa;
            border-radius: 8px;
        }
        .complet-badge {
            background-color: #dc3545;
            color: white;
            padding: 10px 20px;
            border-radius: 6px;
            display: inline-block;
            font-weight: bold;
        }
        .btn-disabled {
            background-color: #cccccc !important;
            cursor: not-allowed !important;
        }
    </style>
</head>
<body>

    <div class="menu-categories">
        <h1 style="margin: 0; font-size: 24px;">OmnesEvent</h1>
        <div class="dropdown"><a href="sommaire.php" class="dropbtn" style="text-decoration: none; color: black;">Accueil</a></div>
        <div class="dropdown"><a href="reglages.php" class="dropbtn" style="text-decoration: none; color: black;">Mon Profil</a></div>
    </div>

    <div class="profil-container">
        
        <?php if (!empty($message)): ?>
            <div class="alerte <?php echo $message_type; ?>">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>

        <div class="profil-section">
            <span style="text-transform: uppercase; font-size: 12px; font-weight: bold; color: #23178d;">
                <?php echo htmlspecialchars($evenement['categorie']); ?> — <?php echo htmlspecialchars($evenement['association']); ?>
            </span>
            
            <h1 style="margin: 5px 0 15px 0; font-size: 32px;"><?php echo htmlspecialchars($evenement['titre']); ?></h1>
            
            <p style="font-size: 16px; color: #444; line-height: 1.6;">
                <?php echo nl2br(htmlspecialchars($evenement['description'])); ?>
            </p>
            
            <p>📅 <strong>Date :</strong> <?php echo date('d/m/Y', strtotime($evenement['date_evenement'])); ?> à <?php echo htmlspecialchars($evenement['heure_evenement']); ?></p>
            <p>📍 <strong>Lieu :</strong> <?php echo htmlspecialchars($evenement['lieu']); ?></p>
            <div class="carte-container">
                <h3 style="font-family: 'Outfit'; font-size: 16px; margin-bottom: 10px;">🗺️ Plan d'accès</h3>
                <div id="map" class="carte-interactive"></div>
            </div>
            <p>💰 <strong>Prix d'entrée :</strong> <?php echo htmlspecialchars($evenement['prix']); ?> €</p>

            <div class="jauge-container">
                <h3>👥 Disponibilités en temps réel</h3>
                <p>Capacité maximale de l'événement : <strong><?php echo $evenement['places_max']; ?> places</strong></p>
                <p>Places encore disponibles : <strong style="color: <?php echo $est_complet ? 'red' : 'green'; ?>;"><?php echo $places_restantes; ?></strong></p>
            </div>

            <form method="post" action="reservation.php?id=<?php echo $evenement_id; ?>">
                <?php if ($est_complet): ?>
                    <div class="complet-badge">❌ ÉVÉNEMENT COMPLET (Jauge atteinte)</div>
                <?php else: ?>
                    <input type="hidden" name="confirmer_reservation" value="1">
                    <button type="submit" class="btn-profil-action" style="font-size: 16px; padding: 12px 30px;">
                        🎟️ Confirmer mon inscription à l'événement
                    </button>
                <?php endif; ?>
            </form>

        </div>
    </div>
    <script>
        // On récupère le nom du lieu depuis le PHP
        const lieuEvenement = "<?php echo addslashes(htmlspecialchars($evenement['lieu'])); ?>";

        // Initialisation de la carte (Centrée sur la France par défaut)
        const map = L.map('map').setView([46.603354, 1.888334], 5);

        // Ajout du fond de carte OpenStreetMap
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
        }).addTo(map);

        // Appel à l'API gratuite Nominatim pour transformer le texte du lieu en coordonnées GPS
        fetch('https://nominatim.openstreetmap.org/search?format=json&q=' + encodeURIComponent(lieuEvenement))
            .then(response => response.json())
            .then(data => {
                if (data.length > 0) {
                    // Si on trouve le lieu, on récupère Latitude et Longitude
                    const lat = data[0].lat;
                    const lon = data[0].lon;
                    
                    // On centre la carte sur ce point avec un zoom de 15
                    map.setView([lat, lon], 15);
                    
                    // On ajoute un marqueur
                    L.marker([lat, lon]).addTo(map)
                        .bindPopup('<b>Lieu de l\'événement :</b><br>' + lieuEvenement)
                        .openPopup();
                } else {
                    console.log("Le lieu n'a pas pu être géolocalisé avec précision.");
                }
            })
            .catch(error => console.error('Erreur API Geocoding:', error));
    </script>

</body>
</html>