<?php
    require_once 'header.php';
    // Inscription en plusieurs étapes 
    $etape = 0;
    $etape_array = array("Date de naissance", "Catégorie d'abonnement", "Informations personnelles","Paiement en ligne");
    $errlog = "";
    // Fonction pour afficher les formulaires en fonction de l'étape
    function form($value){
        switch($value){
            // Date de naissance
            case 0:
                return '<input type="date" name="date_naissance" value="' . (isset($_SESSION["date_naissance"]) ? $_SESSION["date_naissance"] : "") . '" required>';
            // Informations personnelles
            case 1:
                return '<input type="text" name="nom" placeholder="Nom" value="' . (isset($_SESSION["nom"]) ? $_SESSION["nom"] : "") . '" required>
                        <input type="text" name="prenom" placeholder="Prénom" value="' . (isset($_SESSION["prenom"]) ? $_SESSION["prenom"] : "") . '" required>
                        <input type="text" name="adresse" placeholder="Adresse" value="' . (isset($_SESSION["adresse"]) ? $_SESSION["adresse"] : "") . '" required>
                        <input type="tel" name="tel" placeholder="Telephone" value="' . (isset($_SESSION["tel"]) ? $_SESSION["tel"] : "") . '" required>
                        <input type="email" name="email" placeholder="Email" value="' . (isset($_SESSION["email"]) ? $_SESSION["email"] : "") . '" required>
                        <input type="text" name="pseudo" placeholder="Pseudo" value="' . (isset($_SESSION["pseudo"]) ? $_SESSION["pseudo"] : "") . '" required>
                        <input type="password" name="mdp" placeholder="Mot de passe" required>
                        <input type="password" name="mdp2" placeholder="Confirmer mot de passe"  required>';
            // Catégorie d'abonnement
            case 2:
                return '<select name="categorie" required>
                            <option value="jeune">Moins de 18 ans</option>
                            <option value="etudiant">Etudiant</option>
                            <option value="adulte">Adulte</option>
                            <option value="aucune">Aucune</option>
                        </select>';
            // Paiement en ligne
            case 3:
                return '<input type="text" name="numero_carte" placeholder="Numéro de carte" required>
                        <input type="text" name="date_expiration" placeholder="Date d\'expiration" required>
                        <input type="text" name="cvv" placeholder="CVV" required>';
        }
    }
    // Vérifier les informations et passer à l'étape suivante
    if ($_SERVER["REQUEST_METHOD"] == "POST" && $_POST['form'] === 'inscrire') {
        // Vérifier si l'utilisateur veut revenir à une étape précédente
        if(isset($_POST['retour'])){
            if((int)$_POST['retour'] >=3 && (int)$_SESSION['categorie'] != 2){
                $etape =2;
            }else{ 
                $etape = (int)$_POST['retour'];
            }
            unset($_POST['retour']);
            goto fini;
        } else if (isset($_POST['etape'])) {
            $etape = (int)$_POST['etape'];
        }
        // Vérifier les informations en fonction de l'étape
        switch ($etape) {
            // Date de naissance
            case 1:
                if (isset($_POST['date_naissance']) && !empty($_POST['date_naissance'])) {
                    if ((strtotime($_POST['date_naissance']) > strtotime(date("Y-m-d", strtotime("-1 years")))) || (strtotime($_POST['date_naissance']) < strtotime(date("Y-m-d", strtotime("-100 years"))))){
                        $errlog = "Date de naissance invalide.";
                        $etape = 0;
                    }
                    $_SESSION['date_naissance'] = $_POST['date_naissance'];
                }else{
                    $errlog = "La date de naissance est requise.";
                    $etape = 0;
                }
                break;
            // Catégorie d'abonnement
            case 2:
                if (isset($_POST['nom'], $_POST['prenom'], $_POST['adresse'], $_POST['tel'], $_POST['email'], $_POST['mdp'], $_POST['mdp2'], $_POST['pseudo']) && !empty($_POST['nom']) && !empty($_POST['pseudo']) && !empty($_POST['prenom']) && !empty($_POST['adresse']) && !empty($_POST['tel']) && !empty($_POST['email']) && !empty($_POST['mdp']) && !empty($_POST['mdp2'])) {
                    $_SESSION['nom'] = $_POST['nom'];
                    $_SESSION['prenom'] = $_POST['prenom'];
                    $_SESSION['adresse'] = $_POST['adresse'];
                    $_SESSION['tel'] = $_POST['tel'];
                    $_SESSION['email'] = $_POST['email'];
                    $_SESSION['mdp'] = $_POST['mdp'];
                    $_SESSION['mdp2'] = $_POST['mdp2'];
                    $_SESSION['pseudo'] = $_POST['pseudo'];
                    if ($_SESSION['mdp'] !== $_SESSION['mdp2']) {
                        $errlog = "Les mots de passe ne correspondent pas.";
                        $etape = 1;
                    }
                }else{
                    $errlog = "Les informations personnelles sont requises.";
                    $etape = 1;
                }
                break;
            // Informations personnelles
            case 3:
                if (isset($_POST['categorie']) && !empty($_POST['categorie'])) {
                    switch ($_POST['categorie']) {
                        case 'jeune':
                            $_SESSION['categorie'] = 1;
                            $etape = 4;
                            break;
                        case 'etudiant':
                            $_SESSION['categorie'] = 2;
                            $etape = 4;
                            break;
                        case 'adulte':
                            $_SESSION['categorie'] = 3;
                            break;
                        case 'aucune':
                            $_SESSION['categorie'] = 4;
                            $etape = 4;
                            break;
                        default:
                            $errlog = "Catégorie d'abonnement invalide.";
                            $etape = 2;
                            break;
                    }
                }else{
                    $errlog = "La catégorie d'abonnement est requise.";
                    $etape = 2;
                }
                break;
            // Paiement en ligne
            case 4:
                if (isset($_POST['numero_carte'], $_POST['date_expiration'], $_POST['cvv']) && !empty($_POST['numero_carte']) && !empty($_POST['date_expiration']) && !empty($_POST['cvv'])) {
                    $_SESSION['numero_carte'] = $_POST['numero_carte'];
                    $_SESSION['date_expiration'] = $_POST['date_expiration'];
                    $_SESSION['cvv'] = $_POST['cvv'];
                    //payer
                }else{
                    $errlog = "Les informations de paiement sont requises.";
                    $etape = 3;
                }
                break;
            // Inscription terminée
            case 5:
                if(isset($_SESSION['date_naissance'],$_SESSION['nom'],$_SESSION['prenom'],$_SESSION['adresse'],$_SESSION['tel'],$_SESSION['email'],$_SESSION['mdp'],$_SESSION['pseudo'],$_SESSION['categorie'])){
                    $stmt = $conn->prepare("SELECT id_util FROM utilisateur WHERE email = ? OR pseudo = ?");
                    $stmt->bindParam(1, $_SESSION['email']);
                    $stmt->bindParam(2, $_SESSION['pseudo']);
                    $stmt->execute();
                    $user = $stmt->fetch();
                    if($user){
                        $errlog = "Utilisateur déjà existant.";
                        $etape = 0;
                    }else{
                        $stmt = $conn->prepare("INSERT INTO utilisateur (prenom_util, nom_util, adresse_util, tel_util, pseudo, mdp, img_profil, email, date_naissance) VALUES (?, ?, ?, ?, ?, ?, './img/profil/img_def.svg',?, ?)");
                        $stmt->bindParam(1, $_SESSION['prenom']);
                        $stmt->bindParam(2, $_SESSION['nom']);
                        $stmt->bindParam(3, $_SESSION['adresse']);
                        $stmt->bindParam(4, $_SESSION['tel']);
                        $stmt->bindParam(5, $_SESSION['pseudo']);
                        $_SESSION['mdp']=password_hash($_SESSION['mdp'], PASSWORD_DEFAULT);
                        $stmt->bindParam(6, $_SESSION['mdp']);
                        $stmt->bindParam(7, $_SESSION['email']);
                        $stmt->bindParam(8, $_SESSION['date_naissance']);
                        $stmt->execute();
                        $stmt = $conn->prepare("SELECT id_util FROM utilisateur WHERE email = ?");
                        $stmt->bindParam(1, $_SESSION['email']);
                        $stmt->execute();
                        $util = $stmt->fetch();
                        if($_SESSION['categorie']!=4){
                            $stmt = $conn->prepare("INSERT INTO est_abonne (id_abonnement, id_util) VALUES (?, ?)");
                            $stmt->bindParam(1, $_SESSION['categorie']);
                            $stmt->bindParam(2, $util['id_util']);
                            $stmt->execute();
                        }
                        $_SESSION['user'] = $_SESSION['pseudo'];
                        $_SESSION['email'] = $_SESSION['email'];
                        $_SESSION['admin']=0;
                        $_SESSION['id'] = $util['id_util'];
                        unset($_SESSION['date_naissance'],$_SESSION['nom'],$_SESSION['prenom'],$_SESSION['adresse'],$_SESSION['tel'],$_SESSION['mdp'],$_SESSION['pseudo'],$_SESSION['categorie']);
                        header("Location: index.php");
                        exit();
                    }
                }else{
                    $errlog = "Informations manquantes.";
                    $etape = 0;
                }
                break;
            default:
                $etape = 0;
                break;
        }
        fini:
    }

    echo "<div class='login-container'>";

    echo "</div>";
