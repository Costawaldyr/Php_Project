<?php
require_once('../data_base/connection.php');
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tripster - Paiement flexible</title>
    
    <!-- Intégration des polices et icônes -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&family=Fredoka+One&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../styles/style.css">
    <style>
        /* Styles spécifiques à la page de paiement */
        .payment-hero {
            background: linear-gradient(135deg, var(--dark), #3a3468);
            color: white;
            padding: 5rem 0;
            text-align: center;
        }
        
        .payment-hero h1 {
            font-size: 2.5rem;
            margin-bottom: 1rem;
        }
        
        .payment-hero h1 span {
            color: var(--accent);
        }
        
        .payment-hero p {
            font-size: 1.2rem;
            max-width: 600px;
            margin: 0 auto 2rem;
        }

        /* Paiement flexible */
        .payment-details-section {
            padding: 5rem 0;
        }

        .payment-content {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 3rem;
            align-items: center;
        }

        .payment-steps {
            display: grid;
            gap: 2rem;
            margin-top: 2rem;
        }

        .step {
            display: flex;
            gap: 1.5rem;
            align-items: flex-start;
        }

        .step-number {
            width: 40px;
            height: 40px;
            background: var(--primary);
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            flex-shrink: 0;
        }

        .payment-illustration {
            position: relative;
            border-radius: 15px;
            overflow: hidden;
        }

        .payment-illustration img {
            width: 100%;
            border-radius: 15px;
            box-shadow: var(--shadow);
        }

        .payment-features {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            background: rgba(255,255,255,0.9);
            padding: 1.5rem;
        }

        .payment-features .feature {
            display: flex;
            align-items: center;
            margin-bottom: 0.8rem;
        }

        .payment-features .feature i {
            color: var(--primary);
            margin-right: 0.8rem;
        }

    </style>
</head>
<body>
    
    <!-- Hero Section -->
    <section class="payment-hero">
        <div class="container">
            <h1><span>Paiement flexible</span></h1>
            <p>Voyagez maintenant, payez plus tard avec nos solutions adaptées aux budgets jeunes</p>
            <a href="#payment-content" class="btn btn-primary">
                <i class="fas fa-arrow-down"></i> Découvrir
            </a>
        </div>
    </section>

    <!-- Contenu principal -->
    <main class="container" id="payment-content">
        <section class="payment-details-section">
            <div class="section-header">
                <h2 class="section-title"><span>Paiement en plusieurs fois</span></h2>
            </div>
            
            <div class="payment-content">
                <div class="payment-info">
                    <div class="feature-card">
                        <div class="feature-icon" style="background-color: var(--primary);">
                            <i class="fas fa-money-bill-wave"></i>
                        </div>
                        <h3>Voyagez maintenant, payez plus tard</h3>
                        <p>Chez Tripster, nous comprenons que voyager est une priorité, même quand le budget est serré. C'est pourquoi nous proposons des solutions de paiement flexibles pour vous permettre de concrétiser vos rêves sans attendre.</p>
                    </div>
                    
                    <div class="payment-steps">
                        <div class="step">
                            <div class="step-number">1</div>
                            <h3>Réservez votre voyage</h3>
                            <p>Choisissez votre destination et validez votre réservation avec un premier paiement.</p>
                        </div>
                        
                        <div class="step">
                            <div class="step-number">2</div>
                            <h3>Échelonnez vos paiements</h3>
                            <p>Le montant restant sera divisé en plusieurs échéances sans frais supplémentaires.</p>
                        </div>
                        
                        <div class="step">
                            <div class="step-number">3</div>
                            <h3>Partez l'esprit léger</h3>
                            <p>Profitez pleinement de votre voyage pendant que nous gérons les paiements pour vous.</p>
                        </div>
                    </div>
                </div>
                
                <div class="payment-illustration">
                    <img src="../img/payment-illustration.png" alt="Paiement flexible" class="animate-img">
                    <div class="payment-features">
                        <div class="feature">
                            <i class="fas fa-check-circle"></i>
                            <span>Jusqu'à 4 paiements sans frais</span>
                        </div>
                        <div class="feature">
                            <i class="fas fa-check-circle"></i>
                            <span>Aucun intérêt</span>
                        </div>
                        <div class="feature">
                            <i class="fas fa-check-circle"></i>
                            <span>Processus 100% sécurisé</span>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Section FAQ -->
        <section class="faq-section">
            <h2 class="section-title"><span>Questions fréquentes</span></h2>
            
            <div class="faq-container">
                <div class="faq-item">
                    <button class="faq-question">Comment fonctionne le paiement en plusieurs fois ?</button>
                    <div class="faq-answer">
                        <p>Lors de votre réservation, vous payez un premier acompte (généralement 25% du total). Le solde est ensuite divisé en plusieurs paiements égaux, prélevés automatiquement à intervalle régulier avant votre départ.</p>
                    </div>
                </div>
                
                <div class="faq-item">
                    <button class="faq-question">Y a-t-il des frais supplémentaires ?</button>
                    <div class="faq-answer">
                        <p>Non, Tripster ne facture aucun frais supplémentaire pour le paiement en plusieurs fois. Vous payez exactement le même prix qu'un paiement en une seule fois.</p>
                    </div>
                </div>
                
                <div class="faq-item">
                    <button class="faq-question">Puis-je modifier mes dates de paiement ?</button>
                    <div class="faq-answer">
                        <p>Les dates de paiement sont fixes une fois le plan choisi. Cependant, en cas de difficulté, vous pouvez nous contacter pour étudier une solution adaptée.</p>
                    </div>
                </div>
            </div>
            <script>
                // FAQ Accordion
                document.querySelectorAll('.faq-question').forEach(question => {
                    question.addEventListener('click', () => {
                        const answer = question.nextElementSibling;
                        const isActive = question.classList.contains('active');
                        
                        // Fermer toutes les autres réponses
                        document.querySelectorAll('.faq-question').forEach(q => {
                            if (q !== question) {
                                q.classList.remove('active');
                                q.nextElementSibling.style.maxHeight = null;
                            }
                        });
                        
                        // Basculer l'état actuel
                        if (isActive) {
                            question.classList.remove('active');
                            answer.style.maxHeight = null;
                        } else {
                            question.classList.add('active');
                            answer.style.maxHeight = answer.scrollHeight + 'px';
                        }
                    });
                });
            </script>
        </section>

        <!-- CTA Section -->
        <section class="cta-section">
            <div class="cta-content">
                <h2>Prêt à voyager avec un paiement flexible ?</h2>
                <p>Découvrez nos offres et réservez dès maintenant avec un premier paiement réduit.</p>
                <a href="../pages/offre.php" class="btn btn-secondary">
                    <i class="fas fa-suitcase"></i> Voir les offres
                </a>
            </div>
        </section>
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
</body>
</html>