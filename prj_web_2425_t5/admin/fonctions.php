<?php


function uploadCSV(&$file)
{
    $retour = array();
    $retour['state'] = true;
    $retour['message'] = 'ras';
    

    $dossier = 'upload/';
    $fichier = basename($file['name']);
    $taille_maxi = 100000;
    $taille = filesize($file['tmp_name']);
    $extensions = array('.csv', '.txt');
    $extension = strrchr($file['name'], '.');

    if (!in_array($extension, $extensions))
    {
        $retour['mssage'] = 'Vous devez uploader un fichier de type txt ou csv';
        $retour['state'] = false ;
    }

    if ($taille>$taille_maxi)
    {
        $retour['message'] = 'Le fichier eest trop gros';
        $retour['state'] = false;
    }

    if ($retour['state'] !== false)
    {
        $fichier = strtr($fichier, 
			  'ÀÁÂÃÄÅÇÈÉÊËÌÍÎÏÒÓÔÕÖÙÚÛÜÝàáâãäåçèéêëìíîïðòóôõöùúûüýÿ', 
			  'AAAAAACEEEEIIIIOOOOOUUUUYaaaaaaceeeeiiiioooooouuuuyy');
        $fichier = preg_replace('/([^.a-z0-9]+)/i', '-', $fichier);
        $file['name'] = $fichier;

        if(move_uploaded_file($file['tmp_name'], $dossier . $fichier))
        {
            $retour['state'] = true;
			$retour['message'] =  'Upload effectué avec succès !';
        }
        else
        {
            $retour['state'] = false;
			$retour['message'] = 'Echec de l\'upload !';
        }
    }
    return $retour;

}

function formUploadFile($_pageEnCours)
{
	$formUpload = <<<EOT
		<form method="POST" action="{$_pageEnCours}" enctype="multipart/form-data">
				<!-- On limite le fichier à 100Ko -->
				<input type="hidden" name="MAX_FILE_SIZE" value="100000" />
				Fichier : <input type="file" name="fichierCSV" />
				<input type="hidden" name="actionPOST" value="uploadCSV">
				<input type="submit" value="Envoyer le fichier" />
		</form>
EOT;
	return $formUpload;
}
//#######################################################################################
function showUsers($_dbh)
{
    $sTable  = '<form method="post" action="">';
    $sTable .= '<table class="prettyTable">';
    $sTable .= '    <thead >';
    $sTable .= '        <tr>';
    $sTable .= '            <th><input type="checkbox" id="selectAll" onclick="toggleCheckboxes(this)"></th>';
    $sTable .= '            <th>ID</th>';
    $sTable .= '            <th>Nom</th>';
    $sTable .= '            <th>Prénom</th>';
    $sTable .= '            <th>Email</th>';
    $sTable .= '            <th>Genre</th>';
    $sTable .= '            <th>Téléphone</th>';
    $sTable .= '            <th>Statut</th>';
    $sTable .= '            <th>Rôle</th>';
    $sTable .= '        </tr>';
    $sTable .= '    </thead>';
    $sTable .= '    <tbody>';

    $sQuery = 'SELECT id, nom, prenom, email, gender, telephone, statut, access_level FROM users';
    $stmt = $_dbh->prepare($sQuery);
    $stmt->execute();

    while($row = $stmt->fetch(PDO::FETCH_ASSOC)) 
    {
        $sTable .= '<tr>';
        $sTable .= '    <td><input type="checkbox" name="utilisateurs_a_supprimer[]" value="'.$row['id'].'"></td>';
        $sTable .= '    <td>'.$row['id'].'</td>';
        $sTable .= '    <td>'.$row['nom'].'</td>';
        $sTable .= '    <td>'.$row['prenom'].'</td>';
        $sTable .= '    <td>'.$row['email'].'</td>';
        $sTable .= '    <td>'.$row['gender'].'</td>';
        $sTable .= '    <td>'.$row['telephone'].'</td>';
        $sTable .= '    <td>'.$row['statut'].'</td>';
        $sTable .= '    <td>'.$row['access_level'].'</td>';
        $sTable .= '</tr>';
    }

    $sTable .= '</tbody>';
    $sTable .= '</table>';
    $sTable .= '<button type="submit" name="supprimer_selection" class="btn btn-danger">Supprimer les utilisateurs sélectionnés</button>';
    $sTable .= '</form>';

    // JavaScript pour le bouton "tout sélectionner"
    $sTable .= '
    <script>
        function toggleCheckboxes(source) {
            let checkboxes = document.querySelectorAll("input[name=\'utilisateurs_a_supprimer[]\']");
            checkboxes.forEach(function(checkbox) {
                checkbox.checked = source.checked;
            });
        }
    </script>';

    return $sTable;
}

