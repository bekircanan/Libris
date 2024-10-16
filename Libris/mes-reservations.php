<?php
// variables locales pour tester
session_start();
ini_set('dispaly_errors', 1);
ini_set('display_startup_error', 1);
error_reporting(E_ALL);
$livres = array();
?>



<!DOCTYPE html>
<html lang="FR">
    <head>
        <title>Mes réservations</title>
        <meta charset="UTF-8">
		<link rel="stylesheet" href="styles-mes-reservations.css">
    </head>
    <body>
		<?php
            require('header.php');
			$idUti = $_SESSION['idUti'];
		?>
        <h1>Mes réservations</h1>
    </body>
</html>