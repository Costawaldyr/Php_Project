<?php
require_once('data_base/connection.php');
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: auth/connexion.php');
    exit();
}

// Récupérer les données utilisateur
$user = [];
$reservations = [];
$favoris = [];
$groupes = [];
$messages_support = [];

try 
{
    $sql = 'SELECT * FROM users WHERE id = ?';
    $stmt = $dbh->prepare($sql);
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    // Réservations
    $sql = '
        SELECT r.*, o.titre, o.images, d.ville, d.pays 
        FROM reservations r
        JOIN offres o ON r.offre_id = o.id
        JOIN destinations d ON o.destination_id = d.id
        WHERE r.user_id = ?
        ORDER BY r.date_depart DESC';
    $stmt = $dbh->prepare($sql);
    $stmt->execute([$_SESSION['user_id']]);
    $reservations = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Favoris (à implémenter il faut une table favoris)
    // Dans la partie PHP au début du fichier
    $sql = '
    SELECT o.*, d.ville, d.pays, f.date_ajout
    FROM favoris f
    JOIN offres o ON f.offre_id = o.id
    LEFT JOIN destinations d ON o.destination_id = d.id
    WHERE f.user_id = ?
    ORDER BY f.date_ajout DESC';
    $stmt = $dbh->prepare($sql);
    $stmt->execute([$_SESSION['user_id']]);
    $favoris = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Groupes de voyage (à implémenter il faut une table groupes)
    // Groupes créés par l'utilisateur
    $sql = 'SELECT * FROM groupes WHERE createur_id = ? ORDER BY date_creation DESC';
    $stmt = $dbh->prepare($sql);
    $stmt->execute([$_SESSION['user_id']]);
    $groupes_crees = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Groupes rejoints par l'utilisateur
    $sql = '
        SELECT g.*, u.nom AS createur_nom, u.prenom AS createur_prenom
        FROM groupes_utilisateurs gu
        JOIN groupes g ON gu.groupe_id = g.id
        JOIN users u ON g.createur_id = u.id
        WHERE gu.user_id = ? AND g.createur_id != ?
        ORDER BY gu.date_ajout DESC';
    $stmt = $dbh->prepare($sql);
    $stmt->execute([$_SESSION['user_id'], $_SESSION['user_id']]);
    $groupes_rejoints = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Messages support
    $sql1 = 'SELECT * FROM contact_support WHERE user_id = ? ORDER BY date_post DESC';
    $stmt = $dbh->prepare($sql1);
    $stmt->execute([$_SESSION['user_id']]);
    $messages_support = $stmt->fetchAll(PDO::FETCH_ASSOC);

} 
catch (PDOException $e) 
{
    die("Erreur de base de données : " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Mon Profil - Tripster</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../styles/offres.css">
    <style>
        .profile-header {
            background: linear-gradient(135deg, #6e8efb, #a777e3);
            color: white;
            padding: 30px 0;
            border-radius: 10px;
            margin-bottom: 30px;
        }
        .profile-picture {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            object-fit: cover;
            border: 4px solid white;
        }
        .nav-pills .nav-link.active {
            background-color: #6e8efb;
        }
        .card {
            border-radius: 10px;
            margin-bottom: 20px;
            transition: transform 0.3s;
        }
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.1);
        }
        .reservation-status {
            display: inline-block;
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: bold;
        }
        .status-pending {
            background-color: #fff3cd;
            color: #856404;
        }
        .status-confirmed {
            background-color: #d4edda;
            color: #155724;
        }
        .status-cancelled {
            background-color: #f8d7da;
            color: #721c24;
        }
        .tab-content {
            padding: 20px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 0 15px rgba(0,0,0,0.05);
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header class="header-offres">
        <h1>Mon <span>Profil</span></h1>
    </header>
    <a href="index.php" class="btn-back"><i class="fas fa-arrow-left"></i> Retour à l'accueil</a>
    
    <!-- Main Content -->
    <div class="main-container">
        <!-- Profil Header -->
        <div class="profile-header text-center">
            <img src="<?= htmlspecialchars($user['profile_picture'] ?? '../img/default-profile.jpg') ?>" 
                 class="profile-picture" alt="Photo de profil">
            <h2><?= htmlspecialchars($user['prenom'] . ' ' . $user['nom']) ?></h2>
            <p class="mb-0">Membre depuis <?= date('m/Y', strtotime($user['date_inscription'])) ?></p>
        </div>

        <!-- Navigation -->
        <ul class="nav nav-pills mb-4" id="profileTabs" role="tablist">
            <li class="nav-item">
                <a class="nav-link active" id="reservations-tab" data-toggle="pill" href="#reservations" role="tab">
                    <i class="fas fa-calendar-alt"></i> Mes Réservations
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" id="favorites-tab" data-toggle="pill" href="#favorites" role="tab">
                    <i class="fas fa-heart"></i> Favoris
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" id="groups-tab" data-toggle="pill" href="#groups" role="tab">
                    <i class="fas fa-users"></i> Groupes
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" id="account-tab" data-toggle="pill" href="#account" role="tab">
                    <i class="fas fa-user-cog"></i> Mon Compte
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" id="support-tab" data-toggle="pill" href="#support" role="tab">
                    <i class="fas fa-headset"></i> Support
                </a>
            </li>
        </ul>

        <!-- Tab Content -->
        <div class="tab-content" id="profileTabsContent">
            <!-- Onglet Réservations -->
            <div class="tab-pane fade show active" id="reservations" role="tabpanel">
                <h3><i class="fas fa-calendar-alt"></i> Mes Réservations</h3>
                
                <?php if (empty($reservations)): ?>
                    <div class="alert alert-info">
                        Vous n'avez aucune réservation pour le moment.
                        <a href="pages/offre.php" class="alert-link">Découvrez nos offres</a>
                    </div>
                <?php else: ?>
                    <div class="row">
                        <?php foreach ($reservations as $reservation): ?>
                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between">
                                            <h5 class="card-title"><?= htmlspecialchars($reservation['titre']) ?></h5>
                                            <span class="reservation-status status-<?= str_replace('é', 'e', strtolower($reservation['statut'])) ?>">
                                                <?= htmlspecialchars($reservation['statut']) ?>
                                            </span>
                                        </div>
                                        <p class="card-text">
                                            <i class="fas fa-map-marker-alt"></i> 
                                            <?= htmlspecialchars($reservation['ville'] . ', ' . $reservation['pays']) ?>
                                        </p>
                                        <p class="card-text">
                                            <i class="fas fa-calendar-day"></i> 
                                            Du <?= date('d/m/Y', strtotime($reservation['date_depart'])) ?> 
                                            au <?= date('d/m/Y', strtotime($reservation['date_retour'])) ?>
                                        </p>
                                        <p class="card-text">
                                            <i class="fas fa-users"></i> 
                                            <?= htmlspecialchars($reservation['nbr_personnes']) ?> personne(s)
                                        </p>
                                        <p class="card-text font-weight-bold">
                                            Total: <?= number_format($reservation['prix_unitaire'] * $reservation['nbr_personnes'], 2, ',', ' ') ?> €
                                        </p>
                                        <div class="d-flex justify-content-between">
                                            <a href="pages/details_reservation.php?id=<?= $reservation['id'] ?>" class="btn btn-sm btn-outline-primary">
                                                Détails
                                            </a>
                                            <?php if ($reservation['statut'] === 'en attente'): ?>
                                                <button class="btn btn-sm btn-outline-danger annuler-reservation" 
                                                        data-id="<?= $reservation['id'] ?>">
                                                    Annuler
                                                </button>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Onglet Favoris -->
            <div class="tab-pane fade" id="favorites" role="tabpanel">
                <h3><i class="fas fa-heart"></i> Mes Favoris</h3>
                
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
            </div>
            

            <!-- Onglet Groupes -->
            <div class="tab-pane fade" id="groups" role="tabpanel">
                <h3><i class="fas fa-users"></i> Mes Groupes de Voyage</h3>
   
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">Créer un nouveau groupe</h5>
                    </div>
                    <div class="card-body">
                        <form id="createGroupForm" method="POST" action="creer_groupe.php">
                            <div class="form-group">
                                <label for="groupName">Nom du groupe</label>
                                <input type="text" class="form-control" id="groupName" name="nom" required>
                            </div>
                            <div class="form-group">
                                <label for="groupDescription">Description</label>
                                <textarea class="form-control" id="groupDescription" name="description" rows="3"></textarea>
                            </div>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-plus"></i> Créer le groupe
                            </button>
                        </form>
                    </div>
                </div>
                
                <!-- Groupes créés par l'utilisateur -->
                <h4 class="mt-4">Groupes que j'ai créés</h4>
                <?php if (empty($groupes_crees)): ?>
                    <div class="alert alert-info">
                        Vous n'avez créé aucun groupe.
                    </div>
                <?php else: ?>
                    <div class="row">
                        <?php foreach ($groupes_crees as $groupe): ?>
                            <div class="col-md-6 mb-4">
                                <div class="card">
                                    <div class="card-body">
                                        <h5 class="card-title"><?= htmlspecialchars($groupe['nom']) ?></h5>
                                        <p class="card-text text-muted">
                                            Créé le <?= date('d/m/Y', strtotime($groupe['date_creation'])) ?>
                                        </p>
                                        <div class="d-flex justify-content-between">
                                            <a href="groupe_details.php?id=<?= $groupe['id'] ?>" class="btn btn-sm btn-outline-primary">
                                                <i class="fas fa-eye"></i> Voir
                                            </a>
                                            <form method="POST" action="supprimer_groupe.php" class="d-inline">
                                                <input type="hidden" name="groupe_id" value="<?= $groupe['id'] ?>">
                                                <button type="submit" class="btn btn-sm btn-outline-danger">
                                                    <i class="fas fa-trash"></i> Supprimer
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
                
                <!-- Groupes rejoints -->
                <h4 class="mt-4">Groupes auxquels j'ai rejoint</h4>
                <?php if (empty($groupes_rejoints)): ?>
                    <div class="alert alert-info">
                        Vous n'avez rejoint aucun groupe.
                        <a href="#" class="alert-link">Découvrez des groupes existants</a>
                    </div>
                <?php else: ?>
                    <div class="row">
                        <?php foreach ($groupes_rejoints as $groupe): ?>
                            <div class="col-md-6 mb-4">
                                <div class="card">
                                    <div class="card-body">
                                        <h5 class="card-title"><?= htmlspecialchars($groupe['nom']) ?></h5>
                                        <p class="card-text">
                                            Créé par <?= htmlspecialchars($groupe['createur_prenom'] . ' ' . $groupe['createur_nom']) ?>
                                        </p>
                                        <p class="card-text text-muted">
                                            Rejoint le <?= date('d/m/Y', strtotime($groupe['date_ajout'])) ?>
                                        </p>
                                        <div class="d-flex justify-content-between">
                                            <a href="groupe_details.php?id=<?= $groupe['id'] ?>" class="btn btn-sm btn-outline-primary">
                                                <i class="fas fa-eye"></i> Voir
                                            </a>
                                            <form method="POST" action="quitter_groupe.php" class="d-inline">
                                                <input type="hidden" name="groupe_id" value="<?= $groupe['id'] ?>">
                                                <button type="submit" class="btn btn-sm btn-outline-danger">
                                                    <i class="fas fa-sign-out-alt"></i> Quitter
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Onglet Compte -->
            <div class="tab-pane fade" id="account" role="tabpanel">
                <h3><i class="fas fa-user-cog"></i> Mon Compte</h3>
                <div class="row">
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title">Informations personnelles</h5>
                                <form id="profileForm">
                                    <div class="form-group">
                                        <label>Prénom</label>
                                        <input type="text" class="form-control" 
                                               value="<?= htmlspecialchars($user['prenom']) ?>" required>
                                    </div>
                                    <div class="form-group">
                                        <label>Nom</label>
                                        <input type="text" class="form-control" 
                                               value="<?= htmlspecialchars($user['nom']) ?>" required>
                                    </div>
                                    <div class="form-group">
                                        <label>Email</label>
                                        <input type="email" class="form-control" 
                                               value="<?= htmlspecialchars($user['email']) ?>" required>
                                    </div>
                                    <div class="form-group">
                                        <label>Téléphone</label>
                                        <input type="tel" class="form-control" 
                                               value="<?= htmlspecialchars($user['telephone'] ?? '') ?>">
                                    </div>
                                    <div class="form-group">
                                        <label>Date de naissance</label>
                                        <input type="date" class="form-control" 
                                               value="<?= htmlspecialchars($user['date_naissance'] ?? '') ?>">
                                    </div>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save"></i> Enregistrer
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title">Sécurité</h5>
                                <form id="passwordForm">
                                    <div class="form-group">
                                        <label>Nouveau mot de passe</label>
                                        <input type="password" class="form-control">
                                    </div>
                                    <div class="form-group">
                                        <label>Confirmer le mot de passe</label>
                                        <input type="password" class="form-control">
                                    </div>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-lock"></i> Changer mot de passe
                                    </button>
                                </form>
                            </div>
                        </div>
                        
                        <div class="card mt-4">
                            <div class="card-body">
                                <h5 class="card-title">Moyens de paiement</h5>
                                <div class="payment-methods">
                                    <div class="payment-method">
                                        <i class="fab fa-cc-visa"></i> Visa •••• 1234
                                        <button class="btn btn-sm btn-outline-danger float-right">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                    <button class="btn btn-outline-primary mt-2">
                                        <i class="fas fa-plus"></i> Ajouter un moyen de paiement
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Onglet Support -->
            <div class="tab-pane fade" id="support" role="tabpanel">
                <h3><i class="fas fa-headset"></i> Support Client</h3>
                <div class="row">
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title">Nouveau message</h5>
                                <form id="supportForm" action="envoyer_message.php" method="POST">
                                    <div class="form-group">
                                        <label>Sujet</label>
                                        <select class="form-control" name="sujet" required>
                                            <option value="">Choisir un sujet</option>
                                            <option value="Problème de réservation">Problème de réservation</option>
                                            <option value="Question sur une offre">Question sur une offre</option>
                                            <option value="Problème de paiement">Problème de paiement</option>
                                            <option value="Autre question">Autre question</option>
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label>Message</label>
                                        <textarea class="form-control" name="message" rows="5" required></textarea>
                                    </div>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-paper-plane"></i> Envoyer
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <h5>Historique des messages</h5>
                        <?php if (empty($messages_support)): ?>
                            <div class="alert alert-info">
                                Vous n'avez envoyé aucun message au support.
                            </div>
                        <?php else: ?>
                            <?php foreach ($messages_support as $message): ?>
                                <div class="card mb-2">
                                    <div class="card-body">
                                        <h6><?= htmlspecialchars($message['sujet'] ?? 'Sujet non défini') ?></h6>
                                        <p class="small text-muted">
                                            Envoyé le <?= date('d/m/Y H:i', strtotime($message['date_post'])) ?>
                                            • Statut : <?= htmlspecialchars($message['statut']) ?>
                                        </p>
                                        <p><?= nl2br(htmlspecialchars($message['message'])) ?></p>
                                        
                                        <?php if (!empty($message['reponse'])): ?>
                                            <hr>
                                            <h6 class="text-success">Réponse du support :</h6>
                                            <p><?= nl2br(htmlspecialchars($message['reponse'])) ?></p>
                                            <p class="small text-muted">
                                                Répondu le <?= date('d/m/Y H:i', strtotime($message['date_reponse'])) ?>
                                            </p>
                                        <?php else: ?>
                                            <p class="text-muted">En attente de réponse du support.</p>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="footer">
    
    </footer>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script>
        // Annulation de réservation
        $(document).on('click', '.annuler-reservation', function() {
            if (confirm("Êtes-vous sûr de vouloir annuler cette réservation ?")) {
                const reservationId = $(this).data('id');
                // Ici, ajouter un appel AJAX pour annuler la réservation
                alert("Fonctionnalité d'annulation à implémenter");
            }
        });

        // Gestion des formulaires
        $('#profileForm, #passwordForm, #createGroupForm').on('submit', function(e) {
            e.preventDefault();
            alert("Fonctionnalité à implémenter - Les modifications seront enregistrées");
        });

        // Animation des onglets
        $('a[data-toggle="pill"]').on('shown.bs.tab', function() {
            $('.tab-pane').find('.card').each(function(index) {
                $(this).delay(100 * index).fadeTo(200, 1);
            });
        });
    </script>
</body>
</html>