//#######################################################################################
// formCreateUser($_dbh, $_pageEnCours)                                                #
// Affiche le formulaire de création d'un nouvel utilisateur                           #
//#######################################################################################
function formCreateUser($_dbh, $_pageEnCours)
{
    $form = <<<EOT
    <form method="POST" action="{$_pageEnCours}" enctype="multipart/form-data">
        <input type="hidden" name="actionPOST" value="createUser">
        
        <div class="row mb-3">
            <div class="col-md-6">
                <label class="form-label">Nom</label>
                <input type="text" class="form-control" name="nom" required>
            </div>
            <div class="col-md-6">
                <label class="form-label">Prénom</label>
                <input type="text" class="form-control" name="prenom" required>
            </div>
        </div>
        
        <div class="row mb-3">
            <div class="col-md-6">
                <label class="form-label">Email</label>
                <input type="email" class="form-control" name="email" required>
            </div>
            <div class="col-md-6">
                <label class="form-label">Mot de passe</label>
                <input type="password" class="form-control" name="password" required>
            </div>
        </div>
        
        <div class="row mb-3">
            <div class="col-md-4">
                <label class="form-label">Genre</label>
                <select class="form-select" name="gender" required>
                    <option value="M">Masculin</option>
                    <option value="F">Féminin</option>
                    <option value="Autre">Autre</option>
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label">Date de naissance</label>
                <input type="date" class="form-control" name="date_naissance">
            </div>
            <div class="col-md-4">
                <label class="form-label">Téléphone</label>
                <input type="tel" class="form-control" name="telephone">
            </div>
        </div>
        
        <div class="mb-3">
            <label class="form-label">Adresse</label>
            <textarea class="form-control" name="adresse" rows="2"></textarea>
        </div>
        
        <div class="row mb-3">
            <div class="col-md-6">
                <label class="form-label">Statut</label>
                <select class="form-select" name="statut">
                    <option value="actif">Actif</option>
                    <option value="suspendu">Suspendu</option>
                    <option value="désactivé">Désactivé</option>
                </select>
            </div>
            <div class="col-md-6">
                <label class="form-label">Rôle</label>
                <select class="form-select" name="access_level">
                    <option value="user">Utilisateur</option>
                    <option value="admin">Administrateur</option>
                </select>
            </div>
        </div>
        
        <div class="mb-3">
            <label class="form-label">Photo de profil</label>
            <input type="file" class="form-control" name="profile_picture" accept="image/*">
        </div>
        
        <button type="submit" class="btn btn-primary">Créer l'utilisateur</button>
    </form>
EOT;

    return $form;
}

//#######################################################################################
// createUser($_data, $_files, $_dbh)                                                  #
// Crée un nouvel utilisateur dans la base de données                                   #
//#######################################################################################
function createUser($_data, $_files, $_dbh)
{
    try {
        
        // Préparation de la requête
        $sQuery = 'INSERT INTO users (nom, prenom, email, password, gender, date_naissance, adresse, telephone, access_level) VALUES (:nom, :prenom, :email, :password, :gender, :date_naissance, :adresse, :telephone, :access_level,)';
        $stmt = $_dbh->prepare($sQuery);

        $stmt->bindParam(':nom', $_data['nom']);
        $stmt->bindParam(':prenom', $_data['prenom']);
        $stmt->bindParam(':email', $_data['email']);
        $stmt->bindParam(':password', $_data['password']);
        $stmt->bindParam(':gender', $_data['gender']);
        $stmt->bindParam(':date_naissance', $_data['date_naissance']);
        $stmt->bindParam(':adresse', $_data['adresse']);
        $stmt->bindParam(':telephone', $_data['telephone']);
        $stmt->bindParam(':access_level', $_data['access_level']);
        $stmt->bindParam(':statut', $_data['statut']);

        $stmt->execute();
        return "<div class='alert alert-success'>L'utilisateur a été créé avec succès!</div>";
        
    } 
    catch(PDOException $e) 
    {
        return "<div class='alert alert-danger'>Erreur lors de la création de l'utilisateur: " . $e->getMessage() . "</div>";
    }
}




