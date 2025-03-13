<?php
require('header.php');

use PHPMailer\PHPMailer\PHPMailer;
require '.\vendor\autoload.php';
$errlog = '';

// Fonction pour envoyer un mail
function smtp($email, $subject, $body){
    $mail = new PHPMailer();
    $mail->IsSMTP(); 
    $mail->SMTPAuth = true;
    $mail->SMTPSecure = "tls";
    $mail->Host = "smtp.gmail.com";
    $mail->Port = 587;
    $mail->isHTML(true);
    $mail->CharSet = 'UTF-8';
    $mail->Username = "email";
    $mail->Password = "password";
    $mail->AddAddress($email);
    $mail->SetFrom("Libris-supp@gmail.com");
    $mail->Subject = $subject;
    $mail->Body = $body;
    $mail->SMTPOptions=array('ssl'=>array(
        'verify_peer'=>false,
        'verify_peer_name'=>false,
        'allow_self_signed'=>false
    ));
    if(!$mail->Send()){
        return 'Erreur: '.$mail->ErrorInfo;
    }else{
        return 'Mail envoyé';
    }
}


function envoyeMailAll($emprunts){
    $subject = 'Rappel de retour de livre';
    foreach($emprunts as $emprunt){
        if(calculerStatut($emprunt['date_debut_emprunt'])=="En retard"){
            $email = $emprunt['email'];
            $body = 'Bonjour '.$emprunt['prenom_util'].' '.$emprunt['nom_util'].',<br><br>
            Nous vous rappelons que vous avez emprunté le livre "'.$emprunt['titre_livre'].'" le '.$emprunt['date_debut_emprunt'].'. La date de retour prévue est le '.calculerDateRetour($emprunt['date_debut_emprunt']).'.<br><br>
            Merci de bien vouloir nous le retourner dans les plus brefs délais.<br><br>
            Cordialement,<br>
            L\'équipe de Libris';
            $errlog = smtp($email, $subject, $body);
        }
    }
}

function envoyeMail($email){
    $subject = 'Rappel de retour de livre';
    $email = $email;
    $body = 'Bonjour,<br><br>
    Nous vous rappelons que vous avez emprunté le livre.
    Merci de bien vouloir nous le retourner dans les plus brefs délais.<br><br>
    Cordialement,<br>
    L\'équipe de Libris';
    smtp($email, $subject, $body);
}

// 
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

if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['form'])){
    if($_POST['form'] === 'envoyerMail'){
        envoyeMailALL($emprunts);
    }else{
        envoyeMail($_POST['button']);
    }
}

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

function calculerStatut($dateEmprunt) {
    $dateRetourObj = new DateTime(calculerDateRetour($dateEmprunt));
    $dateActuelle = new DateTime();
    if ($dateRetourObj >= $dateActuelle) {
        return "En cours";
    } else {
        return "En retard";
    }
}

function calculerDisponibilite($num_isbn, $nbExemplairesDisponibles) {
    foreach ($nbExemplairesDisponibles as $exemplaire) {
        if ($exemplaire['num_isbn'] == $num_isbn) {
            if ($exemplaire['nb_exemplaires_disponibles'] > 0) {
                return "Disponible (".$exemplaire['nb_exemplaires_disponibles'] ."/".$exemplaire['nb_exemplaires'].")";
            } else {
                return "Indisponible";
            }
        }
    }
}

