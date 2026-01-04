<?php
require_once('../data_base/connection.php');

// Récupérer les paramètres de recherche
$searchType = $_GET['type'] ?? 'voyages';
$destination = $_GET['destination'] ?? '';
$departure = $_GET['departure'] ?? '';
$passengers = $_GET['passengers'] ?? 1;

try {
    if ($searchType === 'voyages') {
        // Recherche d'offres de voyage
        $query = "SELECT o.*, d.ville, d.pays 
                 FROM offres o 
                 JOIN destinations d ON o.destination_id = d.id 
                 WHERE d.ville LIKE :destination 
                 AND o.date_depart >= :departure
                 ORDER BY o.prix ASC";
        
        $stmt = $dbh->prepare($query);
        $stmt->execute([
            ':destination' => "%$destination%",
            ':departure' => $departure
        ]);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Afficher les résultats
        include('resultats_voyages.php');
        
    } elseif ($searchType === 'hebergements') {
        // Recherche d'hébergements
        $query = "SELECT l.*, d.ville, d.pays, 
                 (SELECT image_url FROM logement_images WHERE logement_id = l.id LIMIT 1) as image_url
                 FROM logements l 
                 JOIN destinations d ON l.destination_id = d.id 
                 WHERE d.ville LIKE :destination 
                 ORDER BY l.prix_nuit ASC";
        
        $stmt = $dbh->prepare($query);
        $stmt->execute([
            ':destination' => "%$destination%"
        ]);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Afficher les résultats
        include('resultats_hebergements.php');
    }
} catch(PDOException $e) {
    die('Erreur lors de la recherche : ' . $e->getMessage());
}
?>