?>
<div class="etape">
    <?php
        // Afficher les étapes
        foreach($etape_array as $key => $value){
            if($key < $etape){
                echo '<h2 class="active">'.($key+1)."</h2>";
                if($key < 3){
                    echo '<i class="fa-solid fa-horizontal-rule"></i><i class="fa-solid fa-horizontal-rule"></i><i class="fa-solid fa-horizontal-rule"></i><i class="fa-solid fa-arrow-right-long"></i>';
                }
            }elseif($key == $etape){
                echo "<h2 class='active'>".($key+1)."</h2>";
            }else{
                echo "<h2>".($key+1)."</h2>";
            }
        }
    ?>
</div>
<div class="inscrire">
    <form method="POST">
        <input type="hidden" name="form" value="inscrire">
        <?php 
            // Afficher le formulaire en fonction de l'étape
            if($etape>=4){  
                echo "<h2>Fin</h2>";
            }else{
                echo "<h2>$etape_array[$etape]</h2>";
            }
                echo "<p style='color:red;'>". $errlog . "</p>";
                echo form($etape);
                echo '<button class="background-violet" type="submit" name="etape" value="'.((int)$etape+1).'"> Valider</button>';
                if($etape>0){
                    echo '<input type="hidden" name="form" value="inscrire">';
                    echo '<button class="background-violet" type="button" onclick="retour2('.((int)$etape-1).')" name="retour"> Retour</button>';
                }
        ?>
    </form>
    
