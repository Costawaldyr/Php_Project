<?php
session_start();
require_once('../data_base/connection.php');

if (!isset($_SESSION['user_id']) || $_SESSION['access_level'] !== 'admin') 
{
    header('Location: ../pages/login.php');
    exit;
}

$sSQL = "SELECT * FROM destinations";
$stmt = $dbh->prepare($sSQL);
$stmt->execute();
$destinations = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (isset($_POST['create_destination'])) 
{
    $pays = $_POST['pays'];
    $ville = $_POST['ville'];
    $type_activites = $_POST['type_activites'];
    $activite_jeune = $_POST['activite_jeune'];
    $budget_moyen = $_POST['budget_moyen'];
    $conseils = $_POST['conseils'];
    $endroits_visiter = $_POST['endroits_visiter'];
    $langue = $_POST['langue'];
    $monnaie = $_POST['monnaie'];
    $transport_commun = $_POST['transport_commun'];
    $peuples_culture = $_POST['peuples_culture'];
    $info_pays = $_POST['info_pays'];

    try {
        $dbh->beginTransaction();

        $sSQL1 = "INSERT INTO destinations (pays, ville, type_activites, activite_jeune, budget_moyen, conseils, endroits_visiter, langue, monnaie, transport_commun, peuples_culture, info_pays) 
        VALUES (:pays, :ville, :type_activites, :activite_jeune, :budget_moyen, :conseils, :endroits_visiter, :langue, :monnaie, :transport_commun, :peuples_culture, :info_pays)";
        $stmt1 = $dbh->prepare($sSQL1);
        $stmt1->bindParam(':pays', $pays);
        $stmt1->bindParam(':ville', $ville);
        $stmt1->bindParam(':type_activites', $type_activites);
        $stmt1->bindParam(':activite_jeune', $activite_jeune);
        $stmt1->bindParam(':budget_moyen', $budget_moyen);
        $stmt1->bindParam(':conseils', $conseils);
        $stmt1->bindParam(':endroits_visiter', $endroits_visiter);
        $stmt1->bindParam(':langue', $langue);
        $stmt1->bindParam(':monnaie', $monnaie);
        $stmt1->bindParam(':transport_commun', $transport_commun);
        $stmt1->bindParam(':peuples_culture', $peuples_culture);
        $stmt1->bindParam(':info_pays', $info_pays);
        $stmt1 = $dbh->prepare($sSQL1);

        $stmt->execute();
        $destination_id = $dbh->lastInsertId();

        // Gestion des images
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
        header('Location: gestion_destinations.php?success=Destination créée avec succès');
        exit;
    } catch (Exception $e) {
        $dbh->rollBack();
        header('Location: gestion_destinations.php?error=Erreur lors de la création: ' . $e->getMessage());
        exit;
    }
}

