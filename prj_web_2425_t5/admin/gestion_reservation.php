<?php
session_start();
require_once('../data_base/connection.php');

if (!isset($_SESSION['user_id'])) 
{
    header('Location: ../pages/login.php');
    exit;
}

// Confirmer une réservation
if (isset($_POST['confirm_reservation'])) 
{
    $reservation_id = $_POST['reservation_id'];

    $sSQL = "UPDATE reservations SET statut = 'confirmé' WHERE id = :id";
    $stmt = $dbh->prepare($sSQL);

    $stmt->bindValue(':id', $reservation_id);
    $stmt->execute();
    header('Location: gestion_reservation.php?message=Réservation confirmée&reservation_id=' . $reservation_id);
    exit;
}

// Supprimer une réservation
if (isset($_POST['delete_reservation'])) 
{
    $reservation_id = $_POST['reservation_id'];

    $sSQL1 = "DELETE FROM reservations WHERE id = :id";
    $stmt1 = $dbh->prepare($sSQL1);

    $stmt1->bindValue(':id', $reservation_id);
    $stmt1->execute();
    header('Location: gestion_reservation.php?message=Réservation supprimée');
    exit;
}

// Créer une nouvelle réservation
if (isset($_POST['create_reservation'])) 
{
    $user_id = $_POST['user_id'];
    $offre_id = $_POST['offre_id'];
    $logement_id = $_POST['logement_id'];
    $transport_aller_id = $_POST['transport_aller_id'];
    $transport_retour_id = $_POST['transport_retour_id'];
    $nbr_personnes = $_POST['nbr_personnes'];
    $prix_unitaire = $_POST['prix_unitaire'];
    $methode_paiement = $_POST['methode_paiement'];
    $date_depart = $_POST['date_depart'];
    $date_retour = $_POST['date_retour'];

    $sSQL2 = "INSERT INTO reservations (user_id, offre_id, logement_id, transport_aller_id, transport_retour_id, nbr_personnes, prix_unitaire, methode_paiement, date_depart, date_retour, statut, total_price) VALUES (:user_id, :offre_id, :logement_id, :transport_aller_id, :transport_retour_id, :nbr_personnes, :prix_unitaire, :methode_paiement, :date_depart, :date_retour, 'en attente', :total_price)";
    $stmt2 = $dbh->prepare($sSQL2);
    
    $total_price = $nbr_personnes * $prix_unitaire;
    $stmt2->bindParam(':user_id', $user_id);
    $stmt2->bindParam(':offre_id', $offre_id);
    $stmt2->bindParam(':logement_id', $logement_id);
    $stmt2->bindParam(':transport_aller_id', $transport_aller_id);
    $stmt2->bindParam(':transport_retour_id', $transport_retour_id);
    $stm2t->bindParam(':nbr_personnes', $nbr_personnes);
    $stmt2->bindParam(':prix_unitaire', $prix_unitaire);
    $stmt2->bindParam(':methode_paiement', $methode_paiement);
    $stmt2->bindParam(':date_depart', $date_depart);
    $stmt2->bindParam(':date_retour', $date_retour);
    $stmt2->bindParam(':total_price', $total_price);
    $stmt2->execute();
    header('Location: gestion_reservation.php?message=Nouvelle réservation créée');
    exit;
}

// Annuler une réservation
if (isset($_POST['cancel_reservation'])) 
{
    $reservation_id = $_POST['reservation_id'];

    $sSQL3 = "UPDATE reservations SET statut = 'annulé' WHERE id = :id";
    $stmt3 = $dbh->prepare($sSQL3);

    $stmt3->bindValue(':id', $reservation_id);
    $stmt3->execute();
    header('Location: gestion_reservation.php?message=Réservation annulée');
    exit;
}

// Récupérer les réservations avec les informations utilisateur

// $sSQL4 = " SELECT r.*, u.nom AS user_nom, u.prenom AS user_prenom, u.email AS user_email, u.telephone AS user_telephone 
//     FROM reservations r
//     JOIN users u ON r.user_id = u.id
//     ORDER BY r.date_reservation DESC
// ";

$sSQL4 = "SELECT * FROM reservations";
$stmt4 = $dbh->prepare($sSQL4);
$stmt4->execute();
$reservations = $stmt4->fetchAll(PDO::FETCH_ASSOC);

$sSQLusers = "SELECT id, nom, prenom, email, telephone FROM users";
$stmtUsers = $dbh->prepare($sSQLusers);
$stmtUsers->execute();
$users = $stmtUsers->fetchAll(PDO::FETCH_ASSOC);

foreach ($reservations as &$reservation) 
{
    $user_id = $reservation['user_id'];
    if (isset($users[$user_id])) 
    {
        $reservation['user_nom'] = $users[$user_id]['nom'];
        $reservation['user_prenom'] = $users[$user_id]['prenom'];
        $reservation['user_email'] = $users[$user_id]['email'];
        $reservation['user_telephone'] = $users[$user_id]['telephone'];
    }
}

// Récupérer les utilisateurs pour le formulaire de création
$sSQL5 = "SELECT id, nom, prenom FROM users";
$stmt5 = $dbh->prepare($sSQL5);
$stmt5->execute();
$users = $stmt5->fetchAll(PDO::FETCH_ASSOC);

// Récupérer les offres pour le formulaire de création
$sSQL6 = "SELECT id, titre FROM offres";
$stmt6 = $dbh->prepare($sSQL6);
$stmt6->execute();
$offres = $stmt6->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gérer les Réservations</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .form-container {
            background-color: #f8f9fa;
            padding: 20px;
            border-radius: 5px;
            margin-bottom: 30px;
        }
    </style>
