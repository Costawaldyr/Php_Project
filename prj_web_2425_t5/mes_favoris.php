<?php
require_once('data_base/connection.php');
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: auth/login.php');
    exit();
}

try {
    $stmt = $dbh->prepare('
        SELECT o.*, d.ville, d.pays 
        FROM favoris f
        JOIN offres o ON f.offre_id = o.id
        LEFT JOIN destinations d ON o.destination_id = d.id
        WHERE f.user_id = ?
        ORDER BY f.date_ajout DESC
    ');
    $stmt->execute([$_SESSION['user_id']]);
    $favoris = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die('Erreur : ' . $e->getMessage());
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
    <link rel="stylesheet" href="styles/offres.css">
</head>
<body>
    <header class="header-offres">
        <h1>Mes <span>Favoris</span></h1>
    </header>

    <main class="main-container">
        <a href="index.php" class="btn-back"><i class="fas fa-arrow-left"></i> Retour à l'accueil</a>
        
        <section class="offres-section">
        <?php if (empty($favoris)): ?>
                    <div class="alert alert-info">
                        Vous n'avez aucun favoris pour le moment.
                        <a href="../pages/offre.php" class="alert-link">Découvrez nos offres</a>
                    </div>
                <?php else: ?>
                    <div class="row">
                        <?php foreach ($favoris as $offre): ?>
                            <div class="col-md-4 mb-4">
                                <div class="card h-100">
                                    <?php if (!empty($offre['images'])): ?>
                                        <img src="<?= htmlspecialchars($offre['images']) ?>" class="card-img-top" alt="<?= htmlspecialchars($offre['titre']) ?>" style="height: 180px; object-fit: cover;">
                                    <?php else: ?>
                                        <img src="../img/default-offer.jpg" class="card-img-top" alt="Offre par défaut" style="height: 180px; object-fit: cover;">
                                    <?php endif; ?>
                                    
                                    <div class="card-body">
                                        <h5 class="card-title"><?= htmlspecialchars($offre['titre']) ?></h5>
                                        <p class="card-text">
                                            <i class="fas fa-map-marker-alt"></i> 
                                            <?= htmlspecialchars($offre['ville'] . ', ' . $offre['pays']) ?>
                                        </p>
                                        <p class="card-text">
                                            <i class="fas fa-calendar-alt"></i> 
                                            <?= date('d/m/Y', strtotime($offre['date_depart'])) ?>
                                        </p>
                                        <p class="card-text font-weight-bold">
                                            <?= number_format($offre['prix'], 2, ',', ' ') ?> €
                                        </p>
                                    </div>
                                    <div class="card-footer bg-white">
                                        <div class="d-flex justify-content-between">
                                            <a href="pages/details_offre.php?id=<?= $offre['id'] ?>" class="btn btn-sm btn-outline-primary">
                                                Voir l'offre
                                            </a>
                                            <form method="POST" action="supprimer_favori.php" class="d-inline">
                                                <input type="hidden" name="offre_id" value="<?= $offre['id'] ?>">
                                                <button type="submit" class="btn btn-sm btn-outline-danger">
                                                    <i class="fas fa-trash"></i> Retirer
                                                </button>
                                            </form>
                                        </div>
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