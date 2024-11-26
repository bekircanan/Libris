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

$stmtInsertReservation = $conn->prepare(
    "INSERT INTO reserver (id_livre, id_util) VALUES ( ?, ?)"
);
$stmtInsertReservation->bindParam(1,$id_livre);
$stmtInsertReservation->bindParam(2,$id_util);


if($_SERVER['REQUEST_METHOD'] == 'POST' && ($_POST['form'] === 'res/acha' || $_POST['form'] === 'avis')){
    
    if (isset($_POST['new_avis']) and !isset($_POST['acheter']) && isset($_POST['rating'])){
        /* On se place ici dans le cas où un utilisateur ajoute un commentaire à l'article.*/
        $new_comment = $_POST['new_avis'];
        $idLivre = $_SESSION['idLivreActuel'];
        settype($_SESSION['id'], "integer");
        $new_note = (int)$_POST['note'];
        $idUtilAvis = $_SESSION['id'];
        $stmtInsertAvis->execute();
        header("Location: livre.php?id_livre={$_SESSION['idArticleActuel']}");
        exit;
    }
    elseif(!isset($_SESSION['acheter'])){
        $id_livre = $_SESSION['idLivreActuel'];
        $id_util = $_SESSION['id'];
        $stmtInsertReservation->execute();
        header("Location: livre.php?id_livre={$_SESSION['idLivreActuel']}");
        exit;
    }
    else{
        /* Le cas où l'utilisateur a cliquer sur le bouton ajouter au panier' */
        $idUtilAchat = $_SESSION['id'];
        $idAchatEbook = $_SESSION['idEbook'];
        $new_regle = 0;
        $stmtInsertAchatEbooks->execute();
        header("Location: livre.php?id_livre={$_SESSION['idLivreActuel']}");
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

$stmtTestDisponibilite = $conn->prepare("SELECT * FROM emprunter WHERE id_livre = {$_SESSION['idLivreActuel']}");
$stmtTestDisponibilite->execute();
$disponibilite = $stmtTestDisponibilite->fetchAll();
$stmtSelectNbExemplaires = $conn->prepare("SELECT nb_exemplaires FROM exemplaire WHERE id_livre = {$_SESSION['idLivreActuel']}");
$stmtSelectNbExemplaires->execute();
$nbExemplaires = $stmtSelectNbExemplaires->fetch();
$stmtTestReservation = $conn->prepare("SELECT * FROM reserver WHERE id_livre = {$_SESSION['idLivreActuel']}");
$stmtTestReservation->execute();
$est_reserver = $stmtTestReservation->fetch();

$stmtSelectAuteur = $conn->prepare(
    "SELECT * from auteur a JOIN a_ecrit e ON a.id_auteur = e.id_auteur
                            WHERE e.id_livre = {$_SESSION['idLivreActuel']}"
);



$stmtSelectAllAvis = $conn->prepare(
    "SELECT * FROM avis WHERE id_livre = {$_SESSION['idLivreActuel']} order by date_avis desc"
);

?>
    <section id="livre">
        <div class="img_couverture">
            <img src="<?php echo $infoLivre['img_couverture'] ?>" alt="image de couverture">
        </div>
        <div class="infos_livre_generales"> 
            <h2><?php echo $infoLivre['titre_livre']?></h2>
            <p>Auteurs : 
            <?php 
                $stmtSelectAuteur->execute();
                foreach($stmtSelectAuteur as $rows){
                    echo '<p>'.$rows['nom_auteur'].' '.$rows['prenom_auteur'].', ';   
                }
            ?> </p>
                
                
                <p>
                    <?php
                        if ((empty($disponibilite) || $stmtTestDisponibilite->rowCount() < $nbExemplaires['nb_exemplaires']) and empty($est_reserver)){
                            echo '<p> Disponible en bibliothèque </p>';
                        } 
                        elseif((!empty($disponibilite) || $stmtTestDisponibilite->rowCount() === $nbExemplaires['nb_exemplaires']) and empty($est_reserver)){
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
                            $stmtTestEbookPanier = $conn->prepare("SELECT * FROM achat_ebook WHERE id_util = {$_SESSION['id']} and id_ebook = {$infoEbook['id_ebook']}");
                            $stmtTestEbookPanier->execute();
                            $testEbook = $stmtTestEbookPanier->fetch();
                        }
                    ?>
                </p>
                <?php
                    ?>
                    <form method="post">
                        <input type="hidden" name="form" value="res/acha">
                        <?php echo (!empty($infoEbook) && empty($testEbook)) ? '<button type="submit" name="acheter" id="btn_achat">Ajouter au panier</button>' : ((!empty($infoEbook) && !empty($testEbook)) ? '<button type="submit" name="acheter" id="btn_achat" disabled>Ajouter au panier</button>' : ''); ?>
                        <button type="submit" name="reserver" id="btn_res" <?php echo ((empty($disponibilite) || $stmtTestDisponibilite->rowCount() < $nbExemplaires['nb_exemplaires']) and empty($est_reserver)) or ((!empty($disponibilite) || $stmtTestDisponibilite->rowCount() === $nbExemplaires['nb_exemplaires']) and empty($est_reserver)) ? '': 'disabled' ?>>Réserver</button>
                    </form>
        </div>
    </section>
    <section id="res/carac">
        <div id="Resume">
            <h2>Résumé</h2>
            <p><?php echo $infoLivre['resume']?></p>
        </div>    
        <div id="Caracteristisques">
            <h2>Caractéristiques</h2>
            <?php
                $stmtSelectInfoCaracGenre = $conn->prepare("SELECT g.nom_genre from genre g JOIN livre_genre lg ON g.id_genre = lg.id_genre
                JOIN livre l ON lg.id_livre = l.id_livre
                WHERE l.id_livre = {$_SESSION['idLivreActuel']}");
                $stmtSelectInfoCaracLangue = $conn->prepare("SELECT langue.nom_langue from langue langue JOIN livre_langue ll ON langue.id_langue = ll.id_langue
                        JOIN livre l ON ll.id_livre = l.id_livre
                        WHERE l.id_livre = {$_SESSION['idLivreActuel']}");
                $stmtSelectInfoCaracEdition = $conn->prepare("SELECT e.nom_edition from edition e JOIN livre_edition le ON e.id_edition = le.id_edition
                        JOIN livre l ON le.id_livre = l.id_livre
                        WHERE l.id_livre = {$_SESSION['idLivreActuel']}");
                $stmtSelectInfoCaracPublic = $conn->prepare("SELECT pc.type_public from public_cible pc  JOIN livre_public lp ON pc.id_public = lp.id_public
                        JOIN livre l ON lp.id_livre = l.id_livre
                        WHERE l.id_livre = {$_SESSION['idLivreActuel']}");
                $stmtSelectInfoCaracDate = $conn->prepare("SELECT date_parution/*, l.isbn*/ FROM a_ecrit ae JOIN livre l ON ae.id_livre = l.id_livre 
                    WHERE l.id_livre = {$_SESSION['idLivreActuel']}");
                
                $stmtSelectInfoCaracGenre->execute();
                $stmtSelectInfoCaracLangue->execute();
                $stmtSelectInfoCaracEdition->execute();
                $stmtSelectInfoCaracPublic->execute();
                $stmtSelectInfoCaracDate->execute();
                $infoCaracGenre = $stmtSelectInfoCaracGenre->fetchAll();
                $infoCaracDate = $stmtSelectInfoCaracDate->fetch();
                $infoCaracEdition = $stmtSelectInfoCaracEdition->fetchAll();
                $infoCaracPublic = $stmtSelectInfoCaracPublic->fetchAll();
                $infoCaracLangue = $stmtSelectInfoCaracLangue->fetchAll();


                echo '<p> Date de parution..........................'.$infoCaracDate['date_parution'].'</p>';
                echo '<p> ISBN......................................'./*$infoCaracDate['isbn'].*/'</p>';                
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
                foreach($infoCaracEdition as $rows){
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
                    echo '<img src= "' . $row2['img_profil'] . '" alt="Image de profil">';
                    echo '<div class="description_utilisateur"> <p>' . $pseudo_util . '</p><p>' . $rows['date_avis']. '</p>';
                    for ($i=0; $i<$rows['note_avis'];$i++ ){
                        echo '<label for="etoileJaune">★</label>';
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