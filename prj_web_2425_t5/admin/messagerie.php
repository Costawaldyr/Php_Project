<?php
require_once('../data_base/connection.php');
session_start();

if(!isset($_SESSION['access_level']) || $_SESSION['access_level'] !== 'admin') {
    header('Location: ../auth/login.php');
    exit;
}

// Récupération des données admin
$adminQuery = $dbh->prepare('SELECT id, nom, prenom FROM users WHERE email = :email');
$adminQuery->execute([':email' => $_SESSION['email']]);
$current_admin = $adminQuery->fetch(PDO::FETCH_ASSOC);

// Récupération des autres admins
$admins = $dbh->query("SELECT id, prenom, nom FROM users WHERE access_level = 'admin' AND id != " . $current_admin['id'])->fetchAll(PDO::FETCH_ASSOC);

// Récupération des messages
$messages = [];
if (isset($_GET['with_admin'])) {
    $with_admin = (int)$_GET['with_admin'];
    
    // Marquer les messages comme lus
    $dbh->prepare("UPDATE admin_messages SET is_read = TRUE WHERE sender_id = :sender AND receiver_id = :receiver")
        ->execute([':sender' => $with_admin, ':receiver' => $current_admin['id']]);
    
    // Récupérer la conversation
    $messagesQuery = $dbh->prepare("
        SELECT m.*, u.prenom, u.nom 
        FROM admin_messages m
        JOIN users u ON m.sender_id = u.id
        WHERE (m.sender_id = :current AND m.receiver_id = :with_admin)
           OR (m.sender_id = :with_admin AND m.receiver_id = :current)
        ORDER BY m.created_at
    ");
    $messagesQuery->execute([':current' => $current_admin['id'], ':with_admin' => $with_admin]);
    $messages = $messagesQuery->fetchAll(PDO::FETCH_ASSOC);
}

// Envoi d'un nouveau message
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['receiver_id'], $_POST['subject'], $_POST['message'])) {
    $stmt = $dbh->prepare("
        INSERT INTO admin_messages (sender_id, receiver_id, subject, message, created_at)
        VALUES (:sender, :receiver, :subject, :message, NOW())
    ");
    $stmt->execute([
        ':sender' => $current_admin['id'],
        ':receiver' => $_POST['receiver_id'],
        ':subject' => $_POST['subject'],
        ':message' => $_POST['message']
    ]);
    
    header("Location: messagerie.php?with_admin=" . $_POST['receiver_id']);
    exit;
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Messagerie entre admins</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        .message-container { max-height: 500px; overflow-y: auto; }
        .message-bubble { max-width: 70%; padding: 10px 15px; border-radius: 15px; margin-bottom: 10px; }
        .received { background-color: #f1f1f1; align-self: flex-start; }
        .sent { background-color: #4e73df; color: white; align-self: flex-end; }
        .conversation-item { transition: all 0.2s; }
        .conversation-item:hover { background-color: rgba(0,0,0,.03); }
        .unread-conversation { background-color: rgba(78, 115, 223, 0.05); font-weight: 500; }
    </style>
</head>
<body>
    
    
    <div class="container-fluid">
        <div class="row">
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4">
            <a href="../admin.php" class="btn btn-primary mb-3">Retour</a>
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">Messagerie entre administrateurs</h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#newMessageModal">
                            <i class="bi bi-plus-circle"></i> Nouveau message
                        </button>
                    </div>
                </div>
                
                <div class="row">
                    <!-- Liste des conversations -->
                    <div class="col-md-4">
                        <div class="card shadow mb-4">
                            <div class="card-header bg-primary text-white">
                                <h6 class="mb-0">Administrateurs</h6>
                            </div>
                            <div class="list-group list-group-flush">
                                <?php foreach ($admins as $admin): ?>
                                    <a href="messagerie.php?with_admin=<?= $admin['id'] ?>" 
                                       class="list-group-item list-group-item-action conversation-item <?= isset($_GET['with_admin']) && $_GET['with_admin'] == $admin['id'] ? 'active' : '' ?>">
                                        <div class="d-flex align-items-center">
                                            <img src="https://ui-avatars.com/api/?name=<?= urlencode($admin['prenom'] . '+' . $admin['nom']) ?>" 
                                                 class="rounded-circle me-3" width="40">
                                            <div>
                                                <h6 class="mb-0"><?= htmlspecialchars($admin['prenom'] . ' ' . $admin['nom']) ?></h6>
                                            </div>
                                        </div>
                                    </a>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Messages de la conversation -->
                    <div class="col-md-8">
                        <div class="card shadow">
                            <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                                <h6 class="mb-0">
                                    <?php if (isset($_GET['with_admin']) && !empty($messages)): ?>
                                        Conversation avec <?= htmlspecialchars($messages[0]['prenom'] . ' ' . $messages[0]['nom']) ?>
                                    <?php else: ?>
                                        Sélectionnez un administrateur
                                    <?php endif; ?>
                                </h6>
                            </div>
                            
                            <div class="card-body">
                                <?php if (isset($_GET['with_admin'])): ?>
                                    <!-- Affichage des messages -->
                                    <div class="d-flex flex-column message-container mb-3">
                                        <?php if (empty($messages)): ?>
                                            <div class="text-center text-muted py-4">
                                                <i class="bi bi-chat-square-text" style="font-size: 2rem;"></i>
                                                <p class="mt-2">Aucun message dans cette conversation</p>
                                            </div>
                                        <?php else: ?>
                                            <?php foreach ($messages as $msg): ?>
                                                <div class="d-flex <?= $msg['sender_id'] == $current_admin['id'] ? 'justify-content-end' : 'justify-content-start' ?> mb-2">
                                                    <div class="message-bubble <?= $msg['sender_id'] == $current_admin['id'] ? 'sent' : 'received' ?>">
                                                        <h6><?= htmlspecialchars($msg['subject']) ?></h6>
                                                        <div><?= nl2br(htmlspecialchars($msg['message'])) ?></div>
                                                        <small class="d-block text-end <?= $msg['sender_id'] == $current_admin['id'] ? 'text-white-50' : 'text-muted' ?>">
                                                            <?= date('d/m/Y H:i', strtotime($msg['created_at'])) ?>
                                                        </small>
                                                    </div>
                                                </div>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <!-- Formulaire d'envoi -->
                                    <form method="POST" action="messagerie.php">
                                        <input type="hidden" name="receiver_id" value="<?= $_GET['with_admin'] ?>">
                                        <div class="mb-3">
                                            <label class="form-label">Sujet</label>
                                            <input type="text" class="form-control" name="subject" required>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Message</label>
                                            <textarea class="form-control" name="message" rows="3" required></textarea>
                                        </div>
                                        <button type="submit" class="btn btn-primary">
                                            <i class="bi bi-send"></i> Envoyer
                                        </button>
                                    </form>
                                <?php else: ?>
                                    <div class="text-center text-muted py-5">
                                        <i class="bi bi-chat-square-text" style="font-size: 3rem;"></i>
                                        <h4 class="mt-3">Sélectionnez un administrateur</h4>
                                        <p>Choisissez un administrateur dans la liste à gauche pour afficher les messages</p>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <!-- Modal Nouveau Message -->
    <div class="modal fade" id="newMessageModal" tabindex="-1" aria-labelledby="newMessageModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST" action="messagerie.php">
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title" id="newMessageModalLabel">Nouveau message</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Destinataire</label>
                            <select class="form-select" name="receiver_id" required>
                                <option value="">Choisir un administrateur</option>
                                <?php foreach ($admins as $admin): ?>
                                    <option value="<?= $admin['id'] ?>"><?= htmlspecialchars($admin['prenom'] . ' ' . $admin['nom']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Sujet</label>
                            <input type="text" class="form-control" name="subject" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Message</label>
                            <textarea class="form-control" name="message" rows="5" required></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                        <button type="submit" class="btn btn-primary">Envoyer</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Faire défiler vers le bas des messages
        const messageContainer = document.querySelector('.message-container');
        if (messageContainer) {
            messageContainer.scrollTop = messageContainer.scrollHeight;
        }
    </script>
</body>
</html>