</head>
<body>
    <div class="container mt-5">
        <h1>Gérer les Réservations</h1>
        
        <a href="../admin.php" class="btn btn-primary mb-3">Retour</a>

        <!-- Affichage des messages -->
        <?php if (isset($_GET['message'])): ?>
            <div class="alert alert-info"><?= htmlspecialchars($_GET['message']) ?></div>
        <?php endif; ?>

        <!-- Formulaire de création de réservation -->
        <div class="form-container mt-4">
            <h3>Créer une nouvelle réservation</h3>
            <form method="POST">
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="user_id" class="form-label">Utilisateur</label>
                            <select class="form-select" id="user_id" name="user_id" required>
                                <option value="">Sélectionner un utilisateur</option>
                                <?php foreach ($users as $user): ?>
                                    <option value="<?= $user['id'] ?>"><?= htmlspecialchars($user['nom'] . ' ' . $user['prenom']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="offre_id" class="form-label">Offre</label>
                            <select class="form-select" id="offre_id" name="offre_id" required>
                                <option value="">Sélectionner une offre</option>
                                <?php foreach ($offres as $offre): ?>
                                    <option value="<?= $offre['id'] ?>"><?= htmlspecialchars($offre['nom']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="logement_id" class="form-label">ID Logement</label>
                            <input type="number" class="form-control" id="logement_id" name="logement_id" required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="transport_aller_id" class="form-label">ID Transport Aller</label>
                            <input type="number" class="form-control" id="transport_aller_id" name="transport_aller_id" required>
                        </div>
                        <div class="mb-3">
                            <label for="transport_retour_id" class="form-label">ID Transport Retour</label>
                            <input type="number" class="form-control" id="transport_retour_id" name="transport_retour_id" required>
                        </div>
                        <div class="mb-3">
                            <label for="nbr_personnes" class="form-label">Nombre de personnes</label>
                            <input type="number" class="form-control" id="nbr_personnes" name="nbr_personnes" required>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="prix_unitaire" class="form-label">Prix unitaire</label>
                            <input type="number" step="0.01" class="form-control" id="prix_unitaire" name="prix_unitaire" required>
                        </div>
                        <div class="mb-3">
                            <label for="methode_paiement" class="form-label">Méthode de paiement</label>
                            <select class="form-select" id="methode_paiement" name="methode_paiement" required>
                                <option value="carte bancaire">Carte bancaire</option>
                                <option value="paypal">PayPal</option>
                                <option value="virement">Virement</option>
                                <option value="aucun" selected>Aucun</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="date_depart" class="form-label">Date de départ</label>
                            <input type="date" class="form-control" id="date_depart" name="date_depart" required>
                        </div>
                        <div class="mb-3">
                            <label for="date_retour" class="form-label">Date de retour</label>
                            <input type="date" class="form-control" id="date_retour" name="date_retour" required>
                        </div>
                    </div>
                </div>
                <button type="submit" name="create_reservation" class="btn btn-primary">Créer la réservation</button>
            </form>
        </div>

        <!-- Tableau des réservations -->
        <h3 class="mt-5">Liste des réservations</h3>
        <table class="table table-striped mt-3">
            <thead class="table-dark">
                <tr>
                    <th>ID</th>
                    <th>Client</th>
                    <th>Email</th>
                    <th>Téléphone</th>
                    <th>Date départ</th>
                    <th>Date retour</th>
                    <th>Personnes</th>
                    <th>Prix unitaire</th>
                    <th>Statut</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($reservations as $reservation): ?>
                    <tr>
                        <td><?= htmlspecialchars($reservation['id']) ?></td>
                        <td><?= htmlspecialchars($reservation['user_nom'] . ' ' . htmlspecialchars($reservation['user_prenom'])) ?></td>
                        <td><?= htmlspecialchars($reservation['user_email']) ?></td>
                        <td><?= htmlspecialchars($reservation['user_telephone'] ?? '') ?></td>
                        <td><?= htmlspecialchars($reservation['date_depart']) ?></td>
                        <td><?= htmlspecialchars($reservation['date_retour'] ?? 'N/A') ?></td>
                        <td><?= htmlspecialchars($reservation['nbr_personnes']) ?></td>
                        <td><?= htmlspecialchars($reservation['prix_unitaire']) ?> €</td>
                        <td>
                            <span class="badge 
                                <?= $reservation['statut'] === 'confirmé' ? 'bg-success' : 
                                   ($reservation['statut'] === 'annulé' ? 'bg-danger' : 'bg-warning text-dark') ?>">
                                <?= htmlspecialchars($reservation['statut']) ?>
                            </span>
                        </td>
                        <td>
                            <div class="d-flex gap-2">
                                <?php if ($reservation['statut'] === 'en attente'): ?>
                                    <form method="POST" class="d-inline">
                                        <input type="hidden" name="reservation_id" value="<?= $reservation['id'] ?>">
                                        <button type="submit" name="confirm_reservation" class="btn btn-success btn-sm">Confirmer</button>
                                    </form>
                                <?php endif; ?>
                                
                                <form method="POST" class="d-inline">
                                    <input type="hidden" name="reservation_id" value="<?= $reservation['id'] ?>">
                                    <button type="submit" name="cancel_reservation" class="btn btn-warning btn-sm">Annuler</button>
                                </form>
                                
                                <form method="POST" class="d-inline">
                                    <input type="hidden" name="reservation_id" value="<?= $reservation['id'] ?>">
                                    <button type="submit" name="delete_reservation" class="btn btn-danger btn-sm" onclick="return confirm('Êtes-vous sûr de vouloir supprimer cette réservation ?')">Supprimer</button>
                                </form>
                                
                                <a href="reservation_details.php?id=<?= $reservation['id'] ?>" class="btn btn-info btn-sm">Détails</a>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>