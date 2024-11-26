<?php
require('header.php');

$_SESSION['id'] = 1;

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

function calculerDateRetour($dateEmprunt) {
    $date = new DateTime($dateEmprunt);
    $date->modify('+3 weeks');
    return $date->format('Y-m-d');
}

function calculerStatut($dateEmprunt) {
    $dateRetourObj = new DateTime(calculerDateRetour($dateEmprunt));
    $dateActuelle = new DateTime();
    if ($dateRetourObj >= $dateActuelle) {
        return "En cours";
    } else {
        return "En retard";
    }
}

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

function calculerDisponibilite($disponibilite) {
    if ($disponibilite == "1") {
        return "Disponible";
    }
    else if ($disponibilite == "0") {
        return "Indisponible";
    }
}

function calculerPositionFileAttente($reservations, $id_util, $id_livre) {
    $i = 1;
    foreach ($reservations as $reservation) {
        if ($reservation['id_livre'] == $id_livre) {
            if ($reservation['id_util'] == $id_util) {
                return $i;
            }
            $i = $i+1;
        }
    }
}

function calculerDateDisponibilite($exemplairesDisponibles, $emprunts, $reservations, $id_util, $id_livre) {
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
    while ($user != $id_util) {
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

if($_SERVER['REQUEST_METHOD'] == 'POST' && ($_POST['form'] === 'confirmerRetour')) {
    $id_livre = $_POST['id_livre'];
    $id_util = $_POST['id_util'];

    if (!empty($id_livre) && !empty($id_util)) {
        $stmt = $conn->prepare("DELETE FROM emprunter WHERE id_livre = :id_livre AND id_util = :id_util");
        $stmt->bindParam(':id_livre', $id_livre, PDO::PARAM_INT);
        $stmt->bindParam(':id_util', $id_util, PDO::PARAM_INT);

        $stmt->execute();
        header('Location: gestion-emprunts-reservations.php');
    }
}

if($_SERVER['REQUEST_METHOD'] == 'POST' && ($_POST['form'] === 'annulerReservation')) {
    $id_livre = $_POST['id_livre'];
    $id_util = $_POST['id_util'];

    if (!empty($id_livre) && !empty($id_util)) {
        $stmt = $conn->prepare("DELETE FROM reserver WHERE id_livre = :id_livre AND id_util = :id_util");
        $stmt->bindParam(':id_livre', $id_livre, PDO::PARAM_INT);
        $stmt->bindParam(':id_util', $id_util, PDO::PARAM_INT);

        $stmt->execute();
        header('Location: gestion-emprunts-reservations.php');
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && ($_POST['form'] === 'validerEmprunt')) {
    $id_livre = $_POST['id_livre'];
    $id_util = $_POST['id_util'];

    if (!empty($id_livre) && !empty($id_util)) {
        // Vérifier la disponibilité
        $stmtVerifierDisponibilite = $conn->prepare("
            SELECT (e.nb_exemplaires - COUNT(emp.id_livre)) AS exemplaires_disponibles
            FROM exemplaire e
            LEFT JOIN emprunter emp ON e.id_livre = emp.id_livre
            WHERE e.id_livre = :id_livre
            GROUP BY e.nb_exemplaires
        ");
        $stmtVerifierDisponibilite->bindParam(':id_livre', $id_livre, PDO::PARAM_INT);
        $stmtVerifierDisponibilite->execute();
        $resultDispo = $stmtVerifierDisponibilite->fetch(PDO::FETCH_ASSOC);

        // Si exemplaires disponibles
        if ($resultDispo['exemplaires_disponibles'] > 0) {
            $stmtDelete = $conn->prepare("DELETE FROM reserver WHERE id_livre = :id_livre AND id_util = :id_util");
            $stmtDelete->bindParam(':id_livre', $id_livre, PDO::PARAM_INT);
            $stmtDelete->bindParam(':id_util', $id_util, PDO::PARAM_INT);
            $stmtDelete->execute();

            $stmtValider = $conn->prepare("INSERT INTO emprunter(id_livre, id_util) VALUES (:id_livre, :id_util)");
            $stmtValider->bindParam(':id_livre', $id_livre, PDO::PARAM_INT);
            $stmtValider->bindParam(':id_util', $id_util, PDO::PARAM_INT);
            $stmtValider->execute();

            header('Location: gestion-emprunts-reservations.php');
        } else {
            echo "<script>alert('Aucun exemplaire disponible pour ce livre.');</script>";
        }
    }
}

?>

<!DOCTYPE html>
<html lang="FR">
<head>
    <title>Gestion des emprunts et réservations</title>
    <meta charset="UTF-8">
</head>
<body>
    <main class="gestion-emprunts-reservations">
        <h1>Emprunts</h1>
        <table class="table-emprunts-reservations">
            <thead>
            <tr>
                <th colspan="3">Informations sur le livre</th>
                <th colspan="4" class="border-vertical">Informations sur l’utilisateur</th>
                <th colspan="3" class='border-vertical'>Informations sur l’emprunt</th>
                <th>Action</th>
            </tr>
            <tr>
                <th>ID</th>
                <th>Titre</th>
                <th>Cote</th>
                <th class="border-vertical">ID</th>
                <th>Nom</th>
                <th>Prénom</th>
                <th>Email</th>
                <th class='border-vertical'>Date de début</th>
                <th>Date de retour prévu</th>
                <th>Statut</th>
                <th></th>
            </tr>
            </thead>
            <tbody>
            <?php
            foreach ($emprunts as $emprunt) {
                echo "<tr>";
                echo "<td>". $emprunt['id_livre']. "</td>";
                echo "<td>". $emprunt['titre_livre']. "</td>";
                echo "<td>". $emprunt['cote_livre']. "</td>";
                echo "<td class='border-vertical'>". $emprunt['id_util']. "</td>";
                echo "<td>". $emprunt['nom_util']. "</td>";
                echo "<td>". $emprunt['prenom_util']. "</td>";
                echo "<td>". $emprunt['email']. "</td>";
                echo "<td class='border-vertical'>". $emprunt['date_debut_emprunt']. "</td>";
                echo "<td>". calculerDateRetour($emprunt['date_debut_emprunt']) . "</td>";
                echo "<td class='col-statut'>". calculerStatut($emprunt['date_debut_emprunt']) ."</td>";
                echo "<td>
                    <form method='POST' action='gestion-emprunts-reservations.php' onsubmit='return confirmRetour();'>
                        <input type='hidden' name='form' value='confirmerRetour'>
                        <input type='hidden' name='id_livre' value='" . $emprunt['id_livre'] . "'>
                        <input type='hidden' name='id_util' value='" . $emprunt['id_util'] . "'>
                        <button type='submit'>Confirmer retour</button>
                    </form>
                </td>";
                echo "</tr>";
            }
            ?>
            </tbody>
        </table>
        <h1>Réservations</h1>
        <table class="table-emprunts-reservations">
            <thead>
            <tr>
                <th colspan="4">Informations sur le livre</th>
                <th colspan="4" class='border-vertical'>Informations sur l’utilisateur</th>
                <th colspan="3" class='border-vertical'>Informations sur la réservation</th>
                <th>Action</th>
            </tr>
            <tr>
                <th>ID</th>
                <th>Titre</th>
                <th>Cote</th>
                <th>Disponibilité</th>
                <th class='border-vertical'>ID</th>
                <th>Nom</th>
                <th>Prénom</th>
                <th>Email</th>
                <th class='border-vertical'>Date de réservation</th>
                <th>Date de disponibilité</th>
                <th>Position file d'attente</th>
                <th></th>
            </tr>
            </thead>
            <tbody>
            <?php
            foreach ($reservations as $reservation) {
                echo "<tr>";
                echo "<td>". $reservation['id_livre']. "</td>";
                echo "<td>". $reservation['titre_livre']. "</td>";
                echo "<td>". $reservation['cote_livre']. "</td>";
                echo "<td class='col-disponibilite'>". calculerDisponibilite($reservation['disponibilite']). "</td>";
                echo "<td class='border-vertical'>". $reservation['id_util']. "</td>";
                echo "<td>". $reservation['nom_util']. "</td>";
                echo "<td>". $reservation['prenom_util']. "</td>";
                echo "<td>". $reservation['email']. "</td>";
                echo "<td class='border-vertical'>". $reservation['date_reservation']. "</td>";
                echo "<td class='col-date-disponibilite'>". calculerDateDisponibilite($exemplairesDisponibles, $emprunts, $reservations, $reservation['id_util'], $reservation['id_livre']) . "</td>";
                echo "<td>". calculerPositionFileAttente($reservations, $reservation['id_util'], $reservation['id_livre']) ."</td>";
                echo "<td>
                    <form method='POST' action='gestion-emprunts-reservations.php' onsubmit='return confirmSuppression();'>
                        <input type='hidden' name='form' value='annulerReservation'>
                        <input type='hidden' name='id_livre' value='" . $reservation['id_livre'] . "'>
                        <input type='hidden' name='id_util' value='" . $reservation['id_util'] . "'>
                        <button type='submit'>Annuler</button>
                    </form>
                    <form method='POST' action='gestion-emprunts-reservations.php' onsubmit='return confirmEmprunt();'>
                        <input type='hidden' name='form' value='validerEmprunt'>
                        <input type='hidden' name='id_livre' value='" . $reservation['id_livre'] . "'>
                        <input type='hidden' name='id_util' value='" . $reservation['id_util'] . "'>
                        <button type='submit'>Valider l'emprunt</button>
                    </form>
                </td>";
                echo "</tr>";
            }
            ?>
            </tbody>
        </table>
    </main>
    <script>
        function confirmSuppression() {
            return confirm("Êtes-vous sûr de vouloir annuler cette réservation ?");
        }
        function confirmRetour() {
            return confirm("Êtes-vous sûr de vouloir confirmer le retour de ce livre ?");
        }
        function confirmEmprunt() {
            return confirm("Êtes-vous sûr de vouloir confirmer l'emprunt de ce livre ?");
        }
        document.addEventListener("DOMContentLoaded", function () {
            // Sélectionner toutes les cellules avec la classe "disponibilite"
            const disponibiliteCells = document.querySelectorAll(".col-disponibilite");

            // Parcourir chaque cellule pour vérifier son contenu
            disponibiliteCells.forEach(cell => {
                if (cell.textContent.trim() === "Disponible") {
                    cell.style.backgroundColor = "#BCF5C5";
                } else {
                    cell.style.backgroundColor = "#F5BCCA";
                }
            });

            // Sélectionner toutes les cellules avec la classe "disponibilite"
            const dateDisponibiliteCells = document.querySelectorAll(".col-date-disponibilite");

            // Parcourir chaque cellule pour vérifier son contenu
            dateDisponibiliteCells.forEach(cell => {
                if (cell.textContent.trim() === "Disponible") {
                    cell.style.backgroundColor = "#BCF5C5";
                } else {
                    cell.style.backgroundColor = "#F5BCCA";
                }
            });

            // Sélectionner toutes les cellules avec la classe "disponibilite"
            const statutCells = document.querySelectorAll(".col-statut");

            // Parcourir chaque cellule pour vérifier son contenu
            statutCells.forEach(cell => {
                if (cell.textContent.trim() === "En cours") {
                    cell.style.backgroundColor = "#BCF5C5";
                } else {
                    cell.style.backgroundColor = "#F5BCCA";
                }
            });
        });
    </script>
</body>
</html>

<?php require("footer.php"); ?>