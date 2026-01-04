<?php
require_once("../data_base/connection.php");
session_start();

$sDebutHtml = <<<EOT
<!DOCTYPE html>
<html lang="fr">
    <head>
        <meta charset="utf-8">
        <title>Inscription</title>
        <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&family=Fredoka+One&display=swap" rel="stylesheet">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
        <link rel="stylesheet" href="../styles/auth.css">
    </head>
    <body>
EOT;

$sBody = <<<EOT
        <div class="auth-container">
            <div class="logo">
                <span>Tripster</span>
                <p>Voyagez jeune, vivez libre</p>
            </div>
            <h2>Inscription</h2>
            
            <form class="auth-form" action="inscription.php" method="POST">
                <table>
                    <tr>
                        <td>Nom :</td>
                        <td><input type="text" name="nom" placeholder="Nom" required></td>
                    </tr>
                    <tr>
                        <td>Prénom :</td>
                        <td><input type="text" name="prenom" placeholder="Prenom" required></td>
                    </tr>
                    <tr>
                        <td>Sexe :</td>
                        <td>
                            <input type="radio" name="gender" value="M" required> M
                            <input type="radio" name="gender" value="F" required> F
                            <input type="radio" name="gender" value="Autre" required> Autre
                        </td>
                    </tr>
                    <tr>
                        <td>Email :</td>
                        <td><input type="email" name="email" placeholder="Adresse e-mail" required></td>
                    </tr>
                    <tr>
                        <td>Password :</td>
                        <td><input type="password" name="password" placeholder="Mot de passe" required></td>
                    </tr>
                    <tr>
                        <td>Confirmer Password :</td>
                        <td><input type="password" name="password2" placeholder="Confirmer le mot de passe" required></td>
                    </tr>
                    <tr>
                        <td>Code Master :</td>
                        <td><input type="text" name="access_level" placeholder="(Admin)"></td>
                    </tr>
                    <tr>
                        <td colspan="2">
                            <input type="submit" name="validation" value="Inscrire">
                            <input type="reset" name="reset" value="Effacer">
                        </td>
                    </tr>
                </table>
                
                <div class="auth-links">
                    <a href="../index.php" class="auth-link">
                    <i class="fas fa-arrow-left"></i> Retour à l'accueil
                    </a>
                    <a href="login.php" class="auth-link">
                    <i class="fas fa-sign-in-alt"></i> Déjà un compte ? Connectez-vous
                    </a>
                </div>
            </form>
        </div>
EOT;

$sFinalHtml = <<<EOT
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
            document.addEventListener('DOMContentLoaded', function() 
            {
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
EOT;

if (!empty($_POST['email']) && !empty($_POST['password']) && isset($_POST['access_level'])) {
    if ($_POST['password'] == $_POST['password2']) 
    {
        try 
        {
            $codeMaster = $_POST['access_level'];
            $access_level = ($codeMaster === 'HELB') ? 'admin' : 'user';

            $sQuery = 'SELECT id FROM users WHERE email=:email';
            $stmt = $dbh->prepare($sQuery);
            $stmt->bindValue(':email', $_POST['email']);
            $stmt->execute();
            $nbrResultat = $stmt->rowCount();

            if ($nbrResultat >= 1)
            {
                $sBody .= '<div class="alert alert-danger">Veuillez utiliser un autre email.</div>';
                $status = 'failure';
            } 
            else 
            {
                $sQuery2 = "INSERT INTO users (nom, prenom, gender, email, password, access_level) VALUES (:nom, :prenom, :sexe, :email, :password, :access_level)";
                $stmt2 = $dbh->prepare($sQuery2);
                $stmt2->bindValue(':nom', $_POST['nom']);
                $stmt2->bindValue(':prenom', $_POST['prenom']);
                $stmt2->bindValue(':sexe', $_POST['gender']);
                $stmt2->bindValue(':email', $_POST['email']);
                $stmt2->bindValue(':password', $_POST['password']);
                $stmt2->bindValue(':access_level', $access_level);

                if ($stmt2->execute())
                {
                    $status = 'success';
                    $_SESSION['email'] = $_POST['email'];
                    $_SESSION['access_level'] = $access_level;

                    if ($access_level === 'admin') 
                    {
                        header('Location: ../admin.php');
                    } 
                    else 
                    {
                        header('Location: ../profile.php');
                    }
                } 
                else 
                {
                    $sBody .= '<div class="alert alert-danger">Échec de l\'inscription.</div>';
                    $status = 'failure';
                }
            }
        } 
        catch (PDOException $e)
        {
            $sBody .= '<div class="alert alert-danger">Erreur PDO: ' . $e->getMessage() . '</div>';
            $status = 'failure';
        }
    }
    else 
    {
        $sBody .= '<div class="alert alert-danger">Les mots de passe ne correspondent pas.</div>';
        $status = 'failure';
    }

    $file_name = 'insc.log';
    $time = date('d/m/y H:i:s:u');
    $log = $time . ' ' . $_POST['email'] . ' => ' . $status . ' ' . $_SERVER['REMOTE_ADDR'] . "\n";
    file_put_contents($file_name, $log, FILE_APPEND | LOCK_EX);
}

echo $sDebutHtml . $sBody . $sFinalHtml;