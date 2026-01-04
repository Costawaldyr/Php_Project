<?php
session_start();
require_once('../data_base/connection.php');

if (!isset($_SESSION['user_id']) || $_SESSION['access_level'] !== 'admin') {
    header('Location: ../pages/login.php');
    exit;
}

$destination_id = $_GET['id'] ?? null;
if (!$destination_id) {
    header('Location: gestion_destinations.php?error=Destination invalide');
    exit;
}

// Récupérer les infos de la destination
$stmt = $dbh->prepare("SELECT * FROM destinations WHERE id = ?");
$stmt->execute([$destination_id]);
$destination = $stmt->fetch();

if (!$destination) {
    header('Location: gestion_destinations.php?error=Destination non trouvée');
    exit;
}

// Traitement de la mise à jour
if (isset($_POST['update_destination'])) {
    // Récupération des données du formulaire
    $pays = $_POST['pays'];
    $ville = $_POST['ville'];
    $type_activites = $_POST['type_activites'];
    $activite_jeune = $_POST['activite_jeune'] ?? null;
    $budget_moyen = $_POST['budget_moyen'] ?? null;
    $conseils = $_POST['conseils'] ?? null;
    $endroits_visiter = $_POST['endroits_visiter'] ?? null;
    $langue = $_POST['langue'] ?? null;
    $monnaie = $_POST['monnaie'] ?? null;
    $transport_commun = $_POST['transport_commun'] ?? null;
    $peuples_culture = $_POST['peuples_culture'] ?? null;
    $info_pays = $_POST['info_pays'] ?? null;

    try {
        $dbh->beginTransaction();

        // Préparation et exécution de la requête UPDATE
        $stmt = $dbh->prepare("
            UPDATE destinations SET
                pays = :pays,
                ville = :ville,
                type_activites = :type_activites,
                activite_jeune = :activite_jeune,
                budget_moyen = :budget_moyen,
                conseils = :conseils,
                endroits_visiter = :endroits_visiter,
                langue = :langue,
                monnaie = :monnaie,
                transport_commun = :transport_commun,
                peuples_culture = :peuples_culture,
                info_pays = :info_pays
            WHERE id = :id
        ");

        $stmt->execute([
            ':pays' => $pays,
            ':ville' => $ville,
            ':type_activites' => $type_activites,
            ':activite_jeune' => $activite_jeune,
            ':budget_moyen' => $budget_moyen,
            ':conseils' => $conseils,
            ':endroits_visiter' => $endroits_visiter,
            ':langue' => $langue,
            ':monnaie' => $monnaie,
            ':transport_commun' => $transport_commun,
            ':peuples_culture' => $peuples_culture,
            ':info_pays' => $info_pays,
            ':id' => $destination_id
        ]);

        // Gestion des nouvelles images
        if (!empty($_FILES['images']['name'][0])) {
            $uploadDir = '../uploads/destinations/';
            if (!file_exists($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }

            foreach ($_FILES['images']['tmp_name'] as $key => $tmp_name) {
                $fileName = uniqid() . '_' . basename($_FILES['images']['name'][$key]);
                $uploadFile = $uploadDir . $fileName;

                if (move_uploaded_file($tmp_name, $uploadFile)) {
                    $stmt = $dbh->prepare("INSERT INTO destination_images (destination_id, image_path) VALUES (?, ?)");
                    $stmt->execute([$destination_id, $fileName]);
                }
            }
        }

        $dbh->commit();
        header('Location: gestion_destination.php?success=Destination mise à jour avec succès');
        exit;
    } catch (PDOException $e) {
        $dbh->rollBack();
        header('Location: edit_destination.php?id=' . $destination_id . '&error=Erreur lors de la mise à jour: ' . $e->getMessage());
        exit;
    }
}

// Récupérer les images existantes
$stmt = $dbh->prepare("SELECT image_path FROM destination_images WHERE destination_id = ?");
$stmt->execute([$destination_id]);
$images = $stmt->fetchAll(PDO::FETCH_COLUMN);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modifier Destination - <?= htmlspecialchars($destination['ville']) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        .image-preview {
            width: 150px;
            height: 150px;
            object-fit: cover;
            margin: 5px;
            border-radius: 5px;
        }
        .image-container {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin: 10px 0;
        }
        #newImagesPreview {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-top: 10px;
        }
    </style>
</head>
<body>
    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1>Modifier <?= htmlspecialchars($destination['ville']) ?>, <?= htmlspecialchars($destination['pays']) ?></h1>
            <a href="gestion_destinations.php" class="btn btn-secondary">Retour</a>
        </div>

        <?php if (isset($_GET['success'])): ?>
            <div class="alert alert-success"><?= htmlspecialchars($_GET['success']) ?></div>
        <?php endif; ?>
        <?php if (isset($_GET['error'])): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($_GET['error']) ?></div>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data">
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="pays" class="form-label">Pays *</label>
                        <input type="text" class="form-control" id="pays" name="pays" 
                               value="<?= htmlspecialchars($destination['pays']) ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="ville" class="form-label">Ville *</label>
                        <input type="text" class="form-control" id="ville" name="ville" 
                               value="<?= htmlspecialchars($destination['ville']) ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="type_activites" class="form-label">Type d'activités *</label>
                        <select class="form-select" id="type_activites" name="type_activites" required>
                            <option value="camping" <?= $destination['type_activites'] === 'camping' ? 'selected' : '' ?>>Camping</option>
                            <option value="auberge" <?= $destination['type_activites'] === 'auberge' ? 'selected' : '' ?>>Auberge</option>
                            <option value="randonnée" <?= $destination['type_activites'] === 'randonnée' ? 'selected' : '' ?>>Randonnée</option>
                            <option value="plage" <?= $destination['type_activites'] === 'plage' ? 'selected' : '' ?>>Plage</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="activite_jeune" class="form-label">Activités jeunes</label>
                        <input type="text" class="form-control" id="activite_jeune" name="activite_jeune"
                               value="<?= htmlspecialchars($destination['activite_jeune'] ?? '') ?>">
                    </div>
                    <div class="mb-3">
                        <label for="budget_moyen" class="form-label">Budget moyen (€)</label>
                        <input type="number" step="0.01" class="form-control" id="budget_moyen" name="budget_moyen"
                               value="<?= htmlspecialchars($destination['budget_moyen'] ?? '') ?>">
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="langue" class="form-label">Langue</label>
                        <input type="text" class="form-control" id="langue" name="langue"
                               value="<?= htmlspecialchars($destination['langue'] ?? '') ?>">
                    </div>
                    <div class="mb-3">
                        <label for="monnaie" class="form-label">Monnaie</label>
                        <input type="text" class="form-control" id="monnaie" name="monnaie"
                               value="<?= htmlspecialchars($destination['monnaie'] ?? '') ?>">
                    </div>
                    <div class="mb-3">
                        <label for="images" class="form-label">Ajouter des images</label>
                        <input type="file" class="form-control" id="images" name="images[]" multiple accept="image/*">
                        <div id="newImagesPreview"></div>
                    </div>
                </div>
            </div>
            
            <div class="mb-3">
                <label class="form-label">Images actuelles</label>
                <div class="image-container">
                    <?php if (empty($images)): ?>
                        <p class="text-muted">Aucune image pour cette destination</p>
                    <?php else: ?>
                        <?php foreach ($images as $image): ?>
                            <img src="../uploads/destinations/<?= htmlspecialchars($image) ?>" class="image-preview">
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
                <a href="gestion_images_destination.php?id=<?= $destination_id ?>" class="btn btn-sm btn-outline-primary">
                    Gérer les images
                </a>
            </div>

            <div class="mb-3">
                <label for="conseils" class="form-label">Conseils</label>
                <textarea class="form-control" id="conseils" name="conseils" rows="3"><?= htmlspecialchars($destination['conseils'] ?? '') ?></textarea>
            </div>
            <div class="mb-3">
                <label for="endroits_visiter" class="form-label">Endroits à visiter</label>
                <textarea class="form-control" id="endroits_visiter" name="endroits_visiter" rows="3"><?= htmlspecialchars($destination['endroits_visiter'] ?? '') ?></textarea>
            </div>
            <div class="mb-3">
                <label for="transport_commun" class="form-label">Transports communs</label>
                <textarea class="form-control" id="transport_commun" name="transport_commun" rows="3"><?= htmlspecialchars($destination['transport_commun'] ?? '') ?></textarea>
            </div>
            <div class="mb-3">
                <label for="peuples_culture" class="form-label">Peuples et culture</label>
                <textarea class="form-control" id="peuples_culture" name="peuples_culture" rows="3"><?= htmlspecialchars($destination['peuples_culture'] ?? '') ?></textarea>
            </div>
            <div class="mb-3">
                <label for="info_pays" class="form-label">Informations sur le pays</label>
                <textarea class="form-control" id="info_pays" name="info_pays" rows="3"><?= htmlspecialchars($destination['info_pays'] ?? '') ?></textarea>
            </div>
            
            <div class="d-flex justify-content-between mt-4">
                <a href="gestion_destination.php" class="btn btn-secondary">Annuler</a>
                <button type="submit" name="update_destination" class="btn btn-primary">
                    <i class="bi bi-save"></i> Enregistrer les modifications
                </button>
            </div>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Aperçu des nouvelles images avant upload
        document.getElementById('images').addEventListener('change', function(e) {
            const previewContainer = document.getElementById('newImagesPreview');
            previewContainer.innerHTML = '';
            
            for (const file of e.target.files) {
                if (file.type.startsWith('image/')) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        const img = document.createElement('img');
                        img.src = e.target.result;
                        img.className = 'image-preview';
                        previewContainer.appendChild(img);
                    }
                    reader.readAsDataURL(file);
                }
            }
        });
    </script>
</body>
</html>