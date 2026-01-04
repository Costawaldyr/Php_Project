<?php
require_once('data_base/connection.php');
session_start();

setcookie("user", "value", time() + 1800, "/"); 

// Récupération des offres
try 
{
    $sSQL = 'SELECT * FROM offres LIMIT 6';
    $stmt = $dbh->prepare($sSQL);
    $stmt->execute();
    $offres = $stmt->fetchAll(PDO::FETCH_ASSOC);
} 
catch(PDOException $e) 
{
    die('Erreur lors de l/execution de la requete :').$e->getMessage();
}

// Fonction pour vérifier si une offre est en favoris
function estFavori($dbh, $user_id, $offre_id) 
{
    $sSQL1 = "SELECT id FROM favoris WHERE user_id = :user_id AND offre_id = :offre_id";
    $stmt1 = $dbh->prepare($sSQL1);

    $stmt1-> bindValue(':user_id', $user_id);
    $stmt1-> bindValue(':offre_id', $offre_id);

    $stmt1->execute();

    $favori = $stmt1->fetch(PDO::FETCH_ASSOC);
    return $favori !== false;
}

// Gestion des favoris
if(isset($_POST['action_favori'])) 
{
    if(!isset($_SESSION['user_id']))
    {
        echo 'Veuillez vous connecter pour ajouter aux favoris';
    }

    $offre_id = $_SESSION['offre_id'];
    $user_id = $_SESSION['user_id'];
    $date_ajout = $_SESSION['date_ajout'];

    try
    {
        if($_POST['action_favori'] === 'ajouter') 
        {
            $sSQL3 = "INSERT INTO favoris (user_id, offre_id, date_ajout) VALUES(:user_id, :offre_id, :date_ajout)";
            $stmt3 = $dbh->prepare($sSQL3);
    
            $stmt3->bindParam(':user_id',$user_id);
            $stmt3->bindParam(':offre_id',$offre_id);
            $stmt3->bindParam(':date_ajout',$date_ajout);
    
            $stmt3->execute();
        }
        elseif($_POST['action_favori'] === 'retirer') 
        {
            $sSQL4 = "DELETE FROM favoris WHERE user_id = :user_id AND offre_id = :offre_id";
            $stmt4 = $dbh->prepare($sSQL4);
    
            $stmt4->bindParam(':user_id',$user_id);
            $stmt4->bindParam(':offre_id',$offre_id);
    
            $stmt4->execute();
        }
    }
    catch (PDOException $e) 
    {
        die('Erreur lors de l/execution de la requete :').$e->getMessage();
    }
} 
?>

