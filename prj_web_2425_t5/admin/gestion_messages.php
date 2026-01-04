<?php
require_once('../data_base/connection.php');
session_start();

if (!isset($_SESSION['access_level']) || $_SESSION['access_level'] !== 'admin') {
    header('Location: ../auth/login.php');
    exit;
}

// Récupération des messages de support
$messages = $dbh->query("
    SELECT cs.*, u.prenom, u.nom, u.email 
    FROM contact_support cs
    LEFT JOIN users u ON cs.user_id = u.id
    ORDER BY cs.date_post DESC
")->fetchAll(PDO::FETCH_ASSOC);

// Répondre à un message
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['message_id'], $_POST['response'])) {
    // 1. Mettre à jour la réponse dans la base de données
    $stmt = $dbh->prepare("
        UPDATE contact_support 
        SET reponse = :response, date_reponse = NOW(), statut = 'répondu' 
        WHERE id = :id
    ");
    $stmt->execute([
        ':response' => $_POST['response'],
        ':id' => $_POST['message_id']
    ]);

    // 2. Envoyer un email de réponse (facultatif)
    $message = $dbh->prepare("SELECT * FROM contact_support WHERE id = :id");
    $message->execute([':id' => $_POST['message_id']]);
    $message = $message->fetch(PDO::FETCH_ASSOC);

    if ($message) {
        $to = $message['email'] ?? $message['user_email'];
        $subject = "Réponse à votre demande de support";
        $headers = "From: support@tripster.com";

        mail($to, $subject, $_POST['response'], $headers);
    }

    // 3. Rediriger avec un message de succès
    $_SESSION['success'] = "Réponse envoyée avec succès.";
    header("Location: gestion_messages.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Gestion des messages de support</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        .message-card { border-left: 4px solid; }
        .message-card.pending { border-left-color: #ffc107; }
        .message-card.answered { border-left-color: #28a745; }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">Gestion des messages de support</h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <div class="btn-group me-2">
                            <a href="../admin.php" class="btn btn-sm btn-outline-secondary">
                                <i class="bi bi-arrow-left"></i> Retour
                            </a>
                        </div>
                    </div>
                </div>

                <?php if (isset($_SESSION['success'])): ?>
                    <div class="alert alert-success"><?= htmlspecialchars($_SESSION['success']) ?></div>
                    <?php unset($_SESSION['success']); ?>
                <?php endif; ?>

                <div class="row">
                    <div class="col-md-12">
                        <?php foreach ($messages as $message): ?>
                            <div class="card message-card mb-3 <?= $message['statut'] === 'en attente' ? 'pending' : 'answered' ?>">
                                <div class="card-header d-flex justify-content-between align-items-center">
                                    <div>
                                        <strong><?= htmlspecialchars(($message['prenom'] ?? '') . ' ' . ($message['nom'] ?? '')) ?></strong>
                                        <span class="text-muted ms-2"><?= htmlspecialchars($message['email'] ?? '') ?></span>
                                    </div>
                                    <div>
                                        <span class="badge bg-<?= $message['statut'] === 'en attente' ? 'warning' : 'success' ?>">
                                            <?= $message['statut'] === 'en attente' ? 'En attente' : 'Répondu' ?>
                                        </span>
                                        <small class="text-muted ms-2"><?= date('d/m/Y H:i', strtotime($message['date_post'])) ?></small>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <p><strong>Sujet :</strong> <?= htmlspecialchars($message['sujet'] ?? 'Sujet non défini') ?></p>
                                    <p><?= nl2br(htmlspecialchars($message['message'] ?? '')) ?></p>

                                    <?php if (!empty($message['reponse'])): ?>
                                        <hr>
                                        <h6 class="text-success">Réponse :</h6>
                                        <p><?= nl2br(htmlspecialchars($message['reponse'])) ?></p>
                                        <p class="small text-muted">
                                            Répondu le <?= date('d/m/Y H:i', strtotime($message['date_reponse'])) ?>
                                        </p>
                                    <?php elseif ($message['statut'] === 'en attente'): ?>
                                        <form method="POST" action="gestion_messages.php" class="mt-3">
                                            <input type="hidden" name="message_id" value="<?= $message['id'] ?>">
                                            <div class="mb-3">
                                                <label class="form-label">Réponse</label>
                                                <textarea class="form-control" name="response" rows="3" required></textarea>
                                            </div>
                                            <button type="submit" class="btn btn-success">
                                                <i class="bi bi-send"></i> Envoyer la réponse
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>