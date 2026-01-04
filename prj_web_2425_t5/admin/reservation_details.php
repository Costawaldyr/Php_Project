<?php
session_start();
require_once('../data_base/connection.php');

// Vérification de l'admin
if (!isset($_SESSION['user_id'])) {
    header('Location: ../pages/login.php');
    exit;
}

// Récupération de l'ID de réservation
$reservation_id = $_GET['id'] ?? null;
if (!$reservation_id) {
    header('Location: gestion_reservation.php?error=Aucune réservation spécifiée');
    exit;
}

// Requête principale pour les détails de réservation
$stmt = $dbh->prepare("
    SELECT 
        r.*,
        u.nom AS user_nom, u.prenom AS user_prenom, u.email AS user_email, u.telephone AS user_telephone,
        o.titre AS offre_titre, o.prix AS offre_prix, o.duree_sejour,
        d.pays AS destination_pays, d.ville AS destination_ville,
        l.nom AS logement_nom, l.type_logement, l.etoiles, l.prix_nuit, l.capacite_max,
        l.all_inclusive, l.demi_pension, l.petit_dejeuner, l.wifi,
        ta.type_transport AS transport_aller_type, ta.compagnie AS transport_aller_compagnie,
        ta.numero AS transport_aller_numero, ta.depart AS transport_aller_depart,
        ta.arrivee AS transport_aller_arrivee, ta.classe AS transport_aller_classe,
        tr.type_transport AS transport_retour_type, tr.compagnie AS transport_retour_compagnie,
        tr.numero AS transport_retour_numero, tr.depart AS transport_retour_depart,
        tr.arrivee AS transport_retour_arrivee, tr.classe AS transport_retour_classe
    FROM reservations r
    JOIN users u ON r.user_id = u.id
    JOIN offres o ON r.offre_id = o.id
    JOIN destinations d ON o.destination_id = d.id
    JOIN logements l ON r.logement_id = l.id
    LEFT JOIN transports ta ON r.transport_aller_id = ta.id
    LEFT JOIN transports tr ON r.transport_retour_id = tr.id
    WHERE r.id = ?
");
$stmt->execute([$reservation_id]);
$reservation = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$reservation) {
    header('Location: gestion_reservation.php?error=Réservation non trouvée');
    exit;
}

// Calcul du prix total
$prix_total = $reservation['prix_unitaire'] * $reservation['nbr_personnes'];
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Détails Réservation #<?= htmlspecialchars($reservation_id) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .card { margin-bottom: 20px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .card-header { background-color: #f8f9fa; font-weight: bold; }
        .badge-status { font-size: 1em; padding: 8px 12px; }
        .icon { margin-right: 8px; }
        .equipement { margin-right: 10px; }
    </style>
