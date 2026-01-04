<?php
require_once('../data_base/connection.php');
require_once('fonctionsContenu.php');
session_start();

if(!isset($_SESSION['access_level']) || $_SESSION['access_level'] !== 'admin') {
    header('Location: ../auth/login.php');
    exit;
}

// Récupérer les sections existantes
$sections = $dbh->query("SELECT * FROM sections WHERE est_actif = TRUE ORDER BY nom")->fetchAll(PDO::FETCH_ASSOC);

// Positions disponibles dans chaque section
$positions = [
    'hero' => ['Haut', 'Milieu', 'Bas'],
    'about' => ['Gauche', 'Droite', 'Plein largeur'],
    'offers' => ['Grille', 'Slider', 'Liste'],
    'testimonials' => ['Slider', 'Grille'],
    'newsletter' => ['Centré', 'Plein largeur']
];

$typesContenu = [
    'texte' => 'Texte',
    'image' => 'Image',
    'video' => 'Vidéo',
    'publicite' => 'Publicité',
    'promotion' => 'Promotion',
    'carousel' => 'Carousel',
    'bouton' => 'Bouton'
];

// Gestion des formulaires
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['ajouter_section'])) {
        $nom = htmlspecialchars($_POST['nom_section']);
        $slug = strtolower(preg_replace('/[^a-zA-Z0-9-]/', '-', $nom));
        
        $stmt = $dbh->prepare("INSERT INTO sections (nom, slug) VALUES (?, ?)");
        $stmt->execute([$nom, $slug]);
        
        $_SESSION['message'] = "Section ajoutée avec succès";
        header("Location: gestion_contenu.php");
        exit;
    }
    
    if (isset($_POST['ajouter_contenu']) || isset($_POST['modifier_contenu'])) {
        $type = $_POST['type_contenu'];
        $section_id = $_POST['section_contenu'];
        $sous_position = $_POST['sous_position'] ?? 'default';
        $titre = htmlspecialchars($_POST['titre_contenu']);
        $contenu = $_POST['contenu_texte'];
        $ordre = (int)$_POST['ordre_contenu'];
        $est_actif = isset($_POST['est_actif']) ? 1 : 0;
        $style = $_POST['style_contenu'] ?? null;
        
        // Gestion de l'upload de fichier
        $url = $_POST['url_existante'] ?? null;
        if (in_array($type, ['image', 'video', 'publicite']) && isset($_FILES['fichier_contenu']) && $_FILES['fichier_contenu']['error'] === UPLOAD_ERR_OK) {
            $dossier = '../uploads/contenu/';
            $extension = pathinfo($_FILES['fichier_contenu']['name'], PATHINFO_EXTENSION);
            $nom_fichier = uniqid() . '.' . $extension;
            
            if (move_uploaded_file($_FILES['fichier_contenu']['tmp_name'], $dossier . $nom_fichier)) {
                $url = 'uploads/contenu/' . $nom_fichier;
            }
        }
        
        if (isset($_POST['ajouter_contenu'])) {
            $stmt = $dbh->prepare("
                INSERT INTO contenus 
                (type, position, sous_position, titre, contenu, url, ordre, est_actif, style) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([$type, $section_id, $sous_position, $titre, $contenu, $url, $ordre, $est_actif, $style]);
            $_SESSION['message'] = "Contenu ajouté avec succès";
        } else {
            $stmt = $dbh->prepare("
                UPDATE contenus SET 
                type = ?, position = ?, sous_position = ?, titre = ?, contenu = ?, 
                url = ?, ordre = ?, est_actif = ?, style = ?
                WHERE id = ?
            ");
            $stmt->execute([$type, $section_id, $sous_position, $titre, $contenu, $url, $ordre, $est_actif, $style, $_POST['contenu_id']]);
            $_SESSION['message'] = "Contenu modifié avec succès";
        }
        
        header("Location: gestion_contenu.php");
        exit;
    }
    
    if (isset($_POST['supprimer_contenu'])) {
        $stmt = $dbh->prepare("DELETE FROM contenus WHERE id = ?");
        $stmt->execute([$_POST['contenu_id']]);
        $_SESSION['message'] = "Contenu supprimé avec succès";
        header("Location: gestion_contenu.php");
        exit;
    }
    
    if (isset($_POST['toggle_actif'])) {
        $stmt = $dbh->prepare("UPDATE contenus SET est_actif = NOT est_actif WHERE id = ?");
        $stmt->execute([$_POST['contenu_id']]);
        header("Location: gestion_contenu.php");
        exit;
    }
}

// Récupérer tous les contenus
$contenus = $dbh->query("
    SELECT c.*, s.nom as section_nom 
    FROM contenus c
    LEFT JOIN sections s ON c.position = s.slug
    ORDER BY s.nom, c.ordre
")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion de Contenu - Tripster</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://cdn.ckeditor.com/ckeditor5/36.0.1/classic/ckeditor.css" rel="stylesheet">
    <script src="styles/script.js"></script>
    <link href="styles/style.css" rel="stylesheet">
    
    <style>
        .content-card {
            transition: all 0.3s;
            margin-bottom: 20px;
        }
        .content-card:hover {
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            transform: translateY(-2px);
        }
        .content-image {
            max-height: 150px;
            object-fit: cover;
        }
        .section-active {
            border-left: 4px solid #0d6efd;
        }
        .nav-tabs .nav-link.active {
            font-weight: bold;
            background-color: #f8f9fa;
        }
        #editor {
            min-height: 300px;
            border: 1px solid #dee2e6;
            padding: 10px;
            border-radius: 4px;
        }
        .badge-type {
            font-size: 0.8em;
        }
        .content-actions {
            opacity: 0;
            transition: opacity 0.3s;
        }
        .content-card:hover .content-actions {
            opacity: 1;
        }
        .style-preview {
            padding: 8px;
            margin-top: 8px;
            background-color: #f8f9fa;
            border-radius: 4px;
            font-family: monospace;
            font-size: 0.9em;
        }
        .dropzone {
            border: 2px dashed #0d6efd;
            border-radius: 5px;
            padding: 20px;
            text-align: center;
            margin-top: 10px;
            background-color: #f8f9fa;
        }
        .dz-message {
            color: #6c757d;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">Gestion de Contenu</h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <a href="../admin.php" class="btn btn-sm btn-outline-secondary">
                            <i class="bi bi-arrow-left"></i> Retour
                        </a>
                    </div>
                </div>
                
                <?php if (isset($_SESSION['message'])): ?>
                    <div class="alert alert-success alert-dismissible fade show">
                        <?= $_SESSION['message'] ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                    <?php unset($_SESSION['message']); ?>
                <?php endif; ?>
                
                <ul class="nav nav-tabs mb-4" id="contentTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="sections-tab" data-bs-toggle="tab" data-bs-target="#sections" type="button">
                            <i class="bi bi-collection"></i> Sections
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="add-content-tab" data-bs-toggle="tab" data-bs-target="#add-content" type="button">
                            <i class="bi bi-plus-circle"></i> Ajouter du contenu
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="view-content-tab" data-bs-toggle="tab" data-bs-target="#view-content" type="button">
                            <i class="bi bi-card-list"></i> Voir le contenu
                        </button>
                    </li>
                </ul>
                
                <div class="tab-content" id="contentTabsContent">
                    <!-- Onglet Gestion des sections -->
                    <div class="tab-pane fade show active" id="sections" role="tabpanel">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-header bg-primary text-white">
                                        <h5 class="mb-0">Ajouter une section</h5>
                                    </div>
                                    <div class="card-body">
                                        <form method="POST">
                                            <div class="mb-3">
                                                <label for="nom_section" class="form-label">Nom de la section</label>
                                                <input type="text" class="form-control" id="nom_section" name="nom_section" required>
                                                <small class="text-muted">Exemple: "Hero", "Offres", "Témoignages"</small>
                                            </div>
                                            <button type="submit" name="ajouter_section" class="btn btn-primary">
                                                <i class="bi bi-save"></i> Enregistrer
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-header bg-primary text-white">
                                        <h5 class="mb-0">Sections existantes</h5>
                                    </div>
                                    <div class="card-body">
                                        <?php if (empty($sections)): ?>
                                            <div class="alert alert-info">Aucune section créée</div>
                                        <?php else: ?>
                                            <div class="list-group">
                                                <?php foreach ($sections as $section): ?>
                                                    <div class="list-group-item d-flex justify-content-between align-items-center">
                                                        <div>
                                                            <strong><?= htmlspecialchars($section['nom']) ?></strong>
                                                            <small class="d-block text-muted">Slug: <?= htmlspecialchars($section['slug']) ?></small>
                                                        </div>
                                                        <div>
                                                            <a href="#" class="btn btn-sm btn-outline-primary me-1">
                                                                <i class="bi bi-pencil"></i>
                                                            </a>
                                                            <a href="#" class="btn btn-sm btn-outline-danger">
                                                                <i class="bi bi-trash"></i>
                                                            </a>
                                                        </div>
                                                    </div>
                                                <?php endforeach; ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Onglet Ajout de contenu -->
                    <div class="tab-pane fade" id="add-content" role="tabpanel">
                        <div class="card">
                            <div class="card-header bg-primary text-white">
                                <h5 class="mb-0" id="form-title">Ajouter un nouveau contenu</h5>
                            </div>
                            <div class="card-body">
                                <form method="POST" enctype="multipart/form-data" id="content-form">
                                    <input type="hidden" name="contenu_id" id="contenu_id">
                                    <input type="hidden" name="url_existante" id="url_existante">
                                    
                                    <div class="row mb-3">
                                        <div class="col-md-4">
                                            <label class="form-label">Type de contenu</label>
                                            <select class="form-select" name="type_contenu" id="typeContenu" required>
                                                <option value="">Choisir un type</option>
                                                <option value="texte">Texte</option>
                                                <option value="image">Image</option>
                                                <option value="video">Vidéo</option>
                                                <option value="publicite">Publicité</option>
                                            </select>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label">Section</label>
                                            <select class="form-select" name="section_contenu" id="sectionContenu" required>
                                                <option value="">Choisir une section</option>
                                                <?php foreach ($sections as $section): ?>
                                                    <option value="<?= htmlspecialchars($section['slug']) ?>">
                                                        <?= htmlspecialchars($section['nom']) ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label">Position dans la section</label>
                                            <select class="form-select" name="sous_position" id="sousPosition">
                                                <option value="default">Défaut</option>
                                            </select>
                                        </div>
                                    </div>
                                    
                                    <div class="row mb-3">
                                        <div class="col-md-8">
                                            <label class="form-label">Titre (optionnel)</label>
                                            <input type="text" class="form-control" name="titre_contenu" id="titreContenu">
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label">Ordre d'affichage</label>
                                            <input type="number" class="form-control" name="ordre_contenu" min="0" value="0">
                                        </div>
                                    </div>
                                    
                                    <!-- Champ pour le contenu texte -->
                                    <div class="mb-3" id="contenuTexteGroup">
                                        <label class="form-label">Contenu</label>
                                        <textarea id="editor" name="contenu_texte"></textarea>
                                    </div>
                                    
                                    <!-- Champ pour le fichier (image/video/pub) -->
                                    <div class="mb-3 d-none" id="contenuFichierGroup">
                                        <label class="form-label">Fichier</label>
                                        <input type="file" class="form-control" name="fichier_contenu" id="fichierContenu">
                                        
                                        <div class="dropzone mt-2" id="media-dropzone">
                                            <div class="dz-message">
                                                <i class="bi bi-cloud-arrow-up fs-3"></i><br>
                                                Glissez-déposez votre fichier ici ou cliquez pour sélectionner
                                            </div>
                                        </div>
                                        
                                        <div class="mt-2" id="file-preview-container" style="display:none;">
                                            <small>Fichier actuel:</small>
                                            <div class="d-flex align-items-center mt-1">
                                                <img id="file-preview" src="" style="max-height: 50px; margin-right: 10px;" class="d-none">
                                                <span id="file-name"></span>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label class="form-label">Style CSS personnalisé</label>
                                        <textarea class="form-control" name="style_contenu" rows="3" 
                                                  placeholder="ex: background-color: #f0f0f0; padding: 20px;"></textarea>
                                        <small class="text-muted">Styles CSS à appliquer à ce contenu</small>
                                    </div>
                                    
                                    <div class="mb-3 form-check form-switch">
                                        <input class="form-check-input" type="checkbox" name="est_actif" id="estActif" checked>
                                        <label class="form-check-label" for="estActif">Contenu actif</label>
                                    </div>
                                    
                                    <div class="d-flex justify-content-between">
                                        <button type="submit" name="ajouter_contenu" class="btn btn-primary" id="submit-btn">
                                            <i class="bi bi-save"></i> Enregistrer
                                        </button>
                                        <button type="button" class="btn btn-secondary" id="cancel-edit" style="display:none;">
                                            Annuler
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Onglet Visualisation du contenu -->
                    <div class="tab-pane fade" id="view-content" role="tabpanel">
                        <div class="card">
                            <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                                <h5 class="mb-0">Contenu existant</h5>
                                <div>
                                    <select class="form-select form-select-sm" id="filterSection" style="width: 200px;">
                                        <option value="">Toutes les sections</option>
                                        <?php foreach ($sections as $section): ?>
                                            <option value="<?= htmlspecialchars($section['slug']) ?>">
                                                <?= htmlspecialchars($section['nom']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="card-body">
                                <?php if (empty($contenus)): ?>
                                    <div class="alert alert-info">Aucun contenu créé</div>
                                <?php else: ?>
                                    <div class="row" id="contentGrid">
                                        <?php 
                                        $currentSection = null;
                                        foreach ($contenus as $contenu): 
                                            if ($contenu['section_nom'] !== $currentSection):
                                                $currentSection = $contenu['section_nom'];
                                        ?>
                                        <div class="col-12 mt-3">
                                            <h5 class="border-bottom pb-2"><?= htmlspecialchars($currentSection) ?></h5>
                                        </div>
                                        <?php endif; ?>
                                        
                                        <div class="col-md-6 col-lg-4 content-card" data-section="<?= htmlspecialchars($contenu['position']) ?>">
                                            <div class="card h-100">
                                                <div class="card-header d-flex justify-content-between align-items-center">
                                                    <span class="badge bg-<?= 
                                                        $contenu['type'] === 'texte' ? 'primary' : 
                                                        ($contenu['type'] === 'image' ? 'success' : 
                                                        ($contenu['type'] === 'video' ? 'danger' : 'warning'))
                                                    ?> badge-type">
                                                        <?= ucfirst(htmlspecialchars($contenu['type'])) ?>
                                                        <?php if ($contenu['sous_position'] && $contenu['sous_position'] !== 'default'): ?>
                                                            <small class="ms-1">(<?= htmlspecialchars($contenu['sous_position']) ?>)</small>
                                                        <?php endif; ?>
                                                    </span>
                                                    <small class="text-muted">Ordre: <?= $contenu['ordre'] ?></small>
                                                </div>
                                                
                                                <?php if ($contenu['type'] === 'image' && $contenu['url']): ?>
                                                    <img src="../<?= htmlspecialchars($contenu['url']) ?>" class="card-img-top content-image">
                                                <?php endif; ?>
                                                
                                                <div class="card-body">
                                                    <?php if ($contenu['titre']): ?>
                                                        <h5 class="card-title"><?= htmlspecialchars($contenu['titre']) ?></h5>
                                                    <?php endif; ?>
                                                    
                                                    <?php if ($contenu['type'] === 'texte'): ?>
                                                        <div class="card-text"><?= substr(strip_tags($contenu['contenu']), 0, 100) ?>...</div>
                                                    <?php elseif ($contenu['type'] === 'publicite'): ?>
                                                        <p class="card-text">Publicité: <?= htmlspecialchars($contenu['url']) ?></p>
                                                    <?php elseif ($contenu['type'] === 'video'): ?>
                                                        <p class="card-text">Vidéo: <?= htmlspecialchars($contenu['url']) ?></p>
                                                    <?php endif; ?>
                                                    
                                                    <?php if ($contenu['style']): ?>
                                                        <div class="style-preview mt-2">
                                                            <small>Style:</small>
                                                            <div><?= htmlspecialchars($contenu['style']) ?></div>
                                                        </div>
                                                    <?php endif; ?>
                                                </div>
                                                
                                                <div class="card-footer bg-transparent content-actions">
                                                    <div class="d-flex justify-content-between align-items-center">
                                                        <form method="POST" class="m-0">
                                                            <input type="hidden" name="contenu_id" value="<?= $contenu['id'] ?>">
                                                            <div class="form-check form-switch">
                                                                <input class="form-check-input toggle-actif" type="checkbox" <?= $contenu['est_actif'] ? 'checked' : '' ?> 
                                                                       data-id="<?= $contenu['id'] ?>">
                                                            </div>
                                                        </form>
                                                        <div>
                                                            <button class="btn btn-sm btn-outline-primary me-1 edit-content" 
                                                                    data-id="<?= $contenu['id'] ?>">
                                                                <i class="bi bi-pencil"></i>
                                                            </button>
                                                            <form method="POST" style="display:inline;">
                                                                <input type="hidden" name="contenu_id" value="<?= $contenu['id'] ?>">
                                                                <button type="submit" name="supprimer_contenu" class="btn btn-sm btn-outline-danger" 
                                                                        onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce contenu?')">
                                                                    <i class="bi bi-trash"></i>
                                                                </button>
                                                            </form>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.ckeditor.com/ckeditor5/36.0.1/classic/ckeditor.js"></script>
    <script src="https://unpkg.com/dropzone@5/dist/min/dropzone.min.js"></script>
    <script>
        // Initialiser CKEditor
        let editor;
        ClassicEditor
            .create(document.querySelector('#editor'), {
                toolbar: [
                    'heading', '|',
                    'bold', 'italic', 'underline', 'strikethrough', '|',
                    'link', 'bulletedList', 'numberedList', '|',
                    'blockQuote', 'insertTable', 'mediaEmbed', '|',
                    'undo', 'redo'
                ]
            })
            .then(instance => {
                editor = instance;
            })
            .catch(error => {
                console.error(error);
            });

        // Configuration des positions par section
        const positionsParSection = <?= json_encode($positions) ?>;
        
        // Mettre à jour les positions quand la section change
        document.getElementById('sectionContenu').addEventListener('change', function() {
            const section = this.value;
            const selectPosition = document.getElementById('sousPosition');
            selectPosition.innerHTML = '<option value="default">Défaut</option>';
            
            if (positionsParSection[section]) {
                positionsParSection[section].forEach(pos => {
                    const option = document.createElement('option');
                    option.value = pos;
                    option.textContent = pos;
                    selectPosition.appendChild(option);
                });
            }
        });
        
        // Afficher/masquer les champs selon le type de contenu
        document.getElementById('typeContenu').addEventListener('change', function() {
            const type = this.value;
            const texteGroup = document.getElementById('contenuTexteGroup');
            const fichierGroup = document.getElementById('contenuFichierGroup');
            
            if (type === 'texte') {
                texteGroup.classList.remove('d-none');
                fichierGroup.classList.add('d-none');
            } else {
                texteGroup.classList.add('d-none');
                fichierGroup.classList.remove('d-none');
            }
        });

        // Filtrer le contenu par section
        document.getElementById('filterSection').addEventListener('change', function() {
            const section = this.value;
            const cards = document.querySelectorAll('.content-card');
            
            cards.forEach(card => {
                if (section === '' || card.dataset.section === section) {
                    card.style.display = 'block';
                } else {
                    card.style.display = 'none';
                }
            });
        });
        
        // Configuration de Dropzone pour l'upload
        Dropzone.autoDiscover = false;
        const myDropzone = new Dropzone("#media-dropzone", {
            url: "gestion_contenu.php",
            paramName: "fichier_contenu",
            maxFiles: 1,
            acceptedFiles: "image/*,video/*",
            dictDefaultMessage: "Glissez-déposez votre fichier ici",
            dictFallbackMessage: "Votre navigateur ne supporte pas le drag & drop",
            dictFileTooBig: "Fichier trop volumineux ({{filesize}}MB). Max: {{maxFilesize}}MB",
            dictInvalidFileType: "Type de fichier non autorisé",
            dictCancelUpload: "Annuler",
            dictUploadCanceled: "Téléchargement annulé",
            dictRemoveFile: "Supprimer",
            dictMaxFilesExceeded: "Vous ne pouvez pas télécharger plus d'un fichier",
            success: function(file, response) {
                if (response.success) {
                    document.getElementById('url_existante').value = response.url;
                    this.removeAllFiles(true);
                    
                    // Afficher la prévisualisation
                    const previewContainer = document.getElementById('file-preview-container');
                    const filePreview = document.getElementById('file-preview');
                    const fileName = document.getElementById('file-name');
                    
                    previewContainer.style.display = 'block';
                    fileName.textContent = response.url.split('/').pop();
                    
                    if (response.url.match(/\.(jpg|jpeg|png|gif)$/i)) {
                        filePreview.src = '../' + response.url;
                        filePreview.classList.remove('d-none');
                    } else {
                        filePreview.classList.add('d-none');
                    }
                } else {
                    alert('Erreur: ' + response.error);
                }
            }
        });
        
        // Édition d'un contenu existant
        document.querySelectorAll('.edit-content').forEach(btn => {
            btn.addEventListener('click', function() {
                const id = this.getAttribute('data-id');
                
                fetch(`get_content.php?id=${id}`)
                    .then(response => response.json())
                    .then(data => {
                        document.getElementById('form-title').textContent = 'Modifier le contenu';
                        document.getElementById('contenu_id').value = data.id;
                        document.getElementById('typeContenu').value = data.type;
                        document.getElementById('sectionContenu').value = data.position;
                        document.getElementById('sectionContenu').dispatchEvent(new Event('change'));
                        
                        // Petit délai pour permettre la mise à jour des sous-positions
                        setTimeout(() => {
                            document.getElementById('sousPosition').value = data.sous_position || 'default';
                        }, 100);
                        
                        document.getElementById('titreContenu').value = data.titre || '';
                        document.getElementById('url_existante').value = data.url || '';
                        document.querySelector('input[name="ordre_contenu"]').value = data.ordre;
                        document.querySelector('textarea[name="style_contenu"]').value = data.style || '';
                        document.getElementById('estActif').checked = data.est_actif == 1;
                        
                        // Initialiser l'éditeur avec le contenu existant
                        if (editor) {
                            editor.setData(data.contenu || '');
                        }
                        
                        // Afficher la prévisualisation si fichier existant
                        if (data.url) {
                            const previewContainer = document.getElementById('file-preview-container');
                            const filePreview = document.getElementById('file-preview');
                            const fileName = document.getElementById('file-name');
                            
                            previewContainer.style.display = 'block';
                            fileName.textContent = data.url.split('/').pop();
                            
                            if (data.url.match(/\.(jpg|jpeg|png|gif)$/i)) {
                                filePreview.src = '../' + data.url;
                                filePreview.classList.remove('d-none');
                            } else {
                                filePreview.classList.add('d-none');
                            }
                        }
                        
                        // Afficher le bon groupe de champs selon le type
                        document.getElementById('typeContenu').dispatchEvent(new Event('change'));
                        
                        // Changer le bouton de soumission
                        document.getElementById('submit-btn').innerHTML = '<i class="bi bi-save"></i> Enregistrer';
                        document.getElementById('submit-btn').name = 'modifier_contenu';
                        document.getElementById('cancel-edit').style.display = 'block';
                        
                        // Faire défiler jusqu'au formulaire
                        document.getElementById('add-content-tab').click();
                        document.getElementById('content-form').scrollIntoView({ behavior: 'smooth' });
                    });
            });
        });
        
        // Annuler l'édition
        document.getElementById('cancel-edit').addEventListener('click', function() {
            document.getElementById('content-form').reset();
            document.getElementById('form-title').textContent = 'Ajouter un nouveau contenu';
            document.getElementById('submit-btn').innerHTML = '<i class="bi bi-save"></i> Enregistrer';
            document.getElementById('submit-btn').name = 'ajouter_contenu';
            this.style.display = 'none';
            document.getElementById('contenu_id').value = '';
            document.getElementById('url_existante').value = '';
            document.getElementById('file-preview-container').style.display = 'none';
            
            if (editor) {
                editor.setData('');
            }
        });
        
        // Toggle actif/inactif
        document.querySelectorAll('.toggle-actif').forEach(toggle => {
            toggle.addEventListener('change', function() {
                const id = this.getAttribute('data-id');
                fetch('gestion_contenu.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `toggle_actif=1&contenu_id=${id}`
                });
            });
        });
    </script>
</body>
</html>