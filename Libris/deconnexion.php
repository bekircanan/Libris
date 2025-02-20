<?php 
    require_once 'header.php';
    // Détruire la session
    session_unset();
    session_destroy();
    header('Location: index.php');
    exit();
?>