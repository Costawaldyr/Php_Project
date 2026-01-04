<?php
require_once('data_base/connection.php');
session_start();

if (isset($_SESSION['user_id'])) {
    session_destroy();
    
    if (isset($_COOKIE['user_id']))
    {
        setcookie('user_id', '', time() - 3600, '/');
    }
    if (isset($_COOKIE['access_level'])) 
    {
        setcookie('access_level', '', time() - 3600, '/');
    }
    header('Location: index.php');
}
else
{
    header('Location: index.php');
}