function formUpdate($_nomFichier,$_pageEnCours,$_dbh)
{
	
	$handle = fopen($_nomFichier, 'rb');
	if($handle === false)
	{
		return 'probleme ouverture fichier';
	}

	try
	{
		
		$sPrenom = '';
		$sNom = '';
		$sAdresse = '';
		$sTelephone = '';
        $sEmail = '';
        $sGenre = '';
        $sStatut = '';

		$sQuery0 = 'INSERT INTO users (prenom,nom,adresse,telephone,gender,statut, email) VALUES (:prenom, :nom, :adresse, :telephone, :gender, :email, :statut)';
		$stmt0 = $_dbh->prepare($sQuery0);
		
		$stmt0->bindParam(':prenom', $sPrenom);
		$stmt0->bindParam(':nom', $sNom);
		$stmt0->bindParam(':adresse', $sAdresse);
		$stmt0->bindParam(':email', $sEmail);
        $stmt0->bindParam(':gender', $sGenre);
        $stmt0->bindParam(':statut', $sStatut);
        $stmt0->bindParam(':telephone', $sTelephone);

		
		$sQuery1 = 'SELECT prenom,nom,adresse,telephone FROM users WHERE prenom=:prenom AND nom=:nom';
		$stmt1 = $_dbh->prepare($sQuery1);

		$stmt1->bindParam(':prenom', $sPrenom);
		$stmt1->bindParam(':nom', $sNom);
		
		$iCptAdd = 0;
		$iCptDoublon = 0;


		$sForm = '<form method="POST" action="'.$_pageEnCours.'">';
		$sForm .= '<table class="prettyTable>';
		$sForm .= '<thead>';
		$sForm .= '	<tr>';
		$sForm .= '		<th>Prénom';
		$sForm .= '		<th>Nom';
		$sForm .= '		<th>Adresse';
		$sForm .= '		<th>Téléphone';
        $sForm .= '		<th>email';
        $sForm .= '     <th>Genre';
        $sForm .='      <th>Statut';
		$sForm .= '		<th>Mettre à jour';
		$sForm .= '<tbody>';

		while($row = fgetcsv($handle, 1000, ':'))
		{
			$sPrenom = $row[0];
			$sNom = $row[1];
			$sAdresse = $row[2];
			$sTelephone = $row[3];
            $sEmail = $row[4];
            $sGenre = $row[5];
            $sStatut = $row[6];
			
			
			$stmt1->execute();
			if($stmt1->rowCount() === 0)
			{
				
				$stmt0->execute();
				$iCptAdd++;
			}
			else
			{
			
				$iCptDoublon++;
				
				$sForm .= '	<tr>';
				$sForm .= '		<td>'.$sPrenom;
				$sForm .= '		<td>'.$sNom;
				$sForm .= '		<td>'.$sAdresse;
				$sForm .= '		<td>'.$sTelephone;
			
				$sForm .= '		<td>new <input type="checkbox" name="data[]" value="'.urlencode(serialize($row)).'">'."\n";

				$row = $stmt1->fetch(PDO::FETCH_ASSOC);
				$sForm .= '	<tr class="oldData">';
				$sForm .= '		<td>'.$row['prenom'];
				$sForm .= '		<td>'.$row['nom'];
				$sForm .= '		<td>'.$row['adresse'];
				$sForm .= '		<td>'.$row['telephone'];
				$sForm .= '		<td>old';
			}
			
		}
		fclose($handle);
		$sForm .= '</table>';
		$sForm .= '<input type="hidden" name="actionPOST" value="importData">';
		$sForm .= '<input type="submit" value="Mettre à jour">';
		$sForm .= '</form>';

		return $iCptAdd.' enregistrement(s) ajouté(s) dans la db<br><b>Il y a '.$iCptDoublon.' enregistrement(s) déjà présent(s) dans la db</b>'.$sForm;
	}
	catch(PDOException $e)
	{
		return 'erreur insertion dans la db : '.$e->getMessage();
	}
}



function update($_enregistrements,$_dbh)
{
	try
	{

		$iCpt=0;
		$sQuery = 'UPDATE users SET adresse = :adresse, telephone=:telephone, email=:email, gender=:gender, statut=:statut WHERE prenom =:prenom AND nom = :nom';
		$stmt = $_dbh->prepare($sQuery);
		
		$sPrenom = '';
		$sNom = '';
		$sAdresse = '';
		$sTelephone = '';
        $sEmail = '';
        $sGenre = '';
        $sStatut = '';
		
		$stmt->bindParam(':prenom', $sPrenom);
		$stmt->bindParam(':nom', $sNom);
		$stmt->bindParam(':adresse', $sAdresse);
		$stmt->bindParam(':telephone', $sTelephone);
        $stmt->bindParam(':email', $sEmail);
        $stmt->bindParam(':gender', $sGenre);
        $stmt->bindParam(':statut', $sStatut);
		
		foreach($_enregistrements as $cellule)
		{
			var_dump($cellule);
			$aDataPerson = unserialize(urldecode($cellule));
			
			
			$sPrenom = $aDataPerson[0];
			$sNom = $aDataPerson[1];
			$sAdresse = $aDataPerson[2];
			$sTelephone = $aDataPerson[3];
            $sEmail = $aDataPerson[4];
            $sGenre = $aDataPerson[5];
            $sStatut = $aDataPerson[6];
            
			
			$stmt->execute();
			$iCpt++;
		}
		return 'Mise à jour réussie ! '.$iCpt.' enregistrement(s) modifié(s)';
	}
	catch(PDOException $e)
	{
		return 'Probleme UPDATE : '.$e->getMessage();
	}
}

