<?php
require_once('../data_base/connection.php');
require_once('fonctions.php');

$sBody = '';
$pageEnCours = $_SERVER['PHP_SELF'];

// Gestion de la suppression des utilisateurs
if (isset($_POST['supprimer_selection']) AND !empty($_POST['utilisateurs_a_supprimer'])) 
{
    $ids = implode(',', array_map('intval', $_POST['utilisateurs_a_supprimer']));
    try 
    {
        $sSQL = "DELETE FROM users WHERE id IN ($ids)";
        $stmt = $dbh->prepare($sSQL);
        $stmt->execute();
        $sBody .= "<div class='alert alert-success'>Les utilisateurs sélectionnés ont été supprimés.</div>";
    } 
    catch (PDOException $e) 
    {
        $sBody .= "<div class='alert alert-danger'>Erreur lors de la suppression : " . $e->getMessage() . "</div>";
    }
}


if (isset($_GET['page'])) 
{
    switch($_GET['page']) 
    {
        case 'showdata':
            $sBody .= showUsers($dbh);
            break;
        
        case 'createdata':
            $sBody .= formCreateUser($dbh, $pageEnCours);
            break;
            
        case 'importdata':
            $sBody .= formUploadFile($pageEnCours);
            break;
    }
} 
else 
{
    $sBody .= showUsers($dbh);
}

if(isset($_POST['actionPOST'])) 
{
    switch($_POST['actionPOST']) 
    {
        case 'uploadCSV':
            if(isset($_FILES['fichierCSV'])) 
            {
                $aResult = uploadCSV($_FILES['fichierCSV']);
                
                if($aResult['state'] === true) 
                {
                    $sBody .= formUpdate('upload/annuaire.txt', $pageEnCours, $dbh);
                } 
                else 
                {
                    $sBody .= "<div class='alert alert-danger'>Problème lors de l'upload du fichier: " . ($aResult['message'] ?? 'Erreur inconnue') . "</div>";
                }
            } 
            else 
            {
                $sBody .= "<div class='alert alert-danger'>Aucun fichier n'a été uploadé.</div>";
            }
            break;
        
        case 'importData':
            if(isset($_POST['data'])) 
            {
                $sBody .= update($_POST['data'], $dbh);
            } 
            else
            {
                $sBody .= "<div class='alert alert-danger'>Aucune donnée à importer.</div>";
            }
            break;
            
        case 'createUser':
            $sBody .= createUser($_POST, $_FILES, $dbh);
            $sBody .= formCreateUser($dbh, $pageEnCours);
            break;
    }
}

// Début du HTML
$sDebutHtml = <<<EOT
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Gestion des Utilisateurs</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="style.css" rel="stylesheet" type="text/css">
</head>
<body>
    <div class="container mt-4">
EOT;

// Menu de navigation
$sMenu = <<<EOT
        <h1 class="text-center mb-4">Gestion des Utilisateurs</h1>
        <a href="../admin.php" class="btn btn-primary mb-3">Retour</a>
        
        <div class="card mb-4">
            <div class="card-header">
                <ul class="nav nav-tabs card-header-tabs">
                    <li class="nav-item">
                        <a class="nav-link" href="{$pageEnCours}?page=showdata">Voir les utilisateurs</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{$pageEnCours}?page=createdata">Créer un utilisateur</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{$pageEnCours}?page=importdata">Importer des données</a>
                    </li>
                </ul>
            </div>
            <div class="card-body">
EOT;

// Fin du HTML
$sFinHtml = <<<EOT
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
EOT;

// Affichage final
echo $sDebutHtml . $sMenu . $sBody . $sFinHtml;

