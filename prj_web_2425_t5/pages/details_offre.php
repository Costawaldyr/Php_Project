<?php
require_once('../data_base/connection.php');
session_start();

$offre = null;
$logementImages = [];

try {
    // Récupérer l'offre et les détails du logement associé
    $sQuery = '
    SELECT 
        offres.*, 
        logements.*,
        destinations.pays,
        destinations.ville,
        destinations.type_activites,
        destinations.conseils,
        destinations.endroits_visiter,
        destinations.langue,
        destinations.monnaie,
        destinations.transport_commun,
        destinations.peuples_culture,
        aller.compagnie AS compagnie_aller,
        aller.numero AS numero_aller,
        retour.compagnie AS compagnie_retour,
        retour.numero AS numero_retour
    FROM offres
    JOIN logements ON offres.logement_id = logements.id
    JOIN destinations ON offres.destination_id = destinations.id
    LEFT JOIN transports AS aller ON offres.transport_aller_id = aller.id
    LEFT JOIN transports AS retour ON offres.transport_retour_id = retour.id
    WHERE offres.id = :id
    ';
    $stmt = $dbh->prepare($sQuery);
    $stmt->bindValue(':id', $_GET['id']);
    $stmt->execute();
    $offre = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$offre) {
        die("Offre non trouvée.");
    }

    // Récupérer les images du logement
    $stmt = $dbh->prepare('SELECT image_url FROM logement_images WHERE logement_id = ? ORDER BY ordre');
    $stmt->execute([$offre['logement_id']]);
    $logementImages = $stmt->fetchAll(PDO::FETCH_COLUMN);

} catch (PDOException $e) {
    die("Erreur lors de la récupération des détails de l'offre : " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Détails de l'offre - <?= htmlspecialchars($offre['titre']) ?></title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&family=Fredoka+One&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../styles/offres.css">

    <style>
        .thumbnail-container {
            display: flex;
            gap: 10px;
            margin-top: 15px;
        }
        .thumbnail {
            width: 80px;
            height: 60px;
            object-fit: cover;
            border-radius: 5px;
            cursor: pointer;
            border: 2px solid transparent;
            transition: all 0.3s ease;
        }
        .thumbnail:hover {
            border-color: #007bff;
            transform: scale(1.05);
        }
        .main-image {
            width: 100%;
            height: 400px;
            object-fit: cover;
            border-radius: 8px;
            margin-bottom: 15px;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header class="header-offres">
        <h1>Détails de <span>l'offre</span></h1>
    </header>
    <a href="../index.php" class="btn-back"><i class="fas fa-arrow-left"></i> Retour à l'accueil</a>
    
    <!-- Main Content -->
    <div class="main-container">
        <section class="detail-section">
            <div class="detail-container">
                <!-- Gallery Section -->
                <div class="detail-gallery">
                    <?php if (!empty($logementImages)): ?>
                        <img src="<?= htmlspecialchars($logementImages[0]) ?>" class="main-image" alt="Image principale du logement" id="mainImage">
                        
                        <div class="thumbnail-container">
                            <?php foreach ($logementImages as $index => $image): ?>
                                <img src="<?= htmlspecialchars($image) ?>" 
                                     class="thumbnail" 
                                     alt="Miniature <?= $index + 1 ?>"
                                     onclick="document.getElementById('mainImage').src = '<?= htmlspecialchars($image) ?>'">
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <img src="../img/default-logement.jpg" class="main-image" alt="Image par défaut du logement">
                    <?php endif; ?>
                </div>

                <!-- Info Section (identique à votre version originale) -->
                <div class="detail-meta">
                    <span><i class="fas fa-calendar-alt"></i> <?= $offre['duree_sejour'] ?> jours</span>
                    <span><i class="fas fa-users"></i> Jusqu'à <?= $offre['capacite_max'] ?> personnes</span>
                    <?php if ($offre['etoiles']): ?>
                    <span><i class="fas fa-star"></i> <?= $offre['etoiles'] ?> étoiles</span>
                    <?php endif; ?>
                    <span><i class="fas fa-map-marker-alt"></i> <?= htmlspecialchars($offre['ville']) ?>, <?= htmlspecialchars($offre['pays']) ?></span>
                    <span>
                        <i class="fas fa-<?= 
                            $offre['type_transport'] === 'avion' ? 'plane' : 
                            ($offre['type_transport'] === 'train' ? 'train' :
                            ($offre['type_transport'] === 'bus' ? 'bus' :
                            ($offre['type_transport'] === 'voiture' ? 'car' : 'users')))
                        ?>"></i> 
                        Transport: <?= ucfirst(htmlspecialchars($offre['type_transport'])) ?>
                    </span>
                    <?php if (!empty($offre['numero_aller'])): ?>
                        <?php if ($offre['type_transport'] === 'avion'): ?>
                            <span><i class="fas fa-plane-departure"></i> Vol aller: <?= htmlspecialchars($offre['compagnie_aller']) ?> <?= htmlspecialchars($offre['numero_aller']) ?></span>
                        <?php elseif ($offre['type_transport'] === 'train'): ?>
                            <span><i class="fas fa-train"></i> Train aller: <?= htmlspecialchars($offre['compagnie_aller']) ?> n°<?= htmlspecialchars($offre['numero_aller']) ?></span>
                        <?php elseif ($offre['type_transport'] === 'bus'): ?>
                            <span><i class="fas fa-bus"></i> Bus aller: Ligne <?= htmlspecialchars($offre['numero_aller']) ?></span>
                        <?php elseif ($offre['type_transport'] === 'voiture'): ?>
                            <span><i class="fas fa-car"></i> Voiture: Plaque <?= htmlspecialchars($offre['numero_aller']) ?></span>
                        <?php elseif ($offre['type_transport'] === 'covoiturage'): ?>
                            <span><i class="fas fa-users"></i> Covoiturage: Ref. <?= htmlspecialchars($offre['numero_aller']) ?></span>
                        <?php endif; ?>
                    <?php endif; ?>
                    <?php if (!empty($offre['numero_retour'])): ?>
                        <?php if ($offre['type_transport'] === 'avion'): ?>
                            <span><i class="fas fa-plane-arrival"></i> Vol retour: <?= htmlspecialchars($offre['compagnie_retour']) ?> <?= htmlspecialchars($offre['numero_retour']) ?></span>
                        <?php elseif ($offre['type_transport'] === 'train'): ?>
                            <span><i class="fas fa-train"></i> Train retour: <?= htmlspecialchars($offre['compagnie_retour']) ?> n°<?= htmlspecialchars($offre['numero_retour']) ?></span>
                        <?php elseif ($offre['type_transport'] === 'bus'): ?>
                            <span><i class="fas fa-bus"></i> Bus retour: Ligne <?= htmlspecialchars($offre['numero_retour']) ?></span>
                        <?php elseif ($offre['type_transport'] === 'voiture'): ?>
                            <span><i class="fas fa-car"></i> Voiture: Plaque <?= htmlspecialchars($offre['numero_retour']) ?></span>
                        <?php elseif ($offre['type_transport'] === 'covoiturage'): ?>
                            <span><i class="fas fa-users"></i> Covoiturage: Ref. <?= htmlspecialchars($offre['numero_retour']) ?></span>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
                    
                    <div class="detail-description">
                        <h3>Description du séjour</h3>
                        <p><?= nl2br(htmlspecialchars($offre['description'])) ?></p>
                        
                        <h3>Description du logement</h3>
                        <p><?= nl2br(htmlspecialchars($offre['description'])) ?></p>
                    </div>
                    
                    <div class="detail-highlights">
                        <h3>Points forts</h3>
                        <div class="highlight-item">
                            <i class="fas fa-check"></i>
                            <span>Transport inclus: <?= ucfirst(htmlspecialchars($offre['type_transport'])) ?></span>
                        </div>
                        
                        <?php if ($offre['all_inclusive']): ?>
                        <div class="highlight-item">
                            <i class="fas fa-utensils"></i>
                            <span>Formule All Inclusive</span>
                        </div>
                        <?php endif; ?>
                        
                        <?php if ($offre['demi_pension']): ?>
                        <div class="highlight-item">
                            <i class="fas fa-utensils"></i>
                            <span>Demi-pension incluse</span>
                        </div>
                        <?php endif; ?>
                        
                        <?php if ($offre['petit_dejeuner']): ?>
                        <div class="highlight-item">
                            <i class="fas fa-coffee"></i>
                            <span>Petit déjeuner inclus</span>
                        </div>
                        <?php endif; ?>
                        
                        <?php if ($offre['wifi']): ?>
                        <div class="highlight-item">
                            <i class="fas fa-wifi"></i>
                            <span>Wi-Fi gratuit</span>
                        </div>
                        <?php endif; ?>
                        
                        <?php if (!empty($offre['activite_jeune']) && $offre['activite_jeune'] != 'Non spécifié'): ?>
                        <div class="highlight-item">
                            <i class="fas fa-child"></i>
                            <span>Activités jeunes: <?= htmlspecialchars($offre['activite_jeune']) ?></span>
                        </div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="price-section">
                        <div class="price-individual">
                            <?php if ($offre['ancien_prix'] > $offre['prix']): ?>
                                <p class="price-old"><?= number_format($offre['ancien_prix'], 2, ',', ' ') ?> €</p>
                                <p class="price-new"><?= number_format($offre['prix'], 2, ',', ' ') ?> €</p>
                                <p class="price-discount">Économisez <?= number_format($offre['ancien_prix'] - $offre['prix'], 2, ',', ' ') ?> € !</p>
                            <?php else: ?>
                                <p class="price-current"><?= number_format($offre['prix'], 2, ',', ' ') ?> €</p>
                            <?php endif; ?>
                            <small>Prix par personne</small><hr>
                        </div>
                        
                        <?php if (!empty($offre['prix_groupe'])): ?>
                        <div class="price-group">
                            <p class="price-group-value"><?= number_format($offre['prix_groupe'], 2, ',', ' ') ?> €</p><small>Prix par groupe (3-4 pers.)</small>
                        </div>
                        <?php endif; ?>
                        
                        <p class="availability">
                            <?php if ($offre['disponibilite'] > 0): ?>
                                <i class="fas fa-check-circle"></i> <?= $offre['disponibilite'] ?> places disponibles
                            <?php else: ?>
                                <i class="fas fa-times-circle"></i> Complet
                            <?php endif; ?>
                        </p>
                        
                        <div class="detail-actions">
                            <?php if ($offre['disponibilite'] > 0): ?>
                                <a href="reservation.php?id=<?= $offre['id'] ?>" class="btn-reserver">
                                    <i class="fas fa-calendar-check"></i> Réserver maintenant
                                </a>
                            <?php endif; ?>
                            <a href="offre.php" class="btn-details">
                                <i class="fas fa-search"></i> Voir d'autres offres
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Features Section (identique à votre version originale) -->
        <section class="features-section">
            <div class="main-container">
                <h2>Détails du logement</h2>
                <div class="features-grid">
                    <div class="feature-item">
                        <i class="fas fa-home"></i>
                        <div>
                            <h4>Type de logement</h4>
                            <p><?= htmlspecialchars($offre['type_logement']) ?></p>
                        </div>
                    </div>
                    
                    <div class="feature-item">
                        <i class="fas fa-utensils"></i>
                        <div>
                            <h4>Options de restauration</h4>
                            <p>
                                <?php
                                $options = [];
                                if ($offre['all_inclusive']) $options[] = 'All Inclusive';
                                if ($offre['demi_pension']) $options[] = 'Demi-pension';
                                if ($offre['petit_dejeuner']) $options[] = 'Petit déjeuner';
                                echo implode(', ', $options) ?: 'Aucune option';
                                ?>
                            </p>
                        </div>
                    </div>
                    
                    <div class="feature-item">
                        <i class="fas fa-wifi"></i>
                        <div>
                            <h4>Connectivité</h4>
                            <p><?= $offre['wifi'] ? 'Wi-Fi disponible' : 'Pas de Wi-Fi' ?></p>
                        </div>
                    </div>
                    
                    <div class="feature-item">
                        <i class="fas fa-calendar-alt"></i>
                        <div>
                            <h4>Dates du séjour</h4>
                            <p>Du <?= date('d/m/Y', strtotime($offre['date_depart'])) ?> au <?= date('d/m/Y', strtotime($offre['date_retour'])) ?></p>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>

    <!-- Footer (identique à votre version originale) -->
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
    <script>
        // Animation pour les miniatures
        document.querySelectorAll('.thumbnail').forEach(thumb => {
            thumb.addEventListener('click', function() {
                // Retirer la classe active de toutes les miniatures
                document.querySelectorAll('.thumbnail').forEach(t => {
                    t.style.borderColor = 'transparent';
                });
                // Ajouter la bordure à la miniature cliquée
                this.style.borderColor = '#007bff';
            });
        });
        
        // Animation au chargement
        document.addEventListener('DOMContentLoaded', function() {
            const elements = document.querySelectorAll('.detail-gallery, .detail-info, .feature-item');
            elements.forEach((el, index) => {
                setTimeout(() => {
                    el.style.opacity = 1;
                    el.style.transform = 'translateY(0)';
                }, 100 * index);
            });
        });
    </script>
</body>
</html>