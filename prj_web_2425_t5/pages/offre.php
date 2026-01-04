<?php
require_once('../data_base/connection.php');
session_start();

try {
    // Requête 1 SQL pour récupérer les offres
    $sSQL1 = "SELECT * FROM offres";
    $stmt1 = $dbh->prepare($sSQL1);
    $stmt1->execute();
    $offres = $stmt1->fetchAll(PDO::FETCH_ASSOC);

    // Requête 2 pour récupérer les destinations
    $sSQL2 = "SELECT id, pays, ville FROM destinations";
    $stmt2 = $dbh->prepare($sSQL2);
    $stmt2->execute();
    $dests = $stmt2->fetchAll(PDO::FETCH_ASSOC);

    // Requête 3 pour récupérer les logements
    $sSQL3 = "SELECT id, nom, type_logement FROM logements";
    $stmt3 = $dbh->prepare($sSQL3);
    $stmt3->execute();
    $logs = $stmt3->fetchAll(PDO::FETCH_ASSOC);

    // un tableau associatif pour un accès plus facile
    $destinationsMap = [];
    foreach ($dests as $dest) 
    {
        $destinationsMap[$dest['id']] = $dest;
    }

    $logementsMap = [];
    foreach ($logs as $log)
    {
        $logementsMap[$log['id']] = $log;
    }

    // offres avec les données des destinations et logements
    foreach ($offres as &$offre) 
    {
        // informations de destination
        if (isset($offre['destination_id']) AND isset($destinationsMap[$offre['destination_id']])) 
        {
            $offre['destination_pays'] = $destinationsMap[$offre['destination_id']]['pays'];
            $offre['destination_ville'] = $destinationsMap[$offre['destination_id']]['ville'];
        } 

        // informations de logement
        if (isset($offre['logement_id']) AND isset($logementsMap[$offre['logement_id']])) 
        {
            $offre['logement_nom'] = $logementsMap[$offre['logement_id']]['nom'];
            $offre['logement_type'] = $logementsMap[$offre['logement_id']]['type_logement'];
        } 
    }
    

} 
catch (PDOException $e) 
{
    die('Erreur de la requête : ' . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Offres de voyage</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../styles/offres.css">
</head>
<body>
    <header class="header-offres">
        <h1>Nos <span>Offres</span> de Voyage</h1>
    </header>

    <main class="main-container">
        <a href="../index.php" class="btn-back"><i class="fas fa-arrow-left"></i> Retour à l'accueil</a>
        
        <section class="offres-section">
            <?php if (empty($offres)): ?>
                <div class="no-offers">
                    <i class="fas fa-suitcase"></i>
                    <p>Aucune offre disponible pour le moment.</p>
                </div>
            <?php else: ?>
                <div class="offres-grid">
                    <?php foreach ($offres as $offre): ?>
                        <div class="offer-card">
                            <div class="offer-image">
                                <img src="<?= !empty($offre['images']) ? htmlspecialchars($offre['images']) : '../img/default_offre.jpg'; ?>" alt="<?= htmlspecialchars($offre['titre'] ?? '') ?>">
                            </div>
                            
                            <div class="offer-content">
                                <h3><?= htmlspecialchars($offre['titre'] ?? '') ?></h3>
                                
                                <div class="offer-details">
                                    <span><i class="fas fa-map-marker-alt"></i> <?= htmlspecialchars($offre['destination_pays'] ?? 'Non spécifié') ?></span>
                                    <span><i class="fas fa-city"></i> <?= htmlspecialchars($offre['destination_ville'] ?? 'Non spécifié') ?></span>
                                    <span><i class="fas fa-hotel"></i> <?= htmlspecialchars($offre['logement_nom'] ?? 'Non spécifié') ?></span>
                                    <span><i class="fas fa-calendar-alt"></i> <?= isset($offre['date_depart']) ? date('d/m/Y', strtotime($offre['date_depart'])) : 'Date inconnue' ?></span>
                                </div>

                                <div class="offer-price">
                                    <span class="price-current"><?= isset($offre['prix']) ? number_format($offre['prix'], 2, ',', ' ') . ' €' : 'Prix non disponible' ?></span>
                                </div>
                                
                                <div class="offer-actions">
                                    <a href="details_offre.php?id=<?= $offre['id'] ?>" class="btn-details">Détails <i class="fas fa-arrow-right"></i></a>
                                    <a href="reservation.php?id=<?= $offre['id'] ?>" class="btn-reserver">Réserver <i class="fas fa-shopping-cart"></i></a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>
    </main>
    
    <!-- Footer -->
    <footer class="footer">
        <div class="footer-content">
            <div class="footer-section">
                <div class="logo-footer">
                    <span>Tripster</span>
                    <p>Voyagez jeune, vivez libre</p>
                </div>
                
                <div class="social-links">
                    <a href="#"><i class="fab fa-tiktok"></i></a>
                    <a href="#"><i class="fab fa-instagram"></i></a>
                    <a href="#"><i class="fab fa-snapchat"></i></a>
                    <a href="#"><i class="fab fa-twitter"></i></a>
                </div>
            </div>
            
            <div class="footer-section">
                <h3>Explorer</h3>
                <ul>
                    <li><a href="pages/offre.php">Offres spéciales</a></li>
                    <li><a href="pages/blog.php">Blog voyage</a></li>
                    <li><a href="pages/forum.php">Forum</a></li>
                    <li><a href="#">Destinations tendances</a></li>
                </ul>
            </div>
            
            <div class="footer-section">
                <h3>Aide</h3>
                <ul>
                    <li><a href="pages/contact.php">Contactez-nous</a></li>
                    <li><a href="#">FAQ</a></li>
                    <li><a href="#">Conditions générales</a></li>
                    <li><a href="#">Politique de confidentialité</a></li>
                </ul>
            </div>
            
            <div class="footer-section">
                <h3>Moyens de paiement</h3>
                <div class="payment-methods">
                    <i class="fab fa-cc-visa" title="Visa"></i>
                    <i class="fab fa-cc-mastercard" title="Mastercard"></i>
                    <i class="fab fa-cc-paypal" title="PayPal"></i>
                    <i class="fab fa-cc-apple-pay" title="Apple Pay"></i>
                </div>
                
                <div class="app-download">
                    <p>Téléchargez notre app</p>
                    <div class="app-buttons">
                        <a href="#"><img src="img/app-store.png" alt="App Store"></a>
                        <a href="#"><img src="img/google-play.png" alt="Google Play"></a>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="footer-bottom">
            <p>&copy; 2025 Tripster. Tous droits réservés. | Conçu avec <i class="fas fa-heart"></i> pour les jeunes voyageurs</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>