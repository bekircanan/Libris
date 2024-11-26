<?php
require('header.php');

$_SESSION['id'] = 1;

$stmtReservationsDisponibles = $conn->prepare("
    SELECT DISTINCT l.titre_livre, l.img_couverture, l.id_livre
    FROM livre l
    JOIN reserver r ON l.id_livre = r.id_livre
    WHERE r.id_util = :idUtilisateur AND l.disponibilite > 0
");
$stmtReservationsDisponibles->execute([':idUtilisateur' => $_SESSION['id']]);
$reservationsDisponibles = $stmtReservationsDisponibles->fetchAll();

$stmtReservationsIndisponibles = $conn->prepare("
    SELECT DISTINCT l.titre_livre, l.img_couverture, l.id_livre
    FROM livre l
    JOIN reserver r ON l.id_livre = r.id_livre
    WHERE r.id_util = :idUtilisateur AND l.disponibilite = 0
");
$stmtReservationsIndisponibles->execute([':idUtilisateur' => $_SESSION['id']]);
$reservationsIndisponibles = $stmtReservationsIndisponibles->fetchAll();
?>

<!DOCTYPE html>
<html lang="FR">
    <head>
        <title>Mes réservations</title>
        <meta charset="UTF-8">
    </head>
    <body>
		<div class="reservations-ebooks">
			<h1>Mes Réservations</h1>
			<?php
			// Si un livre disponible est trouvé, affichage de la section correspondante
			if (!empty($reservationsDisponibles)) {
				echo '<div class="reservations-disponibles">';
				echo '<h2>DISPONIBLE</h2>';
				echo '<div class="livres">';
				foreach ($reservationsDisponibles as $reservation) {
                    echo '<div class="livre">';
                    echo '<a href="info_livre.php?id_livre=' . $reservation['id_livre'] . '">';  /* Lien vers la page info-livre */
                    echo '<img class="imgCouverture" src="'.$reservation['img_couverture'].'" alt="Couverture du livre">';
                    echo "</a>";
                    echo '<h3>'.$reservation['titre_livre'].'</h3>';
                    echo "<p> Disponible jusqu'au <strong>" ."XX/XX/XXXX".'</strong></p>';
                    echo '</div>';
				}
				echo '</div>';
				echo '</div>';
			}
			// Si un livre indisponible est trouvé, affichage de la section correspondante
			if (!empty($reservationsIndisponibles)) {
				echo '<div class="reservations-indisponibles">';
				echo '<h2>INDISPONIBLE</h2>';
				echo '<div class="livres">';
				foreach ($reservationsIndisponibles as $reservation) {
                    echo '<div class="livre">';
                    echo '<a href="info_livre.php?id_livre=' . $reservation['id_livre'] . '">';  /* Lien vers la page info-livre */
                    echo '<img class="imgCouverture" src="'.$reservation['img_couverture'].'" alt="Couverture du livre">';
                    echo "</a>";
                    echo '<h3>'.$reservation['titre_livre'].'</h3>';
                    echo "<p> Disponible le <strong>" ."XX/XX/XXX".'</strong></p>';
                    echo '</div>';
				}
				echo '</div>';
				echo '</div>';
			}
			?>
		</div>
    </body>
</html>

<?php require("footer.php"); ?>