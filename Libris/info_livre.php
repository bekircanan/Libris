<?php
    require_once 'header.php';


$idAvis=0;

$stmtInsertAvis = $conn->prepare(
    "INSERT INTO avis (id_util, id_livre, note_avis, comment_avis) VALUES ( ?, ?, ?, ?)"
);
$stmtInsertAvis->bindParam(1,$idUtilAvis);
$stmtInsertAvis->bindParam(2,$idLivre);
$stmtInsertAvis->bindParam(3,$new_note);
$stmtInsertAvis->bindParam(4,$new_comment);


$stmtInsertAchatEbooks = $conn->prepare(
    "INSERT INTO achat_ebook (id_util, id_ebook, regle) VALUES ( ?, ?, ?)"
);
$stmtInsertAchatEbooks->bindParam(1,$idUtilAchat);
$stmtInsertAchatEbooks->bindParam(2,$idAchatEbook);
$stmtInsertAchatEbooks->bindParam(3,$new_regle);





if($_SERVER['REQUEST_METHOD'] == 'POST' && ($_POST['form'] === 'res/acha' || $_POST['form'] === 'avis' || $_POST['form'] === 'edition')){
    if(isset($_SESSION['id'])){
        if (isset($_POST['new_avis']) and !isset($_POST['acheter']) && isset($_POST['note']) && !isset($_POST['edition']) && !isset($_POST['reserver'])) {
            /* On se place ici dans le cas où un utilisateur ajoute un commentaire à l'article.*/
            $new_comment = $_POST['new_avis'];
            $idLivre = $_SESSION['idLivreActuel'];
            settype($_SESSION['id'], "integer");
            $new_note = (int)$_POST['note'];
            $idUtilAvis = $_SESSION['id'];
            $stmtInsertAvis->execute();
            header("Location: info_livre.php?id_livre={$_SESSION['idLivreActuel']}");
            exit;
        }
        elseif(!isset($_POST['acheter']) && !isset($_POST['edition']) && isset($_POST['reserver'])){
            $stmtSelectInfoCaracEdition = $conn->prepare("SELECT e.nom_edition, e.id_edition from       edition e JOIN isbn i ON e.id_edition = i.id_edition
                JOIN livre l ON i.id_livre = l.id_livre
                WHERE l.id_livre = {$_SESSION['idLivreActuel']}");
            $stmtSelectInfoCaracEdition->execute();
            $infoCaracEdition = $stmtSelectInfoCaracEdition->fetchAll();
            
            echo '<script type="text/javascript">
                function openEditionModal() {
                    var modal = document.getElementById("editionModal");
                    modal.style.display = "block";
                }

                function closeEditionModal() {
                    var modal = document.getElementById("editionModal");
                    modal.style.display = "none";

                }
            </script>';
            
            echo '<div id="editionModal" style="display:none; position:fixed; z-index:1; left:0; top:0; width:100%; height:100%; overflow:auto; background-color:rgb(0,0,0); background-color:rgba(0,0,0,0.4);">
                    <div style="background-color:#fefefe; margin:15% auto; padding:20px; border:1px solid #888; width:80%;">
                        <span onclick="closeEditionModal()" style="color:#aaa; float:right; font-size:28px; font-weight:bold;">&times;</span>

                        <h2>Choisissez une édition</h2>';
            
            foreach ($infoCaracEdition as $edition) {
                $stmtSelectExemplaire = $conn->prepare("SELECT ex.num_isbn FROM exemplaire ex JOIN isbn i ON ex.num_isbn = i.num_isbn WHERE i.id_edition = ? AND i.id_livre = ?");
                
                $stmtTestEdition = $conn->prepare("SELECT count(*) as nb_ex from exemplaire e Join isbn i ON e.num_isbn = i.num_isbn WHERE i.id_edition = ? AND i.id_livre = ?");
                $stmtTestEditionEmprunt = $conn->prepare("SELECT count(*) as nb_ex from emprunter e Join exemplaire ex ON e.id_exemplaire = ex.id_exemplaire Join isbn i ON ex.num_isbn = i.num_isbn WHERE i.id_edition = ? AND i.id_livre = ?");
                $stmtTestEditionReservation = $conn->prepare("SELECT count(*) as nb_ex from reserver r Join isbn i ON r.num_isbn = i.num_isbn WHERE i.id_edition = ? AND i.id_livre = ?");
                $stmtTestEditionReservation->bindParam(2, $_SESSION['idLivreActuel']);
                $stmtTestEditionReservation->bindParam(1, $edition['id_edition']);
                $stmtTestEditionReservation->execute();
                $nbExemplairesReserve = $stmtTestEditionReservation->fetch();
                $stmtTestEditionEmprunt->bindParam(2, $_SESSION['idLivreActuel']);
                $stmtTestEditionEmprunt->bindParam(1, $edition['id_edition']);
                $stmtTestEditionEmprunt->execute();
                $nbExemplairesEmprunt = $stmtTestEditionEmprunt->fetch();                
                $stmtTestEdition->bindParam(2, $_SESSION['idLivreActuel']);
                $stmtTestEdition->bindParam(1, $edition['id_edition']);
                $stmtTestEdition->execute();
                $nbExemplairesEdition = $stmtTestEdition->fetch();
                $stmtSelectExemplaire->bindParam(1, $edition['id_edition']);
                $stmtSelectExemplaire->bindParam(2, $_SESSION['idLivreActuel']);
                $stmtSelectExemplaire->execute();
                $exemplaire = $stmtSelectExemplaire->fetch();
                if($nbExemplairesEdition['nb_ex'] > $nbExemplairesEmprunt['nb_ex'] + $nbExemplairesReserve['nb_ex']){
                    echo '<form method="post" style="display:inline;">
                        <input type="hidden" name="form" value="edition">
                        <input type="hidden" name="id_livre" value="' . $_SESSION['idLivreActuel'] . '">
                        <input type="hidden" name="id" value="' . $_SESSION['id'] . '">
                        <input type="hidden" name="id_edition" value="' . $edition['id_edition'] . '">
                        <button type="submit" name = "edition" value="'.$edition['id_edition'].'">' . $edition['nom_edition'] . '</button>
                      </form>';
                }
                
            }

            echo '      </div>
                  </div>';

            echo '<script type="text/javascript">openEditionModal();</script>';
        }
        elseif(!isset($_POST['acheter']) && isset($_POST['edition'])){
            echo 'test';
            $selectedEdition = $_POST['edition'];
                $stmtSelectExemplaire = $conn->prepare("SELECT ex.num_isbn FROM exemplaire ex JOIN isbn i ON ex.num_isbn = i.num_isbn WHERE i.id_edition = ? AND i.id_livre = ? And ex.id_exemplaire NOT IN (SELECT id_exemplaire FROM emprunter) And ex.num_isbn NOT IN (SELECT num_isbn FROM reserver)");
                $stmtSelectExemplaire->bindParam(1, $selectedEdition);
                $stmtSelectExemplaire->bindParam(2, $_SESSION['idLivreActuel']);
                $stmtSelectExemplaire->execute();
                $exemplaire = $stmtSelectExemplaire->fetch();
                $idExemplaire = $exemplaire['num_isbn'];
                $stmtInsertReservation = $conn->prepare("INSERT INTO reserver (num_isbn, id_util) VALUES (?, ?)");
                $stmtInsertReservation->bindParam(1, $idExemplaire);
                $stmtInsertReservation->bindParam(2, $_SESSION['id']);
                $stmtInsertReservation->execute();
                $_post['reserver'] = null;
                header("Location: info_livre.php?id_livre={$_SESSION['idLivreActuel']}");
                exit;
                
        }   
        
        elseif(isset($_POST['acheter'])){
            /* Le cas où l'utilisateur a cliquer sur le bouton ajouter au panier' */
            $idUtilAchat = $_SESSION['id'];
            $idAchatEbook = $_SESSION['idEbook'];
            $new_regle = 0;
            $stmtInsertAchatEbooks->execute();
            header("Location: info_livre.php?id_livre={$_SESSION['idLivreActuel']}");
            exit;
        }
    }
    else{
        header("Location: inscrire.php");
        exit;
    }
        
}
elseif($_SERVER['REQUEST_METHOD'] == 'GET'){
    /* On ne passera qu'une fois dans cette boucle, lorsque l'on arrivera sur la page via la page d'acceuil ou la page Mes favoris. */
    $_SESSION['idLivreActuel'] = $_GET['id_livre'];
    
}

/* Requête permettant de tester si l'utilisateur actuel a déjà ajouter l'article a ses favoris. */
$stmtSelectLivre = $conn->prepare(
    "SELECT * from livre where id_livre = {$_SESSION['idLivreActuel']}"
);
$stmtSelectLivre->execute();
$infoLivre = $stmtSelectLivre->fetch();

$stmtTestDisponibilite = $conn->prepare("SELECT * FROM emprunter e Join exemplaire ex ON e.id_exemplaire = ex.id_exemplaire Join isbn i ON ex.num_isbn = i.num_isbn WHERE id_livre = {$_SESSION['idLivreActuel']}");
$stmtTestDisponibilite->execute();
$disponibilite = $stmtTestDisponibilite->fetchAll();
$stmtSelectNbExemplaires = $conn->prepare("SELECT count(*) as nb_exemplaires FROM exemplaire ex join isbn i on ex.num_isbn = i.num_isbn WHERE i.id_livre = {$_SESSION['idLivreActuel']}");
$stmtSelectNbExemplaires->execute();
$nbExemplaires = $stmtSelectNbExemplaires->fetch();
$stmtTestReservation = $conn->prepare("SELECT * FROM reserver r Join isbn i ON r.num_isbn = i.num_isbn WHERE id_livre = {$_SESSION['idLivreActuel']}");
$stmtTestReservation->execute();
$est_reserver = $stmtTestReservation->fetch();




$stmtSelectInfoCaracGenre = $conn->prepare("SELECT g.nom_genre from genre g JOIN livre_genre lg ON g.id_genre = lg.id_genre
JOIN livre l ON lg.id_livre = l.id_livre
WHERE l.id_livre = {$_SESSION['idLivreActuel']}");
$stmtSelectInfoCaracLangue = $conn->prepare("SELECT Distinct langue.nom_langue from langue langue JOIN isbn i ON langue.id_langue = i.id_langue
        JOIN livre l ON i.id_livre = l.id_livre
        WHERE l.id_livre = {$_SESSION['idLivreActuel']}");

$stmtSelectInfoCaracPublic = $conn->prepare("SELECT pc.type_public from public_cible pc  JOIN livre_public lp ON pc.id_public = lp.id_public
        JOIN livre l ON lp.id_livre = l.id_livre
        WHERE l.id_livre = {$_SESSION['idLivreActuel']}");
$stmtSelectInfoCaracDate = $conn->prepare("SELECT ae.date_parution FROM a_ecrit ae JOIN livre l ON ae.id_livre = l.id_livre 
    WHERE l.id_livre = {$_SESSION['idLivreActuel']}");
$stmtSelectInfoCaracEdition2 = $conn->prepare("SELECT e.nom_edition, e.id_edition from       edition e JOIN isbn i ON e.id_edition = i.id_edition
    JOIN livre l ON i.id_livre = l.id_livre
    WHERE l.id_livre = {$_SESSION['idLivreActuel']}");
$stmtSelectInfoCaracEdition2->execute();
$infoCaracEdition2 = $stmtSelectInfoCaracEdition2->fetchAll();

$stmtSelectInfoCaracGenre->execute();
$stmtSelectInfoCaracLangue->execute();
$stmtSelectInfoCaracPublic->execute();
$stmtSelectInfoCaracDate->execute();
$infoCaracGenre = $stmtSelectInfoCaracGenre->fetchAll();
$infoCaracDate = $stmtSelectInfoCaracDate->fetch();

$infoCaracPublic = $stmtSelectInfoCaracPublic->fetchAll();
$infoCaracLangue = $stmtSelectInfoCaracLangue->fetchAll();



$stmtSelectAuteur = $conn->prepare(
    "SELECT * from auteur a JOIN a_ecrit e ON a.id_auteur = e.id_auteur
                            WHERE e.id_livre = {$_SESSION['idLivreActuel']}"
);



$stmtSelectAllAvis = $conn->prepare(
    "SELECT * FROM avis WHERE id_livre = {$_SESSION['idLivreActuel']} order by date_avis desc"
);

?>
    <section id="livre">
        
        <div class="div_img_couverture">
            <img src="<?php echo $infoLivre['img_couverture'] ?>" alt="image de couverture" class = "img_couverture">
        </div>
        <div class="actions"> 
            <h2><?php echo $infoLivre['titre_livre']?></h2>
            <p>Auteurs : 
            <?php 
                $stmtSelectAuteur->execute();
                $auteurs = [];
                foreach($stmtSelectAuteur as $rows){
                    $auteurs[] = $rows['nom_auteur'].' '.$rows['prenom_auteur'];
                }
                echo implode(', ', $auteurs);
            ?> </p>
                
                
                <p>
                    <?php
                        $stmtSelectAllAvis->execute();
                        $allAvis = $stmtSelectAllAvis->fetchAll();
                        $totalAvis = count($allAvis);
                        $sumAvis = 0;

                        foreach ($allAvis as $avis) {
                            $sumAvis += $avis['note_avis'];
                        }

                        $averageAvis = $totalAvis ? $sumAvis / $totalAvis : 0;
                        $averageAvis = round($averageAvis, 1);

                        echo '<div class="average-avis">';
                        echo '<h3>Moyenne des avis : ' . $averageAvis . ' / 5</h3>';
                        echo '<div class="star-rating">';
                        for ($i = 1; $i <= 5; $i++) {
                            if ($i <= $averageAvis) {
                                echo '<span class="star filled">★</span>';
                            }
                        }
                        echo '</div>';
                        echo '<p>Nombre d\'avis : ' . $totalAvis . '</p>';
                        echo '<a href="#avis-form">Donner un avis</a>';
                        echo '</div>';
                        if (($stmtTestDisponibilite->rowCount() < $nbExemplaires['nb_exemplaires']) && $nbExemplaires['nb_exemplaires']- $stmtTestDisponibilite->rowCount() > $stmtTestReservation->rowCount()){
                            echo '<p> Disponible en bibliothèque </p>';
                        } 
                        elseif(($stmtTestDisponibilite->rowCount() < $nbExemplaires['nb_exemplaires']) && ($nbExemplaires['nb_exemplaires']- $stmtTestDisponibilite->rowCount() === $stmtTestReservation->rowCount())){
                            $stmtSelectDateFinEmprunt = $conn->prepare("SELECT date_fin_emprunt FROM emprunter WHERE id_livre = {$_SESSION['idLivreActuel']}");
                            $stmtSelectDateFinEmprunt->execute();
                            $dateFinEmrpunt = $stmtSelectDateFinEmprunt->fetch();
                            echo '<p> Disponible en réservation au maximum le </p>'.$dateFinEmprunt['date_fin_emprunt'];
                        }
                        else{
                            echo '<p> Indisponible pour le moment </p>';
                        }               
                    ?>
                </p>
                <p>
                    <?php
                        $stmtSelectEbook = $conn->prepare("SELECT * FROM ebook WHERE id_livre = {$_SESSION['idLivreActuel']}");
                        $stmtSelectEbook->execute();
                        $infoEbook = $stmtSelectEbook->fetch();
                        if (!empty($infoEbook)){
                            $_SESSION['idEbook'] = $infoEbook['id_ebook'];
                            echo '<p> E-BOOK | '.$infoEbook['prix'];
                            if (isset($_SESSION['id'])){
                                $stmtTestEbookPanier = $conn->prepare("SELECT * FROM achat_ebook WHERE id_util = {$_SESSION['id']} and id_ebook = {$infoEbook['id_ebook']}");
                                $stmtTestEbookPanier->execute();
                                $testEbook = $stmtTestEbookPanier->fetch();
                            }
                            else{
                                $testEbook = null;
                            }
                        }
                    ?>
                </p>
                <?php
                    ?>
                    <form method="post">
                        <input type="hidden" name="form" value="res/acha">
                        <?php   
                        if (!empty($infoEbook) && empty($testEbook)) {
                            echo '<button type="submit" name="acheter" id="btn_achat">Ajouter au panier</button>';
                        } elseif (!empty($infoEbook) && !empty($testEbook)) {
                            echo '<button type="submit" name="acheter" id="btn_achat" disabled>Ajouter au panier</button>';
                        }
                        ?>
                        
                        <button type="submit" name="reserver" id="btn_res" <?php 
                            if (isset($_SESSION['id'])){
                                $stmtSelectIsbn = $conn->prepare("SELECT e.num_isbn FROM exemplaire e JOIN isbn i ON e.num_isbn = i.num_isbn WHERE i.id_livre = {$_SESSION['idLivreActuel']}");
                                $stmtSelectIsbn->execute();
                                $isbn = $stmtSelectIsbn->fetchAll();
                                $stmtAiReserver = $conn->prepare("SELECT * FROM reserver WHERE id_util = ?");
                                $stmtAiReserver->bindParam(1, $_SESSION['id']);
                                $stmtAiReserver->execute();
                                $reservations = $stmtAiReserver->fetchAll();
                                $testReserver = false;
                                foreach($reservations as $rows){
                                    foreach($isbn as $row){
                                        if($rows['num_isbn'] === $row['num_isbn']){
                                            $testReserver = true;
                                        }
                                    }
                                }
                                if ($testReserver){
                                    echo 'disabled';
                                }
                                elseif (($stmtTestDisponibilite->rowCount() < $nbExemplaires['nb_exemplaires']) && ($nbExemplaires['nb_exemplaires']- $stmtTestDisponibilite->rowCount() >= $stmtTestReservation->rowCount()) ){ 
                                    echo ''; 
                                }
                                else{
                                    echo 'disabled'; 
                                }
                            }
                             
                            else { 
                                echo ''; 
                            } ?>>Réserver</button>
                    </form>
        </div>
    </section>
    <section id ="res/carac">
        <div id="Resume">
            <h2>Résumé</h2>
            <p><?php echo $infoLivre['resume']?></p>
        </div>    
        <div id="Caracteristisques">
            <h2>Caractéristiques</h2>
            <?php


                echo '<p> Date de parution..........................'.$infoCaracDate['date_parution'].'</p>';              
                echo '<p> Cote......................................'.$infoLivre['cote_livre'].'</p>';
                echo '<p> Genre.....................................';
                foreach($infoCaracGenre as $rows){
                    echo $rows['nom_genre']." ";
                }
                echo '</p>';
                echo '<p> Langue....................................';
                foreach($infoCaracLangue as $rows){
                    echo $rows['nom_langue']." ";
                }
                echo '</p>';
                echo '<p> Edition...................................';
                foreach($infoCaracEdition2 as $rows){
                    echo $rows['nom_edition']." ";
                }
                echo '</p>';
                echo '<p> Type littéraire...........................'.$infoLivre['type_litteraire'].'</p>';
                echo '<p> Public cible..............................';
                foreach($infoCaracPublic as $rows){
                    echo $rows['type_public']." ";
                }
                echo '</p>';
            
            
                
            ?>
        </div>  
            
            
        
    </section>

    <form  method="post">
        <input type="hidden" name="form" value="avis">
        <h3>Laisser un avis :</h3>
        <div class="note">
            <input type="radio" id="etoile5" name="note" value="5" />
            <label for="etoile5" title="5 étoiles">★</label>
            <input type="radio" id="etoile4" name="note" value="4" />
            <label for="etoile4" title="4 étoiles">★</label>
            <input type="radio" id="etoile3" name="note" value="3" />
            <label for="etoile3" title="3 étoiles">★</label>
            <input type="radio" id="etoile2" name="note" value="2" />
            <label for="etoile2" title="2 étoiles">★</label>
            <input type="radio" id="etoile1" name="note" value="1" />
            <label for="etoile1" title="1 étoile">★</label>
        </div>

        <textarea name="new_avis" rows="5" required></textarea>

        <button type="submit">Commenter</button>
    </form>

    <section class="affichage_avis">
        <h3> Avis : </h3>

        <?php
            $stmtSelectAllAvis->execute();
            foreach ($stmtSelectAllAvis as $rows){
                $_SESSION['idUtilAvis'] = $rows['id_util'];
                $sql = "SELECT * from utilisateur where id_util = {$_SESSION['idUtilAvis']}";
                $result = $conn->query($sql);
                $row2 = $result->fetch();
                $pseudo_util = $row2['pseudo'];
                echo '<div class="affichage_avis">';
                echo '<img src= "'.$row2['img_profil'].'" alt="Image de profil">';
                echo '<div class="description_utilisateur"> <p>' . $pseudo_util . '</p><p>' . $rows['date_avis']. '</p> </div>';
                echo '<div class="star-rating">';
                for ($i = 1; $i <= 5; $i++) {
                    if ($i <= $rows['note_avis']) {
                        echo '<span class="star filled">★</span>';
                    } 
                }
                echo '</div>';
                echo "<p>" . $rows['comment_avis']."</p>";
                echo '</div>';
            }
        ?>
    </section>

       



<?php
    require_once 'footer.php';
?>