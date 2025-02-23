<?php
require('header.php');

// Récupérer les utilisateurs
$stmtUtilisateurs = $conn->prepare("
    SELECT 
        u.*, 
        a.*, 
        ea.date_abonnement
    FROM utilisateur u
    LEFT JOIN est_abonne ea ON u.id_util = ea.id_util
    LEFT JOIN abonnement a ON ea.id_abonnement = a.id_abonnement
    WHERE u.id_util NOT IN (
        SELECT id_util FROM bibliotecaire
    )
");
$stmtUtilisateurs->execute();
$utilisateurs = $stmtUtilisateurs->fetchAll();


// Fonction pour convertir une date en format français
// si $heuremin est vrai, la fonction retourne la date et l'heure
// sinon, elle retourne seulement la date
function convertirDate($date, $heuremin = false) {
    if ($date==null) {
        return "" ;
    }
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

    if ($heuremin){
        return "$jour " . $mois[$moisNum] . " $annee à $heure:$minute";
    }
    return "$jour " . $mois[$moisNum] . " $annee";
}

// Suppression d'un utilisateur
if($_SERVER['REQUEST_METHOD'] == 'POST' && ($_POST['form'] === 'confirmerSuppression')) {
    $id_util = $_POST['id_util'];

    if (!empty($id_util)) {
        $stmt = $conn->prepare("DELETE FROM utilisateur WHERE id_util = :id_util;");
        $stmt->bindParam(':id_util', $id_util, PDO::PARAM_INT);

        $stmt->execute();
        header('Location: gestion-utilisateurs.php');
    }
}
?>
<main class="gestion-utilisateurs">
    <h1>Gestion des comptes</h1>
    <div class="onglet-gestion-content active-gestion" id="emprunts">
        <input type="text" id="search-utilisateurs-input" placeholder="Rechercher un utilisateur..." onkeyup="searchUtilisateurs()">
        <table class="table-gestion" id="table-utilisateurs">
            <thead>
            <tr>
                <th>Nom complet</th>
                <th>Pseudo</th>
                <th>Adresse</th>
                <th>Tel</th>
                <th>Email</th>
                <th>Date de naissance</th>
                <th>Abonnement</th>
                <th>Date d'abonnement</th>
                <th>Actions</th>
            </tr>
            </thead>
            <tbody>
            <?php
            // Afficher les utilisateurs
            foreach ($utilisateurs as $utilisateur) {
                echo "<td>". $utilisateur['prenom_util'] ." ". $utilisateur['nom_util'] ."</td>";
                echo "<td>". $utilisateur['pseudo']. "</td>";
                echo "<td>". $utilisateur['adresse_util']. "</td>";
                echo "<td>". $utilisateur['tel_util']. "</td>";
                echo "<td>". $utilisateur['email']. "</td>";
                echo "<td>". convertirDate($utilisateur['date_naissance']). "</td>";
                echo "<td>". $utilisateur['nom_abonnement']. "</td>";
                echo "<td>". convertirDate($utilisateur['date_abonnement'],true). "</td>";
                echo "<td>
                    <button onclick=popupConfirmSuppression(".$utilisateur['id_util'].")>Supprimer</button>
                </td>";
                echo "</tr>";
            }
            ?>
            </tbody>
        </table>
    </div>
</main>
<script>
    // Fonction pour afficher la popup de confirmation de suppression
    function popupConfirmSuppression(id_util) {
        let popup = document.createElement('div');
        popup.className = 'popupGestion hidden';
        popup.innerHTML = `
            <div class="popupGestion-content">
                <h2>Confirmation de suppresion</h2>
                <p>Êtes-vous sûr de vouloir supprimer cet utilisateur ?</p>
                <form method="POST" action="gestion-utilisateurs.php">
                    <input type="hidden" name="form" value="confirmerSuppression">
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

    // Fonction pour rechercher un utilisateur
    function searchUtilisateurs() {
        let input = document.getElementById('search-utilisateurs-input').value.toLowerCase();

        let table = document.querySelector('#table-utilisateurs tbody');
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
</script>

<?php require("footer.php"); ?>