</head>
<body>
    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1>Détails Réservation #<?= htmlspecialchars($reservation_id) ?></h1>
            <a href="gestion_reservation.php" class="btn btn-secondary">Retour</a>
        </div>

        <!-- Statut et actions -->
        <div class="alert alert-<?= $reservation['statut'] === 'confirmé' ? 'success' : 
                                ($reservation['statut'] === 'annulé' ? 'danger' : 'warning') ?>">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <span class="badge badge-status bg-<?= $reservation['statut'] === 'confirmé' ? 'success' : 
                                                      ($reservation['statut'] === 'annulé' ? 'danger' : 'warning') ?>">
                        <?= htmlspecialchars(ucfirst($reservation['statut'])) ?>
                    </span>
                    <span class="ms-3">Créée le : <?= htmlspecialchars($reservation['date_reservation']) ?></span>
                </div>
                <div class="d-flex gap-2">
                    <?php if ($reservation['statut'] === 'en attente'): ?>
                        <form method="POST" action="gestion_reservation.php" class="d-inline">
                            <input type="hidden" name="reservation_id" value="<?= $reservation_id ?>">
                            <button type="submit" name="confirm_reservation" class="btn btn-success">Confirmer</button>
                        </form>
                    <?php endif; ?>
                    <form method="POST" action="gestion_reservation.php" class="d-inline">
                        <input type="hidden" name="reservation_id" value="<?= $reservation_id ?>">
                        <button type="submit" name="cancel_reservation" class="btn btn-warning">Annuler</button>
                    </form>
                    <form method="POST" action="gestion_reservation.php" class="d-inline">
                        <input type="hidden" name="reservation_id" value="<?= $reservation_id ?>">
                        <button type="submit" name="delete_reservation" class="btn btn-danger" 
                                onclick="return confirm('Êtes-vous sûr de vouloir supprimer cette réservation ?')">
                            Supprimer
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Colonne gauche -->
            <div class="col-md-6">
                <!-- Client -->
                <div class="card">
                    <div class="card-header">Client</div>
                    <div class="card-body">
                        <p><strong>Nom :</strong> <?= htmlspecialchars($reservation['user_nom'] . ' ' . $reservation['user_prenom']) ?></p>
                        <p><strong>Email :</strong> <?= htmlspecialchars($reservation['user_email']) ?></p>
                        <p><strong>Téléphone :</strong> <?= htmlspecialchars($reservation['user_telephone'] ?? 'Non renseigné') ?></p>
                    </div>
                </div>

                <!-- Offre -->
                <div class="card">
                    <div class="card-header">Offre</div>
                    <div class="card-body">
                        <p><strong>Titre :</strong> <?= htmlspecialchars($reservation['offre_titre']) ?></p>
                        <p><strong>Destination :</strong> <?= htmlspecialchars($reservation['destination_pays'] . ' - ' . $reservation['destination_ville']) ?></p>
                        <p><strong>Durée :</strong> <?= htmlspecialchars($reservation['duree_sejour']) ?> jours</p>
                        <p><strong>Prix offre :</strong> <?= htmlspecialchars($reservation['offre_prix']) ?> €</p>
                    </div>
                </div>

                <!-- Paiement -->
                <div class="card">
                    <div class="card-header">Paiement</div>
                    <div class="card-body">
                        <p><strong>Méthode :</strong> <?= htmlspecialchars(ucfirst($reservation['methode_paiement'])) ?></p>
                        <p><strong>Prix unitaire :</strong> <?= htmlspecialchars($reservation['prix_unitaire']) ?> €</p>
                        <p><strong>Nombre de personnes :</strong> <?= htmlspecialchars($reservation['nbr_personnes']) ?></p>
                        <p><strong>Prix total :</strong> <span class="fw-bold"><?= htmlspecialchars($prix_total) ?> €</span></p>
                    </div>
                </div>
            </div>

            <!-- Colonne droite -->
            <div class="col-md-6">
                <!-- Logement -->
                <div class="card">
                    <div class="card-header">Logement</div>
                    <div class="card-body">
                        <p><strong>Nom :</strong> <?= htmlspecialchars($reservation['logement_nom']) ?></p>
                        <p><strong>Type :</strong> <?= htmlspecialchars(ucfirst($reservation['type_logement'])) ?></p>
                        <p><strong>Étoiles :</strong> <?= str_repeat('★', $reservation['etoiles'] ?? 0) ?></p>
                        <p><strong>Capacité max :</strong> <?= htmlspecialchars($reservation['capacite_max']) ?> personnes</p>
                        <p><strong>Prix/nuit :</strong> <?= htmlspecialchars($reservation['prix_nuit']) ?> €</p>
                        
                        <h6 class="mt-3">Options :</h6>
                        <div class="d-flex flex-wrap">
                            <?php if ($reservation['all_inclusive']): ?>
                                <span class="badge bg-success mb-2 equipement">All Inclusive</span>
                            <?php endif; ?>
                            <?php if ($reservation['demi_pension']): ?>
                                <span class="badge bg-primary mb-2 equipement">Demi-pension</span>
                            <?php endif; ?>
                            <?php if ($reservation['petit_dejeuner']): ?>
                                <span class="badge bg-info mb-2 equipement">Petit déjeuner</span>
                            <?php endif; ?>
                            <?php if ($reservation['wifi']): ?>
                                <span class="badge bg-secondary mb-2 equipement">WiFi</span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Transport Aller -->
                <div class="card">
                    <div class="card-header">Transport Aller</div>
                    <div class="card-body">
                        <?php if ($reservation['transport_aller_type']): ?>
                            <p><strong>Type :</strong> <?= htmlspecialchars(ucfirst($reservation['transport_aller_type'])) ?></p>
                            <p><strong>Compagnie :</strong> <?= htmlspecialchars($reservation['transport_aller_compagnie'] ?? 'Non spécifié') ?></p>
                            <p><strong>Numéro :</strong> <?= htmlspecialchars($reservation['transport_aller_numero'] ?? 'N/A') ?></p>
                            <p><strong>Départ :</strong> <?= htmlspecialchars($reservation['transport_aller_depart']) ?></p>
                            <p><strong>Arrivée :</strong> <?= htmlspecialchars($reservation['transport_aller_arrivee']) ?></p>
                            <p><strong>Classe :</strong> <?= htmlspecialchars(ucfirst($reservation['transport_aller_classe'])) ?></p>
                        <?php else: ?>
                            <p class="text-muted">Aucun transport aller spécifié</p>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Transport Retour -->
                <div class="card">
                    <div class="card-header">Transport Retour</div>
                    <div class="card-body">
                        <?php if ($reservation['transport_retour_type']): ?>
                            <p><strong>Type :</strong> <?= htmlspecialchars(ucfirst($reservation['transport_retour_type'])) ?></p>
                            <p><strong>Compagnie :</strong> <?= htmlspecialchars($reservation['transport_retour_compagnie'] ?? 'Non spécifié') ?></p>
                            <p><strong>Numéro :</strong> <?= htmlspecialchars($reservation['transport_retour_numero'] ?? 'N/A') ?></p>
                            <p><strong>Départ :</strong> <?= htmlspecialchars($reservation['transport_retour_depart']) ?></p>
                            <p><strong>Arrivée :</strong> <?= htmlspecialchars($reservation['transport_retour_arrivee']) ?></p>
                            <p><strong>Classe :</strong> <?= htmlspecialchars(ucfirst($reservation['transport_retour_classe'])) ?></p>
                        <?php else: ?>
                            <p class="text-muted">Aucun transport retour spécifié</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>