//********************************    Offres   *****************************************#
//#######################################################################################
// showOffre($_dbh)                                                                     #
// Affiche la liste des offres avec cases à cocher pour suppression                     #
//#######################################################################################
function showOffre($_dbh)
{
    $sTable  = '<form method="post" action="">';
    $sTable .= '<table class="prettyTable">';
    $sTable .= '<thead>';
    $sTable .= '    <tr>';
    $sTable .= '        <th><input type="checkbox" id="selectAll" onclick="toggleCheckboxes(this)"> Sélectionner tout</th>';
    $sTable .= '        <th>ID</th>';
    $sTable .= '        <th>Titre</th>';
    $sTable .= '        <th>Description</th>';
    $sTable .= '        <th>Destination</th>';
    $sTable .= '        <th>Logement</th>';
    $sTable .= '        <th>Type Transport</th>';
    $sTable .= '        <th>Durée (jours)</th>';
    $sTable .= '        <th>Prix</th>';
    $sTable .= '        <th>Date départ</th>';
    $sTable .= '        <th>Date retour</th>';
    $sTable .= '        <th>Disponibilité</th>';
    $sTable .= '    </tr>';
    $sTable .= '</thead>';
    $sTable .= '<tbody>';

    $sSQL1 = "SELECT * FROM offres";
    $stmt1 = $_dbh->prepare($sSQL1);
    $stmt1->execute();
    $offres = $stmt1->fetchAll(PDO::FETCH_ASSOC);


    $sSQL2 = "SELECT id, pays, ville FROM destinations";
    $stmt2 = $_dbh->prepare($sSQL2);
    $stmt2->execute();
    $dests = $stmt2->fetchAll(PDO::FETCH_ASSOC);

    $sSQL3 = "SELECT id, nom, type_logement FROM logements";
    $stmt3 = $_dbh->prepare($sSQL3);
    $stmt3->execute();
    $logs = $stmt3->fetchAll(PDO::FETCH_ASSOC);


    $destinationsMap = [];
    foreach ($dests as $dest) 
    {
        $destinationsMap[$dest['id']] = $dest;
    }

    $logementsMap = [];
    foreach ($logs as $log)
    {
        $logementsMap[$log['id']] = $log;
    }

    foreach ($offres as &$offre) 
    {
        if (isset($offre['destination_id']) AND isset($destinationsMap[$offre['destination_id']])) 
        {
            $offre['destination_pays'] = $destinationsMap[$offre['destination_id']]['pays'];
            $offre['destination_ville'] = $destinationsMap[$offre['destination_id']]['ville'];
        } 

        if (isset($offre['logement_id']) AND isset($logementsMap[$offre['logement_id']])) 
        {
            $offre['logement_nom'] = $logementsMap[$offre['logement_id']]['nom'];
            $offre['logement_type'] = $logementsMap[$offre['logement_id']]['type_logement'];
        } 
    }

    foreach($offres as $offre) 
    {
        $sTable .= '<tr>';
        $sTable .= '<td><input type="checkbox" name="offres_selectionnees[]" value="'.$offre['id'].'"></td>';
        $sTable .= '<td>'.$offre['id'].'</td>';
        $sTable .= '<td>'.$offre['titre'].'</td>';
        $sTable .= '<td>'.$offre['description'].'</td>';
        $sTable .= '<td>'.$offre['destination_pays'].' - '.$offre['destination_ville'].'</td>';
        $sTable .= '<td>'.$offre['logement_nom'].'</td>';
        $sTable .= '<td>'.$offre['type_transport'].'</td>';
        $sTable .= '<td>'.$offre['duree_sejour'].' jours</td>';
        $sTable .= '<td>'.$offre['prix'].' €</td>';
        $sTable .= '<td>'.$offre['date_depart'].'</td>';
        $sTable .= '<td>'.$offre['date_retour'].'</td>';
        $sTable .= '<td>'.($offre['disponibilite'] ? 'Disponible' : 'Complet').'</td>';
        $sTable .= '</tr>';
    }

    $sTable .= '</tbody>';
    $sTable .= '</table>';
    $sTable .= '<br><input type="submit" name="supprimer_selection" value="Supprimer les offres sélectionnées">';
    $sTable .= '<input type="submit" name="editer_selection" value="Editer les offres sélectionnées">';
    $sTable .= '</form>';

    // JavaScript pour le bouton "tout sélectionner"
    $sTable .= '
    <script>
        function toggleCheckboxes(source) {
            let checkboxes = document.querySelectorAll("input[name=\'offres_selectionnees[]\']");
            checkboxes.forEach(function(checkbox) {
                checkbox.checked = source.checked;
            });
        }
    </script>';

    return $sTable;
}

