<?php
require_once('data_base/connection.php');
session_start();

// Vérification de l'accès admin
if(!isset($_SESSION['access_level']) || $_SESSION['access_level'] !== 'admin') 
{
    header('Location: auth/login.php');
    exit;
}

$sSQL = "SELECT id, nom, prenom, email, profile_picture, date_inscription FROM users WHERE email = :email";
$stmt = $dbh->prepare($sSQL);
$stmt->bindValue(':email', $_SESSION['email']);
$stmt->execute();
$admin = $stmt->fetch(PDO::FETCH_ASSOC);


// Récupération des messages de support non traités
$support_messages = $dbh->query("
    SELECT cs.*, u.prenom, u.nom 
    FROM contact_support cs
    LEFT JOIN users u ON cs.user_id = u.id
    WHERE cs.statut = 'en attente'
    ORDER BY cs.date_post DESC
    LIMIT 5
")->fetchAll(PDO::FETCH_ASSOC);

// Récupération des messages entre admins
$admin_messages = $dbh->prepare("
    SELECT m.*, u.prenom, u.nom 
    FROM admin_messages m
    JOIN users u ON m.sender_id = u.id
    WHERE m.receiver_id = :admin_id
    ORDER BY m.created_at DESC
    LIMIT 5
");
$admin_messages->execute([':admin_id' => $admin['id']]);
$admin_messages = $admin_messages->fetchAll(PDO::FETCH_ASSOC);

// Statistiques de base
$total_users = $dbh->query('SELECT COUNT(*) FROM users')->fetchColumn();
$new_users_today = $dbh->query("SELECT COUNT(*) FROM users WHERE DATE(date_inscription) = CURDATE()")->fetchColumn();
$total_reservations = $dbh->query("SELECT COUNT(*) FROM reservations")->fetchColumn();
$total_revenus = $dbh->query("SELECT SUM(prix_total) FROM reservations WHERE statut != 'annulé'")->fetchColumn() ?: 0.00;

// Données pour les graphiques
$reservations_par_mois = $dbh->query("
    SELECT DATE_FORMAT(date_reservation, '%Y-%m') as mois, COUNT(*) as count 
    FROM reservations 
    WHERE date_reservation >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
    GROUP BY mois
    ORDER BY mois
")->fetchAll(PDO::FETCH_ASSOC);

$revenus_par_type = $dbh->query("
    SELECT o.titre, SUM(r.prix_total) as total
    FROM reservations r
    JOIN offres o ON r.offre_id = o.id
    WHERE r.statut != 'annulé'
    GROUP BY o.titre
    ORDER BY total DESC
    LIMIT 5
")->fetchAll(PDO::FETCH_ASSOC);

// Derniers utilisateurs inscrits
$new_users = $dbh->query("
    SELECT prenom, nom, email, date_inscription 
    FROM users 
    ORDER BY date_inscription DESC 
    LIMIT 5
")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin - Tableau de Bord Tripster</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/chart.js@3.7.1/dist/chart.min.css" rel="stylesheet">
    <style>
        :root {
            --tripster-primary: #4e73df;
            --tripster-secondary: #1cc88a;
            --tripster-accent: #f6c23e;
        }
        body { background-color: #f8f9fc; }
        .sidebar { 
            width: 250px; 
            background: linear-gradient(180deg, var(--tripster-primary) 0%, #224abe 100%);
            color: white; 
            min-height: 100vh; 
            position: fixed; 
            box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
        }
        .sidebar-brand { 
            height: 4.375rem;
            text-decoration: none;
            font-size: 1.2rem;
            font-weight: 800;
            padding: 1.5rem 1rem;
            text-align: center;
            letter-spacing: 0.05rem;
            z-index: 1;
            background: rgba(255, 255, 255, 0.1);
        }
        .sidebar hr { border-top: 1px solid rgba(255, 255, 255, 0.15); }
        .sidebar .nav-item { position: relative; }
        .sidebar .nav-link { 
            color: rgba(255, 255, 255, 0.8);
            font-weight: 600;
            padding: 0.75rem 1rem;
        }
        .sidebar .nav-link i { 
            font-size: 0.85rem;
            margin-right: 0.25rem;
        }
        .sidebar .nav-link:hover { color: white; }
        .sidebar .nav-link.active { color: #fff; }
        .sidebar .nav-link.active i { color: var(--tripster-accent); }
        .content { margin-left: 250px; width: calc(100% - 250px); }
        .topbar { height: 4.375rem; box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15); }
        .card { border: none; border-radius: 0.35rem; box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.1); }
        .card-header { background-color: #f8f9fc; border-bottom: 1px solid #e3e6f0; }
        .stat-card { border-left: 0.25rem solid; }
        .stat-card.primary { border-left-color: var(--tripster-primary); }
        .stat-card.success { border-left-color: var(--tripster-secondary); }
        .stat-card.warning { border-left-color: var(--tripster-accent); }
        .stat-card.info { border-left-color: #36b9cc; }
        .stat-card .text-xs { font-size: 0.7rem; }
        .bg-tripster-primary { background-color: var(--tripster-primary); }
        .bg-tripster-secondary { background-color: var(--tripster-secondary); }
        .bg-tripster-accent { background-color: var(--tripster-accent); }
        .chart-area { position: relative; height: 10rem; width: 100%; }
        .chart-pie { position: relative; height: 15rem; width: 100%; }
        .profile-pic { width: 60px; height: 60px; object-fit: cover; border-radius: 50%; border: 3px solid #fff; box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.2); }
        .dropdown-list-image { width: 3rem; height: 3rem; border-radius: 50%; }
        .progress-sm { height: 0.5rem; }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <nav class="sidebar navbar-dark">
        <div class="sidebar-brand d-flex align-items-center justify-content-center">
            <div class="text-center">
                <i class="bi bi-globe-americas"></i>
                <span class="ml-2">Tripster Admin</span>
            </div>
        </div>
        <hr class="mt-0">
        <div class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link active" href="admin.php">
                    <i class="bi bi-speedometer2"></i>
                    <span>Tableau de bord</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="admin/gestion_utilisateur.php">
                    <i class="bi bi-people"></i>
                    <span>Utilisateurs</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="admin/gestion_reservation.php">
                    <i class="bi bi-calendar-check"></i>
                    <span>Réservations</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="admin/gestion_offre.php">
                    <i class="bi bi-tags"></i>
                    <span>Offres</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="admin/gestion_destination.php">
                    <i class="bi bi-geo-alt"></i>
                    <span>Destinations</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="admin/gestion_contenu.php">
                    <i class="bi bi-pencil-square"></i>
                    <span>Gestion de Contenu</span>
                </a>
            </li>
            <hr>
            <li class="nav-item">
                <a class="nav-link" href="index.php">
                    <i class="bi bi-house"></i>
                    <span>Site public</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="admin/messagerie.php">
                    <i class="bi bi-chat-left-text"></i>
                    <span>Messagerie</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link text-danger" href="logout.php">
                    <i class="bi bi-box-arrow-right"></i>
                    <span>Déconnexion</span>
                </a>
            </li>
        </div>
    </nav>

    <!-- Content Wrapper -->
    <div class="content">
        <!-- Topbar -->
        <nav class="navbar navbar-expand topbar bg-white shadow mb-4 static-top">
            <div class="container-fluid justify-content-end">
                <ul class="navbar-nav">
                    <li class="nav-item dropdown no-arrow">
                        <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown">
                            <span class="me-2 d-none d-lg-inline text-gray-600 small"><?= htmlspecialchars($admin['prenom'] . ' ' . htmlspecialchars($admin['nom'])) ?></span>
                            <img class="img-profile rounded-circle" src="<?= htmlspecialchars($admin['profile_picture'] ?? 'https://via.placeholder.com/150') ?>" width="40">
                        </a>
                        <div class="dropdown-menu dropdown-menu-end shadow">
                            <a class="dropdown-item" href="#">
                                <i class="bi bi-person fs-6 text-gray-400 me-2"></i>
                                Profil
                            </a>
                            <a class="dropdown-item" href="#">
                                <i class="bi bi-gear fs-6 text-gray-400 me-2"></i>
                                Paramètres
                            </a>
                            <div class="dropdown-divider"></div>
                            <a class="dropdown-item" href="logout.php">
                                <i class="bi bi-box-arrow-right fs-6 text-gray-400 me-2"></i>
                                Déconnexion
                            </a>
                        </div>
                    </li>
                </ul>
            </div>
        </nav>

        <!-- Begin Page Content -->
        <div class="container-fluid px-4">
            <!-- Page Heading -->
            <div class="d-sm-flex align-items-center justify-content-between mb-4">
                <h1 class="h3 mb-0 text-gray-800">Tableau de bord</h1>
                <a href="#" class="d-none d-sm-inline-block btn btn-sm btn-tripster-primary shadow-sm">
                    <i class="bi bi-download text-white-50 me-1"></i> 
                    Générer rapport
                </a>
            </div>

            <!-- Content Row -->
            <div class="row">
                <!-- Utilisateurs Card -->
                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card stat-card primary h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col me-2">
                                    <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                        Utilisateurs</div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $total_users ?></div>
                                </div>
                                <div class="col-auto">
                                    <i class="bi bi-people fs-2 text-gray-300"></i>
                                </div>
                            </div>
                            <div class="mt-2">
                                <span class="text-success small">
                                    <i class="bi bi-arrow-up"></i> <?= $new_users_today ?> nouveaux aujourd'hui
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Réservations Card -->
                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card stat-card success h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col me-2">
                                    <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                        Réservations</div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800"><?= $total_reservations ?></div>
                                </div>
                                <div class="col-auto">
                                    <i class="bi bi-calendar-check fs-2 text-gray-300"></i>
                                </div>
                            </div>
                            <div class="mt-2">
                                <span class="text-success small">
                                    <i class="bi bi-arrow-up"></i> 12% ce mois-ci
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Revenus Card -->
                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card stat-card info h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col me-2">
                                    <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                        Revenus</div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800"><?= number_format($total_revenus, 2, ',', ' ') ?> €</div>
                                </div>
                                <div class="col-auto">
                                    <i class="bi bi-currency-euro fs-2 text-gray-300"></i>
                                </div>
                            </div>
                            <div class="mt-2">
                                <span class="text-success small">
                                    <i class="bi bi-arrow-up"></i> 8% ce mois-ci
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Taux de conversion Card -->
                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card stat-card warning h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col me-2">
                                    <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                        Taux de conversion</div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800">24%</div>
                                </div>
                                <div class="col-auto">
                                    <i class="bi bi-percent fs-2 text-gray-300"></i>
                                </div>
                            </div>
                            <div class="mt-2">
                                <div class="progress progress-sm">
                                    <div class="progress-bar bg-warning" style="width: 24%"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Content Row -->
            <div class="row">
                <!-- Graphique des réservations -->
                <div class="col-xl-8 col-lg-7">
                    <div class="card shadow mb-4">
                        <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                            <h6 class="m-0 font-weight-bold text-primary">Évolution des réservations</h6>
                        </div>
                        <div class="card-body">
                            <div class="chart-area">
                                <canvas id="reservationsChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Graphique circulaire des revenus -->
                <div class="col-xl-4 col-lg-5">
                    <div class="card shadow mb-4">
                        <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                            <h6 class="m-0 font-weight-bold text-primary">Répartition des revenus</h6>
                        </div>
                        <div class="card-body">
                            <div class="chart-pie pt-4 pb-2">
                                <canvas id="revenusChart"></canvas>
                            </div>
                            <div class="mt-4 text-center small">
                                <?php foreach ($revenus_par_type as $revenu): ?>
                                    <span class="me-2">
                                        <i class="bi bi-circle-fill" style="color: <?= sprintf('#%06X', mt_rand(0, 0xFFFFFF)) ?>"></i>
                                        <?= htmlspecialchars($revenu['titre']) ?>
                                    </span>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
           
            <div class="row mt-4">
                <!-- Messages de support -->
                <div class="col-lg-6 mb-4">
                    <div class="card shadow">
                        <div class="card-header bg-warning text-white d-flex justify-content-between align-items-center">
                            <h6 class="m-0 font-weight-bold"><i class="bi bi-headset"></i> Messages de support</h6>
                            <span class="badge bg-danger"><?= count($support_messages) ?> non lus</span>
                        </div>
                        <div class="card-body">
                            <?php if (empty($support_messages)): ?>
                                <div class="text-center text-muted py-4">
                                    <i class="bi bi-check-circle" style="font-size: 2rem;"></i>
                                    <p class="mt-2">Aucun message en attente</p>
                                </div>
                            <?php else: ?>
                                <div class="list-group list-group-flush">
                                    <?php foreach ($support_messages as $message): ?>
                                        <a href="admin/gestion_messages.php?message_id=<?= $message['id'] ?>" 
                                        class="list-group-item list-group-item-action">
                                            <div class="d-flex w-100 justify-content-between">
                                                <h6 class="mb-1"><?= htmlspecialchars($message['prenom'] . ' ' . htmlspecialchars($message['nom'])) ?></h6>
                                                <small><?= date('d/m/Y H:i', strtotime($message['date_post'])) ?></small>
                                            </div>
                                            <p class="mb-1 text-truncate"><?= htmlspecialchars(substr($message['message'], 0, 50)) ?>...</p>
                                            <small class="text-muted">Cliquez pour répondre</small>
                                        </a>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                            <a href="admin/gestion_messages.php" class="btn btn-sm btn-warning mt-3">Voir tous les messages</a>
                        </div>
                    </div>
                </div>

                <!-- Messagerie entre admins -->
                <div class="col-lg-6 mb-4">
                    <div class="card shadow">
                        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                            <h6 class="m-0 font-weight-bold"><i class="bi bi-chat-left-text"></i> Messagerie interne</h6>
                            <a href="admin/messagerie.php" class="btn btn-sm btn-light">Nouveau message</a>
                        </div>
                        <div class="card-body">
                            <?php if (empty($admin_messages)): ?>
                                <div class="text-center text-muted py-4">
                                    <i class="bi bi-chat-square-text" style="font-size: 2rem;"></i>
                                    <p class="mt-2">Aucun message récent</p>
                                </div>
                            <?php else: ?>
                                <div class="list-group list-group-flush">
                                    <?php foreach ($admin_messages as $message): ?>
                                        <a href="admin/messagerie_admin.php?message_id=<?= $message['id'] ?>" 
                                        class="list-group-item list-group-item-action <?= $message['is_read'] ? '' : 'fw-bold' ?>">
                                            <div class="d-flex w-100 justify-content-between">
                                                <h6 class="mb-1"><?= htmlspecialchars($message['prenom'] . ' ' . htmlspecialchars($message['nom'])) ?></h6>
                                                <small><?= date('d/m/Y H:i', strtotime($message['created_at'])) ?></small>
                                            </div>
                                            <p class="mb-1"><?= htmlspecialchars($message['subject']) ?></p>
                                            <small class="text-muted"><?= htmlspecialchars(substr($message['message'], 0, 50)) ?>...</small>
                                        </a>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                            <a href="admin/messagerie.php" class="btn btn-sm btn-primary mt-3">Voir tous les messages</a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Content Row -->
            <div class="row">
                <!-- Derniers utilisateurs -->
                <div class="col-lg-6 mb-4">
                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">Derniers utilisateurs inscrits</h6>
                        </div>
                        <div class="card-body">
                            <?php foreach ($new_users as $user): ?>
                                <div class="d-flex align-items-center mb-4">
                                    <img class="rounded-circle me-3" src="https://ui-avatars.com/api/?name=<?= urlencode($user['prenom'] . '+' . $user['nom']) ?>&background=random" width="40">
                                    <div class="flex-grow-1">
                                        <div class="small">
                                            <span class="font-weight-bold"><?= htmlspecialchars($user['prenom'] . ' ' . $user['nom']) ?></span>
                                            <div class="text-muted"><?= htmlspecialchars($user['email']) ?></div>
                                        </div>
                                    </div>
                                    <div class="text-end">
                                        <small class="text-muted"><?= date('d/m/Y', strtotime($user['date_inscription'])) ?></small>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                            <a href="admin/gestion_utilisateur.php" class="btn btn-sm btn-tripster-primary mt-2">Voir tous les utilisateurs</a>
                        </div>
                    </div>
                </div>

                       <!-- À propos de Tripster -->
                       <div class="col-lg-6 mb-4">
                    <div class="card shadow mb-4">
                        <div class="card-header py-3 bg-tripster-primary">
                            <h6 class="m-0 font-weight-bold text-white">À propos de Tripster</h6>
                        </div>
                        <div class="card-body">
                            <div class="text-center mb-4">
                                <img src="https://via.placeholder.com/150x50?text=Tripster+Logo" alt="Tripster Logo" class="mb-3">
                                <p class="mb-4">Votre plateforme de voyage préférée depuis 2025</p>
                            </div>
                            <div class="row text-center">
                                <div class="col-md-6 mb-4">
                                    <i class="bi bi-globe fs-1 text-primary mb-3"></i>
                                    <h5 class="font-weight-bold">50+ Destinations</h5>
                                    <p class="text-muted">À travers le monde</p>
                                </div>
                                <div class="col-md-6 mb-4">
                                    <i class="bi bi-emoji-smile fs-1 text-primary mb-3"></i>
                                    <h5 class="font-weight-bold">10 000+ Clients</h5>
                                    <p class="text-muted">Satisfaits</p>
                                </div>
                            </div>
                            <hr>
                            <div class="text-center small text-muted">
                                <p>Version 2.1.0 - Dernière mise à jour: <?= date('d/m/Y') ?></p>
                                <a href="#" class="btn btn-sm btn-outline-primary">Voir les mises à jour</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@3.7.1/dist/chart.min.js"></script>
    <script>
        // Graphique des réservations
        const reservationsCtx = document.getElementById('reservationsChart').getContext('2d');
        const reservationsChart = new Chart(reservationsCtx, {
            type: 'line',
            data: {
                labels: [
                    <?php 
                    $months = [];
                    foreach ($reservations_par_mois as $row) {
                        $date = DateTime::createFromFormat('Y-m', $row['mois']);
                        $months[] = "'" . $date->format('M Y') . "'";
                    }
                    echo implode(', ', $months);
                    ?>
                ],
                datasets: [{
                    label: "Réservations",
                    lineTension: 0.3,
                    backgroundColor: "rgba(78, 115, 223, 0.05)",
                    borderColor: "rgba(78, 115, 223, 1)",
                    pointRadius: 3,
                    pointBackgroundColor: "rgba(78, 115, 223, 1)",
                    pointBorderColor: "rgba(78, 115, 223, 1)",
                    pointHoverRadius: 3,
                    pointHoverBackgroundColor: "rgba(78, 115, 223, 1)",
                    pointHoverBorderColor: "rgba(78, 115, 223, 1)",
                    pointHitRadius: 10,
                    pointBorderWidth: 2,
                    data: [
                        <?php 
                        $counts = [];
                        foreach ($reservations_par_mois as $row) {
                            $counts[] = $row['count'];
                        }
                        echo implode(', ', $counts);
                        ?>
                    ],
                }]
            },
            options: {
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            precision: 0
                        }
                    }
                }
            }
        });

        // Graphique des revenus
        const revenusCtx = document.getElementById('revenusChart').getContext('2d');
        const revenusChart = new Chart(revenusCtx, {
            type: 'doughnut',
            data: {
                labels: [
                    <?php 
                    $labels = [];
                    foreach ($revenus_par_type as $revenu) {
                        $labels[] = "'" . htmlspecialchars($revenu['titre']) . "'";
                    }
                    echo implode(', ', $labels);
                    ?>
                ],
                datasets: [{
                    data: [
                        <?php 
                        $totals = [];
                        foreach ($revenus_par_type as $revenu) {
                            $totals[] = $revenu['total'];
                        }
                        echo implode(', ', $totals);
                        ?>
                    ],
                    backgroundColor: [
                        <?php 
                        foreach ($revenus_par_type as $revenu) {
                            echo "'" . sprintf('#%06X', mt_rand(0, 0xFFFFFF)) . "',";
                        }
                        ?>
                    ],
                    hoverBorderColor: "rgba(234, 236, 244, 1)",
                }]
            },
            options: {
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                cutout: '70%',
            }
        });
    </script>
</body>
</html>