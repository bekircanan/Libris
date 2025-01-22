<?php
require('header.php');

<<<<<<< HEAD
=======

>>>>>>> 5415ed6d23de4e9f719e973348b93a845cedc2c4
$stmtReservationsUtil = $conn->prepare("
    SELECT l.titre_livre, l.img_couverture, l.id_livre, r.num_isbn, r.id_util
    FROM reserver r
    JOIN isbn ON isbn.num_isbn = r.num_isbn
    JOIN livre l ON l.id_livre = isbn.id_livre
    WHERE r.id_util = :idUtilisateur
");
$stmtReservationsUtil->execute([':idUtilisateur' => $_SESSION['id']]);
$reservationsUtil = $stmtReservationsUtil->fetchAll();

$stmtNbExemplairesDisponibles = $conn->prepare("
    SELECT 
        l.id_livre,
        l.titre_livre,
        i.id_edition,
        ed.nom_edition,
        i.num_isbn,
        COUNT(e.id_exemplaire) AS nb_exemplaires,
        (COUNT(e.id_exemplaire) - COUNT(emp.id_exemplaire)) AS nb_exemplaires_disponibles
    FROM 
        exemplaire e
    INNER JOIN 
        isbn i ON e.num_isbn = i.num_isbn
    INNER JOIN 
        edition ed ON i.id_edition = ed.id_edition
    INNER JOIN 
        livre l ON i.id_livre = l.id_livre
    LEFT JOIN 
        emprunter emp ON e.id_exemplaire = emp.id_exemplaire
    GROUP BY 
        i.num_isbn, l.id_livre, l.titre_livre, i.id_edition, ed.nom_edition
");
$stmtNbExemplairesDisponibles->execute();
$nbExemplairesDisponibles = $stmtNbExemplairesDisponibles->fetchAll(PDO::FETCH_ASSOC);

$stmtExemplairesDisponibles = $conn->prepare("
    SELECT 
        e.num_isbn,
        e.id_exemplaire
    FROM 
        exemplaire e
    LEFT JOIN 
        emprunter emp ON e.id_exemplaire = emp.id_exemplaire 
        AND emp.date_fin_emprunt IS NULL
    WHERE 
        emp.id_exemplaire IS NULL
    ORDER BY 
        e.num_isbn
");
$stmtExemplairesDisponibles->execute();
$exemplairesDisponibles = $stmtExemplairesDisponibles->fetchAll(PDO::FETCH_ASSOC);

$stmtEmprunts = $conn->prepare("
    SELECT 
        l.id_livre, 
        l.titre_livre, 
        l.cote_livre, 
        e.id_exemplaire,
        e.num_isbn,
        u.id_util, 
        u.nom_util, 
        u.prenom_util, 
        u.email, 
        emp.date_debut_emprunt, 
        emp.date_fin_emprunt
    FROM 
        emprunter emp
    INNER JOIN 
        exemplaire e ON emp.id_exemplaire = e.id_exemplaire
    INNER JOIN 
        isbn i ON e.num_isbn = i.num_isbn
    INNER JOIN 
        livre l ON i.id_livre = l.id_livre
    INNER JOIN 
        utilisateur u ON emp.id_util = u.id_util
    ORDER BY
        l.id_livre, emp.date_debut_emprunt
");
$stmtEmprunts->execute();
$emprunts = $stmtEmprunts->fetchAll(PDO::FETCH_ASSOC);


$stmtReservations = $conn->prepare("
    SELECT 
        reserver.date_reservation,
        reserver.num_isbn,
        livre.id_livre, 
        livre.titre_livre, 
        livre.cote_livre, 
        livre.type_litteraire, 
        livre.img_couverture, 
        utilisateur.id_util, 
        utilisateur.prenom_util, 
        utilisateur.nom_util, 
        utilisateur.email, 
        utilisateur.pseudo
    FROM 
        reserver
    INNER JOIN 
        isbn ON reserver.num_isbn = isbn.num_isbn
    INNER JOIN 
        livre ON isbn.id_livre = livre.id_livre
    INNER JOIN 
        utilisateur ON reserver.id_util = utilisateur.id_util
    ORDER BY 
        reserver.date_reservation ASC, livre.titre_livre
");
$stmtReservations->execute();
$reservations = $stmtReservations->fetchAll(PDO::FETCH_ASSOC);

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

function calculerDateDisponibilite($exemplairesDisponibles, $emprunts, $reservations, $id_util, $num_isbn) {
    // Créer la file d'attente des utilisateurs ayant réservé le livre
    $fileAttente = [];
    foreach ($reservations as $reservation) {
        if ($reservation['num_isbn'] == $num_isbn) {
            array_push($fileAttente, $reservation['id_util']);
        }
    }

    // Créer la file d'attente des dates de retour des emprunts pour ce livre
    $fileRetoursEmprunts = [];
    foreach ($emprunts as $emprunt) {
        if ($emprunt['num_isbn'] == $num_isbn) {
            array_push($fileRetoursEmprunts, calculerDateRetour($emprunt['date_debut_emprunt']));
        }
    }

    // Récupérer le nombre d'exemplaires disponibles pour le livre
    $nbExemplairesDisponibles = 0;
    foreach ($exemplairesDisponibles as $exemplaire) {
        if ($exemplaire['num_isbn'] == $num_isbn) {
            $nbExemplairesDisponibles = $exemplaire['nb_exemplaires_disponibles'];
            break;
        }
    }

    $user = $fileAttente[0];
    $dateDisponibilite = "Disponible";
    if ($user == $id_util) {
        if ($nbExemplairesDisponibles > 0) {
            $nbExemplairesDisponibles--;
        }
        else {
            $dateDisponibilite = convertirDate(array_shift($fileRetoursEmprunts));
        }
    }
    else {
        while ($user != $id_util) {
            $user = array_shift($fileAttente);
            if ($nbExemplairesDisponibles > 0) {
                $nbExemplairesDisponibles--;
            }
            else {
                $dateDisponibilite = convertirDate(array_shift($fileRetoursEmprunts));
            }
        }
    }

    return $dateDisponibilite;
}

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

if($_SERVER['REQUEST_METHOD'] == 'POST' && ($_POST['form'] === 'confirmerAnnulation')) {
    $num_isbn = $_POST['num_isbn'];
    $id_util = $_POST['id_util'];

    if (!empty($num_isbn) && !empty($id_util)) {
        $stmt = $conn->prepare("DELETE FROM reserver WHERE num_isbn = :num_isbn AND id_util = :id_util");
        $stmt->bindParam(':num_isbn', $num_isbn, PDO::PARAM_STR);
        $stmt->bindParam(':id_util', $id_util, PDO::PARAM_INT);

        $stmt->execute();
        header('Location: mes-reservations.php');
        exit;
    }
}
?>
<div class="reservations-ebooks">
    <h1>Mes Réservations</h1>
    <?php
    $hasDisponible = false;
    foreach ($reservationsUtil as $reservation) {
        if (calculerDateDisponibilite($nbExemplairesDisponibles, $emprunts, $reservations, $_SESSION['id'], $reservation['num_isbn']) === 'Disponible') {
            $hasDisponible = true;
            break;
        }
    }

    $hasIndisponible = false;
    foreach ($reservationsUtil as $reservation) {
        if (calculerDateDisponibilite($nbExemplairesDisponibles, $emprunts, $reservations, $_SESSION['id'], $reservation['num_isbn']) != 'Disponible') {
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
            if (calculerDateDisponibilite($nbExemplairesDisponibles, $emprunts, $reservations, $_SESSION['id'], $reservation['num_isbn']) === 'Disponible') {
                echo '<div class="livre">';
                echo '<a href="info_livre.php?id_livre=' . $reservation['id_livre'] . '">';  /* Lien vers la page info-livre */
                echo '<img class="imgCouverture" src="'.$reservation['img_couverture'].'" alt="Couverture du livre">';
                echo "</a>";
                echo '<h3>'.$reservation['titre_livre'].'</h3>';
                echo "<button class='btReservationsEbooks2' onclick=popupConfirmAnnulation('".$reservation['num_isbn']."',".$_SESSION['id'].")>Annuler</button>";
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
            if (calculerDateDisponibilite($nbExemplairesDisponibles, $emprunts, $reservations, $_SESSION['id'], $reservation['num_isbn']) != 'Disponible') {
                echo '<div class="livre">';
                echo '<a href="info_livre.php?id_livre=' . $reservation['id_livre'] . '">';  /* Lien vers la page info-livre */
                echo '<img class="imgCouverture" src="'.$reservation['img_couverture'].'" alt="Couverture du livre">';
                echo "</a>";
                echo '<h3>'.$reservation['titre_livre'].'</h3>';
                echo "<p>Disponible le <strong>" .calculerDateDisponibilite($nbExemplairesDisponibles, $emprunts, $reservations, $_SESSION['id'], $reservation['num_isbn']).'</strong></p>';
                echo calculerPositionFileAttente($reservations, $reservation["id_livre"]). "e dans la file d'attente";
                echo "<button class='btReservationsEbooks' onclick=popupConfirmAnnulation('".$reservation['num_isbn']."',".$_SESSION['id'].")>Annuler</button>";
                echo '</div>';
            }
        }
    }
    ?>
</div>
<script>
    function popupConfirmAnnulation(num_isbn, id_util) {
        let popup = document.createElement('div');
        popup.className = 'popupGestion hidden';
        popup.innerHTML = `
                <div class="popupGestion-content">
                    <h2>Annulation de la réservation</h2>
                    <p>Êtes-vous sûr de vouloir annuler cette réservation ?</p>
                    <form method="POST" action="mes-reservations.php">
                        <input type="hidden" name="form" value="confirmerAnnulation">
                        <input type="hidden" name="num_isbn" value="${num_isbn}">
                        <input type="hidden" name="id_util" value="${id_util}">
                        <button type="submit">OK</button>
                    </form>
                    <button class='popupGestionAnnuler'>Annuler</button>
                </div>
            `;
        const main = document.querySelector('main');
        main.appendChild(popup);

        const cancelButton = popup.querySelector('.popupGestionAnnuler');
        cancelButton.addEventListener('click', () => {
            main.removeChild(popup);
        });
    }
</script>

<?php require("footer.php"); ?>