//#######################################################################################
// formUpdateOffre($_nomFichier, $_pageEnCours, $_dbh)                                  #
// Gère l'import des offres depuis un fichier CSV                                       #
//#######################################################################################
function formUpdateOffre($_nomFichier, $_pageEnCours, $_dbh)
{
    $handle = fopen($_nomFichier, 'rb');
    if($handle === false) {
        return 'Problème lors de l\'ouverture du fichier';
    }

    try {

        $sTitre = '';
        $sDescription = '';
        $iDestinationId = 0;
        $iLogementId = 0;
        $sTypeTransport = '';
        $iDureeSejour = 0;
        $fPrix = 0.0;
        $sDateDepart = '';
        $sDateRetour = '';
        $iDisponibilite = 0;
        $sImages = '';

        $sQuery = 'INSERT INTO offres (titre, description, destination_id, logement_id, type_transport, duree_sejour, prix, date_depart, date_retour, disponibilite, images) VALUES (:titre, :description, :destination_id, :logement_id, :type_transport,:duree_sejour, :prix, :date_depart, :date_retour, :disponibilite, :images)';
        $stmt = $_dbh->prepare($sQuery);

        $stmt->bindParam(':titre', $sTitre);
        $stmt->bindParam(':description', $sDescription);
        $stmt->bindParam(':destination_id', $iDestinationId);
        $stmt->bindParam(':logement_id', $iLogementId);
        $stmt->bindParam(':type_transport', $sTypeTransport);
        $stmt->bindParam(':duree_sejour', $iDureeSejour);
        $stmt->bindParam(':prix', $fPrix);
        $stmt->bindParam(':date_depart', $sDateDepart);
        $stmt->bindParam(':date_retour', $sDateRetour);
        $stmt->bindParam(':disponibilite', $iDisponibilite);
        $stmt->bindParam(':images', $sImages);
        
        $sQuery2 = 'SELECT id FROM offres WHERE titre = :titre AND date_depart = :date_depart';
        $stmt2 = $_dbh->prepare($sQuery2);
        
        $stmt2->bindParam(':prenom', $sTitre);
		$stmt2->bindParam(':nom', $sDateDepart);

        $iCptAdd = 0;
        $iCptDoublon = 0;
        
        $sForm = '<form method="POST" action="'.$_pageEnCours.'">';
        $sForm .= '<table class="prettyTable">';
        $sForm .= '<thead>';
        $sForm .= '    <tr>';
        $sForm .= '        <th>Titre';
        $sForm .= '        <th>Description';
        $sForm .= '        <th>Destination';
        $sForm .= '        <th>Logement';
        $sForm .= '        <th>Transport';
        $sForm .= '        <th>Durée';
        $sForm .= '        <th>Prix';
        $sForm .= '        <th>Dates';
        $sForm .= '        <th>Mettre à jour';
        $sForm .= '<tbody>';
        
        while($row = fgetcsv($handle, 1000, ':')) 
        {
            $sTitre = $row[0] ;
            $sDescription = $row[1] ;
            $iDestinationId = $row[2] ;
            $iLogementId = $row[3] ;
            $sTypeTransport = $row[4] ;
            $iDureeSejour = $row[5] ;
            $fPrix = $row[6] ;
            $sDateDepart = $row[7] ;
            $sDateRetour = $row[8] ;
            $iDisponibilite = $row[9] ;
            $sImages = $row[10] ;
            
            
            $stmt2->execute();
            
            if($stmt2->rowCount() === 0) 
            {
                $stmt->execute();
                $iCptAdd++;
            } 
            else 
            {
                $iCptDoublon++;
                
                $sQuery3 = 'SELECT ville FROM destinations WHERE id = :id';
                $stmt3 = $_dbh->prepare($sQuery3);

                $stmt3->bindParam(':id', $iDestinationId);
                $stmt3->execute();
                $dest = $stmt3->fetch(PDO::FETCH_ASSOC);
                
                $sQuery4 = 'SELECT nom FROM logements WHERE id = :id';
                $stmt4 = $_dbh->prepare($sQuery4);

                $stmt4->bindParam(':id', $iLogementId);
                $stmt4->execute();
                $log = $stmt4->fetch(PDO::FETCH_ASSOC);
                
                $sForm .= '    <tr>';
                $sForm .= '        <td>'.$row[0];
                $sForm .= '        <td>'.$row[1];
                $sForm .= '        <td>'.($dest['ville'] ?? 'Inconnue');
                $sForm .= '        <td>'.($log['nom'] ?? 'Inconnu');
                $sForm .= '        <td>'.$row[4];
                $sForm .= '        <td>'.$row[5];
                $sForm .= '        <td>'.$row[6].' €';
                $sForm .= '        <td>'.$row[7].' au '.$row[8];
                $sForm .= '        <td>new <input type="checkbox" name="data[]" value="'.urlencode(serialize($row)).'">';


                $existing = $stmt2->fetch(PDO::FETCH_ASSOC);

                $sQuery5 = 'SELECT * FROM offres WHERE id = :id';
                $stmt5 = $_dbh->prepare($sQuery5);

                $stmt5->bindParam(':id', $existing['id']);
                $stmt5->execute();
                $offre = $stmt5->fetch(PDO::FETCH_ASSOC);
                
                
                $dest = null;
                if (!empty($offre['destination_id'])) 
                {
                    $sQuery6 = 'SELECT ville , pays FROM destinations WHERE id = :id';
                    $stmt6 = $_dbh->prepare($sQuery6);

                    $stmt6->bindParam(':id', $offre['destination_id']);
                    $stmt6->execute();
                    $dest = $stmt6->fetch(PDO::FETCH_ASSOC);
                }
                
                $loge = null;
                if (!empty($offre['logement_id'])) 
                {
                    $sQuery7 = 'SELECT nom FROM logements WHERE id = :id';
                    $stmt7 = $_dbh->prepare($sQuery7);
                    $stmt7->bindParam(':id', $offre['logement_id']);
                    $stmt7->execute();
                    $loge = $stmt7->fetch(PDO::FETCH_ASSOC);
                }
                
                
                $Data = $offre;
                $Data['destination_ville'] = $dest['ville'];
                $Data['logement_nom'] = $loge['nom'] ;
                
                $sForm .= '    <tr class="oldData">';
                $sForm .= '        <td>'.$Data['titre'];
                $sForm .= '        <td>'.$Data['description'];
                $sForm .= '        <td>'.$Data['destination_pays'].' - '.$Data['destination_ville'];
                $sForm .= '        <td>'.$Data['logement_nom'];
                $sForm .= '        <td>'.$Data['type_transport'];
                $sForm .= '        <td>'.$Data['duree_sejour'];
                $sForm .= '        <td>'.$Data['prix'].' €';
                $sForm .= '        <td>'.$Data['date_depart'].' au '.$Data['date_retour'];
                $sForm .= '        <td>old';
            }
        }
        
        fclose($handle);
        
        $sForm .= '</table>';
        $sForm .= '<input type="hidden" name="actionPOST" value="importOffre">';
        $sForm .= '<input type="submit" value="Mettre à jour">';
        $sForm .= '</form>';
        
        return $iCptAdd.' offre(s) ajoutée(s)<br><b>Il y a '.$iCptDoublon.' offre(s) déjà présente(s)</b>'.$sForm;
        
    } 
    catch(PDOException $e) 
    {
        return 'Erreur lors de l\'insertion dans la DB : '.$e->getMessage();
    }
}

