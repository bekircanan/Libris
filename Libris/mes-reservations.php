<?php
require('header.php');

$_SESSION['id'] = 1;

$stmtReservationsUtil = $conn->prepare("
    SELECT l.titre_livre, l.img_couverture, l.id_livre
    FROM livre l
    JOIN reserver r ON l.id_livre = r.id_livre
    WHERE r.id_util = :idUtilisateur
");
$stmtReservationsUtil->execute([':idUtilisateur' => $_SESSION['id']]);
$reservationsUtil = $stmtReservationsUtil->fetchAll();

$stmtExemplairesDisponibles = $conn->prepare("
    SELECT 
        e.id_livre, 
        e.nb_exemplaires, 
        (e.nb_exemplaires - COUNT(emp.id_livre)) AS exemplaires_disponibles
    FROM 
        exemplaire e
    LEFT JOIN 
        emprunter emp ON e.id_livre = emp.id_livre
    GROUP BY 
        e.id_livre, e.nb_exemplaires
");
$stmtExemplairesDisponibles->execute();
$exemplairesDisponibles = $stmtExemplairesDisponibles->fetchAll(PDO::FETCH_ASSOC);

$stmtEmprunts = $conn->prepare("
    SELECT 
        livre.id_livre, 
        livre.titre_livre, 
        livre.cote_livre, 
        utilisateur.id_util, 
        utilisateur.nom_util, 
        utilisateur.prenom_util, 
        utilisateur.email, 
        emprunter.date_debut_emprunt
    FROM 
        emprunter
    INNER JOIN 
        livre ON emprunter.id_livre = livre.id_livre
    INNER JOIN 
        utilisateur ON emprunter.id_util = utilisateur.id_util
    ORDER BY
        emprunter.id_livre, emprunter.date_debut_emprunt
");
$stmtEmprunts->execute();
$emprunts = $stmtEmprunts->fetchAll();

$stmtReservations = $conn->prepare("
    SELECT 
        reserver.date_reservation,
        livre.id_livre, 
        livre.titre_livre, 
        livre.cote_livre, 
        livre.disponibilite,
        utilisateur.id_util, 
        utilisateur.nom_util, 
        utilisateur.prenom_util, 
        utilisateur.email
    FROM 
        reserver
    INNER JOIN 
        livre ON reserver.id_livre = livre.id_livre
    INNER JOIN 
        utilisateur ON reserver.id_util = utilisateur.id_util
    ORDER BY 
        reserver.id_livre, reserver.date_reservation
");
$stmtReservations->execute();
$reservations = $stmtReservations->fetchAll();

function calculerDateRetour($dateEmprunt) {
    $date = new DateTime($dateEmprunt);
    $date->modify('+3 weeks');
    return $date->format('Y-m-d');
}

function calculerPositionFileAttente($reservations, $id_livre) {
    $i = 1;
    foreach ($reservations as $reservation) {
        if ($reservation['id_livre'] == $id_livre) {
            if ($reservation['id_util'] == $_SESSION['id']) {
                return $i;
            }
            $i = $i+1;
        }
    }
}

function calculerDateDisponibilite($exemplairesDisponibles, $emprunts, $reservations, $id_livre) {
    // Créer la file d'attente des utilisateurs ayant réservé le livre
    $fileAttente = [];
    foreach ($reservations as $reservation) {
        if ($reservation['id_livre'] == $id_livre) {
            array_push($fileAttente, $reservation['id_util']);
        }
    }

    // Créer la file d'attente des dates de retour des emprunts pour ce livre
    $fileRetoursEmprunts = [];
    foreach ($emprunts as $emprunt) {
        if ($emprunt['id_livre'] == $id_livre) {
            array_push($fileRetoursEmprunts, calculerDateRetour($emprunt['date_debut_emprunt']));
        }
    }

    // Récupérer le nombre d'exemplaires disponibles pour le livre
    $nbExemplairesDisponibles = 0;
    foreach ($exemplairesDisponibles as $exemplaire) {
        if ($exemplaire['id_livre'] == $id_livre) {
            $nbExemplairesDisponibles = $exemplaire['exemplaires_disponibles'];
            break;
        }
    }

    $user = $fileAttente[0];
    $dateDisponibilite = "Disponible";
    while ($user != $_SESSION['id']) {
        $user = array_shift($fileAttente);
        if ($nbExemplairesDisponibles > 0) {
            $nbExemplairesDisponibles--;
        }
        else {
            $dateDisponibilite = array_shift($fileRetoursEmprunts);
        }
    }
    return $dateDisponibilite;
}

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

function convertirDate($date) {
    $timestamp = strtotime($date);

    $mois = [
        "01" => "janvier", "02" => "février", "03" => "mars",
        "04" => "avril", "05" => "mai", "06" => "juin",
        "07" => "juillet", "08" => "août", "09" => "septembre",
        "10" => "octobre", "11" => "novembre", "12" => "décembre"
    ];

    $jour = date("d", $timestamp);
    $moisNum = date("m", $timestamp);
    $annee = date("Y", $timestamp);

    return "$jour " . $mois[$moisNum] . " $annee";
}
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
            $hasDisponible = false;
            foreach ($reservationsUtil as $reservation) {
                if (calculerDateDisponibilite($exemplairesDisponibles, $emprunts, $reservations, $reservation['id_livre']) === 'Disponible') {
                    $hasDisponible = true;
                    break;
                }
            }

            $hasIndisponible = false;
            foreach ($reservationsUtil as $reservation) {
                if (calculerDateDisponibilite($exemplairesDisponibles, $emprunts, $reservations, $reservation['id_livre']) != 'Disponible') {
                    $hasIndisponible = true;
                    break;
                }
            }

            if(!$hasIndisponible && !$hasDisponible) {
                echo "<p>Vous n'avez aucune réservation.</p>";
            }

            // Si un livre disponible est trouvé, affichage de la section correspondante
            if ($hasDisponible) {
                echo '<div class="reservations-disponibles">';
                echo '<h2>DISPONIBLE</h2>';
                echo '<div class="livres">';
                foreach ($reservationsUtil as $reservation) {
                    if (calculerDateDisponibilite($exemplairesDisponibles, $emprunts, $reservations, $reservation['id_livre']) === 'Disponible') {
                        echo '<div class="livre">';
                        echo '<a href="info_livre.php?id_livre=' . $reservation['id_livre'] . '">';  /* Lien vers la page info-livre */
                        echo '<img class="imgCouverture" src="'.$reservation['img_couverture'].'" alt="Couverture du livre">';
                        echo "</a>";
                        echo '<h3>'.$reservation['titre_livre'].'</h3>';
                        echo '</div>';
                    }
                }
                echo '</div>';
                echo '</div>';
            }

            // Si un livre indisponible est trouvé, affichage de la section correspondante
            if ($hasIndisponible) {
                echo '<div class="reservations-indisponibles">';
                echo '<h2>INDISPONIBLE</h2>';
                echo '<div class="livres">';
                foreach ($reservationsUtil as $reservation) {
                    if (calculerDateDisponibilite($exemplairesDisponibles, $emprunts, $reservations, $reservation['id_livre']) != 'Disponible') {
                        echo '<div class="livre">';
                        echo '<a href="info_livre.php?id_livre=' . $reservation['id_livre'] . '">';  /* Lien vers la page info-livre */
                        echo '<img class="imgCouverture" src="'.$reservation['img_couverture'].'" alt="Couverture du livre">';
                        echo "</a>";
                        echo '<h3>'.$reservation['titre_livre'].'</h3>';
                        echo "<p>Disponible le <strong>" .convertirDate(calculerDateDisponibilite($exemplairesDisponibles, $emprunts, $reservations, $reservation['id_livre'])).'</strong></p>';
                        echo calculerPositionFileAttente($reservations, $reservation["id_livre"]). "e dans la file d'attente";
                        echo '</div>';
                    }
                }
            }
            ?>
		</div>
    </body>
</html>

<?php require("footer.php"); ?>