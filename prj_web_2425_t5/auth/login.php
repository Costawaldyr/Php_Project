<?php
require_once('../data_base/connection.php');
session_start();

if (isset($_SESSION['access_level'])) 
{
    if (isset($_GET['logout'])) 
    {
        session_destroy();
        setcookie("user_email", "", time() - 3600, "/"); // Supprimer le cookie
        setcookie("user_id", "", time() - 3600, "/"); // Supprimer le cookie
        header('Location: ../index.php');
        exit();
    }
}

if (!empty($_POST['email']) && !empty($_POST['password'])) 
{
    $sQuery = 'SELECT id, access_level FROM users WHERE email=:email AND password=:password';
    $stmt = $dbh->prepare($sQuery);
    $stmt->bindValue(':email', $_POST['email']);
    $stmt->bindValue(':password', $_POST['password']);
    $stmt->execute();

    if ($stmt->rowCount() == 1) 
    {
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $_SESSION['access_level'] = $row['access_level'];
        $_SESSION['user_id'] = $row['id'];
        $_SESSION['email'] = $_POST['email'];

        // Définir les cookies
        if ($row['access_level'] === 'admin') 
        {
            setcookie("user_email", $_POST['email'], time() + (86400 * 15), "/"); // Cookie valide 15 jours
            setcookie("user_id", $row['id'], time() + (86400 * 15), "/"); // Cookie valide 15 jours
            header('Location: ../admin.php');
        } 
        else 
        {
            setcookie("user_email", $_POST['email'], time() + 1800, "/"); // Cookie valide 30 minutes
            setcookie("user_id", $row['id'], time() + 1800, "/"); // Cookie valide 30 minutes
            header('Location: ../profile.php');
        }
        exit();
    } 
    else 
    {
        $error_message = "Email ou mot de passe incorrect.";
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Connexion - Tripster</title>
        <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&family=Fredoka+One&display=swap" rel="stylesheet">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
        <link rel="stylesheet" href="../styles/auth.css">
    </head>
    <body>
        <!-- Animation de fond -->
        <div class="background">
            <div class="bubble" style="--size: 3rem; --distance: 6rem; --position: 10%; --time: 6s; --delay: 0s;"></div>
            <div class="bubble" style="--size: 2rem; --distance: 8rem; --position: 20%; --time: 5s; --delay: 1s;"></div>
            <div class="bubble" style="--size: 4rem; --distance: 7rem; --position: 50%; --time: 7s; --delay: 0.5s;"></div>
            <div class="bubble" style="--size: 1.5rem; --distance: 9rem; --position: 70%; --time: 6s; --delay: 2s;"></div>
        </div>

        <!-- Contenu principal -->
        <div class="login-container">
            <div class="logo">
                <span>Tripster</span>
                <p>Voyagez jeune, vivez libre</p>
            </div>

            <h2>Connexion</h2>

            <?php if (isset($error_message)): ?>
                <div class="error-message">
                    <i class="fas fa-exclamation-circle"></i> <?= $error_message ?>
                </div>
            <?php endif; ?>

            <form action="login.php" method="POST">
                <div class="form-group">
                    <label for="email">Adresse e-mail</label>
                    <input type="email" id="email" name="email" placeholder="votre@email.com" required>
                    <i class="fas fa-envelope"></i>
                </div>

                <div class="form-group">
                    <label for="password">Mot de passe</label>
                    <input type="password" id="password" name="password" placeholder="Votre mot de passe" required>
                    <i class="fas fa-lock"></i>
                </div>

                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-sign-in-alt"></i> Se connecter
                </button>
            </form>

            <div class="links">
                <a href="forgot-password.php">
                    <i class="fas fa-key"></i> Mot de passe oublié ?
                </a>
                <a href="inscription.php">
                    <i class="fas fa-user-plus"></i> Créer un compte
                </a>
            </div>

            <div class="back-button">
                <a href="../index.php">
                    <i class="fas fa-arrow-left"></i> Retour à l'accueil
                </a>
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