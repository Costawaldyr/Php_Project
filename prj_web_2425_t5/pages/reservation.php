<?php
require_once('../data_base/connection.php');
session_start();

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header('Location: ../auth/login.php');
    exit();
}

// Initialisation des variables
$confirmation = '';
$error = '';
$offre = null;
$user = null;

try {
    // Récupérer les infos de l'utilisateur
    $stmt = $dbh->prepare('SELECT * FROM users WHERE id = ?');
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        throw new Exception("Utilisateur non trouvé.");
    }

    // Récupérer les détails de l'offre
    if (isset($_GET['id'])) {
        $stmt = $dbh->prepare('
            SELECT o.*, l.*, d.ville, d.pays, 
                   t1.compagnie AS compagnie_aller, t1.numero AS numero_aller, 
                   t2.compagnie AS compagnie_retour, t2.numero AS numero_retour
            FROM offres o
            JOIN logements l ON o.logement_id = l.id
            JOIN destinations d ON o.destination_id = d.id
            LEFT JOIN transports t1 ON o.transport_aller_id = t1.id
            LEFT JOIN transports t2 ON o.transport_retour_id = t2.id
            WHERE o.id = ?
        ');
        $stmt->execute([$_GET['id']]);
        $offre = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$offre) {
            throw new Exception("Offre non trouvée.");
        }
    }

    // Traitement du formulaire de réservation
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reserver'])) {
        $nbr_personnes = (int)$_POST['nbr_personnes'];
        $methode_paiement = $_POST['methode_paiement'];
        $participants = $_POST['participants'] ?? [];

        // Validation
        if ($nbr_personnes < 1 || $nbr_personnes > $offre['capacite_max']) {
            throw new Exception("Nombre de personnes invalide. Maximum: " . $offre['capacite_max']);
        }

        // Vérifier les participants supplémentaires
        $participantsSupplementaires = $nbr_personnes - 1;
        if (count($participants) !== $participantsSupplementaires && $participantsSupplementaires > 0) {
            throw new Exception("Veuillez renseigner les informations de tous les participants supplémentaires.");
        }

        // Calcul du prix avec réduction
        $prix_unitaire = $offre['prix'];
        if ($nbr_personnes >= 3 && $nbr_personnes <= 4) {
            $prix_unitaire = $offre['prix'] * 0.7; // 30% de réduction
        } elseif ($nbr_personnes >= 5) {
            $prix_unitaire = $offre['prix'] * 0.6; // 40% de réduction
        }

        // Démarrer une transaction
        $dbh->beginTransaction();

        try {
            // Insertion de la réservation
            $stmt = $dbh->prepare('
                INSERT INTO reservations (
                    user_id, offre_id, logement_id, transport_aller_id, transport_retour_id,
                    nbr_personnes, prix_unitaire, statut, methode_paiement, date_depart, date_retour
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ');

            $stmt->execute([
                $_SESSION['user_id'],
                $offre['id'],
                $offre['logement_id'],
                $offre['transport_aller_id'],
                $offre['transport_retour_id'],
                $nbr_personnes,
                $prix_unitaire,
                'en attente',
                $methode_paiement,
                $offre['date_depart'],
                $offre['date_retour']
            ]);

            $reservation_id = $dbh->lastInsertId();

            // Ajout du participant principal (l'utilisateur connecté)
            $stmt_participant = $dbh->prepare('
                INSERT INTO participants (reservation_id, nom, prenom, date_naissance)
                VALUES (?, ?, ?, ?)
            ');
            $stmt_participant->execute([
                $reservation_id,
                $user['nom'],
                $user['prenom'],
                $user['date_naissance']
            ]);

            // Ajout des participants supplémentaires
            foreach ($participants as $participant) {
                $stmt_participant->execute([
                    $reservation_id,
                    htmlspecialchars($participant['nom']),
                    htmlspecialchars($participant['prenom']),
                    $participant['date_naissance']
                ]);
            }

            // Mise à jour de la disponibilité
            $stmt_update = $dbh->prepare('UPDATE offres SET disponibilite = disponibilite - ? WHERE id = ?');
            $stmt_update->execute([$nbr_personnes, $offre['id']]);

            // Valider la transaction
            $dbh->commit();

            $confirmation = 'success';
        } catch (Exception $e) {
            // Annuler la transaction en cas d'erreur
            $dbh->rollBack();
            throw $e;
        }
    }
} catch (PDOException $e) {
    $error = "Erreur de base de données : " . $e->getMessage();
    $confirmation = 'error';
} catch (Exception $e) {
    $error = $e->getMessage();
    $confirmation = 'error';
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Réservation - <?= isset($offre['titre']) ? htmlspecialchars($offre['titre']) : 'Offre' ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../styles/offres.css">
    <style>
        .reservation-container {
            max-width: 800px;
            margin: 30px auto;
            padding: 20px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
        }
        .reservation-header {
            border-bottom: 2px solid var(--primary);
            padding-bottom: 15px;
            margin-bottom: 20px;
        }
        .reservation-summary {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        .confirmation-box {
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            text-align: center;
        }
        .confirmation-success {
            background: #d4edda;
            color: #155724;
        }
        .confirmation-error {
            background: #f8d7da;
            color: #721c24;
        }
        .participant-group {
            background: #f8f9fa;
            margin-bottom: 15px;
        }
        .price-highlight {
            font-size: 1.5rem;
            color: var(--primary);
            font-weight: bold;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header class="header-offres">
        <h1>Réservation <span>de voyage</span></h1>
    </header>
    <div class="container">
        <a href="../index.php" class="btn-back"><i class="fas fa-arrow-left"></i> Retour à l'accueil</a>
    </div>
    
    <!-- Main Content -->
    <div class="main-container">
        <div class="reservation-container">
            <?php if ($confirmation === 'success'): ?>
                <div class="confirmation-box confirmation-success">
                    <h3><i class="fas fa-check-circle"></i> Réservation confirmée !</h3>
                    <p>Votre réservation a bien été enregistrée et est en attente de confirmation.</p>
                    <p>Un email de confirmation vous a été envoyé à <?= htmlspecialchars($user['email']) ?>.</p>
                    <a href="mes_reservations.php" class="btn btn-primary">Voir mes réservations</a>
                </div>
            <?php elseif ($confirmation === 'error'): ?>
                <div class="confirmation-box confirmation-error">
                    <h3><i class="fas fa-exclamation-circle"></i> Erreur lors de la réservation</h3>
                    <p><?= htmlspecialchars($error) ?></p>
                    <?php if (isset($offre['id'])): ?>
                        <a href="offre.php?id=<?= $offre['id'] ?>" class="btn btn-primary">Réessayer</a>
                    <?php else: ?>
                        <a href="offre.php" class="btn btn-primary">Retour aux offres</a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <?php if (isset($offre) && !$confirmation): ?>
                <div class="reservation-header">
                    <h2><?= htmlspecialchars($offre['titre']) ?></h2>
                    <?php if (isset($offre['ville']) && isset($offre['pays'])): ?>
                        <p class="text-muted"><?= htmlspecialchars($offre['ville']) ?>, <?= htmlspecialchars($offre['pays']) ?></p>
                    <?php endif; ?>
                </div>

                <div class="reservation-summary">
                    <div class="row">
                        <div class="col-md-6">
                            <h4>Dates du séjour</h4>
                            <p><i class="fas fa-calendar-alt"></i> Du <?= date('d/m/Y', strtotime($offre['date_depart'])) ?> au <?= date('d/m/Y', strtotime($offre['date_retour'])) ?></p>
                            <p><i class="fas fa-clock"></i> <?= $offre['duree_sejour'] ?> jours</p>
                        </div>
                        <div class="col-md-6">
                            <h4>Transport</h4>
                            <p>
                                <i class="fas fa-<?= 
                                    $offre['type_transport'] === 'avion' ? 'plane' : 
                                    ($offre['type_transport'] === 'train' ? 'train' :
                                    ($offre['type_transport'] === 'bus' ? 'bus' :
                                    ($offre['type_transport'] === 'voiture' ? 'car' : 'users')))
                                ?>"></i> 
                                <?= ucfirst($offre['type_transport']) ?>
                            </p>
                            <?php if (!empty($offre['compagnie_aller'])): ?>
                                <p><i class="fas fa-arrow-right"></i> <?= htmlspecialchars($offre['compagnie_aller']) ?> <?= htmlspecialchars($offre['numero_aller']) ?></p>
                            <?php endif; ?>
                            <?php if (!empty($offre['compagnie_retour'])): ?>
                                <p><i class="fas fa-arrow-left"></i> <?= htmlspecialchars($offre['compagnie_retour']) ?> <?= htmlspecialchars($offre['numero_retour']) ?></p>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="row mt-3">
                        <div class="col-md-6">
                            <h4>Logement</h4>
                            <p><i class="fas fa-home"></i> <?= isset($offre['type_logement']) ? htmlspecialchars($offre['type_logement']) : 'Non spécifié' ?></p>
                            <p><i class="fas fa-users"></i> Capacité max: <?= $offre['capacite_max'] ?> personnes</p>
                        </div>
                        <div class="col-md-6">
                            <h4>Prix</h4>
                            <p><i class="fas fa-tag"></i> Prix unitaire: <span class="prix-unitaire"><?= number_format($offre['prix'], 2, ',', ' ') ?> €</span></p>
                            <p><i class="fas fa-calculator"></i> Prix total: <span class="prix-total"><?= number_format($offre['prix'], 2, ',', ' ') ?> €</span></p>
                            <?php if (isset($offre['ancien_prix']) && $offre['ancien_prix'] > $offre['prix']): ?>
                                <p class="text-danger"><i class="fas fa-percentage"></i> Économisez <?= number_format($offre['ancien_prix'] - $offre['prix'], 2, ',', ' ') ?> €</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <form method="POST" action="">
                    <h3>Informations de réservation</h3>
                    
                    <div class="form-group">
                        <label for="nbr_personnes">Nombre de personnes</label>
                        <input type="number" class="form-control" id="nbr_personnes" name="nbr_personnes" 
                               min="1" max="<?= $offre['capacite_max'] ?>" value="1" required>
                    </div>

                    <!-- Section Participants supplémentaires -->
                    <div id="participants-container" style="display: none;">
                        <h4>Participants supplémentaires</h4>
                        <div id="participants-fields">
                            <!-- Les champs seront ajoutés dynamiquement -->
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Méthode de paiement</label>
                        <select class="form-control" name="methode_paiement" id="methode_paiement" required>
                            <option value="carte bancaire">Carte bancaire</option>
                            <option value="apple pay">Apple Pay</option>
                            <option value="paypal">PayPal</option>
                            <option value="virement">Virement bancaire</option>
                        </select>
                    </div>

                    <!-- Champs dynamiques pour le paiement -->
                    <div id="payment-details" style="display: none;">
                        <!-- Carte bancaire -->
                        <div id="carte-bancaire-fields" style="display: none;">
                            <div class="form-group">
                                <label for="card_number">Numéro de carte</label>
                                <input type="text" class="form-control" id="card_number" name="card_number" placeholder="1234 5678 9012 3456">
                            </div>
                            <div class="form-group">
                                <label for="card_expiry">Date d'expiration</label>
                                <input type="text" class="form-control" id="card_expiry" name="card_expiry" placeholder="MM/AA">
                            </div>
                            <div class="form-group">
                                <label for="card_cvc">CVC</label>
                                <input type="text" class="form-control" id="card_cvc" name="card_cvc" placeholder="123">
                            </div>
                        </div>

                        <!-- PayPal -->
                        <div id="paypal-fields" style="display: none;">
                            <div class="form-group">
                                <label for="paypal_email">Email PayPal</label>
                                <input type="email" class="form-control" id="paypal_email" name="paypal_email" placeholder="email@example.com">
                            </div>
                        </div>

                        <!-- Virement bancaire -->
                        <div id="virement-fields" style="display: none;">
                            <div class="form-group">
                                <label for="iban">IBAN</label>
                                <input type="text" class="form-control" id="iban" name="iban" placeholder="FR76 1234 5678 9012 3456 7890 123">
                            </div>
                        </div>

                        <!-- Apple Pay -->
                        <div id="apple-pay-fields" style="display: none;">
                            <div class="form-group">
                                <label for="apple_pay_email">Email Apple Pay</label>
                                <input type="email" class="form-control" id="apple_pay_email" name="apple_pay_email" placeholder="email@example.com">
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label>Informations personnelles</label>
                        <div class="card p-3">
                            <p><strong>Nom:</strong> <?= htmlspecialchars($user['nom']) ?></p>
                            <p><strong>Prénom:</strong> <?= htmlspecialchars($user['prenom']) ?></p>
                            <p><strong>Email:</strong> <?= htmlspecialchars($user['email']) ?></p>
                            <p><strong>Téléphone:</strong> <?= isset($user['telephone']) ? htmlspecialchars($user['telephone']) : 'Non renseigné' ?></p>
                        </div>
                    </div>
                    
                    <div class="form-group form-check">
                        <input type="checkbox" class="form-check-input" id="conditions" required>
                        <label class="form-check-label" for="conditions">J'accepte les conditions générales de vente</label>
                    </div>
                    
                    <div class="price-summary text-center my-4">
                        <h4>Total à payer</h4>
                        <p class="price-highlight" id="total-a-payer"><?= number_format($offre['prix'], 2, ',', ' ') ?> €</p>
                        <small class="text-muted">Prix TTC pour 1 personne</small>
                    </div>
                    
                    <button type="submit" name="reserver" class="btn btn-primary btn-block">
                        <i class="fas fa-calendar-check"></i> Confirmer la réservation
                    </button>
                </form>
            <?php elseif (!isset($offre)): ?>
                <div class="alert alert-danger">
                    Offre non trouvée. <a href="offre.php">Retour aux offres</a>
                </div>
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
                    <li><a href="offre.php">Offres spéciales</a></li>
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
        // Gestion des participants supplémentaires
        document.addEventListener('DOMContentLoaded', function() {
            const nbrPersonnesInput = document.getElementById('nbr_personnes');
            const participantsContainer = document.getElementById('participants-container');
            const participantsFields = document.getElementById('participants-fields');
            const prixUnitaire = <?= $offre['prix'] ?? 0 ?>;
            const prixTotalElement = document.getElementById('total-a-payer');

            function updateParticipants() {
                const nbrPersonnes = parseInt(nbrPersonnesInput.value);
                
                // Afficher/masquer la section participants
                participantsContainer.style.display = nbrPersonnes > 1 ? 'block' : 'none';
                
                // Générer les champs pour les participants supplémentaires
                participantsFields.innerHTML = '';
                for (let i = 1; i < nbrPersonnes; i++) {
                    participantsFields.innerHTML += `
                        <div class="participant-group mb-3 p-3 border rounded">
                            <h5>Participant ${i}</h5>
                            <div class="form-group">
                                <label for="participant_nom_${i}">Nom</label>
                                <input type="text" class="form-control" id="participant_nom_${i}" 
                                       name="participants[${i}][nom]" required>
                            </div>
                            <div class="form-group">
                                <label for="participant_prenom_${i}">Prénom</label>
                                <input type="text" class="form-control" id="participant_prenom_${i}" 
                                       name="participants[${i}][prenom]" required>
                            </div>
                            <div class="form-group">
                                <label for="participant_date_naissance_${i}">Date de naissance</label>
                                <input type="date" class="form-control" id="participant_date_naissance_${i}" 
                                       name="participants[${i}][date_naissance]" required>
                            </div>
                        </div>
                    `;
                }

                // Calcul du prix total
                let prixUnitaireActuel = prixUnitaire;
                if (nbrPersonnes >= 3 && nbrPersonnes <= 4) {
                    prixUnitaireActuel = prixUnitaire * 0.7; // 30% de réduction
                } else if (nbrPersonnes >= 5) {
                    prixUnitaireActuel = prixUnitaire * 0.6; // 40% de réduction
                }

                const prixTotal = prixUnitaireActuel * nbrPersonnes;
                prixTotalElement.textContent = prixTotal.toFixed(2) + ' €';
            }

            nbrPersonnesInput.addEventListener('input', updateParticipants);
            updateParticipants(); // Initialiser au chargement

            // Gestion des méthodes de paiement
            const methodePaiement = document.getElementById('methode_paiement');
            const paymentDetails = document.getElementById('payment-details');
            const carteBancaireFields = document.getElementById('carte-bancaire-fields');
            const paypalFields = document.getElementById('paypal-fields');
            const virementFields = document.getElementById('virement-fields');
            const applePayFields = document.getElementById('apple-pay-fields');

            methodePaiement.addEventListener('change', function() {
                // Masquer tous les champs dynamiques
                carteBancaireFields.style.display = 'none';
                paypalFields.style.display = 'none';
                virementFields.style.display = 'none';
                applePayFields.style.display = 'none';
                paymentDetails.style.display = 'block';

                // Afficher les champs correspondants
                switch (methodePaiement.value) {
                    case 'carte bancaire':
                        carteBancaireFields.style.display = 'block';
                        break;
                    case 'paypal':
                        paypalFields.style.display = 'block';
                        break;
                    case 'virement':
                        virementFields.style.display = 'block';
                        break;
                    case 'apple pay':
                        applePayFields.style.display = 'block';
                        break;
                    default:
                        paymentDetails.style.display = 'none';
                }
            });
        });
    </script>
</body>
</html>