if (isset($_GET['delete'])) {
    $destination_id = $_GET['delete'];
    
    try {
        $dbh->beginTransaction();

        // Suppression des images associées
        $stmt = $dbh->prepare("SELECT image_path FROM destination_images WHERE destination_id = ?");
        $stmt->execute([$destination_id]);
        $images = $stmt->fetchAll(PDO::FETCH_COLUMN);

        foreach ($images as $image) {
            $filePath = '../uploads/destinations/' . $image;
            if (file_exists($filePath)) {
                unlink($filePath);
            }
        }

        // Suppression des entrées images
        $stmt = $dbh->prepare("DELETE FROM destination_images WHERE destination_id = ?");
        $stmt->execute([$destination_id]);

        // Suppression de la destination
        $stmt = $dbh->prepare("DELETE FROM destinations WHERE id = ?");
        $stmt->execute([$destination_id]);

        $dbh->commit();
        header('Location: gestion_destinations.php?success=Destination supprimée avec succès');
        exit;
    } catch (Exception $e) {
        $dbh->rollBack();
        header('Location: gestion_destinations.php?error=Erreur lors de la suppression: ' . $e->getMessage());
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des Destinations</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        .card-img-top {
            height: 200px;
            object-fit: cover;
        }
        .img-thumbnail {
            max-width: 100px;
            max-height: 100px;
        }
        .form-container {
            background-color: #f8f9fa;
            padding: 20px;
            border-radius: 5px;
            margin-bottom: 30px;
        }
        .image-preview-container {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-top: 10px;
        }
        .image-preview {
            width: 100px;
            height: 100px;
            object-fit: cover;
            border-radius: 5px;
        }
    </style>
</head>
<body>
    <div class="container mt-4">
    <a href="../admin.php" class="btn btn-primary mb-3">Retour</a>
    <h1 class="mb-4">Gestion des Destinations</h1>
    <h3 class="mt-5">Liste des destinations</h3>
    
        <div class="row">
            <?php foreach ($destinations as $destination): ?>
                <?php 
                $stmt = $dbh->prepare("SELECT image_path FROM destination_images WHERE destination_id = ?");
                $stmt->execute([$destination['id']]);
                $images = $stmt->fetchAll(PDO::FETCH_COLUMN);
                ?>
                <div class="col-md-4 mb-4">
                    <div class="card h-100">
                        <?php if (!empty($images)): ?>
                            <img src="../uploads/destinations/<?= htmlspecialchars($images[0]) ?>" class="card-img-top" alt="<?= htmlspecialchars($destination['ville']) ?>">
                        <?php else: ?>
                            <div class="card-img-top bg-light d-flex align-items-center justify-content-center">
                                <i class="bi bi-image text-muted" style="font-size: 3rem;"></i>
                            </div>
                        <?php endif; ?>
                        <div class="card-body">
                            <h5 class="card-title"><?= htmlspecialchars($destination['ville']) ?>, <?= htmlspecialchars($destination['pays']) ?></h5>
                            <p class="card-text">
                                <span class="badge bg-info"><?= htmlspecialchars(ucfirst($destination['type_activites'])) ?></span>
                                <?php if ($destination['budget_moyen']): ?>
                                    <span class="badge bg-success ms-1">~<?= htmlspecialchars($destination['budget_moyen']) ?>€</span>
                                <?php endif; ?>
                            </p>
                        </div>
                        <div class="card-footer bg-transparent">
                            <div class="d-flex justify-content-between">
                                <a href="edit_destination.php?id=<?= $destination['id'] ?>" class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-pencil"></i> Modifier
                                </a>
                                <a href="gestion_destinations.php?delete=<?= $destination['id'] ?>" 
                                   class="btn btn-sm btn-outline-danger" 
                                   onclick="return confirm('Êtes-vous sûr de vouloir supprimer cette destination ?')">
                                    <i class="bi bi-trash"></i> Supprimer
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        

        <?php if (isset($_GET['success'])): ?>
            <div class="alert alert-success"><?= htmlspecialchars($_GET['success']) ?></div>
        <?php endif; ?>
        <?php if (isset($_GET['error'])): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($_GET['error']) ?></div>
        <?php endif; ?>

        <div class="form-container">
            <h3>Ajouter une nouvelle destination</h3>
            <form method="POST" enctype="multipart/form-data">
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="pays" class="form-label">Pays *</label>
                            <input type="text" class="form-control" id="pays" name="pays" required>
                        </div>
                        <div class="mb-3">
                            <label for="ville" class="form-label">Ville *</label>
                            <input type="text" class="form-control" id="ville" name="ville" required>
                        </div>
                        <div class="mb-3">
                            <label for="type_activites" class="form-label">Type d'activités *</label>
                            <select class="form-select" id="type_activites" name="type_activites" required>
                                <option value="camping">Camping</option>
                                <option value="auberge">Auberge</option>
                                <option value="randonnée">Randonnée</option>
                                <option value="plage">Plage</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="activite_jeune" class="form-label">Activités jeunes</label>
                            <input type="text" class="form-control" id="activite_jeune" name="activite_jeune">
                        </div>
                        <div class="mb-3">
                            <label for="budget_moyen" class="form-label">Budget moyen (€)</label>
                            <input type="number" step="0.01" class="form-control" id="budget_moyen" name="budget_moyen">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="langue" class="form-label">Langue</label>
                            <input type="text" class="form-control" id="langue" name="langue">
                        </div>
                        <div class="mb-3">
                            <label for="monnaie" class="form-label">Monnaie</label>
                            <input type="text" class="form-control" id="monnaie" name="monnaie">
                        </div>
                        <div class="mb-3">
                            <label for="images" class="form-label">Images</label>
                            <input type="file" class="form-control" id="images" name="images[]" multiple accept="image/*">
                            <div class="image-preview-container" id="imagePreview"></div>
                        </div>
                    </div>
                </div>
                <div class="mb-3">
                    <label for="conseils" class="form-label">Conseils</label>
                    <textarea class="form-control" id="conseils" name="conseils" rows="3"></textarea>
                </div>
                <div class="mb-3">
                    <label for="endroits_visiter" class="form-label">Endroits à visiter</label>
                    <textarea class="form-control" id="endroits_visiter" name="endroits_visiter" rows="3"></textarea>
                </div>
                <div class="mb-3">
                    <label for="transport_commun" class="form-label">Transports communs</label>
                    <textarea class="form-control" id="transport_commun" name="transport_commun" rows="3"></textarea>
                </div>
                <div class="mb-3">
                    <label for="peuples_culture" class="form-label">Peuples et culture</label>
                    <textarea class="form-control" id="peuples_culture" name="peuples_culture" rows="3"></textarea>
                </div>
                <div class="mb-3">
                    <label for="info_pays" class="form-label">Informations sur le pays</label>
                    <textarea class="form-control" id="info_pays" name="info_pays" rows="3"></textarea>
                </div>
                <button type="submit" name="create_destination" class="btn btn-primary">Créer la destination</button>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Aperçu des images avant upload
        document.getElementById('images').addEventListener('change', function(e) {
            const previewContainer = document.getElementById('imagePreview');
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