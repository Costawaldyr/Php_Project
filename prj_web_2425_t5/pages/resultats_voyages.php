<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Résultats de recherche - Voyages</title>
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
        
        .results-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 1.5rem;
        }
        
        .result-card {
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            transition: transform 0.3s, box-shadow 0.3s;
        }
        
        .result-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.15);
        }
        
        .card-image {
            height: 200px;
            overflow: hidden;
        }
        
        .card-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.5s;
        }
        
        .result-card:hover .card-image img {
            transform: scale(1.05);
        }
        
        .card-content {
            padding: 1.5rem;
        }
        
        .card-price {
            font-size: 1.5rem;
            font-weight: bold;
            color: var(--primary);
            margin: 1rem 0;
        }
        
        .card-actions {
            display: flex;
            justify-content: space-between;
            margin-top: 1.5rem;
        }
        
        .no-results {
            text-align: center;
            padding: 3rem;
            background: var(--light);
            border-radius: 10px;
        }
        
        @media (max-width: 768px) {
            .results-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    
    <div class="search-results-container">
        <div class="results-header">
            <h2>Résultats pour les voyages à <?= htmlspecialchars($destination) ?></h2>
            <p><?= count($results) ?> offres trouvées</p>
        </div>
        
        <?php if (!empty($results)): ?>
            <div class="results-grid">
                <?php foreach ($results as $offre): ?>
                    <div class="result-card">
                        <div class="card-image">
                            <img src="<?= htmlspecialchars($offre['images'] ?? '../img/default-offer.jpg') ?>" 
                                 alt="<?= htmlspecialchars($offre['titre']) ?>">
                        </div>
                        
                        <div class="card-content">
                            <h3><?= htmlspecialchars($offre['titre']) ?></h3>
                            <p>
                                <i class="fas fa-map-marker-alt"></i> 
                                <?= htmlspecialchars($offre['ville']) ?>, <?= htmlspecialchars($offre['pays']) ?>
                            </p>
                            <p>
                                <i class="fas fa-calendar-day"></i> 
                                <?= date('d/m/Y', strtotime($offre['date_depart'])) ?> - <?= date('d/m/Y', strtotime($offre['date_retour'])) ?>
                            </p>
                            
                            <div class="card-price">
                                <?= number_format($offre['prix'], 2, ',', ' ') ?> €
                            </div>
                            
                            <div class="card-actions">
                                <a href="details_offre.php?id=<?= $offre['id'] ?>" class="btn btn-primary no-ajax">
                                    <i class="fas fa-eye"></i> Détails
                                </a>
                                <a href="reservation.php?id=<?= $offre['id'] ?>" class="btn btn-secondary no-ajax">
                                    <i class="fas fa-shopping-cart"></i> Réserver
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="no-results">
                <i class="fas fa-compass fa-3x" style="color: var(--secondary); margin-bottom: 1rem;"></i>
                <h3>Aucun voyage trouvé pour ces critères</h3>
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
    
    <script src="../styles/script.js"></script>
</body>
</html>