//#######################################################################################
// updateOffre($_enregistrements, $_dbh)                                                #
// Met à jour les offres sélectionnées                                                  #
//#######################################################################################
function updateOffre($_enregistrements, $_dbh)
{
    try {
        $iCpt = 0;

        $sQuery = 'UPDATE offres SET titre = :titre,description = :description,destination_id = :destination_id,logement_id = :logement_id,type_transport = :type_transport,duree_sejour = :duree_sejour,prix = :prix,date_depart = :date_depart,
        date_retour = :date_retour,disponibilite = :disponibilite,images = :images WHERE titre = :titre AND date_depart = :date_depart ';
        $stmt = $_dbh->prepare($sQuery);

        $sTitre = '';
        $sDescription = '';
        $iDestinationId = 0;
        $iLogementId = 0;
        $sTypeTransport = '';
        $iDureeSejour = 0;
        $fPrix = 0.0;
        $sDateDepart = '';
        $sDateRetour = '';
        $iDisponibilite = 0;
        $sImages = '';

        $stmt->bindParam(':titre', $sTitre);    
        $stmt->bindParam(':description', $sDescription);
        $stmt->bindParam(':destination_id', $iDestinationId);
        $stmt->bindParam(':logement_id', $iLogementId);
        $stmt->bindParam(':type_transport', $sTypeTransport);
        $stmt->bindParam(':duree_sejour', $iDureeSejour);
        $stmt->bindParam(':prix', $fPrix);
        $stmt->bindParam(':date_depart', $sDateDepart);
        $stmt->bindParam(':date_retour', $sDateRetour);
        $stmt->bindParam(':disponibilite', $iDisponibilite);
        $stmt->bindParam(':images', $sImages);

        
        foreach($_enregistrements as $cellule) 
        {
            $aData = unserialize(urldecode($cellule));
            $sTitre = $aData[0];    
            $sDescription = $aData[1];
            $iDestinationId = $aData[2];
            $iLogementId = $aData[3];
            $sTypeTransport = $aData[4];
            $iDureeSejour = $aData[5];
            $fPrix = $aData[6];
            $sDateDepart = $aData[7];
            $sDateRetour = $aData[8];
            $iDisponibilite = $aData[9];
            $sImages = $aData[10];

            $stmt->execute();
            $iCpt++;
        }
        
        return 'Mise à jour réussie ! '.$iCpt.' offre(s) modifiée(s)';
        
    } 
    catch(PDOException $e) 
    {
        return 'Problème lors de la mise à jour : '.$e->getMessage();
    }
}
//#######################################################################################
// formCreateOffre($_dbh, $_pageEnCours)                                               #
// Affiche le formulaire de création d'une nouvelle offre                              #
//#######################################################################################
function formCreateOffre($_dbh, $_pageEnCours)
{
    $sQuery = "SELECT id, ville FROM destinations";
    $stmt = $_dbh->prepare($sQuery);
    $stmt->execute();
    $destinations = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $sQuery1 = "SELECT id, nom FROM logements";
    $stmt1 = $_dbh->prepare($sQuery1);
    $stmt1->execute();
    $logements = $stmt1->fetchAll(PDO::FETCH_ASSOC);

    $optionsDest = '';
    foreach ($destinations as $dest) 
    {
        $optionsDest .= '<option value="'.$dest['id'].'">'.$dest['ville'].'</option>';
    }

    $optionsLog = '';
    foreach ($logements as $log) 
    {
        $optionsLog .= '<option value="'.$log['id'].'">'.$log['nom'].'</option>';
    }
    
    $form = <<<EOT
    <form method="POST" action="{$_pageEnCours}" enctype="multipart/form-data">
        <input type="hidden" name="actionPOST" value="createOffre">
        
        <div class="mb-3">
            <label class="form-label">Titre de l'offre</label>
            <input type="text" class="form-control" name="titre" required>
        </div>
        
        <div class="mb-3">
            <label class="form-label">Description</label>
            <textarea class="form-control" name="description" rows="3" required></textarea>
        </div>
        
        <div class="row mb-3">
            <div class="col-md-6">
                <label class="form-label">Destination</label>
                <select class="form-select" name="destination_id" required>
                    <option value="">Choisir une destination</option>
                    {$optionsDest}
                </select>
            </div>
            <div class="col-md-6">
                <label class="form-label">Logement</label>
                <select class="form-select" name="logement_id" required>
                    <option value="">Choisir un logement</option>
                    {$optionsLog}
                </select>
            </div>
        </div>
        
        <div class="row mb-3">
            <div class="col-md-4">
                <label class="form-label">Type de transport</label>
                <select class="form-select" name="type_transport" required>
                    <option value="avion">Avion</option>
                    <option value="bus">Bus</option>
                    <option value="train">Train</option>
                    <option value="covoiturage">Covoiturage</option>
                    <option value="voiture">Voiture</option>
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label">Durée (jours)</label>
                <input type="number" class="form-control" name="duree_sejour" min="1" required>
            </div>
            <div class="col-md-4">
                <label class="form-label">Prix (€)</label>
                <input type="number" step="0.01" class="form-control" name="prix" min="0" required>
            </div>
        </div>
        
        <div class="row mb-3">
            <div class="col-md-6">
                <label class="form-label">Date de départ</label>
                <input type="date" class="form-control" name="date_depart" required>
            </div>
            <div class="col-md-6">
                <label class="form-label">Date de retour</label>
                <input type="date" class="form-control" name="date_retour" required>
            </div>
        </div>
        
        <div class="mb-3">
            <label class="form-label">Disponibilité</label>
            <select class="form-select" name="disponibilite" required>
                <option value="1">Disponible</option>
                <option value="0">Complet</option>
            </select>
        </div>
        
        <div class="mb-3">
            <label class="form-label">Image (optionnel)</label>
            <input type="file" class="form-control" name="image" accept="image/*">
        </div>
        
        <button type="submit" class="btn btn-primary">Créer l'offre</button>
    </form>
EOT;

    return $form;
}

