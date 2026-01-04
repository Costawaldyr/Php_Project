<?php
session_start();
require_once('../data_base/connection.php');

$sSQL = "SELECT f.*, u.nom FROM forum f JOIN users u ON f.user_id = u.id ORDER BY date_post DESC";
$stmt_forum = $dbh->prepare($sSQL);
$stmt_forum->execute();
$posts = $stmt_forum->fetchAll(PDO::FETCH_ASSOC);


if ($_SERVER['REQUEST_METHOD'] === 'POST') 
{
    $titre = $_POST['titre'];
    $message = $_POST['message'];
    $user_id = $_SESSION['user_id']; 

    if (empty($titre) || empty($message) || empty($user_id))
    {
        die("Le titre, le message et l'utilisateur sont obligatoires.");
    }

    $sSQL1 = 'INSERT INTO forum (titre, message, user_id) VALUES (:titre, :message, :user_id)';

    $stmt1 = $dbh->prepare($sSQL1);
    $stmt1->bindParam(':titre', $titre);
    $stmt1->bindParam(':message', $message);
    $stmt1->bindParam(':user_id', $user_id);
    

    if ($stmt1->execute()) 
    {
        header('Location: forum.php');
        exit();
    } 
    else 
    {
        die("Erreur lors de l'insertion du message.");
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Forum</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../styles/pages.css" >
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&family=Fredoka+One&display=swap" rel="stylesheet">
    
</head>
<body>
    <div class="container mt-4">
        <h2>💬 Forum de Discussion</h2>
        
        <!-- Formulaire pour poster un message -->
        <form action="forum.php" method="POST">
            <label class="form-label">Titre :</label>
            <input type="text" name="titre" class="form-control" required>
            <label class="form-label mt-2">Message :</label>
            <textarea name="message" class="form-control" rows="3" required></textarea>
            <button type="submit" class="btn btn-primary mt-2">Poster</button>
        </form>

        <!-- Affichage des messages du forum -->
        <h3 class="mt-4">🗨️ Discussions</h3>
        <?php foreach ($posts as $post) : ?>
            <div class="card mt-3">
                <div class="card-body">
                    <h5><?php echo htmlspecialchars($post['titre']); ?></h5>
                    <p><?php echo htmlspecialchars($post['message']); ?></p>
                    <small>Posté par <?php echo htmlspecialchars($post['nom']); ?> le <?php echo $post['date_post']; ?></small>
                </div>
            </div>
        <?php endforeach; ?>
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

</body>
</html>
