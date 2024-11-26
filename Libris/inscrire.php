<?php
    require_once 'header.php';

    $etape = 0;
    $etape_array = array("Date de naissance", "Catégorie d'abonnement", "Informations personnelles","Paiement en ligne");
    $errlog = "";
    function form($value){
        switch($value){
            case 0:
                return '<input type="date" name="date_naissance" required>';
            case 1:
                return '<input type="text" name="nom" placeholder="Nom" required>
                        <input type="text" name="prenom" placeholder="Prénom" required>
                        <input type="text" name="adresse" placeholder="Adresse" required>
                        <input type="tel" name="tel" placeholder="Telephone" required>
                        <input type="email" name="email" placeholder="Email" required>
                        <input type="text" name="pseudo" placeholder="Pseudo" required>
                        <input type="password" name="mdp" placeholder="Mot de passe" required>
                        <input type="password" name="mdp2" placeholder="Confirmer mot de passe" required>';
            case 2:
                return '<select name="categorie" required>
                            <option value="jeune">Moins de 18 ans</option>
                            <option value="etudiant">Etudiant</option>
                            <option value="adulte">Adulte</option>
                            <option value="aucune">Aucune</option>
                        </select>';
            case 3:
                return '<input type="text" name="numero_carte" placeholder="Numéro de carte" required>
                        <input type="text" name="date_expiration" placeholder="Date d\'expiration" required>
                        <input type="text" name="cvv" placeholder="CVV" required>';
        }
    }
    if ($_SERVER["REQUEST_METHOD"] == "POST" && $_POST['form'] === 'inscrire') {
        if (isset($_POST['etape'])) {
            $etape = (int)$_POST['etape'];
        }
        switch ($etape) {
            case 1:
                if (isset($_POST['date_naissance']) && !empty($_POST['date_naissance'])) {
                    $_SESSION['date_naissance'] = $_POST['date_naissance'];
                }else{
                    $errlog = "<p>La date de naissance est requise.</p>";
                    $etape = 0;
                }
                break;
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
                        $errlog = "<p>Les mots de passe ne correspondent pas.</p>";
                        $etape = 1;
                    }
                }else{
                    $errlog = "<p>Les informations personnelles sont requises.</p>";
                    $etape = 1;
                }
                break;
            case 3:
                if (isset($_POST['categorie']) && !empty($_POST['categorie'])) {
                    switch ($_POST['categorie']) {
                        case 'jeune':
                            $_SESSION['categorie'] = 1;
                            break;
                        case 'etudiant':
                            $_SESSION['categorie'] = 2;
                            break;
                        case 'adulte':
                            $_SESSION['categorie'] = 3;
                            break;
                        case 'aucune':
                            $_SESSION['categorie'] = 4;
                            goto fini;
                            break;
                        default:
                            $errlog = "<p>Catégorie d'abonnement invalide.</p>";
                            $etape = 2;
                            break;
                    }
                }else{
                    $errlog = "<p>La catégorie d'abonnement est requise.</p>";
                    $etape = 2;
                }
                break;
            case 4:
                if (isset($_POST['numero_carte'], $_POST['date_expiration'], $_POST['cvv']) && !empty($_POST['numero_carte']) && !empty($_POST['date_expiration']) && !empty($_POST['cvv'])) {
                    $_SESSION['numero_carte'] = $_POST['numero_carte'];
                    $_SESSION['date_expiration'] = $_POST['date_expiration'];
                    $_SESSION['cvv'] = $_POST['cvv'];
                    fini:
                    $stmt = $conn->prepare("INSERT INTO utilisateur (prenom_util, nom_util, adresse_util, tel_util, pseudo, mdp, img_profil, email, date_naissance) VALUES (?, ?, ?, ?, ?, ?, 'test',?, ?)");
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
                    $stmt = $conn->prepare("INSERT INTO est_abonne (id_abonnement, id_util) VALUES (?, ?)");
                    $stmt->bindParam(1, $_SESSION['categorie']);
                    $stmt->bindParam(2, $util['id_util']);
                    $stmt->execute();
                    $_SESSION['user'] = $_SESSION['pseudo'];
                    $_SESSION['email'] = $_SESSION['email'];
                    $_SESSION['admin']=0;
                    $_SESSION['id'] = $util['id_util'];
                    header("Location: index.php");
                    exit();
                }else{
                    $errlog = "<p>Les informations de paiement sont requises.</p>";
                    $etape = 3;
                }
                break;
            default:
                $etape = 0;
                break;
        }
    }

    echo "<div class='login-container'>";

    echo "</div>";
?>
<div class="etape">
    <?php
        foreach($etape_array as $key => $value){
            if($key < $etape){
                echo '<h2 class="active">'.($key+1)."</h2>";
            }elseif($key == $etape){
                echo "<h2 class='active'>".($key+1)."</h2>";
                echo "<h2 class='text'>$value</h2>";
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
            echo "<h2>$etape_array[$etape]</h2>";
            echo $errlog;
            echo form($etape);
            echo '<button class="background-violet" type="submit" name="etape" value="'.((int)$etape+1).'"> Valider</button>';
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