//#######################################################################################
// createOffre($_data, $_dbh)                                                          #
// Crée une nouvelle offre dans la base de données                                      #
//#######################################################################################
function createOffre($_data, $_files, $_dbh)
{
    try 
    {
        $sTitre = $_data['titre'];
        $sDescription = $_data['description'];
        $iDestinationId = $_data['destination_id'];
        $iLogementId = $_data['logement_id'];
        $sTypeTransport = $_data['type_transport'];
        $iDureeSejour = $_data['duree_sejour'];
        $fPrix = $_data['prix'];
        $sDateDepart = $_data['date_depart'];
        $sDateRetour = $_data['date_retour'];
        $iDisponibilite = $_data['disponibilite'];
        $sImages = null;

        if (!empty($_files['image']['name'])) 
        {
            $uploadDir = 'uploads/offres/';
            if (!is_dir($uploadDir)) 
            {
                mkdir($uploadDir, 0777, true);
            }

            $sImages = uniqid() . '_' . basename($_files['image']['name']);
            $targetFile = $uploadDir . $sImages;

            if (!move_uploaded_file($_files['image']['tmp_name'], $targetFile)) 
            {
                return 'Erreur lors de l\'upload de l\'image.';
            }
        }

        $sQuery = 'INSERT INTO offres (titre, description, destination_id, logement_id, type_transport, duree_sejour, prix, date_depart, date_retour, disponibilite, images) VALUES (:titre, :description, :destination_id, :logement_id, :type_transport, :duree_sejour, :prix, :date_depart, :date_retour, :disponibilite, :images)';
        $stmt = $_dbh->prepare($sQuery);

        $stmt->bindParam(':titre', $sTitre);
        $stmt->bindParam(':description', $sDescription);
        $stmt->bindParam(':destination_id', $iDestinationId);
        $stmt->bindParam(':logement_id', $iLogementId);
        $stmt->bindParam(':type_transport', $sTypeTransport);
        $stmt->bindParam(':duree_sejour', $iDureeSejour);
        $stmt->bindParam(':prix', $fPrix);
        $stmt->bindParam(':date_depart', $sDateDepart);
        $stmt->bindParam(':date_retour', $sDateRetour);
        $stmt->bindParam(':disponibilite', $iDisponibilite);
        $stmt->bindParam(':images', $sImages);

        $stmt->execute();

        return 'L\'offre a été créée avec succès!';
    } 
    catch(PDOException $e) 
    {
        return 'Erreur lors de la création de l\'offre : ' . $e->getMessage();
    }
}

