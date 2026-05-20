<?php
session_start();

// Sécurité : utilisateur connecté uniquement
if (!isset($_SESSION['utilisateur_id'])) {
    header("Location: index.php");
    exit();
}

// Connexion BDD
try {
    $bdd = new PDO(
        "mysql:host=localhost;port=3306;dbname=projet2526;charset=utf8",
        "root", ""
    );
    $bdd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (Exception $e) {
    die("Erreur de connexion : " . $e->getMessage());
}

$id_connecte     = $_SESSION['utilisateur_id'];
$code_billet_get = isset($_GET['code']) ? trim($_GET['code']) : null;

if (!$code_billet_get) {
    die("Code billet manquant.");
}

// Récupérer le billet + événement + utilisateur — on vérifie que le billet appartient bien à l'utilisateur connecté
$req = $bdd->prepare("
    SELECT b.code_billet, b.statut, b.date_achat,
           e.titre, e.date_evenement, e.heure_evenement, e.lieu, e.prix,
           u.nom, u.prenom, u.email
    FROM billets b
    JOIN evenements e ON b.evenement_id = e.id
    JOIN utilisateurs u ON b.utilisateur_id = u.id
    WHERE b.code_billet = ? AND b.utilisateur_id = ?
");
$req->execute([$code_billet_get, $id_connecte]);
$billet = $req->fetch(PDO::FETCH_ASSOC);

if (!$billet) {
    die("Billet introuvable ou accès non autorisé.");
}

// Données encodées dans le QR code (lisibles par n'importe quel lecteur)
$qr_data = implode(' | ', [
    'OmnesEvent',
    'Code: '    . $billet['code_billet'],
    'Billet: '  . $billet['titre'],
    'Date: '    . date('d/m/Y', strtotime($billet['date_evenement'])) . ' ' . $billet['heure_evenement'],
    'Lieu: '    . $billet['lieu'],
    'Titulaire: ' . $billet['prenom'] . ' ' . $billet['nom'],
    'Email: '   . $billet['email'],
    'Statut: '  . $billet['statut'],
]);

// URL de l'API QR (bibliothèque externe, aucune installation requise)
$qr_url = 'https://api.qrserver.com/v1/create-qr-code/?size=260x260&ecc=H&data=' . urlencode($qr_data);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Billet — <?php echo htmlspecialchars($billet['titre']); ?></title>
    <link rel="stylesheet" href="billet_qr.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;600;700;800&family=Plus+Jakarta+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
</head>
<body>

    <!-- Fond décoratif -->
    <div class="bg-orb orb-1"></div>
    <div class="bg-orb orb-2"></div>

    <div class="page-wrapper">

        <!-- Header -->
        <header class="top-bar">
            <span class="logo-text">OmnesEvent</span>
            <a href="reglages.php" class="back-link">← Mon espace</a>
        </header>

        <!-- Ticket principal -->
        <main class="ticket-wrapper">

            <!-- Partie gauche : infos -->
            <div class="ticket-body">

                <div class="ticket-badge">🎟️ Billet électronique</div>

                <h1 class="event-title"><?php echo htmlspecialchars($billet['titre']); ?></h1>

                <div class="info-grid">
                    <div class="info-item">
                        <span class="info-icon">📅</span>
                        <div>
                            <span class="info-label">Date</span>
                            <span class="info-value"><?php echo date('d/m/Y', strtotime($billet['date_evenement'])); ?> à <?php echo htmlspecialchars($billet['heure_evenement']); ?></span>
                        </div>
                    </div>
                    <div class="info-item">
                        <span class="info-icon">📍</span>
                        <div>
                            <span class="info-label">Lieu</span>
                            <span class="info-value"><?php echo htmlspecialchars($billet['lieu']); ?></span>
                        </div>
                    </div>
                    <div class="info-item">
                        <span class="info-icon">👤</span>
                        <div>
                            <span class="info-label">Titulaire</span>
                            <span class="info-value"><?php echo htmlspecialchars($billet['prenom'] . ' ' . $billet['nom']); ?></span>
                        </div>
                    </div>
                    <div class="info-item">
                        <span class="info-icon">✉️</span>
                        <div>
                            <span class="info-label">Email</span>
                            <span class="info-value"><?php echo htmlspecialchars($billet['email']); ?></span>
                        </div>
                    </div>
                    <div class="info-item">
                        <span class="info-icon">💳</span>
                        <div>
                            <span class="info-label">Prix</span>
                            <span class="info-value"><?php echo htmlspecialchars($billet['prix']); ?> €</span>
                        </div>
                    </div>
                    <div class="info-item">
                        <span class="info-icon">📆</span>
                        <div>
                            <span class="info-label">Acheté le</span>
                            <span class="info-value"><?php echo date('d/m/Y', strtotime($billet['date_achat'])); ?></span>
                        </div>
                    </div>
                </div>

                <div class="ticket-code-bar">
                    <span class="code-label">Code billet</span>
                    <span class="code-value"><?php echo htmlspecialchars($billet['code_billet']); ?></span>
                    <?php
                        $statut = strtolower($billet['statut']);
                        $badge_class = ($statut === 'validé' || $statut === 'valide') ? 'badge-valide' : 'badge-actif';
                    ?>
                    <span class="statut-badge <?php echo $badge_class; ?>"><?php echo htmlspecialchars($billet['statut']); ?></span>
                </div>

            </div>

            <!-- Séparateur perforé -->
            <div class="ticket-separator">
                <div class="sep-dot sep-top"></div>
                <div class="sep-line"></div>
                <div class="sep-dot sep-bot"></div>
            </div>

            <!-- Partie droite : QR Code -->
            <div class="ticket-qr">
                <div class="qr-frame">
                    <img src="<?php echo $qr_url; ?>"
                         alt="QR Code billet <?php echo htmlspecialchars($billet['code_billet']); ?>"
                         class="qr-img"
                         loading="lazy">
                </div>
                <p class="qr-hint">Présentez ce QR code<br>à l'entrée de l'événement</p>
                <a href="<?php echo $qr_url; ?>" download="billet-<?php echo htmlspecialchars($billet['code_billet']); ?>.png" class="btn-download" target="_blank">
                    ⬇ Télécharger le QR
                </a>
            </div>

        </main>

        <!-- Action imprimer -->
        <div class="actions-bar">
            <button onclick="window.print()" class="btn-print">🖨️ Imprimer le billet</button>
            <a href="reglages.php" class="btn-retour">← Retour à mon espace</a>
        </div>

    </div>

</body>
</html>