<?php
require_once('data_base/connection.php');
session_start();

// Vérifie que l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    $_SESSION['error'] = "Vous devez être connecté pour envoyer un message.";
    header('Location: ../auth/login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user_id'];
    $sujet = trim($_POST['sujet'] ?? '');
    $message = trim($_POST['message'] ?? '');

    // Tu peux aussi stocker prenom, nom, email, téléphone depuis $_SESSION si tu veux
    $prenom = $_SESSION['prenom'] ?? null;
    $nom = $_SESSION['nom'] ?? null;
    $email = $_SESSION['email'] ?? null;
    $telephone = $_SESSION['telephone'] ?? null;

    if (empty($sujet) || empty($message)) {
        $_SESSION['error'] = "Veuillez remplir tous les champs obligatoires.";
        header("Location: profile.php");
        exit;
    }

    try {
        $stmt = $dbh->prepare("
            INSERT INTO contact_support (user_id, sujet, message, statut, date_post, prenom, nom, email, telephone)
            VALUES (:user_id, :sujet, :message, 'en attente', NOW(), :prenom, :nom, :email, :telephone)
        ");

        $stmt->execute([
            ':user_id' => $user_id,
            ':sujet' => $sujet,
            ':message' => $message,
            ':prenom' => $prenom,
            ':nom' => $nom,
            ':email' => $email,
            ':telephone' => $telephone,
        ]);

        $_SESSION['success'] = "Message envoyé avec succès.";
        header("Location: profile.php");
        exit;
    } catch (PDOException $e) {
        $_SESSION['error'] = "Erreur lors de l'envoi : " . $e->getMessage();
        header("Location: profile.php");
        exit;
    }
}
?>
