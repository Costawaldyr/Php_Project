<?php
require_once('config.php');

// Connexion de la base de données 



$dsn = "mysql:host={$sDB_host}; dbname={$sDB_name}";

try
{
    $dbh = new PDO($dsn,$sDB_user,$sDB_pwd);
}
catch(PDOException $e)
{
    die('Error :'.$e->getMessage());
}

