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
                return '<select name="categorie" required>
                            <option value="jeune">Moins de 18 ans</option>
                            <option value="etudiant">Etudiant</option>
                            <option value="adulte">Adulte</option>
                        </select>';
            case 2:
                return '<input type="text" name="nom" placeholder="Nom" required>
                        <input type="text" name="prenom" placeholder="Prénom" required>
                        <input type="text" name="adresse" placeholder="Adresse" required>
                        <input type="text" name="ville" placeholder="Ville" required>
                        <input type="text" name="code_postal" placeholder="Code postal" required>
                        <input type="email" name="email" placeholder="Email" required>
                        <input type="password" name="mdp" placeholder="Mot de passe" required>
                        <input type="password" name="mdp2" placeholder="Confirmer mot de passe" required>';
            case 3:
                return '<input type="text" name="numero_carte" placeholder="Numéro de carte" required>
                        <input type="text" name="date_expiration" placeholder="Date d\'expiration" required>
                        <input type="text" name="cvv" placeholder="CVV" required>';
        }
    }
    if($_SERVER["REQUEST_METHOD"] == "POST"){
        if(isset($_POST['etape'])){
            $etape = $_POST['etape'];
        }
        if(isset($_Post['date_naissance'])){
            $date_naissance = $_POST['date_naissance'];
        }elseif(isset($_POST['categorie'])){
            $categorie = $_POST['categorie'];
        }elseif(isset($_POST['nom'])){}

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