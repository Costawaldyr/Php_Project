<?php
require_once('../data_base/connection.php');
session_start();

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header('Location: ../connexion.php');
    exit();
}

// Initialisation des variables
$reservations = [];
$message = '';
$message_type = ''; // success, error, warning

try {
    // Récupérer les réservations de l'utilisateur
    $stmt = $dbh->prepare('
        SELECT r.*, o.titre AS offre_titre, o.date_depart, o.date_retour, 
               l.nom AS logement_nom, t1.compagnie AS transport_aller_compagnie,
               t2.compagnie AS transport_retour_compagnie, d.ville, d.pays
        FROM reservations r
        JOIN offres o ON r.offre_id = o.id
        JOIN logements l ON r.logement_id = l.id
        JOIN destinations d ON o.destination_id = d.id
        LEFT JOIN transports t1 ON r.transport_aller_id = t1.id
        LEFT JOIN transports t2 ON r.transport_retour_id = t2.id
        WHERE r.user_id = ?
        ORDER BY r.date_reservation DESC
    ');
    $stmt->execute([$_SESSION['user_id']]);
    $reservations = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    $message = "Erreur lors de la récupération des réservations: " . $e->getMessage();
    $message_type = 'error';
}

// Traitement de l'annulation de réservation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['annuler_reservation'])) {
    try {
        $reservation_id = $_POST['reservation_id'];
        
        // Vérifier que la réservation appartient bien à l'utilisateur
        $stmt = $dbh->prepare('SELECT id FROM reservations WHERE id = ? AND user_id = ?');
        $stmt->execute([$reservation_id, $_SESSION['user_id']]);
        $reservation = $stmt->fetch();

        if (!$reservation) {
            throw new Exception("Réservation introuvable ou vous n'avez pas les droits pour la modifier.");
        }
        
        // Mettre à jour le statut
        $stmt = $dbh->prepare("UPDATE reservations SET statut = 'annulé' WHERE id = ?");
        $stmt->execute([$reservation_id]);
        
        $message = "La réservation #$reservation_id a été annulée avec succès.";
        $message_type = 'success';
        
        // Rafraîchir la liste des réservations
        $stmt = $dbh->prepare('
            SELECT r.*, o.titre AS offre_titre, o.date_depart, o.date_retour, 
                   l.nom AS logement_nom, t1.compagnie AS transport_aller_compagnie,
                   t2.compagnie AS transport_retour_compagnie, d.ville, d.pays
            FROM reservations r
            JOIN offres o ON r.offre_id = o.id
            JOIN logements l ON r.logement_id = l.id
            JOIN destinations d ON o.destination_id = d.id
            LEFT JOIN transports t1 ON r.transport_aller_id = t1.id
            LEFT JOIN transports t2 ON r.transport_retour_id = t2.id
            WHERE r.user_id = ?
            ORDER BY r.date_reservation DESC
        ');
        $stmt->execute([$_SESSION['user_id']]);
        $reservations = $stmt->fetchAll(PDO::FETCH_ASSOC);

    } catch (Exception $e) {
        $message = $e->getMessage();
        $message_type = 'error';
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Mes Réservations</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../styles/offres.css">
    <style>
        .reservation-container {
            max-width: 1200px;
            margin: 30px auto;
            padding: 20px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
        }
        .reservation-header {
            border-bottom: 2px solid #007bff;
            padding-bottom: 15px;
            margin-bottom: 20px;
        }
        .reservation-card {
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
            background: #f9f9f9;
        }
        .reservation-status {
            display: inline-block;
            padding: 5px 10px;
            border-radius: 20px;
            font-weight: bold;
            font-size: 0.9em;
        }
        .status-en-attente {
            background-color: #FFC107;
            color: #000;
        }
        .status-confirme {
            background-color: #28A745;
            color: #FFF;
        }
        .status-annule {
            background-color: #DC3545;
            color: #FFF;
        }
        .message {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 5px;
        }
        .message-success {
            background-color: #D4EDDA;
            color: #155724;
            border: 1px solid #C3E6CB;
        }
        .message-error {
            background-color: #F8D7DA;
            color: #721C24;
            border: 1px solid #F5C6CB;
        }
        .no-reservations {
            text-align: center;
            padding: 40px;
            color: #6C757D;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header class="header-offres">
        <h1>Mes <span>Réservations</span></h1>
    </header>
    <a href="../index.php" class="btn-back"><i class="fas fa-arrow-left"></i> Retour à l'accueil</a>
    
    <!-- Main Content -->
    <div class="main-container">
        <div class="reservation-container">
            <?php if ($message): ?>
                <div class="message message-<?= $message_type ?>"><?= htmlspecialchars($message) ?></div>
            <?php endif; ?>

            <div class="reservation-header">
                <h2>Historique de vos réservations</h2>
                <p>Retrouvez ici toutes vos réservations passées et en cours</p>
            </div>

            <?php if (empty($reservations)): ?>
                <div class="no-reservations">
                    <i class="fas fa-calendar-times fa-3x mb-3"></i>
                    <h3>Vous n'avez aucune réservation</h3>
                    <p>Commencez par explorer nos offres et réservez votre prochain voyage !</p>
                    <a href="offres.php" class="btn btn-primary">Voir les offres</a>
                </div>
            <?php else: ?>
                <?php foreach ($reservations as $reservation): ?>
                    <div class="reservation-card">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div>
                                <h3><?= htmlspecialchars($reservation['offre_titre']) ?></h3>
                                <p class="text-muted">
                                    <i class="fas fa-map-marker-alt"></i> 
                                    <?= htmlspecialchars($reservation['ville']) ?>, <?= htmlspecialchars($reservation['pays']) ?>
                                </p>
                            </div>
                            <span class="reservation-status status-<?= str_replace(' ', '-', $reservation['statut']) ?>">
                                <?= ucfirst($reservation['statut']) ?>
                            </span>
                        </div>

                        <div class="row">
                            <div class="col-md-4">
                                <h5>Dates</h5>
                                <p><i class="fas fa-calendar-alt"></i> 
                                    Du <?= date('d/m/Y', strtotime($reservation['date_depart'])) ?> 
                                    au <?= date('d/m/Y', strtotime($reservation['date_retour'])) ?>
                                </p>
                                <p><i class="fas fa-clock"></i> 
                                    <?= (strtotime($reservation['date_retour']) - strtotime($reservation['date_depart'])) / (60 * 60 * 24) ?> jours
                                </p>
                            </div>
                            <div class="col-md-4">
                                <h5>Détails</h5>
                                <p><i class="fas fa-home"></i> <?= htmlspecialchars($reservation['logement_nom']) ?></p>
                                <p><i class="fas fa-users"></i> <?= $reservation['nbr_personnes'] ?> personne(s)</p>
                            </div>
                            <div class="col-md-4">
                                <h5>Transport</h5>
                                <?php if ($reservation['transport_aller_compagnie']): ?>
                                    <p><i class="fas fa-arrow-right"></i> <?= htmlspecialchars($reservation['transport_aller_compagnie']) ?></p>
                                <?php endif; ?>
                                <?php if ($reservation['transport_retour_compagnie']): ?>
                                    <p><i class="fas fa-arrow-left"></i> <?= htmlspecialchars($reservation['transport_retour_compagnie']) ?></p>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="row mt-3">
                            <div class="col-md-6">
                                <h5>Paiement</h5>
                                <p><i class="fas fa-money-bill-wave"></i> 
                                    <?= ucfirst($reservation['methode_paiement']) ?>
                                    <?php if ($reservation['date_paiement']): ?>
                                        - Payé le <?= date('d/m/Y', strtotime($reservation['date_paiement'])) ?>
                                    <?php endif; ?>
                                </p>
                            </div>
                            <div class="col-md-6 text-right">
                                <h4 class="text-primary">
                                    <?= number_format($reservation['prix_total'], 2, ',', ' ') ?> €
                                </h4>
                                <p class="text-muted"><?= number_format($reservation['prix_unitaire'], 2, ',', ' ') ?> € par personne</p>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between align-items-center mt-3 pt-3 border-top">
                            <small class="text-muted">
                                <i class="fas fa-calendar-plus"></i> Réservé le <?= date('d/m/Y H:i', strtotime($reservation['date_reservation'])) ?>
                            </small>
                            
                            <?php if ($reservation['statut'] === 'en attente'): ?>
                                <form method="POST" action="">
                                    <input type="hidden" name="reservation_id" value="<?= $reservation['id'] ?>">
                                    <button type="submit" name="annuler_reservation" class="btn btn-sm btn-outline-danger">
                                        <i class="fas fa-times"></i> Annuler
                                    </button>
                                </form>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
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
                    <li><a href="offres.php">Offres spéciales</a></li>
                    <li><a href="blog.php">Blog voyage</a></li>
                    <li><a href="forum.php">Forum</a></li>
                    <li><a href="#">Destinations tendances</a></li>
                </ul>
            </div>
            
            <div class="footer-section">
                <h3>Aide</h3>
                <ul>
                    <li><a href="contact.php">Contactez-nous</a></li>
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
                        <a href="#"><img src="../img/app-store.png" alt="App Store"></a>
                        <a href="#"><img src="../img/google-play.png" alt="Google Play"></a>
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
        // Animation au chargement
        document.addEventListener('DOMContentLoaded', function() {
            const cards = document.querySelectorAll('.reservation-card');
            cards.forEach((card, index) => {
                setTimeout(() => {
                    card.style.opacity = 1;
                    card.style.transform = 'translateY(0)';
                }, 100 * index);
            });
        });
    </script>
</body>
</html>