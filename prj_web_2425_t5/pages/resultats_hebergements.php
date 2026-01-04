<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Résultats de recherche - Hébergements</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
        <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../styles/offres.css">
    <style>
        .search-results-container {
            padding: 2rem;
            max-width: 1200px;
            margin: 0 auto;
        }
        
        .results-header {
            margin-bottom: 2rem;
            text-align: center;
        }
        
        .lodging-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 1.5rem;
        }
        
        .lodging-card {
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            transition: transform 0.3s, box-shadow 0.3s;
        }
        
        .lodging-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.15);
        }
        
        .lodging-image {
            height: 200px;
            overflow: hidden;
            position: relative;
        }
        
        .lodging-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.5s;
        }
        
        .lodging-card:hover .lodging-image img {
            transform: scale(1.05);
        }
        
        .lodging-badge {
            position: absolute;
            top: 10px;
            right: 10px;
            background: var(--primary);
            color: white;
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 0.8rem;
        }
        
        .lodging-content {
            padding: 1.5rem;
        }
        
        .lodging-price {
            font-size: 1.5rem;
            font-weight: bold;
            color: var(--primary);
            margin: 1rem 0;
        }
        
        .lodging-actions {
            margin-top: 1.5rem;
        }
        
        .no-results {
            text-align: center;
            padding: 3rem;
            background: var(--light);
            border-radius: 10px;
        }
        
        @media (max-width: 768px) {
            .lodging-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    
    <div class="search-results-container">
        <div class="results-header">
            <h2>Résultats pour les hébergements à <?= htmlspecialchars($destination) ?></h2>
            <p><?= count($results) ?> hébergements trouvés</p>
        </div>
        
        <?php if (!empty($results)): ?>
            <div class="lodging-grid">
                <?php foreach ($results as $logement): ?>
                    <div class="lodging-card">
                        <div class="lodging-image">
                            <img src="<?= htmlspecialchars($logement['image_url'] ?? '../img/default-lodging.jpg') ?>" 
                                 alt="<?= htmlspecialchars($logement['nom'] ?? 'Hébergement') ?>">
                            <div class="lodging-badge">
                                <?= htmlspecialchars($logement['type_logement'] ?? 'Hébergement') ?>
                            </div>
                        </div>
                        
                        <div class="lodging-content">
                            <h3><?= htmlspecialchars($logement['nom'] ?? 'Hébergement sans nom') ?></h3>
                            <p>
                                <i class="fas fa-map-marker-alt"></i> 
                                <?= htmlspecialchars($logement['ville'] ?? 'Ville inconnue') ?>, 
                                <?= htmlspecialchars($logement['pays'] ?? 'Pays inconnu') ?>
                            </p>
                            
                            <div class="lodging-price">
                                <?= number_format($logement['prix_nuit'] ?? 0, 2, ',', ' ') ?> € / nuit
                            </div>
                            
                            <div class="lodging-actions">
                                <a href="details_logement.php?id=<?= $logement['id'] ?>" class="btn btn-primary">
                                    <i class="fas fa-eye"></i> Voir l'hébergement
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="no-results">
                <i class="fas fa-bed fa-3x" style="color: var(--secondary); margin-bottom: 1rem;"></i>
                <h3>Aucun hébergement trouvé pour ces critères</h3>
                <p>Essayez de modifier vos filtres de recherche</p>
                <a href="../index.php" class="btn btn-primary">
                    <i class="fas fa-arrow-left"></i> Retour à l'accueil
                </a>
            </div>
        <?php endif; ?>
    </div>
    
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