function calculerPositionFileAttente($reservations, $id_util, $num_isbn) {
    $i = 1;
    foreach ($reservations as $reservation) {
        if ($reservation['num_isbn'] == $num_isbn) {
            if ($reservation['id_util'] == $id_util) {
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
            $dateDisponibilite = convertirDate2(array_shift($fileRetoursEmprunts));
        }
    }
    else {
        while ($user != $id_util) {
            $user = array_shift($fileAttente);
            if ($nbExemplairesDisponibles > 0) {
                $nbExemplairesDisponibles--;
            }
            else {
                $dateDisponibilite = convertirDate2(array_shift($fileRetoursEmprunts));
            }
        }
    }

    return $dateDisponibilite;
}

function convertirDate($date) {
    $timestamp = strtotime($date);

    // Formats
    $mois = [
        "01" => "janvier", "02" => "février", "03" => "mars",
        "04" => "avril", "05" => "mai", "06" => "juin",
        "07" => "juillet", "08" => "août", "09" => "septembre",
        "10" => "octobre", "11" => "novembre", "12" => "décembre"
    ];

    $jour = date("d", $timestamp);
    $moisNum = date("m", $timestamp);
    $annee = date("Y", $timestamp);
    $heure = date("H", $timestamp);
    $minute = date("i", $timestamp);

    return "$jour " . $mois[$moisNum] . " $annee à $heure:$minute";
}

function convertirDate2($date) {
    $timestamp = strtotime($date);

    // Formats
    $mois = [
        "01" => "janvier", "02" => "février", "03" => "mars",
        "04" => "avril", "05" => "mai", "06" => "juin",
        "07" => "juillet", "08" => "août", "09" => "septembre",
        "10" => "octobre", "11" => "novembre", "12" => "décembre"
    ];

    $jour = date("d", $timestamp);
    $moisNum = date("m", $timestamp);
    $annee = date("Y", $timestamp);
    $heure = date("H", $timestamp);
    $minute = date("i", $timestamp);

    return "$jour " . $mois[$moisNum] . " $annee";
}

if($_SERVER['REQUEST_METHOD'] == 'POST' && ($_POST['form'] === 'confirmerRetour')) {
    $id_exemplaire = $_POST['id_exemplaire'];
    $id_util = $_POST['id_util'];

    if (!empty($id_exemplaire) && !empty($id_util)) {
        $stmt = $conn->prepare("DELETE FROM emprunter WHERE id_exemplaire = :id_exemplaire AND id_util = :id_util");
        $stmt->bindParam(':id_exemplaire', $id_exemplaire, PDO::PARAM_INT);
        $stmt->bindParam(':id_util', $id_util, PDO::PARAM_INT);

        $stmt->execute();
        header('Location: gestion-emprunts-reservations.php');
        exit;
    }
}

if($_SERVER['REQUEST_METHOD'] == 'POST' && ($_POST['form'] === 'annulerReservation')) {
    $num_isbn = $_POST['num_isbn'];
    $id_util = $_POST['id_util'];

    if (!empty($num_isbn) && !empty($id_util)) {
        $stmt = $conn->prepare("DELETE FROM reserver WHERE num_isbn = :num_isbn AND id_util = :id_util");
        $stmt->bindParam(':num_isbn', $num_isbn, PDO::PARAM_STR);
        $stmt->bindParam(':id_util', $id_util, PDO::PARAM_INT);

        $stmt->execute();
        header('Location: gestion-emprunts-reservations.php');
        exit;
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && ($_POST['form'] === 'validerEmprunt')) {
    $num_isbn = $_POST['num_isbn'];
    $id_util = $_POST['id_util'];

    if (!empty($num_isbn) && !empty($id_util)) {
        $stmtDelete = $conn->prepare("DELETE FROM reserver WHERE num_isbn = :num_isbn AND id_util = :id_util");
        $stmtDelete->bindParam(':num_isbn', $num_isbn, PDO::PARAM_STR);
        $stmtDelete->bindParam(':id_util', $id_util, PDO::PARAM_INT);
        $stmtDelete->execute();

        $stmtExemplaire = $conn->prepare("
            SELECT e.id_exemplaire
            FROM EXEMPLAIRE e
            LEFT JOIN EMPRUNTER em ON e.id_exemplaire = em.id_exemplaire AND em.date_fin_emprunt IS NULL
            WHERE e.num_isbn = :num_isbn AND em.id_exemplaire IS NULL
            LIMIT 1
        ");
        $stmtExemplaire->bindParam(':num_isbn', $num_isbn, PDO::PARAM_STR);
        $stmtExemplaire->execute();
        $idExemplaireDisponible = $stmtExemplaire->fetchColumn();


        foreach ($exemplairesDisponibles as $exemplaire) {
            if ($exemplaire['num_isbn'] == $num_isbn) {
                $idExemplaire = $exemplaire['id_exemplaire'];
                break;
            }
        }

        $stmtValider = $conn->prepare("INSERT INTO emprunter(id_exemplaire, id_util) VALUES (:id_exemplaire, :id_util)");
        $stmtValider->bindParam(':id_exemplaire', $idExemplaire, PDO::PARAM_INT);
        $stmtValider->bindParam(':id_util', $id_util, PDO::PARAM_INT);
        $stmtValider->execute();

        header('Location: gestion-emprunts-reservations.php');
        exit;
    }
}

?>

    <!DOCTYPE html>
    <html lang="FR">
    <head>
        <title>Gestion des emprunts et des réservations</title>
        <meta charset="UTF-8">
    </head>
    <body>
    <main class="gestion-emprunts-reservations">

        <div class="onglets-gestion">
            <div class="onglet-gestion active-gestion" id="onglet-gestion-emprunts" onclick="openTab('emprunts')">Emprunts</div>
            <div class="onglet-gestion" id="onglet-gestion-reservations" onclick="openTab('reservations')">Réservations</div>
        </div>

        <div class="onglet-gestion-content active-gestion" id="emprunts">
            <input type="text" id="search-emprunts-input" placeholder="Rechercher un emprunt..." onkeyup="searchEmprunts()">
            <form method="Post">
                <input type="hidden" name="form" value="envoyerMail">
                <button class="sumbit">Envoyer un mail de rappel</button>
            </form>
            <table class="table-gestion" id="table-emprunts">
                <thead>
                <tr>
                    <th>Utilisateur</th>
                    <th>Livre</th>
                    <th>ISBN</th>
                    <th>ID exemplaire</th>
                    <th>Date de début</th>
                    <th>Date de retour prévu</th>
                    <th>Statut</th>
                    <th>Actions</th>
                </tr>
                </thead>
                <tbody>
                <?php
                foreach ($emprunts as $emprunt) {
                    echo "<tr>";
                    echo "<td>". $emprunt['prenom_util']." ". $emprunt['nom_util'] ."</td>";
                    echo "<td>". $emprunt['titre_livre']. "</td>";
                    echo "<td>". $emprunt['num_isbn']. "</td>";
                    echo "<td>". $emprunt['id_exemplaire']. "</td>";
                    echo "<td>". convertirDate($emprunt['date_debut_emprunt']). "</td>";
                    echo "<td>". convertirDate(calculerDateRetour($emprunt['date_debut_emprunt']))  . "</td>";
                    echo "<td class='col-statut'>". calculerStatut($emprunt['date_debut_emprunt']) ."</td>";
                    echo "<td>
                        <button onclick=popupConfirmRetour(".$emprunt['id_exemplaire'].",".$emprunt['id_util'].")>Retour</button>
                        <form method='Post'>
                            <input type='hidden' name='form' value='envoyerMail2'>
                            <button class='sumbit' name='button' value='".$emprunt['email']."'>Envoyer</button>
                        </form>
                    </td>";
                    echo "</tr>";
                }
                ?>
                </tbody>
            </table>
        </div>
        <div class="onglet-gestion-content" id="reservations">
            <input type="text" id="search-reservations-input" placeholder="Rechercher une réservation..." onkeyup="searchReservations()">
            <table class="table-gestion" id="table-reservations">
                <thead>
                <tr>
                    <th>Utilisateur</th>
                    <th>Livre</th>
                    <th>Cote</th>
                    <th>ISBN</th>
                    <th>Disponibilité édition</th>
                    <th>Date de réservation</th>
                    <th>Date de disponibilité</th>
                    <th>Position file d'attente</th>
                    <th>Actions</th>
                </tr>
                </thead>
                <tbody>
                <?php
                foreach ($reservations as $reservation) {
                    echo "<tr>";
                    echo "<td>". $reservation['prenom_util']." ". $reservation['nom_util'] ."</td>";
                    echo "<td>". $reservation['titre_livre']. "</td>";
                    echo "<td>". $reservation['cote_livre']. "</td>";
                    echo "<td>". $reservation['num_isbn']. "</td>";
                    echo "<td class='col-disponibilite'>". calculerDisponibilite($reservation['num_isbn'], $nbExemplairesDisponibles). "</td>";
                    echo "<td>". convertirDate($reservation['date_reservation']). "</td>";
                    echo "<td class='col-date-disponibilite'>". calculerDateDisponibilite($nbExemplairesDisponibles, $emprunts, $reservations, $reservation['id_util'], $reservation['num_isbn']) . "</td>";
                    echo "<td>". calculerPositionFileAttente($reservations, $reservation['id_util'], $reservation['num_isbn']) ."</td>";
                    echo "<td>
                        <button onclick=popupConfirmSuppression('".$reservation['num_isbn']."',".$reservation['id_util'].")>Supprimer</button>";
                    if (calculerDateDisponibilite($nbExemplairesDisponibles, $emprunts, $reservations, $reservation['id_util'], $reservation['num_isbn']) == "Disponible") {
                        echo "<button onclick=popupConfirmEmprunt('".$reservation['num_isbn']."',".$reservation['id_util'].")>Accepter</button>";
                    }
                    echo "</td>";
                    echo "</tr>";
                }
                ?>
                </tbody>
            </table>
        </div>
    </main>
    <script>

        // Fonction pour ouvrir l'onglet sélectionné
        function openTab(tabId) {
            // Supprime la classe active des onglets
            document.querySelectorAll('.onglet-gestion').forEach(tab => tab.classList.remove('active-gestion'));
            // Supprime la classe active des contenus
            document.querySelectorAll('.onglet-gestion-content').forEach(content => content.classList.remove('active-gestion'));

            // Active l'onglet cliqué
            document.querySelector(`[onclick="openTab('${tabId}')"]`).classList.add('active-gestion');
            // Active le contenu correspondant
            document.getElementById(tabId).classList.add('active-gestion');
        }

        function searchEmprunts() {
            let input = document.getElementById('search-emprunts-input').value.toLowerCase();

            let table = document.querySelector('#table-emprunts tbody');
            let rows = table.getElementsByTagName('tr');

            // Parcourir les lignes du tableau
            for (let i = 0; i < rows.length; i++) {
                let row = rows[i];
                let cells = row.getElementsByTagName('td');
                let rowText = "";

                // Concaténer le texte des colonnes de la ligne actuelle
                for (let j = 0; j < cells.length; j++) {
                    rowText += cells[j].innerText.toLowerCase() + " ";
                }

                // Vérifier si le texte de la ligne contient la valeur recherchée
                if (rowText.includes(input)) {
                    row.style.display = "";
                } else {
                    row.style.display = "none";
                }
            }
        }
        function searchReservations() {
            let input = document.getElementById('search-reservations-input').value.toLowerCase();

            let table = document.querySelector('#table-reservations tbody');
            let rows = table.getElementsByTagName('tr');

            // Parcourir les lignes du tableau
            for (let i = 0; i < rows.length; i++) {
                let row = rows[i];
                let cells = row.getElementsByTagName('td');
                let rowText = "";

                // Concaténer le texte des colonnes de la ligne actuelle
                for (let j = 0; j < cells.length; j++) {
                    rowText += cells[j].innerText.toLowerCase() + " ";
                }

                // Vérifier si le texte de la ligne contient la valeur recherchée
                if (rowText.includes(input)) {
                    row.style.display = "";
                } else {
                    row.style.display = "none";
                }
            }
        }

        function popupConfirmRetour(id_exemplaire, id_util) {
            let popup = document.createElement('div');
            popup.className = 'popupGestion hidden';
            popup.innerHTML = `
                <div class="popupGestion-content">
                    <h2>Confirmation de retour</h2>
                    <p>Êtes-vous sûr de vouloir confirmer le retour de cet emprunt ?</p>
                    <form method="POST" action="gestion-emprunts-reservations.php">
                        <input type="hidden" name="form" value="confirmerRetour">
                        <input type="hidden" name="id_exemplaire" value="${id_exemplaire}">
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

        function popupConfirmSuppression(num_isbn, id_util) {
            let popup = document.createElement('div');
            popup.className = 'popupGestion hidden';
            popup.innerHTML = `
                <div class="popupGestion-content">
                    <h2>Confirmation d'annulation</h2>
                    <p>Êtes-vous sûr de vouloir annuler cette réservation ?</p>
                    <form method="POST" action="gestion-emprunts-reservations.php">
                        <input type="hidden" name="form" value="annulerReservation">
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

        function popupConfirmEmprunt(num_isbn, id_util) {
            let popup = document.createElement('div');
            popup.className = 'popupGestion hidden';
            popup.innerHTML = `
                <div class="popupGestion-content">
                    <h2>Confirmation d'emprunt</h2>
                    <p>Êtes-vous sûr de vouloir accepter l'emprunt de ce livre ?</p>
                    <form method="POST" action="gestion-emprunts-reservations.php">
                        <input type="hidden" name="form" value="validerEmprunt">
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

        document.addEventListener("DOMContentLoaded", function () {
            // Sélectionner toutes les cellules avec la classe "disponibilite"
            const disponibiliteCells = document.querySelectorAll(".col-disponibilite");

            // Parcourir chaque cellule pour vérifier son contenu
            disponibiliteCells.forEach(cell => {

                if (cell.textContent.substring(0, 10) === "Disponible") {
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