//#######################################################################################   //
// editSelectedOffers($ids, $_dbh)                                                     #
function editSelectedOffers($ids, $_dbh)
{
    try 
    {
        $sQuery = "SELECT * FROM offres WHERE id IN ($ids)";
        $stmt = $_dbh->prepare($sQuery);

        $stmt->execute();
        $offres = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (empty($offres)) 
        {
            return "<div class='alert alert-warning'>Aucune offre trouvée pour édition.</div>";
        }

        $form = '<form method="POST" action="" enctype="multipart/form-data">';
        $form .= '<input type="hidden" name="actionPOST" value="updateSelectedOffers">';
        $form .= '<table class="prettyTable">';
        $form .= '<thead>';
        $form .= '    <tr>';
        $form .= '        <th>ID</th>';
        $form .= '        <th>Titre</th>';
        $form .= '        <th>Description</th>';
        $form .= '        <th>Destination</th>';
        $form .= '        <th>Logement</th>';
        $form .= '        <th>Transport</th>';
        $form .= '        <th>Durée</th>';
        $form .= '        <th>Prix</th>';
        $form .= '        <th>Date départ</th>';
        $form .= '        <th>Date retour</th>';
        $form .= '        <th>Disponibilité</th>';
        $form .= '    </tr>';
        $form .= '</thead>';
        $form .= '<tbody>';

        foreach ($offres as $offre) 
        {
            $form .= '<tr>';
            $form .= '<td>' . $offre['id'] . '<input type="hidden" name="offres[' . $offre['id'] . '][id]" value="' . $offre['id'] . '"></td>';
            $form .= '<td><input type="text" name="offres[' . $offre['id'] . '][titre]" value="' . htmlspecialchars($offre['titre']) . '"></td>';
            $form .= '<td><textarea name="offres[' . $offre['id'] . '][description]">' . htmlspecialchars($offre['description']) . '</textarea></td>';
            $form .= '<td><input type="text" name="offres[' . $offre['id'] . '][destination_id]" value="' . $offre['destination_id'] . '"></td>';
            $form .= '<td><input type="text" name="offres[' . $offre['id'] . '][logement_id]" value="' . $offre['logement_id'] . '"></td>';
            $form .= '<td><input type="text" name="offres[' . $offre['id'] . '][type_transport]" value="' . $offre['type_transport'] . '"></td>';
            $form .= '<td><input type="number" name="offres[' . $offre['id'] . '][duree_sejour]" value="' . $offre['duree_sejour'] . '"></td>';
            $form .= '<td><input type="number" step="0.01" name="offres[' . $offre['id'] . '][prix]" value="' . $offre['prix'] . '"></td>';
            $form .= '<td><input type="date" name="offres[' . $offre['id'] . '][date_depart]" value="' . $offre['date_depart'] . '"></td>';
            $form .= '<td><input type="date" name="offres[' . $offre['id'] . '][date_retour]" value="' . $offre['date_retour'] . '"></td>';
            $form .= '<td><select name="offres[' . $offre['id'] . '][disponibilite]">';
            $form .= '<option value="1"' . ($offre['disponibilite']) . '>Disponible</option>';
            $form .= '<option value="0"' . (!$offre['disponibilite']) . '>Complet</option>';
            $form .= '</select></td>';
            $form .= '</tr>';
        }

        $form .= '</tbody>';
        $form .= '</table>';
        $form .= '<button type="submit" class="btn btn-primary">Mettre à jour les offres</button>';
        $form .= '</form>';

        return $form;
    } 
    catch (Exception $e) 
    {
        return "<div class='alert alert-danger'>Erreur lors de la récupération des offres : " . $e->getMessage() . "</div>";
    }
}

function deleteOffres($ids, $_dbh)
{
    try {
        $sQuery = "DELETE FROM offres WHERE id IN ($ids)";
        $stmt = $_dbh->prepare($sQuery);
        $stmt->execute();

        return "<div class='alert alert-success'>Les offres sélectionnées ont été supprimées avec succès.</div>";
    } 
    catch (PDOException $e) 
    {
        return "<div class='alert alert-danger'>Erreur lors de la suppression des offres : " . $e->getMessage() . "</div>";
    }
}