<?php
require_once('../data_base/connection.php');
session_start();

$success_message = '';
$error_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['prenom'], $_POST['nom'], $_POST['email'], $_POST['telephone'], $_POST['message'])) {
        try 
        {
            $sQuery = "INSERT INTO contact_support (prenom, nom, email, telephone, message) VALUES (:prenom, :nom, :email, :telephone, :message)";
            $stmt = $dbh->prepare($sQuery);
            $stmt->bindValue(':prenom', $_POST['prenom']);
            $stmt->bindValue(':nom', $_POST['nom']);
            $stmt->bindValue(':email', $_POST['email']);
            $stmt->bindValue(':telephone', $_POST['telephone']);
            $stmt->bindValue(':message', $_POST['message']);
            $stmt->execute();
            $success_message = "Votre message a été envoyé avec succès.";

            $subject = "Merci pour votre message!";
            $body = "Bonjour " . $_POST['prenom'] . ",\n\nNous avons bien reçu votre message et nous vous répondrons bientôt.\n\nMerci !";
            $headers = "From: noreply@tripster.com";

            mail($_POST['email'], $subject, $body, $headers);
        } 
        catch (PDOException $e)
        {
            $error_message = "Erreur lors de l'envoi du message : " . $e->getMessage();
        }
    } 
    else 
    {
        $error_message = "Tous les champs sont requis.";
    }
}
?>


<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    
    <link rel="stylesheet" href="../styles/offres.css">
    <link rel="stylesheet" href="../styles/pages.css" >
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&family=Fredoka+One&display=swap" rel="stylesheet">
    
</head>
<body>
    <header class="bg-primary text-white text-center py-3">
        <h1>Contact</h1>
        <p>Vous pouvez nous contacter en remplissant le formulaire ci-dessous.</p>
    </header>

    <main class="container mt-5">
        <!-- Formulaire de contact -->
        <div class="contact-container">
        <p>Tripster est representé par des equipes creatives.</p>
        <p>E-mail : <a href="mailto:support@tripster.prive.be">support@tripster.prive.be</a></p>
        <p>Téléphone : +32 2 643 23 23</p>
            <div class="contact-form">
                <?php if (!empty($success_message)): ?>
                    <div class="alert alert-success"><?= $success_message ?></div>
                <?php elseif (!empty($error_message)): ?>
                    <div class="alert alert-danger"><?= $error_message ?></div>
                <?php endif; ?>
                <form action="contact.php" method="POST">
                    <div class="form-group">
                        <label for="nom">Nom</label>
                        <input type="text" class="form-control" id="nom" name="nom" required>
                    </div>
                    <div class="form-group">
                        <label for="prenom">Prénom</label>
                        <input type="text" class="form-control" id="prenom" name="prenom" required>
                    </div>
                    <div class="form-group">
                        <label for="email">E-mail</label>
                        <input type="email" class="form-control" id="email" name="email" required>
                    </div>
                    <div class="form-group">
                        <label for="telephone">Téléphone</label>
                        <input type="tel" class="form-control" id="telephone" name="telephone" required pattern="^\+32\s?[1-9][0-9\s\-]{7,12}$" placeholder="+32 4XX XX XX XX">
                    </div>
                    <div class="form-group">
                        <label for="message">Message</label>
                        <textarea class="form-control" id="message" name="message" placeholder="Laissez-nous un message..." rows="5" required></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary">Envoyer</button>
                </form>
                <!-- Publicité  -->
                <div class="advertisement mt-4">
                    <h2>Publicité</h2>
                    <p>Découvrez nos offres spéciales!</p>
                    <img id="slideshow" src="../img/IMG_2948.AVIF" alt="Publicité" class="img-fluid">
                </div>
            </div>
        </div>
        <div class="back-button mt-4">
            <a href="../index.php" class="btn btn-secondary">← Retour à l'accueil</a>
        </div>
    </main>

   
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
                    <li><a href="offres.php">Offres spéciales</a></li>
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

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Animation au chargement
        document.addEventListener('DOMContentLoaded', function() {
            const container = document.querySelector('.detail-container');
            if (container) {
                setTimeout(() => {
                    container.style.opacity = 1;
                    container.style.transform = 'translateY(0)';
                }, 100);
            }
        });
    </script>
    
</body>
</html>