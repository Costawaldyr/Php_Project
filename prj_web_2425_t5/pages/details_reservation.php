<?php
require_once('../data_base/connection.php');
session_start();

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header('Location: ../connexion.php');
    exit();
}

// Initialisation des variables
$reservation = null;
$message = '';
$message_type = ''; // success, error, warning

try {
    // Vérifier que l'ID de réservation est présent dans l'URL
    if (!isset($_GET['id'])) {
        throw new Exception("Aucune réservation spécifiée.");
    }

    $reservation_id = $_GET['id'];

    // Récupérer les détails complets de la réservation
    $stmt = $dbh->prepare('
        SELECT 
            r.*, 
            o.titre AS offre_titre, o.description AS offre_description, o.images AS offre_images,
            o.date_depart, o.date_retour, o.duree_sejour, o.type_transport,
            l.nom AS logement_nom, l.description AS logement_description, l.equipements,
            t1.compagnie AS transport_aller_compagnie, t1.numero AS transport_aller_numero,
            t1.depart AS transport_aller_date, t1.heure_depart AS transport_aller_heure,
            t1.aeroport_gare AS transport_aller_lieu,
            t2.compagnie AS transport_retour_compagnie, t2.numero AS transport_retour_numero,
            t2.depart AS transport_retour_date, t2.heure_depart AS transport_retour_heure,
            t2.aeroport_gare AS transport_retour_lieu,
            d.ville, d.pays, d.images AS destination_images,
            u.nom AS user_nom, u.prenom AS user_prenom, u.email, u.telephone, u.adresse
        FROM reservations r
        JOIN offres o ON r.offre_id = o.id
        JOIN logements l ON r.logement_id = l.id
        JOIN destinations d ON o.destination_id = d.id
        LEFT JOIN transports t1 ON r.transport_aller_id = t1.id
        LEFT JOIN transports t2 ON r.transport_retour_id = t2.id
        JOIN users u ON r.user_id = u.id
        WHERE r.id = ? AND r.user_id = ?
    ');
    $stmt->execute([$reservation_id, $_SESSION['user_id']]);
    $reservation = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$reservation) {
        throw new Exception("Réservation introuvable ou vous n'avez pas accès à cette réservation.");
    }

    // Traitement de l'annulation de réservation
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['annuler_reservation'])) {
        if ($reservation['statut'] !== 'en attente') {
            throw new Exception("Seules les réservations en attente peuvent être annulées.");
        }

        $stmt = $dbh->prepare("UPDATE reservations SET statut = 'annulé' WHERE id = ?");
        $stmt->execute([$reservation_id]);
        
        // Rafraîchir les données de la réservation
        $stmt = $dbh->prepare('
            SELECT 
                r.*, 
                o.titre AS offre_titre, o.description AS offre_description, o.images AS offre_images,
                o.date_depart, o.date_retour, o.duree_sejour, o.type_transport,
                l.nom AS logement_nom, l.description AS logement_description, l.equipements,
                t1.compagnie AS transport_aller_compagnie, t1.numero AS transport_aller_numero,
                t1.date_depart AS transport_aller_date, t1.heure_depart AS transport_aller_heure,
                t1.aeroport_gare AS transport_aller_lieu,
                t2.compagnie AS transport_retour_compagnie, t2.numero AS transport_retour_numero,
                t2.date_depart AS transport_retour_date, t2.heure_depart AS transport_retour_heure,
                t2.aeroport_gare AS transport_retour_lieu,
                d.ville, d.pays, d.images AS destination_images,
                u.nom AS user_nom, u.prenom AS user_prenom, u.email, u.telephone, u.adresse
            FROM reservations r
            JOIN offres o ON r.offre_id = o.id
            JOIN logements l ON r.logement_id = l.id
            JOIN destinations d ON o.destination_id = d.id
            LEFT JOIN transports t1 ON r.transport_aller_id = t1.id
            LEFT JOIN transports t2 ON r.transport_retour_id = t2.id
            JOIN users u ON r.user_id = u.id
            WHERE r.id = ? AND r.user_id = ?
        ');
        $stmt->execute([$reservation_id, $_SESSION['user_id']]);
        $reservation = $stmt->fetch(PDO::FETCH_ASSOC);
        
        $message = "La réservation #$reservation_id a été annulée avec succès.";
        $message_type = 'success';
    }

} catch (PDOException $e) {
    $message = "Erreur de base de données : " . $e->getMessage();
    $message_type = 'error';
} catch (Exception $e) {
    $message = $e->getMessage();
    $message_type = 'error';
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Détails de la réservation #<?= isset($reservation['id']) ? $reservation['id'] : '' ?></title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../styles/offres.css">
    <style>
        .detail-container {
            max-width: 1200px;
            margin: 30px auto;
            padding: 20px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
        }
        .detail-header {
            border-bottom: 2px solid #007bff;
            padding-bottom: 15px;
            margin-bottom: 20px;
        }
        .detail-section {
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 1px solid #eee;
        }
        .detail-section:last-child {
            border-bottom: none;
        }
        .reservation-status {
            display: inline-block;
            padding: 5px 15px;
            border-radius: 20px;
            font-weight: bold;
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
        .gallery {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-top: 15px;
        }
        .gallery img {
            width: 150px;
            height: 100px;
            object-fit: cover;
            border-radius: 5px;
            cursor: pointer;
            transition: transform 0.3s;
        }
        .gallery img:hover {
            transform: scale(1.05);
        }
        .equipements-list {
            column-count: 2;
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
        .info-card {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 15px;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header class="header-offres">
        <h1>Détails de la <span>réservation</span></h1>
    </header>
    <a href="mes_reservations.php" class="btn-back"><i class="fas fa-arrow-left"></i> Retour à mes réservations</a>
    
    <!-- Main Content -->
    <div class="main-container">
        <?php if ($message): ?>
            <div class="message message-<?= $message_type ?>"><?= htmlspecialchars($message) ?></div>
        <?php endif; ?>

        <?php if ($reservation): ?>
            <div class="detail-container">
                <div class="detail-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h2><?= htmlspecialchars($reservation['offre_titre']) ?></h2>
                            <p class="text-muted mb-0">
                                <i class="fas fa-map-marker-alt"></i> 
                                <?= htmlspecialchars($reservation['ville']) ?>, <?= htmlspecialchars($reservation['pays']) ?>
                            </p>
                        </div>
                        <span class="reservation-status status-<?= str_replace(' ', '-', $reservation['statut']) ?>">
                            <?= ucfirst($reservation['statut']) ?>
                        </span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mt-2">
                        <p class="mb-0">
                            <i class="fas fa-calendar-alt"></i> 
                            Du <?= date('d/m/Y', strtotime($reservation['date_depart'])) ?> 
                            au <?= date('d/m/Y', strtotime($reservation['date_retour'])) ?>
                            (<?= $reservation['duree_sejour'] ?> jours)
                        </p>
                        <h4 class="text-primary mb-0">
                            <?= number_format($reservation['prix_total'], 2, ',', ' ') ?> €
                            <small class="text-muted">(<?= number_format($reservation['prix_unitaire'], 2, ',', ' ') ?> €/pers)</small>
                        </h4>
                    </div>
                </div>

                <!-- Section Informations générales -->
                <div class="detail-section">
                    <h3><i class="fas fa-info-circle"></i> Informations générales</h3>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="info-card">
                                <h5>Référence</h5>
                                <p>Réservation #<?= $reservation['id'] ?></p>
                                <p>Créée le <?= date('d/m/Y H:i', strtotime($reservation['date_reservation'])) ?></p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-card">
                                <h5>Participants</h5>
                                <p><?= $reservation['nbr_personnes'] ?> personne(s)</p>
                                <p>Prix total: <?= number_format($reservation['prix_total'], 2, ',', ' ') ?> €</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Section Description de l'offre -->
                <div class="detail-section">
                    <h3><i class="fas fa-suitcase"></i> Description du voyage</h3>
                    <p><?= nl2br(htmlspecialchars($reservation['offre_description'])) ?></p>
                    
                    <?php if ($reservation['offre_images']): ?>
                        <h5 class="mt-4">Galerie photos</h5>
                        <div class="gallery">
                            <?php 
                            $images = explode(',', $reservation['offre_images']);
                            foreach ($images as $image): 
                            ?>
                                <img src="../uploads/offres/<?= htmlspecialchars(trim($image)) ?>" alt="Photo du voyage" class="img-thumbnail">
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Section Logement -->
                <div class="detail-section">
                    <h3><i class="fas fa-home"></i> Logement</h3>
                    <div class="row">
                        <div class="col-md-6">
                            <h5><?= htmlspecialchars($reservation['logement_nom']) ?></h5>
                            <p><?= nl2br(htmlspecialchars($reservation['logement_description'])) ?></p>
                            
                            <?php if ($reservation['equipements']): ?>
                                <h5 class="mt-3">Équipements</h5>
                                <ul class="equipements-list">
                                    <?php 
                                    $equipements = explode(',', $reservation['equipements']);
                                    foreach ($equipements as $equipement): 
                                    ?>
                                        <li><?= htmlspecialchars(trim($equipement)) ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            <?php endif; ?>
                        </div>
                        <div class="col-md-6">
                            <?php if ($reservation['destination_images']): ?>
                                <div class="gallery">
                                    <?php 
                                    $images = explode(',', $reservation['destination_images']);
                                    foreach ($images as $image): 
                                    ?>
                                        <img src="../uploads/destinations/<?= htmlspecialchars(trim($image)) ?>" alt="Photo du logement" class="img-thumbnail">
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Section Transport -->
                <div class="detail-section">
                    <h3><i class="fas fa-<?= 
                        $reservation['type_transport'] === 'avion' ? 'plane' : 
                        ($reservation['type_transport'] === 'train' ? 'train' :
                        ($reservation['type_transport'] === 'bus' ? 'bus' :
                        ($reservation['type_transport'] === 'voiture' ? 'car' : 'users')))
                    ?>"></i> Transport</h3>
                    
                    <div class="row">
                        <!-- Transport Aller -->
                        <div class="col-md-6">
                            <div class="info-card">
                                <h5>Aller</h5>
                                <?php if ($reservation['transport_aller_compagnie']): ?>
                                    <p><strong>Compagnie:</strong> <?= htmlspecialchars($reservation['transport_aller_compagnie']) ?></p>
                                    <p><strong>Numéro:</strong> <?= htmlspecialchars($reservation['transport_aller_numero']) ?></p>
                                    <p><strong>Date:</strong> <?= date('d/m/Y', strtotime($reservation['transport_aller_date'])) ?></p>
                                    <p><strong>Heure:</strong> <?= substr($reservation['transport_aller_heure'], 0, 5) ?></p>
                                    <p><strong>Lieu de départ:</strong> <?= htmlspecialchars($reservation['transport_aller_lieu']) ?></p>
                                <?php else: ?>
                                    <p>Transport aller non spécifié</p>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <!-- Transport Retour -->
                        <div class="col-md-6">
                            <div class="info-card">
                                <h5>Retour</h5>
                                <?php if ($reservation['transport_retour_compagnie']): ?>
                                    <p><strong>Compagnie:</strong> <?= htmlspecialchars($reservation['transport_retour_compagnie']) ?></p>
                                    <p><strong>Numéro:</strong> <?= htmlspecialchars($reservation['transport_retour_numero']) ?></p>
                                    <p><strong>Date:</strong> <?= date('d/m/Y', strtotime($reservation['transport_retour_date'])) ?></p>
                                    <p><strong>Heure:</strong> <?= substr($reservation['transport_retour_heure'], 0, 5) ?></p>
                                    <p><strong>Lieu de départ:</strong> <?= htmlspecialchars($reservation['transport_retour_lieu']) ?></p>
                                <?php else: ?>
                                    <p>Transport retour non spécifié</p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Section Paiement -->
                <div class="detail-section">
                    <h3><i class="fas fa-credit-card"></i> Paiement</h3>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="info-card">
                                <h5>Informations de paiement</h5>
                                <p><strong>Méthode:</strong> <?= ucfirst($reservation['methode_paiement']) ?></p>
                                <?php if ($reservation['date_paiement']): ?>
                                    <p><strong>Date de paiement:</strong> <?= date('d/m/Y H:i', strtotime($reservation['date_paiement'])) ?></p>
                                <?php else: ?>
                                    <p><strong>Statut:</strong> Non payé</p>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-card">
                                <h5>Détails du prix</h5>
                                <p><strong>Prix unitaire:</strong> <?= number_format($reservation['prix_unitaire'], 2, ',', ' ') ?> €</p>
                                <p><strong>Nombre de personnes:</strong> <?= $reservation['nbr_personnes'] ?></p>
                                <p><strong>Total:</strong> <?= number_format($reservation['prix_total'], 2, ',', ' ') ?> €</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Section Voyageur -->
                <div class="detail-section">
                    <h3><i class="fas fa-user"></i> Voyageur</h3>
                    <div class="info-card">
                        <div class="row">
                            <div class="col-md-6">
                                <p><strong>Nom:</strong> <?= htmlspecialchars($reservation['user_nom']) ?></p>
                                <p><strong>Prénom:</strong> <?= htmlspecialchars($reservation['user_prenom']) ?></p>
                                <p><strong>Email:</strong> <?= htmlspecialchars($reservation['email']) ?></p>
                            </div>
                            <div class="col-md-6">
                                <p><strong>Téléphone:</strong> <?= htmlspecialchars($reservation['telephone'] ?? 'Non renseigné') ?></p>
                                <p><strong>Adresse:</strong> <?= htmlspecialchars($reservation['adresse'] ?? 'Non renseignée') ?></p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Actions -->
                <div class="text-center mt-4">
                    <?php if ($reservation['statut'] === 'en attente'): ?>
                        <form method="POST" action="" class="d-inline-block">
                            <button type="submit" name="annuler_reservation" class="btn btn-danger mr-2">
                                <i class="fas fa-times"></i> Annuler la réservation
                            </button>
                        </form>
                    <?php endif; ?>
                    
                    <a href="mes_reservations.php" class="btn btn-outline-primary">
                        <i class="fas fa-list"></i> Retour à la liste
                    </a>
                </div>
            </div>
        <?php else: ?>
            <div class="alert alert-danger text-center">
                <h4><i class="fas fa-exclamation-triangle"></i> Réservation introuvable</h4>
                <p>La réservation que vous cherchez n'existe pas ou vous n'y avez pas accès.</p>
                <a href="mes_reservations.php" class="btn btn-primary">Retour à mes réservations</a>
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
            const container = document.querySelector('.detail-container');
            if (container) {
                setTimeout(() => {
                    container.style.opacity = 1;
                    container.style.transform = 'translateY(0)';
                }, 100);
            }
        });
    </script>
</body>
</html>