<?php
require_once('../data_base/connection.php');
header('Content-Type: application/json');

if(!isset($_GET['id'])) {
    die(json_encode(['error' => 'ID manquant']));
}

$stmt = $dbh->prepare("SELECT * FROM contenus WHERE id = ?");
$stmt->execute([$_GET['id']]);
$content = $stmt->fetch(PDO::FETCH_ASSOC);

echo json_encode($content);