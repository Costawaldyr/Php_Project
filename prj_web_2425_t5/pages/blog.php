<?php
session_start();
require_once('../data_base/connection.php');

// Récupérer toutes les destinations avec leurs images
$stmt = $dbh->query("
    SELECT d.*, 
           (SELECT GROUP_CONCAT(image_path) FROM destination_images WHERE destination_id = d.id) as images
    FROM destinations d
    ORDER BY d.date_publication DESC
");
$destinations = $stmt->fetchAll();

// Récupérer les 5 derniers commentaires avec les noms des destinations
$stmt = $dbh->query("
    SELECT c.*, d.ville, d.pays 
    FROM commentaires c
    JOIN destinations d ON c.destination_id = d.id
    ORDER BY c.date_commentaire DESC 
    LIMIT 5
");
$commentaires = $stmt->fetchAll();

// Récupérer 3 destinations aléatoires pour la section "Découvrez aussi"
$stmt = $dbh->query("
    SELECT id, ville, pays, type_activites 
    FROM destinations 
    ORDER BY RAND() 
    LIMIT 3
");
$decouvrez_aussi = $stmt->fetchAll();

$stmt = $dbh->prepare("SELECT * FROM offres WHERE id = ?");
$stmt->execute([1]);
$offres = $stmt->fetch();



// Gérer l'ajout d'un commentaire
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ajouter_commentaire'])) {
    $destination_id = $_POST['destination_id'];
    $nom_utilisateur = htmlspecialchars(trim($_POST['nom_utilisateur']));
    $commentaire = htmlspecialchars(trim($_POST['commentaire']));
    $note = (int)$_POST['note'];

    // Validation
    if (empty($nom_utilisateur) || empty($commentaire) || $note < 1 || $note > 5) {
        $message_erreur = "Veuillez remplir tous les champs correctement.";
    } else {
        $stmt = $dbh->prepare("
            INSERT INTO commentaires 
            (destination_id, nom_utilisateur, commentaire, note, date_commentaire) 
            VALUES (?, ?, ?, ?, NOW())
        ");
        $stmt->execute([$destination_id, $nom_utilisateur, $commentaire, $note]);

        header("Location: blog.php#commentaires");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tripster Blog - Conseils & Récits de Voyage pour Jeunes</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&family=Playfair+Display:wght@700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../styles/offres.css">
    <style>
        /* Styles spécifiques au blog */
        .hero-blog {
            background: linear-gradient(rgba(46, 41, 78, 0.8), rgba(46, 41, 78, 0.8)), 
                        url('https://images.unsplash.com/photo-1520250497591-112f2f40a3f4?ixlib=rb-1.2.1&auto=format&fit=crop&w=1350&q=80') center/cover no-repeat;
            height: 500px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            text-align: center;
            position: relative;
        }
        
        .blog-search {
            max-width: 600px;
            margin: 0 auto;
            position: relative;
        }
        
        .blog-card {
            background: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            transition: transform 0.3s, box-shadow 0.3s;
            height: 100%;
        }
        
        .blog-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 30px rgba(0,0,0,0.15);
        }
        
        .blog-card-img {
            height: 250px;
            overflow: hidden;
        }
        
        .blog-card-img img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.5s;
        }
        
        .blog-card:hover .blog-card-img img {
            transform: scale(1.1);
        }
        
        .blog-card-body {
            padding: 1.5rem;
        }
        
        .blog-card-meta {
            display: flex;
            justify-content: space-between;
            margin-bottom: 1rem;
            font-size: 0.9rem;
            color: #666;
        }
        
        .blog-card-tag {
            display: inline-block;
            padding: 0.3rem 0.8rem;
            background: var(--accent);
            color: var(--dark);
            border-radius: 50px;
            font-size: 0.8rem;
            font-weight: 600;
            margin-bottom: 1rem;
        }
        
        .comment-card {
            background: white;
            border-radius: 10px;
            padding: 1.5rem;
            box-shadow: 0 3px 10px rgba(0,0,0,0.05);
            margin-bottom: 1.5rem;
        }
        
        .comment-header {
            display: flex;
            align-items: center;
            margin-bottom: 1rem;
        }
        
        .comment-avatar {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background: var(--secondary);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 1.2rem;
            margin-right: 1rem;
        }
        
        .comment-rating {
            color: var(--accent);
            margin-left: auto;
        }
        
        .pub-card {
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 3px 10px rgba(0,0,0,0.1);
            margin-bottom: 2rem;
            transition: transform 0.3s;
        }
        
        .pub-card:hover {
            transform: translateY(-5px);
        }
        
        .pub-img {
            height: 150px;
            overflow: hidden;
        }
        
        .pub-img img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        .pub-body {
            padding: 1rem;
            text-align: center;
        }
        
        .pub-tag {
            display: inline-block;
            background: var(--primary);
            color: white;
            padding: 0.2rem 0.8rem;
            border-radius: 50px;
            font-size: 0.7rem;
            margin-bottom: 0.5rem;
        }
        
        .star-rating {
            color: var(--accent);
            margin-bottom: 0.5rem;
        }
        
        .star-rating .far {
            color: #ddd;
        }
    </style>
</head>
<body>
    <!-- Hero Section -->
    <section class="hero-blog">
        <div class="container">
            <h1 class="display-3 mb-4">Le Blog Tripster</h1>
            <p class="lead mb-5">Conseils, récits et astuces de voyage par et pour les jeunes</p>
            
            <div class="blog-search">
                <div class="input-group">
                    <input type="text" class="form-control form-control-lg" placeholder="Rechercher des articles...">
                    <button class="btn btn-primary" type="button">
                        <i class="fas fa-search"></i>
                    </button>
                </div>
            </div>
        </div>
    </section>


    <!-- Contenu Principal -->
    <main class="container my-5">

    <a href="../index.php" class="btn btn-outline-primary">Retour</a>
        <div class="row">
            <!-- Colonne principale -->
            <div class="col-lg-8">
                <!-- Section Articles -->
                <section class="mb-5">
                    <h2 class="mb-4">Nos Derniers Articles</h2>
                    
                    <?php foreach ($destinations as $destination): ?>
                        <article class="blog-card mb-4">
                            <div class="blog-card-img">
                                <?php if ($destination['images']): ?>
                                    <img src="<?= explode(',', $destination['images'])[0] ?>" alt="<?= htmlspecialchars($destination['ville']) ?>">
                                <?php else: ?>
                                    <img src="https://via.placeholder.com/800x500?text=Tripster" alt="Image par défaut">
                                <?php endif; ?>
                            </div>
                            <div class="blog-card-body">
                                <span class="blog-card-tag"><?= htmlspecialchars($destination['type_activites']) ?></span>
                                <h3><?= htmlspecialchars($destination['ville']) ?>, <?= htmlspecialchars($destination['pays']) ?></h3>
                                <div class="blog-card-meta">
                                    <span><i class="far fa-calendar-alt"></i> <?= date('d/m/Y', strtotime($destination['date_publication'])) ?></span>
                                    <span><i class="far fa-comment"></i> <?= rand(3, 15) ?> commentaires</span>
                                </div>
                                <p><?= htmlspecialchars(substr($destination['conseils'], 0, 200)) ?>...</p>
                                <a href="#destination-<?= $destination['id'] ?>" class="btn btn-outline-primary">Lire la suite</a>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </section>
                
                <!-- Section Détails des Destinations -->
                <section id="destinations" class="my-5">
                    <h2 class="mb-4">Découvrez nos Destinations</h2>
                    
                    <?php foreach ($destinations as $destination): ?>
                        <div id="destination-<?= $destination['id'] ?>" class="mb-5 p-4 bg-white rounded shadow-sm">
                            <h3 class="mb-3"><?= htmlspecialchars($destination['ville']) ?>, <?= htmlspecialchars($destination['pays']) ?></h3>
                            
                            <div class="row mb-4">
                                <?php if ($destination['images']): ?>
                                    <?php $images = explode(',', $destination['images']); ?>
                                    <div class="col-md-6">
                                        <img src="<?= htmlspecialchars($images[0]) ?>" class="img-fluid rounded mb-3" alt="<?= htmlspecialchars($destination['ville']) ?>">
                                    </div>
                                    <?php if (count($images) > 1): ?>
                                        <div class="col-md-6">
                                            <div class="row">
                                                <?php for ($i = 1; $i < min(4, count($images)); $i++): ?>
                                                    <div class="col-6 mb-3">
                                                        <img src="<?= htmlspecialchars($images[$i]) ?>" class="img-fluid rounded" alt="<?= htmlspecialchars($destination['ville']) ?>">
                                                    </div>
                                                <?php endfor; ?>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <h4 class="mb-3"><i class="fas fa-info-circle text-primary"></i> Informations</h4>
                                    <ul class="list-unstyled">
                                        <li class="mb-2"><strong>Activité :</strong> <?= htmlspecialchars($destination['type_activites']) ?></li>
                                        <li class="mb-2"><strong>Budget moyen :</strong> <?= htmlspecialchars($destination['budget_moyen']) ?> €/jour</li>
                                        <li class="mb-2"><strong>Langue :</strong> <?= htmlspecialchars($destination['langue']) ?></li>
                                        <li class="mb-2"><strong>Monnaie :</strong> <?= htmlspecialchars($destination['monnaie']) ?></li>
                                        <li class="mb-2"><strong>Transport :</strong> <?= htmlspecialchars($destination['transport_commun']) ?></li>
                                    </ul>
                                </div>
                                <div class="col-md-6">
                                    <h4 class="mb-3"><i class="fas fa-map-marked-alt text-primary"></i> À visiter</h4>
                                    <p><?= htmlspecialchars($destination['endroits_visiter']) ?></p>
                                    
                                    <h4 class="mb-3 mt-4"><i class="fas fa-users text-primary"></i> Culture</h4>
                                    <p><?= htmlspecialchars($destination['peuples_culture']) ?></p>
                                </div>
                            </div>
                            
                            <div class="mt-4">
                                <h4 class="mb-3"><i class="fas fa-lightbulb text-primary"></i> Conseils pratiques</h4>
                                <p><?= htmlspecialchars($destination['conseils']) ?></p>
                            </div>
                            
                            <!-- Publicité intégrée -->
                            <div class="pub-card mt-4">
                                <div class="pub-img">
                                    <img src="https://via.placeholder.com/800x200?text=Publicit%C3%A9+Tripster" alt="Publicité">
                                </div>
                                <div class="pub-body">
                                    <span class="pub-tag">SPONSORISÉ</span>
                                    <h5>Voyagez malin avec Tripster</h5>
                                    <p>Profitez de -30% sur votre première réservation avec le code BLOG30</p>
                                    <a href= "offre.php" class="btn btn-sm btn-primary">Voir l'offre</a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </section>
                
                <!-- Section Commentaires -->
                <section id="commentaires" class="my-5">
                    <h2 class="mb-4">Derniers Témoignages</h2>
                    
                    <?php foreach ($commentaires as $commentaire): ?>
                        <div class="comment-card">
                            <div class="comment-header">
                                <div class="comment-avatar"><?= substr($commentaire['nom_utilisateur'], 0, 1) ?></div>
                                <div>
                                    <h5 class="mb-0"><?= htmlspecialchars($commentaire['nom_utilisateur']) ?></h5>
                                    <small class="text-muted"><?= date('d/m/Y', strtotime($commentaire['date_commentaire'])) ?></small>
                                </div>
                                <div class="comment-rating">
                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                        <?php if ($i <= $commentaire['note']): ?>
                                            <i class="fas fa-star"></i>
                                        <?php else: ?>
                                            <i class="far fa-star"></i>
                                        <?php endif; ?>
                                    <?php endfor; ?>
                                </div>
                            </div>
                            <p><?= htmlspecialchars($commentaire['commentaire']) ?></p>
                            <small class="text-muted">À propos de <?= htmlspecialchars($commentaire['ville']) ?>, <?= htmlspecialchars($commentaire['pays']) ?></small>
                        </div>
                    <?php endforeach; ?>
                    
                    <!-- Formulaire d'ajout de commentaire -->
                    <div class="bg-white p-4 rounded shadow-sm mt-5">
                        <h3 class="mb-4">Ajouter un commentaire</h3>
                        
                        <?php if (isset($message_erreur)): ?>
                            <div class="alert alert-danger"><?= $message_erreur ?></div>
                        <?php endif; ?>
                        
                        <form method="POST" action="">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="nom_utilisateur" class="form-label">Votre nom</label>
                                    <input type="text" class="form-control" id="nom_utilisateur" name="nom_utilisateur" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="destination_id" class="form-label">Destination</label>
                                    <select class="form-select" id="destination_id" name="destination_id" required>
                                        <?php foreach ($destinations as $destination): ?>
                                            <option value="<?= $destination['id'] ?>"><?= htmlspecialchars($destination['ville']) ?>, <?= htmlspecialchars($destination['pays']) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="note" class="form-label">Note (1-5 étoiles)</label>
                                <select class="form-select" id="note" name="note" required>
                                    <option value="1">★☆☆☆☆ - 1 étoile</option>
                                    <option value="2">★★☆☆☆ - 2 étoiles</option>
                                    <option value="3" selected>★★★☆☆ - 3 étoiles</option>
                                    <option value="4">★★★★☆ - 4 étoiles</option>
                                    <option value="5">★★★★★ - 5 étoiles</option>
                                </select>
                            </div>
                            
                            <div class="mb-3">
                                <label for="commentaire" class="form-label">Votre commentaire</label>
                                <textarea class="form-control" id="commentaire" name="commentaire" rows="4" required></textarea>
                            </div>
                            
                            <button type="submit" name="ajouter_commentaire" class="btn btn-primary">
                                <i class="fas fa-paper-plane"></i> Publier le commentaire
                            </button>
                        </form>
                    </div>
                </section>
            </div>
            
            <!-- Sidebar -->
            <div class="col-lg-4">
                <!-- Widget: Découvrez aussi -->
                <div class="bg-white p-4 rounded shadow-sm mb-4">
                    <h3 class="mb-4">Découvrez aussi</h3>
                    
                    <?php foreach ($decouvrez_aussi as $destination): ?>
                        <div class="d-flex mb-3">
                            <img src="https://via.placeholder.com/100x70?text=<?= urlencode($destination['ville']) ?>" 
                                 alt="<?= htmlspecialchars($destination['ville']) ?>" 
                                 class="rounded me-3" style="width: 100px; height: 70px; object-fit: cover;">
                            <div>
                                <h5 class="mb-1"><?= htmlspecialchars($destination['ville']) ?></h5>
                                <p class="text-muted small mb-1"><?= htmlspecialchars($destination['type_activites']) ?></p>
                                <a href="#destination-<?= $destination['id'] ?>" class="btn btn-sm btn-outline-primary">Voir</a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                
                <!-- Widget: Publicité -->
                <div class="pub-card mb-4">
                    <div class="pub-img">
                        <img src="https://via.placeholder.com/400x200?text=Publicit%C3%A9" alt="Publicité">
                    </div>
                    <div class="pub-body">
                        <span class="pub-tag">PARTENAIRE</span>
                        <h5>Voyagez avec nos partenaires</h5>
                        <p>Des réductions exclusives pour nos lecteurs</p>
                        <a href="#" class="btn btn-sm btn-primary">Découvrir</a>
                    </div>
                </div>
                
                <!-- Widget: Newsletter -->
                <div class="bg-white p-4 rounded shadow-sm mb-4">
                    <h3 class="mb-3">Newsletter</h3>
                    <p class="text-muted small mb-3">Recevez nos conseils et offres exclusives</p>
                    <form>
                        <div class="mb-3">
                            <input type="email" class="form-control" placeholder="Votre email">
                        </div>
                        <button type="submit" class="btn btn-primary w-100">S'abonner</button>
                    </form>
                </div>
                
                <!-- Widget: Top Destinations -->
                <div class="bg-white p-4 rounded shadow-sm">
                    <h3 class="mb-3">Destinations populaires</h3>
                    <ul class="list-unstyled">
                        <li class="mb-2"><a href="#" class="text-decoration-none">Bali, Indonésie</a></li>
                        <li class="mb-2"><a href="#" class="text-decoration-none">Bangkok, Thaïlande</a></li>
                        <li class="mb-2"><a href="#" class="text-decoration-none">Barcelone, Espagne</a></li>
                        <li class="mb-2"><a href="#" class="text-decoration-none">Berlin, Allemagne</a></li>
                        <li class="mb-2"><a href="#" class="text-decoration-none">Lisbonne, Portugal</a></li>
                    </ul>
                </div>
            </div>
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
                    <li><a href="blog.php" class="text-white">Blog voyage</a></li>
                    <li><a href="forum.php" class="text-white">Forum</a></li>
                    <li><a href="#" class="text-white">Destinations tendances</a></li>
                    <li><a href="#" class="text-white">Itinéraires</a></li>
                </ul>
            </div>
            
            <div class="footer-section">
                <h3>Aide</h3>
                <ul>
                    <li><a href="contact.php" class="text-white">Contactez-nous</a></li>
                    <li><a href="#" class="text-white">FAQ</a></li>
                    <li><a href="#" class="text-white">Conditions générales</a></li>
                    <li><a href="#" class="text-white">Politique de confidentialité</a></li>
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
        // Animation des cartes au défilement
        document.addEventListener('DOMContentLoaded', function() {
            const cards = document.querySelectorAll('.blog-card, .pub-card');
            
            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.style.opacity = 1;
                        entry.target.style.transform = 'translateY(0)';
                        observer.unobserve(entry.target);
                    }
                });
            }, { threshold: 0.1 });
            
            cards.forEach(card => {
                card.style.opacity = 0;
                card.style.transform = 'translateY(20px)';
                card.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
                observer.observe(card);
            });
        });
    </script>
</body>
</html>