<!DOCTYPE html>
<html lang="fr">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Tripster - Voyages jeunes et abordables</title>
        
        <link rel="stylesheet" href="styles/style.css">
        <script src="styles/script.js" defer></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
        <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&family=Fredoka+One&display=swap" rel="stylesheet">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
        
        <link rel="preload" href="styles/style.css" as="style">
        <link rel="preload" href="styles/script.js" as="script">
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <style>
            :root {
                --primary: #FF5E5B;
                --secondary: #00CECB;
                --accent: #FFED66;
                --dark: #2E294E;
                --light: #F7F7FF;
                --action: #5D5FEF;
            }
            body {
                font-family: 'Poppins', sans-serif;
                background-color: var(--light);
                color: var(--dark);
                margin: 0;
                padding: 0;
                overflow-x: hidden;
            }
            .loading {
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background: var(--light);
                display: flex;
                justify-content: center;
                align-items: center;
                z-index: 9999;
            }
            .loader {
                width: 48px;
                height: 48px;
                border: 3px solid var(--primary);
                border-radius: 50%;
                display: inline-block;
                position: relative;
                box-sizing: border-box;
                animation: rotation 1s linear infinite;
            }
            .loader::after {
                content: '';  
                box-sizing: border-box;
                position: absolute;
                left: 50%;
                top: 50%;
                transform: translate(-50%, -50%);
                width: 56px;
                height: 56px;
                border-radius: 50%;
                border: 3px solid transparent;
                border-bottom-color: var(--secondary);
            }
            @keyframes rotation {
                0% { transform: rotate(0deg); }
                100% { transform: rotate(360deg); }
            }
        </style>
    </head>
    <body>
        <!-- Pré-chargement -->
        <div class="loading">
            <div class="loader"></div>
        </div>

        <!-- Header dynamique -->
        <header class="hero">
            <div class="hero-image" style="background-image: url('img/IMG_2935.AVIF');"></div>
            <nav class="navbar">
                <div class="navbar-container">
                    <div class="logo">
                        <span>Tripster</span>
                        <div class="logo-icon"></div>
                    </div>
                    
                    <div class="menu-toggle" id="mobile-menu">
                        <span class="bar"></span>
                        <span class="bar"></span>
                        <span class="bar"></span>
                    </div>

                    <ul class="nav-menu">
                        <li><a href="pages/offre.php" class="nav-link no-ajax"><i class="fas fa-tag"></i> Offres</a></li>
                        <li><a href="pages/blog.php" class="nav-link no-ajax"><i class="fas fa-blog"></i> Blog</a></li>
                        <li><a href="pages/contact.php" class="nav-link no-ajax"><i class="fas fa-envelope"></i> Contact</a></li>
                        <li><a href="pages/about.php" class="nav-link no-ajax"><i class="fas fa-info-circle"></i> À propos</a></li>
                        <li><a href="pages/forum.php" class="nav-link no-ajax"><i class="fas fa-comments"></i> Forum</a></li>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="currencyDropdown">
                                <i class="fas fa-euro-sign"></i> EUR
                            </a>
                        </li>
                        
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="profileDropdown">
                                <i class="fas fa-user"></i>
                                <?php if (isset($_SESSION['user_id'])): ?>
                                    Mon compte
                                <?php else: ?>
                                    Connexion
                                <?php endif; ?>
                            </a>
                            <div class="dropdown-menu">
                                <?php if (!isset($_SESSION['user_id'])): ?>
                                    <a class="dropdown-item no-ajax" href="auth/login.php"><i class="fas fa-sign-in-alt"></i> Connexion</a>
                                    <a class="dropdown-item no-ajax" href="auth/inscription.php"><i class="fas fa-user-plus"></i> Inscription</a>
                                <?php else: ?>
                                    <?php if (!empty($_SESSION['access_level']) AND $_SESSION['access_level'] === 'admin'): ?>
                                        <a class="dropdown-item no-ajax" href="admin.php"><i class="fas fa-crown"></i> Admin</a>
                                    <?php endif; ?>
                                    <a class="dropdown-item no-ajax" href="profile.php"><i class="fas fa-user-circle"></i> Profil</a>
                                    <a class="dropdown-item no-ajax" href="mes_favoris.php"><i class="fas fa-heart"></i> Favoris</a>
                                    <a class="dropdown-item no-ajax" href="logout.php"><i class="fas fa-sign-out-alt"></i> Déconnexion</a>
                                <?php endif; ?>
                            </div>
                        </li>
                    </ul>
                </div>
            </nav>
            
            <!-- Contenu du hero -->
            <div class="hero-content">
                <h1 class="hero-title">Voyagez <span>Jeune</span>, Vivez <span>Libre</span></h1>
                <p class="hero-subtitle">Des expériences uniques à petits prix pour les aventuriers comme vous</p>
                
                <div class="hero-buttons">
                    <a href="#offres" class="btn btn-primary animate-float no-ajax">
                        <i class="fas fa-arrow-down"></i> Découvrir
                    </a>
                    <a href="pages/offre.php" class="btn btn-secondary animate-float-delay no-ajax">
                        <i class="fas fa-bolt"></i> Promos
                    </a>
                </div>
                
                <div class="scroll-indicator">
                    <div class="mouse">
                        <div class="wheel"></div>
                    </div>
                    <div class="arrow"></div>
                </div>
            </div>
            
            <div class="hero-bubbles">
                <div class="bubble" style="--size: 3rem; --distance: 6rem; --position: 10%; --time: 6s; --delay: 0s;"></div>
                <div class="bubble" style="--size: 2rem; --distance: 8rem; --position: 20%; --time: 5s; --delay: 1s;"></div>
                <div class="bubble" style="--size: 4rem; --distance: 7rem; --position: 50%; --time: 7s; --delay: 0.5s;"></div>
                <div class="bubble" style="--size: 1.5rem; --distance: 9rem; --position: 70%; --time: 6s; --delay: 2s;"></div>
            </div>
        </header>

        <!-- Contenu principal -->
        <main class="container">
            <!-- Barre de recherche -->
            <section class="search-section no-ajax">
                <div class="search-tabs">
                    <button type="button" class="tab-btn active" data-tab="voyages"><i class="fas fa-suitcase-rolling"></i> Voyages</button>
                    <button type="button" class="tab-btn" data-tab="hebergements"><i class="fas fa-hotel"></i> Hébergements</button>
                </div>
                
                <form class="search-form" id="searchForm" action="pages/recherche.php" method="GET">
                    <input type="hidden" name="type" id="searchType" value="voyages">
                    
                    <div class="form-group">
                        <label for="destination"><i class="fas fa-map-marker-alt"></i> 
                            <span id="destinationLabel">Où allez-vous ?</span>
                        </label>
                        <input type="text" id="destination" name="destination" placeholder="Destination de rêve..." required>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="depart"><i class="fas fa-calendar-day"></i> 
                                <span id="departLabel">Départ</span>
                            </label>
                            <input type="date" id="depart" name="departure" required>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="voyageurs"><i class="fas fa-users"></i> Voyageurs</label>
                        <select id="voyageurs" name="passengers">
                            <option value="1">1 voyageur</option>
                            <option value="2">2 voyageurs</option>
                            <option value="3">3 voyageurs</option>
                            <option value="4">4 voyageurs</option>
                            <option value="groupe">Groupe (+5)</option>
                        </select>
                    </div>
                    
                    <button type="submit" class="search-btn no-ajax">
                        <i class="fas fa-search"></i> Trouver mon aventure
                    </button>
                </form>
            </section>

            <!-- Section Vidéo Hero -->
            <section class="video-hero-section">
                <div class="video-container">
                    <video autoplay loop muted playsinline>
                        <source src="videos/voyage-hero.mp4" type="video/mp4">
                        Votre navigateur ne supporte pas les vidéos HTML5.
                    </video>
                    <div class="video-overlay"></div>
                    <div class="video-content">
                        <h2>Vivez l'aventure comme jamais</h2>
                        <p>Découvrez des expériences uniques avec des voyageurs comme vous</p>
                        <a href="pages/offre.php" class="btn btn-primary">Voir nos offres</a>
                    </div>
                </div>
            </section>

            <!-- Section Qui sommes-nous ? -->
            <section class="about-section">
                <h2 class="section-title"><span>Qui sommes-nous ?</span></h2>
                
                <div class="about-content">
                    <div class="about-text">
                        <p class="highlight">Bienvenue sur Tripster, la plateforme de voyage conçue par et pour les jeunes aventuriers !</p>
                        
                        <div class="about-features">
                            <div class="feature-card">
                                <div class="feature-icon" style="background-color: var(--primary);">
                                    <i class="fas fa-euro-sign"></i>
                                </div>
                                <h3>Voyages à petit budget</h3>
                                <p>Hébergements pas chers, auberges de jeunesse, logements entre jeunes.</p>
                            </div>
                            
                            <div class="feature-card">
                                <div class="feature-icon" style="background-color: var(--secondary);">
                                    <i class="fas fa-mountain"></i>
                                </div>
                                <h3>Camping et nature</h3>
                                <p>Spots insolites, road-trips en van, randonnées.</p>
                            </div>
                            
                            <div class="feature-card">
                                <div class="feature-icon" style="background-color: var(--accent);">
                                    <i class="fas fa-city"></i>
                                </div>
                                <h3>City breaks à prix mini</h3>
                                <p>Séjours en ville avec des bons plans exclusifs.</p>
                            </div>
                        </div>
                        
                        <div class="mission-statement">
                            <h3>Notre mission</h3>
                            <p>Chez Tripster, on croit que voyager ne devrait pas être un luxe. C'est pourquoi on t'aide à explorer le monde sans exploser ton budget, avec des séjours funs, accessibles et authentiques.</p>
                        </div>
                    </div>
                    
                    <div class="about-image">
                        <img src="img/van1.jpg" alt="Jeunes voyageurs" class="animate-img">
                        <div class="image-badge">
                            <span>+10,000 voyageurs satisfaits</span>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Offres de voyage -->
            <section class="offers-section" id="offres">
                <div class="section-header">
                    <h2 class="section-title"><span>Nos offres jeunes</span></h2>
                    <a href="pages/offre.php" class="see-all no-ajax">Voir tout <i class="fas fa-arrow-right"></i></a>
                </div>
                
                <div class="offers-grid">
                    <?php if (empty($offres)): ?>
                        <div class="no-offers">
                            <i class="fas fa-compass"></i>
                            <p>Nos offres arrivent bientôt !</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($offres as $offre): ?>
                            <div class="offer-card">
                                <div class="offer-badge">Jeune prix</div>
                                
                                <div class="offer-image">
                                    <a href="pages/details_offre.php?id=<?= $offre['id'] ?>">
                                        <?php if (!empty($offre['images'])): ?>
                                            <img src="<?= htmlspecialchars($offre['images']) ?>" alt="<?= htmlspecialchars($offre['titre']) ?>">
                                        <?php else: ?>
                                            <img src="img/default-offer.jpg" alt="Offre Tripster">
                                        <?php endif; ?>
                                    </a>
                                </div>
                                
                                <div class="offer-content">
                                    <h3><?= htmlspecialchars($offre['titre']) ?></h3>
                                    
                                    <div class="offer-details">
                                        <span><i class="fas fa-clock"></i> <?= htmlspecialchars($offre['duree_sejour']) ?> jours</span>
                                        <span><i class="fas fa-map-marker-alt"></i> Europe</span>
                                        <span>
                                            <i class="fas fa-<?= 
                                                $offre['type_transport'] === 'avion' ? 'plane' : 
                                                ($offre['type_transport'] === 'train' ? 'train' :
                                                ($offre['type_transport'] === 'bus' ? 'bus' :
                                                ($offre['type_transport'] === 'voiture' ? 'car' : 'users')))
                                            ?>"></i> 
                                            <?= ucfirst(htmlspecialchars($offre['type_transport'])) ?>
                                        </span>
                                    </div>
                                    
                                    <div class="offer-price">
                                        <?php if (!empty($offre['ancien_prix']) && $offre['ancien_prix'] > $offre['prix']): ?>
                                            <div class="price-old"><?= number_format($offre['ancien_prix'], 2, ',', ' ') ?> €</div>
                                            <div class="price-new"><?= number_format($offre['prix'], 2, ',', ' ') ?> €</div>
                                        <?php else: ?>
                                            <div class="price-current"><?= number_format($offre['prix'], 2, ',', ' ') ?> €</div>
                                        <?php endif; ?>
                                        <div class="price-group">
                                            <small>Groupe (3-4 pers.)</small>
                                            <strong><?= number_format($offre['prix_groupe'], 2, ',', ' ') ?> €</strong>
                                        </div>
                                    </div>
                                    
                                    <div class="offer-actions">
                                        <a href="pages/details_offre.php?id=<?= $offre['id'] ?>" class="btn-details no-ajax">
                                            <i class="fas fa-eye"></i> Voir l'offre
                                        </a>
                                        <form method="POST" class="favorite-form" onsubmit="return false;">
                                            <input type="hidden" name="offre_id" value="<?= $offre['id'] ?>">
                                            <?php if (isset($_SESSION['user_id']) AND estFavori($dbh, $_SESSION['user_id'], $offre['id'])): ?>
                                                <input type="hidden" name="action_favori" value="retirer">
                                                <button type="submit" class="btn-favorite" style="color: red;">
                                                    <i class="fas fa-heart"></i> 
                                                </button>
                                            <?php else: ?>
                                                <input type="hidden" name="action_favori" value="ajouter">
                                                <button type="submit" class="btn-favorite">
                                                    <i class="far fa-heart"></i> 
                                                </button>
                                            <?php endif; ?>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </section>


            <!-- Section Best-sellers -->
            <section class="bestsellers-section">
                <div class="container">
                    <h2 class="section-title"><span>Nos best-sellers</span></h2>
                    <p class="section-subtitle">Les voyages préférés de notre communauté</p>
                    
                    <div class="bestsellers-carousel">
                        <button class="carousel-btn prev-btn" aria-label="Précédent">
                            <i class="fas fa-chevron-left"></i>
                        </button>
                        
                        <div class="bestsellers-slider">
                            <!-- Slide 1 -->
                            <div class="bestseller-slide active">
                                <div class="slide-image" style="background-image: url('img/lisbonne_digital.jpg');">
                                    <div class="slide-content">
                                        <div class="slide-badge"></div>
                                        <h3>Roadtrip Portugal</h3>
                                        <div class="slide-details">
                                            <span><i class="fas fa-map-marker-alt"></i> Lisbonne → Porto</span>
                                            <span><i class="fas fa-clock"></i> 8 jours</span>
                                        </div>
                                        <div class="slide-price">À partir de <strong>379,99 €</strong></div>
                                        <div class="slide-rating">
                                            <i class="fas fa-star"></i>
                                            <i class="fas fa-star"></i>
                                            <i class="fas fa-star"></i>
                                            <i class="fas fa-star"></i>
                                            <i class="fas fa-star"></i>
                                            <span>4.9 (128 avis)</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Slide 2 -->
                            <div class="bestseller-slide">
                                <div class="slide-image" style="background-image: url('img/barcelone.jpg');">
                                    <div class="slide-content">
                                        <div class="slide-badge"></div>
                                        <h3>Weekend Barcelone</h3>
                                        <div class="slide-details">
                                            <span><i class="fas fa-map-marker-alt"></i> Barcelone</span>
                                            <span><i class="fas fa-clock"></i> 3 jours</span>
                                        </div>
                                        <div class="slide-price">À partir de <strong>149€</strong></div>
                                        <div class="slide-rating">
                                            <i class="fas fa-star"></i>
                                            <i class="fas fa-star"></i>
                                            <i class="fas fa-star"></i>
                                            <i class="fas fa-star"></i>
                                            <i class="fas fa-star-half-alt"></i>
                                            <span>4.8 (97 avis)</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Slide 3 -->
                            <div class="bestseller-slide">
                                <div class="slide-image" style="background-image: url('img/londres.jpg');">
                                    <div class="slide-content">
                                        <div class="slide-badge"></div>
                                        <h3>Aventure a Londres</h3>
                                        <div class="slide-details">
                                            <span><i class="fas fa-map-marker-alt"></i> Split → Dubrovnik</span>
                                            <span><i class="fas fa-clock"></i> 7 jours</span>
                                        </div>
                                        <div class="slide-price">À partir de <strong>349€</strong></div>
                                        <div class="slide-rating">
                                            <i class="fas fa-star"></i>
                                            <i class="fas fa-star"></i>
                                            <i class="fas fa-star"></i>
                                            <i class="fas fa-star"></i>
                                            <i class="fas fa-star"></i>
                                            <span>4.9 (84 avis)</span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                                <!-- Slide 4 -->
                            <div class="bestseller-slide">
                                <div class="slide-image" style="background-image: url('img/rome.jpg');">
                                    <div class="slide-content">
                                        <div class="slide-badge"></div>
                                        <h3>Gladiator Academy</h3>
                                        <div class="slide-details">
                                            <span><i class="fas fa-map-marker-alt"></i>Rome</span>
                                            <span><i class="fas fa-clock"></i> 7 jours</span>
                                        </div>
                                        <div class="slide-price">À partir de <strong>349€</strong></div>
                                        <div class="slide-rating">
                                            <i class="fas fa-star"></i>
                                            <i class="fas fa-star"></i>
                                            <i class="fas fa-star"></i>
                                            <i class="fas fa-star"></i>
                                            <i class="fas fa-star"></i>
                                            <span>4.9 (84 avis)</span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                                <!-- Slide 5 -->
                            <div class="bestseller-slide">
                                <div class="slide-image" style="background-image: url('img/copenhague.jpg');">
                                    <div class="slide-content">
                                        <div class="slide-badge"></div>
                                        <h3>Cristiana Town</h3>
                                        <div class="slide-details">
                                            <span><i class="fas fa-map-marker-alt"></i>Copenhague</span>
                                            <span><i class="fas fa-clock"></i> 7 jours</span>
                                        </div>
                                        <div class="slide-price">À partir de <strong>299,99 €</strong></div>
                                        <div class="slide-rating">
                                            <i class="fas fa-star"></i>
                                            <i class="fas fa-star"></i>
                                            <i class="fas fa-star"></i>
                                            <i class="fas fa-star"></i>
                                            <i class="fas fa-star"></i>
                                            <span>4.9 (84 avis)</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                        </div>
                        
                        <button class="carousel-btn next-btn" aria-label="Suivant">
                            <i class="fas fa-chevron-right"></i>
                        </button>
                    </div>
                    
                    <div class="carousel-dots"></div>
                    
                    <div class="section-footer">
                        <a href="pages/offre.php" class="see-all no-ajax">
                            Voir tous nos best-sellers <i class="fas fa-arrow-right"></i>
                        </a>
                    </div>
                </div>
            </section>

            <!-- Destinations Futures -->
            <section class="future-destinations-section">
                <div class="container">
                    <div class="section-header">
                        <h2 class="section-title"><span>Nos Prochaines Destinations</span></h2>
                        <p class="section-subtitle">Découvrez les merveilles que nous préparons pour vous</p>
                    </div>
                    
                    <div class="destinations-timeline">
            </section>


            <div class="main-content">
                <!--Type de Vacances -->
                <section class="section">
                    <div class="container">
                        <h2 class="section-title"><span>Nos types de vacances</span></h2>
                        <div class="vacances-container offers-grid">
                        <div class="vacance offer-card">
                            <div class="offer-image">
                            <img src="img/nature.jpg" alt="Vacances nature">
                            </div>
                            <div class="offer-content">
                            <h3>Nature & Randonnée</h3>
                            <p>Explore les plus beaux sentiers d’Europe en mode éco-friendly.</p>
                            </div>
                        </div>
                        <div class="vacance offer-card">
                            <div class="offer-image">
                            <img src="img/roadtrip.png" alt="Roadtrip entre amis">
                            </div>
                            <div class="offer-content">
                            <h3>Roadtrip entre amis</h3>
                            <p>Partage un van et des souvenirs sur les routes d’Europe.</p>
                            </div>
                        </div>
                        <div class="vacance offer-card">
                            <div class="offer-image">
                            <img src="img/festival.jpg" alt="Festivals">
                            </div>
                            <div class="offer-content">
                            <h3>Festivals & Événements</h3>
                            <p>Vibre au rythme des plus grands festivals avec ta team Tripster.</p>
                            </div>
                        </div>
                        </div>
                    </div>
                </section>
            </div>

            <!-- Section Pourquoi Nous -->
            <section class="why-us-section">
                <div class="container">
                    <h2 class="section-title"><span>Pourquoi choisir Tripster ?</span></h2>
                    
                    <div class="why-us-grid">
                        <div class="why-us-card">
                            <div class="why-us-icon">
                                <i class="fas fa-euro-sign"></i>
                            </div>
                            <h3>Prix jeunes</h3>
                            <p>Tarifs jusqu'à 40% moins chers que les agences traditionnelles, spécialement étudiés pour les budgets serrés</p>
                        </div>
                        
                        <div class="why-us-card">
                            <div class="why-us-icon">
                                <i class="fas fa-users"></i>
                            </div>
                            <h3>Communauté active</h3>
                            <p>Rejoignez +50 000 jeunes voyageurs et profitez de leurs retours d'expérience</p>
                        </div>
                        
                        <div class="why-us-card">
                            <div class="why-us-icon">
                                <i class="fas fa-bolt"></i>
                            </div>
                            <h3>Offres flash</h3>
                            <p>Accès exclusif aux meilleures promotions avec nos alertes en temps réel</p>
                        </div>
                        
                        <div class="why-us-card">
                            <div class="why-us-icon">
                                <i class="fas fa-shield-alt"></i>
                            </div>
                            <h3>Sécurité garantie</h3>
                            <p>Tous nos partenaires sont vérifiés et nos paiements 100% sécurisés</p>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Section Paiement Flexible -->
            <section class="flexible-payment-section">
                <div class="container">
                    <h2 class="section-title"><span>Paiement flexible</span></h2>
                    
                    <div class="payment-features">
                        <div class="payment-card">
                            <div class="payment-icon">
                                <i class="fas fa-calendar-alt"></i>
                            </div>
                            <h3>Paiement en plusieurs fois</h3>
                            <p>Étalez votre paiement jusqu'à 4 fois sans frais pour plus de flexibilité.</p>
                            <a href="pages/paiement-flexible.php" class="btn-payment no-ajax">En savoir plus</a>
                        </div>
                        
                        <div class="payment-card">
                            <div class="payment-icon">
                                <i class="fas fa-lock"></i>
                            </div>
                            <h3>100% sécurisé</h3>
                            <p>Transactions cryptées et protégées pour une réservation en toute confiance.</p>
                            <a href="pages/securite-paiement.php" class="btn-payment no-ajax">Nos garanties</a>
                        </div>
                        
                        <div class="payment-card">
                            <div class="payment-icon">
                                <i class="fas fa-euro-sign"></i>
                            </div>
                            <h3>Sans frais cachés</h3>
                            <p>Le prix que vous voyez est le prix que vous payez, sans mauvaises surprises.</p>
                            <a href="pages/transparence.php" class="btn-payment no-ajax">Transparence</a>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Section Contact & Chat AI -->
            <section class="contact-section">
                <div class="container">
                    <h2 class="section-title"><span>Besoin d'aide ?</span></h2>
                    <p class="section-subtitle">Nous sommes là pour répondre à vos questions</p>
                    
                    <div class="contact-options">
                        <!-- Option WhatsApp -->
                        <div class="contact-option whatsapp-option">
                            <div class="contact-icon">
                                <i class="fab fa-whatsapp"></i>
                            </div>
                            <h3>Chat WhatsApp</h3>
                            <p>Contactez-nous directement via WhatsApp</p>
                            <button class="btn-contact" id="whatsappBtn">
                                <i class="fab fa-whatsapp"></i> Ouvrir le chat
                            </button>
                            
                            <!-- Popup WhatsApp (caché par défaut) -->
                            <div class="contact-popup whatsapp-popup">
                                <div class="popup-header">
                                    <h4>Chat WhatsApp</h4>
                                    <button class="close-popup">&times;</button>
                                </div>
                                <div class="popup-body">
                                    <p>Envoyez-nous un message directement sur WhatsApp :</p>
                                    <a href="https://wa.me/33612345678" class="whatsapp-link no-ajax" target="_blank">
                                        <i class="fab fa-whatsapp"></i> +32 6 12 34 56 09
                                    </a>
                                    <div class="whatsapp-qr">
                                        <img src="img/whatsapp-qr.png" alt="Scanner le QR code WhatsApp">
                                        <p>Ou scannez ce QR code</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Option Téléphone -->
                        <div class="contact-option phone-option">
                            <div class="contact-icon">
                                <i class="fas fa-phone-alt"></i>
                            </div>
                            <h3>Appelez-nous</h3>
                            <p>Du lundi au vendredi, 9h-18h</p>
                            <button class="btn-contact" id="phoneBtn">
                                <i class="fas fa-phone-alt"></i> Voir le numéro
                            </button>
                            
                            <!-- Popup Téléphone (caché par défaut) -->
                            <div class="contact-popup phone-popup">
                                <div class="popup-header">
                                    <h4>Contact téléphonique</h4>
                                    <button class="close-popup">&times;</button>
                                </div>
                                <div class="popup-body">
                                    <p>Appelez notre service client :</p>
                                    <a href="tel:+33123456789" class="phone-link no-ajax" >
                                        <i class="fas fa-phone-alt"></i> +32 1 23 45 67 89
                                    </a>
                                    <div class="call-hours">
                                        <p><strong>Horaires d'ouverture :</strong></p>
                                        <p>Lundi-Vendredi : 9h-18h</p>
                                        <p>Samedi : 10h-15h</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Option Chat AI -->
                        <div class="contact-option ai-option">
                            <div class="contact-icon">
                                <i class="fas fa-robot"></i>
                            </div>
                            <h3>Assistant AI</h3>
                            <p>Obtenez des réponses instantanées</p>
                            <button class="btn-contact no-ajax" id="aiChatBtn">
                                <i class="fas fa-comment-dots"></i> Ouvrir le chat
                            </button>
                        </div>
                    </div>
                </div>
                
                <!-- Chat AI Container (fixe en bas à droite) -->
                <div class="ai-chat-container" id="aiChatContainer">
                    <div class="chat-header">
                        <h4>Assistant Tripster</h4>
                        <button class="close-chat">&times;</button>
                    </div>
                    <div class="chat-messages" id="chatMessages">
                        <div class="ai-message">
                            <div class="message-content">
                                Bonjour ! Je suis l'assistant Tripster. Comment puis-je vous aider aujourd'hui ?
                            </div>
                        </div>
                    </div>
                    <div class="chat-input">
                        <input type="text" id="userMessage" placeholder="Tapez votre message...">
                        <button id="sendMessage"><i class="fas fa-paper-plane"></i></button>
                    </div>
                </div>
            </section>

            <!-- Bouton déclencheur du chat AI -->
            <div class="ai-chat-trigger" id="aiChatTrigger">
                <i class="fas fa-robot"></i>
                <div class="notification-badge" id="chatNotification" style="display: none;">!</div>
            </div>

            <!-- Section témoignages -->
            <section class="testimonials-section">
                <h2 class="section-title"><span>Ils ont voyagé avec nous</span></h2>
                
                <div class="testimonials-slider">
                    <div class="testimonial-card">
                        <div class="testimonial-header">
                            <img src="img/testimonials/user1.jpg" alt="Marie">
                            <div class="user-info">
                                <h4>Marie, 22 ans</h4>
                                <div class="rating">
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star"></i>
                                </div>
                            </div>
                        </div>
                        <p>"Mon road-trip en Espagne avec Tripster était incroyable ! J'ai rencontré des gens géniaux et économisé plus de 40% par rapport aux autres sites."</p>
                    </div>
                    
                    <div class="testimonial-card">
                        <div class="testimonial-header">
                            <img src="img/testimonials/user2.jpg" alt="Thomas">
                            <div class="user-info">
                                <h4>Thomas, 24 ans</h4>
                                <div class="rating">
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star-half-alt"></i>
                                </div>
                            </div>
                        </div>
                        <p>"L'auberge de jeunesse à Lisbonne recommandée par Tripster était parfaite : propre, bien située et super ambiance. Je recommande !"</p>
                    </div>
                    
                    <div class="testimonial-card">
                        <div class="testimonial-header">
                            <img src="img/testimonials/user3.jpg" alt="Sophie">
                            <div class="user-info">
                                <h4>Sophie, 19 ans</h4>
                                <div class="rating">
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star"></i>
                                    <i class="fas fa-star"></i>
                                </div>
                            </div>
                        </div>
                        <p>"Grâce à Tripster, j'ai pu partir en Grèce avec mon petit budget. Leur sélection de bons plans est vraiment adaptée aux étudiants."</p>
                    </div>
                </div>
            </section>

            <!-- Section newsletter -->
            <section class="newsletter-section">
                <div class="newsletter-content">
                    <div class="newsletter-text">
                        <h2>Ne ratez aucun bon plan !</h2>
                        <p>Inscrivez-vous à notre newsletter pour recevoir les offres exclusives réservées aux jeunes voyageurs.</p>
                    </div>
                    
                    <form class="newsletter-form">
                        <div class="form-group">
                            <input type="email" placeholder="Votre email" required>
                            <button type="submit">
                                <i class="fas fa-paper-plane"></i> S'inscrire
                            </button>
                        </div>
                        <div class="form-check">
                            <input type="checkbox" id="consent" required>
                            <label for="consent">J'accepte de recevoir les offres Tripster</label>
                        </div>
                    </form>
                </div>
            </section>

            <!-- Cookie Consent Modal -->
            <div class="cookie-consent" id="cookieConsent">
                <div class="cookie-content">
                    <div class="cookie-header">
                        <h3>Tripster</h3>
                        <p>Trouvez le voyage de vos rêves</p>
                    </div>
                    
                    <div class="cookie-body">
                        <h4>Tripster</h4>
                        <p>Afin de vous offrir une expérience personnalisée, nous utilisons des cookies. Voici quelques raisons de les accepter :</p>
                        
                        <ul class="cookie-reasons">
                            <li><i class="fas fa-check-circle"></i> Une expérience plus fluide</li>
                            <li><i class="fas fa-check-circle"></i> Du contenu adapté à vos intérêts</li>
                            <li><i class="fas fa-check-circle"></i> Une meilleure qualité de service</li>
                        </ul>
                        
                        <div class="cookie-actions">
                            <button class="btn-cookie" id="configureCookie">Paramétrer</button>
                            <button class="btn-cookie primary" id="acceptCookie">Accepter et continuer</button>
                        </div>
                        
                        <a href="#" class="continue-without">Continuer sans accepter →</a>
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
                        <li><a href="pages/offre.php" data-ajax="">Offres spéciales</a></li>
                        <li><a href="pages/blog.php" data-ajax>Blog voyage</a></li>
                        <li><a href="pages/forum.php" data-ajax="">Forum</a></li>
                        <li><a href="#">Destinations tendances</a></li>
                    </ul>
                </div>
                
                <div class="footer-section">
                    <h3>Aide</h3>
                    <ul>
                        <li><a href="pages/contact.php" data-ajax="">Contactez-nous</a></li>
                        <li><a href="FAQ.php" data-ajax="">FAQ</a></li>
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
        <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
        <script src="styles/script.js"></script>
        <script>
            // Supprimer le préchargement une fois la page chargée
            window.addEventListener('load', function() {
                setTimeout(function() {
                    document.querySelector('.loading').style.opacity = '0';
                    setTimeout(function() {
                        document.querySelector('.loading').style.display = 'none';
                    }, 500);
                }, 500);
            });
            
            // Gestion du cookie consent
            document.addEventListener('DOMContentLoaded', function() {
                const cookieConsent = document.getElementById('cookieConsent');
                const acceptBtn = document.getElementById('acceptCookie');
                const configureBtn = document.getElementById('configureCookie');
                
                // Vérifier si le consentement a déjà été donné
                if (!localStorage.getItem('cookieConsent')) {
                    setTimeout(() => {
                        cookieConsent.classList.add('show');
                    }, 2000);
                }
                
                // Accepter les cookies
                acceptBtn.addEventListener('click', function() {
                    localStorage.setItem('cookieConsent', 'accepted');
                    cookieConsent.classList.remove('show');
                });
                
                // Configurer les cookies (vous pouvez ajouter plus de logique ici)
                configureBtn.addEventListener('click', function() {
                    alert('Options de configuration des cookies seront disponibles ici.');
                });
                
                // Continuer sans accepter
                document.querySelector('.continue-without').addEventListener('click', function(e) {
                    e.preventDefault();
                    cookieConsent.classList.remove('show');
                });
            });

            document.addEventListener('DOMContentLoaded', function() {
                const menuToggle = document.getElementById('mobile-menu');
                const navMenu = document.querySelector('.nav-menu');
                
                menuToggle.addEventListener('click', function() {
                    navMenu.classList.toggle('active');
                    menuToggle.classList.toggle('open');
                });
            });
                        
            // Optimize initial load
            document.addEventListener('DOMContentLoaded', function() {
                const nonCriticalCSS = [
                    'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css',
                    'https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css'
                ];
                
                nonCriticalCSS.forEach(url => {
                    const link = document.createElement('link');
                    link.rel = 'stylesheet';
                    link.href = url;
                    link.media = 'print';
                    link.onload = () => { link.media = 'all'; };
                    document.head.appendChild(link);
                });
                
                // Load non-critical JS after page load
                window.addEventListener('load', function() {
                    const nonCriticalJS = [
                        'https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js'
                    ];
                    
                    nonCriticalJS.forEach(url => {
                        const script = document.createElement('script');
                        script.src = url;
                        document.body.appendChild(script);
                    });
                });
            });
        </script>
    </body>
</html>