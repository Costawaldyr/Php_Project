<?php
require_once("../data_base/connection.php");
session_start();

$error = "";
$success = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST' && !empty($_POST['email'])) {
    $email = $_POST['email'];

    // Validation de l'email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Adresse e-mail invalide.";
    } else {
        try {
            // Vérifier si l'email existe dans la base de données
            $sQuery = 'SELECT id FROM users WHERE email = :email';
            $stmt = $dbh->prepare($sQuery);
            $stmt->bindValue(':email', $email);
            $stmt->execute();

            if ($stmt->rowCount() == 1) {
                // Générer un token unique
                $token = bin2hex(random_bytes(50));

                // Insérer le token dans la base de données avec une expiration
                $sQuery = 'INSERT INTO password_resets (email, token, expires_at) VALUES (:email, :token, DATE_ADD(NOW(), INTERVAL 1 HOUR))';
                $stmt = $dbh->prepare($sQuery);
                $stmt->bindValue(':email', $email);
                $stmt->bindValue(':token', $token);
                $stmt->execute();

                // Envoyer l'email de réinitialisation
                $resetLink = "http://yourwebsite.com/pages/reset-password.php?token=$token";
                $subject = "Réinitialisation de votre mot de passe";
                $message = "Cliquez sur le lien suivant pour réinitialiser votre mot de passe : $resetLink";
                $headers = "From: no-reply@yourwebsite.com";

                if (mail($email, $subject, $message, $headers)) {
                    $success = "Un email de réinitialisation a été envoyé à votre adresse.";
                } else {
                    $error = "Erreur lors de l'envoi de l'email.";
                }
            } else {
                $error = "Aucun compte trouvé avec cette adresse e-mail.";
            }
        } catch (PDOException $e) {
            error_log("Erreur SQL: " . $e->getMessage());
            $error = "Une erreur s'est produite. Veuillez réessayer plus tard.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mot de passe oublié</title>
    
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&family=Fredoka+One&display=swap" rel="stylesheet">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
        <link rel="stylesheet" href="../styles/auth.css">
    
</head>
<body>
    <div class="container">
        <div class="card">
            <div class="card-header">
                <h3>Mot de passe oublié</h3>
            </div>
            <div class="card-body">
                <?php if (!empty($error)): ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>
                <?php if (!empty($success)): ?>
                    <div class="alert alert-success"><?php echo $success; ?></div>
                <?php endif; ?>
                <form action="forgot-password.php" method="POST">
                    <div class="form-group">
                        <label for="email">Adresse e-mail :</label>
                        <input type="email" name="email" id="email" class="form-control" required>
                    </div>
                    <button type="submit" class="btn btn-primary btn-block">Envoyer</button>
                </form>
                <div class="text-center mt-3">
                    <a href="../index.php" class="text-secondary">← Retour à l'accueil</a>
                </div>
            </div>
        </div>
    </div>
    
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
                        <li><a href="pages/offre.php">Offres spéciales</a></li>
                        <li><a href="pages/blog.php">Blog voyage</a></li>
                        <li><a href="pages/forum.php">Forum</a></li>
                        <li><a href="#">Destinations tendances</a></li>
                    </ul>
                </div>
                
                <div class="footer-section">
                    <h3>Aide</h3>
                    <ul>
                        <li><a href="pages/contact.php">Contactez-nous</a></li>
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
                            <a href="#"><img src="img/app-store.png" alt="App Store"></a>
                            <a href="#"><img src="img/google-play.png" alt="Google Play"></a>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="footer-bottom">
                <p>&copy; 2025 Tripster. Tous droits réservés. | Conçu avec <i class="fas fa-heart"></i> pour les jeunes voyageurs</p>
            </div>
        </footer>

        <script>
            // Animation des bulles de fond
            document.addEventListener('DOMContentLoaded', function() {
                const bubbles = document.querySelectorAll('.bubble');
                bubbles.forEach(bubble => {
                    bubble.style.width = bubble.style.height = `var(--size)`;
                    bubble.style.left = `var(--position)`;
                    bubble.style.animationDuration = `var(--time)`;
                    bubble.style.animationDelay = `var(--delay)`;
                });
            });
        </script>
    </body>
</html>