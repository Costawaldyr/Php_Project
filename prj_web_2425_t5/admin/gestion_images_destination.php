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

$stmt = $dbh->prepare("SELECT * FROM destinations WHERE id = ?");
$stmt->execute([$destination_id]);
$destination = $stmt->fetch();

if (!$destination) {
    header('Location: gestion_destinations.php?error=Destination non trouvée');
    exit;
}

$uploadDir = '../uploads/destinations/';
if (!file_exists($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

if (isset($_POST['upload_images'])) {
    if (!empty($_FILES['images']['name'][0])) {
        try {
            $dbh->beginTransaction();
            
            foreach ($_FILES['images']['tmp_name'] as $key => $tmp_name) {
                $fileName = uniqid() . '_' . basename($_FILES['images']['name'][$key]);
                $uploadFile = $uploadDir . $fileName;
                
                if (move_uploaded_file($tmp_name, $uploadFile)) {
                    $stmt = $dbh->prepare("INSERT INTO destination_images (destination_id, image_path) VALUES (?, ?)");
                    $stmt->execute([$destination_id, $fileName]);
                    $success = true;
                }
            }
            
            $dbh->commit();
            
            if ($success ?? false) {
                header('Location: gestion_images_destination.php?id=' . $destination_id . '&success=Images uploadées avec succès');
                exit;
            }
        } catch (Exception $e) {
            $dbh->rollBack();
            header('Location: gestion_images_destination.php?id=' . $destination_id . '&error=Erreur lors de l\'upload: ' . $e->getMessage());
            exit;
        }
    }
}

if (isset($_GET['delete_image'])) {
    $image_name = $_GET['delete_image'];
    
    try {
        $dbh->beginTransaction();
        
        $image_path = $uploadDir . $image_name;
        if (file_exists($image_path)) {
            unlink($image_path);
        }
        
        $stmt = $dbh->prepare("DELETE FROM destination_images WHERE destination_id = ? AND image_path = ?");
        $stmt->execute([$destination_id, $image_name]);
        
        $dbh->commit();
        header('Location: gestion_images_destination.php?id=' . $destination_id . '&success=Image supprimée');
        exit;
    } catch (Exception $e) {
        $dbh->rollBack();
        header('Location: gestion_images_destination.php?id=' . $destination_id . '&error=Erreur lors de la suppression: ' . $e->getMessage());
        exit;
    }
}

$stmt = $dbh->prepare("SELECT image_path FROM destination_images WHERE destination_id = ?");
$stmt->execute([$destination_id]);
$images = $stmt->fetchAll(PDO::FETCH_COLUMN);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des Images - <?= htmlspecialchars($destination['ville']) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        .image-thumbnail {
            width: 200px;
            height: 200px;
            object-fit: cover;
            margin: 5px;
            border-radius: 5px;
            transition: transform 0.3s;
        }
        .image-thumbnail:hover {
            transform: scale(1.05);
        }
        .image-container {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            margin-top: 20px;
        }
        .image-wrapper {
            position: relative;
        }
        .delete-btn {
            position: absolute;
            top: 10px;
            right: 10px;
            opacity: 0;
            transition: opacity 0.3s;
        }
        .image-wrapper:hover .delete-btn {
            opacity: 1;
        }
        #imagePreview {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-top: 10px;
        }
        .preview-image {
            width: 100px;
            height: 100px;
            object-fit: cover;
            border-radius: 5px;
        }
    </style>
</head>
<body>
    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1>Gestion des images - <?= htmlspecialchars($destination['ville']) ?>, <?= htmlspecialchars($destination['pays']) ?></h1>
            <a href="edit_destination.php?id=<?= $destination_id ?>" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> Retour à la modification
            </a>
        </div>

        <?php if (isset($_GET['success'])): ?>
            <div class="alert alert-success"><?= htmlspecialchars($_GET['success']) ?></div>
        <?php endif; ?>
        <?php if (isset($_GET['error'])): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($_GET['error']) ?></div>
        <?php endif; ?>

        <div class="card mb-4">
            <div class="card-header bg-primary text-white">
                <i class="bi bi-cloud-arrow-up"></i> Ajouter des images
            </div>
            <div class="card-body">
                <form method="POST" enctype="multipart/form-data">
                    <div class="mb-3">
                        <label for="images" class="form-label">Sélectionnez des images</label>
                        <input type="file" class="form-control" id="images" name="images[]" multiple accept="image/*" required>
                        <div id="imagePreview"></div>
                    </div>
                    <button type="submit" name="upload_images" class="btn btn-primary">
                        <i class="bi bi-upload"></i> Uploader les images
                    </button>
                </form>
            </div>
        </div>

        <div class="card">
            <div class="card-header bg-info text-white">
                <i class="bi bi-images"></i> Images existantes (<?= count($images) ?>)
            </div>
            <div class="card-body">
                <?php if (empty($images)): ?>
                    <div class="alert alert-info">Aucune image n'a été ajoutée pour cette destination</div>
                <?php else: ?>
                    <div class="image-container">
                        <?php foreach ($images as $image): ?>
                            <div class="image-wrapper">
                                <img src="<?= $uploadDir . htmlspecialchars($image) ?>" class="image-thumbnail rounded">
                                <a href="gestion_images_destination.php?id=<?= $destination_id ?>&delete_image=<?= urlencode($image) ?>" 
                                   class="btn btn-danger delete-btn"
                                   onclick="return confirm('Êtes-vous sûr de vouloir supprimer cette image ?')">
                                    <i class="bi bi-trash"></i>
                                </a>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
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
                        img.className = 'preview-image';
                        previewContainer.appendChild(img);
                    }
                    reader.readAsDataURL(file);
                }
            }
        });
    </script>
</body>
</html>