</div>
<div class="forfait">
    <h2>Liste des forfaits:</h2>
    <table>
        <tr>
            <th>
                <h4>Moins de 18 ans</h4>
            </th>
            <td>
                <h4>Pass Jeune :</h4>
                <p>abonnement gratuit pour les moins de 18 ans (valide 1 an)</p>
                <ul>
                    <li>prêts de 20 documents pour 3 semaines</li>
                </ul>
            </td>
        </tr>
        <tr>
            <th>Etudiants</th>
            <td>
                <h4>Pass Culture :</h4>
                <p>abonnement gratuit pour les étudiants (valide 1 an)</p>
                <ul>
                    <li>prêts de 20 documents pour 3 semaines</li>
                </ul>
            </td>
        </tr>
        <tr>
            <th>Plus de 18 ans</th>
            <td>
                <h4>Pass Lib :</h4>
                <p>abonnement à 18€ pour les plus de 18 ans (valide 1 an)</p>
                <ul>
                    <li>prêts de 20 documents pour 3 semaines   </li>
                </ul>
            </td>
        </tr>   
    </table>
</div>

<?php
    require_once 'footer.php';
?>

<script>
    // Fonction pour revenir à une étape précédente
    function retour2(etape){
        document.querySelector('.inscrire form').innerHTML += '<input type="hidden" name="retour" value="'+etape+'">';
        document.querySelector('.inscrire form').submit();
    }
</script>