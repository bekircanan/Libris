<?php
    require_once 'header.php';


$idAvis=0;


/* 
    On commence par préparer les requêtes SQL qui seront utilisées dans le code. 
    On prépare les requêtes pour éviter les injections SQL.
    Les deux requètes suivantes permettront l'insertion d'un avis et l'achat d'un ebook dans la base de donnée.
*/
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
        if (isset($_POST['new_avis']) and !isset($_POST['acheter']) && !isset($_POST['edition']) && !isset($_POST['reserver'])) {
            /* 
                On se place ici dans le cas où un utilisateur ajoute un commentaire à l'article.
                On vérifie que l'utilisateur a bien saisi un commentaire et une note.
                Dans le cas où l'utilisateur n'a pas saisi de bote, on affiche un message d'erreur.
                Si l'utilisateur a bien saisi une note et un commentaire, on insère ces informations dans la base de donnée 
                et une popup apparaît, informant l'utilisateur du succès de l'opération.
            */
            if (empty($_POST['note'])) {
                echo '<script>
                
                    document.addEventListener("DOMContentLoaded", function() {
                        event.preventDefault();
                        let popup = document.createElement("div");
                        popup.className = "popupGestion";
                        popup.innerHTML = `
                            <div class="popupGestion-content">
                                <h2>Erreur </h2>
                                <p>Veuillez saisir une note</p>
                                <form method="POST" >
                                    <input type="hidden" name="form" value="res/acha">
                                    <input type="hidden" name="id_livre">
                                    <button type="submit" name="acheter" id="btn_achat">OK</button>
                                </form>
                                <br>
                            </div>
                        `;
                        document.body.appendChild(popup);

                        function closePopup() {
                            document.body.removeChild(popup);
                        }
                    });
                </script>';
            }
            else{
                $new_comment = $_POST['new_avis'];
                $idLivre = $_SESSION['idLivreActuel'];
                settype($_SESSION['id'], "integer");
                $new_note = (int)$_POST['note'];
                $idUtilAvis = $_SESSION['id'];
                $stmtInsertAvis->execute();
                echo '<script>
                    document.addEventListener("DOMContentLoaded", function() {
                        let popup = document.createElement("div");
                        popup.className = "popupGestion";
                        popup.innerHTML = `
                            <div class="popupGestion-content">
                                <h2>Confirmation de saisie.</h2>
                                <p>Votre commentaire a bien été saisi.</p>
                                <button onclick="closePopup()">OK</button>
                            </div>
                        `;
                        document.body.appendChild(popup);

                        function closePopup() {
                            popup.style.display = "none";
                        }
                    });
                </script>';
                header("Location: info_livre.php?id_livre={$_SESSION['idLivreActuel']}");
                exit;
            }
        }
        elseif(!isset($_POST['acheter']) && !isset($_POST['edition']) && isset($_POST['reserver'])){
            /*
                Au click sur le bouton réserver, on arrive sur une popup qui nous permet de choisir une édition.
            */

            $stmtSelectInfoCaracEdition = $conn->prepare("SELECT e.nom_edition, e.id_edition from edition e JOIN isbn i ON e.id_edition = i.id_edition
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
            // on parcours les différentes éditions de livres et on affiche un bouton pour chaque édition.
            foreach ($infoCaracEdition as $edition) {
                /*
                    On récupère le nombre d'exemplaires de l'édition en question qui sont actuellement réservés.
                */
                $stmtNbExemplaireReserves = $conn->prepare("SELECT count(*) as nb_ex from reserver r Join isbn i ON r.num_isbn = i.num_isbn WHERE i.id_edition = ? AND i.id_livre = ?");
                $stmtNbExemplaireReserves->bindParam(1, $edition['id_edition']);
                $stmtNbExemplaireReserves->bindParam(2, $_SESSION['idLivreActuel']);
                $stmtNbExemplaireReserves->execute();
                $nbExemplaireReserves = $stmtNbExemplaireReserves->fetch();
                $stmtNbExemplairesEditions = $conn->prepare("SELECT count(*) as nb_ex from exemplaire ex JOIN isbn i ON ex.num_isbn = i.num_isbn WHERE i.id_edition = ? AND i.id_livre = ? ");
                $stmtNbExemplairesEditions->bindParam(1, $edition['id_edition']);
                $stmtNbExemplairesEditions->bindParam(2, $_SESSION['idLivreActuel']);
                $stmtNbExemplairesEditions->execute();
                $nbExemplairesEditions = $stmtNbExemplairesEditions->fetch();

               
                
                /*
                    On teste si le nombre d'exemplaire de cette édition qui sont actuellement réservés est inférieur à 2 afin de prendre en compte la file d'attente. 
                */
                if($nbExemplaireReserves['nb_ex'] < 2 * $nbExemplairesEditions['nb_ex']){
                    echo '<form method="post" style="display:inline;">
                        <input type="hidden" name="form" value="edition">
                        <input type="hidden" name="id_livre" value="' . $_SESSION['idLivreActuel'] . '">
                        <input type="hidden" name="id" value="' . $_SESSION['id'] . '">
                        <input type="hidden" name="id_edition" value="' . $edition['id_edition'] . '">
                        <button onclick="popup(' . $edition['id_edition'] . ', 2)" type="submit" name = "edition" value="'.$edition['id_edition'].'">' . $edition['nom_edition'] . '</button>
                      </form>';
                }
                
            }

            echo '      </div>
                  </div>';

            echo '<script type="text/javascript">openEditionModal();</script>';
        }
        elseif(!isset($_POST['acheter']) && isset($_POST['edition'])){
                /*
                    On se place ici dans le cas où l'utilisateur a choisi une édition et a cliqué sur le bouton réserver.
                    On récupère l'édition choisie et on insère la réservation dans la base de donnée.*
                    On récupère également l'isbn d'un exemplaire non réservé, correspondant à l'édition choisie, dans la limite de deux réservations par exemplaire.
                */
                $selectedEdition = $_POST['edition'];
                $stmtSelectExemplaire = $conn->prepare("SELECT ex.num_isbn FROM exemplaire ex JOIN isbn i ON ex.num_isbn = i.num_isbn WHERE i.id_edition = ? AND i.id_livre = ? And ex.num_isbn NOT IN (SELECT num_isbn FROM reserver)");
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
            /* 
                Le cas où l'utilisateur a cliqué sur le bouton ajouter au panier,
                On insère l'achat de l'ebook dans la base de donnée.
            */
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

$stmtSelectLivre = $conn->prepare(
    "SELECT * from livre where id_livre = {$_SESSION['idLivreActuel']}"
);
$stmtSelectLivre->execute();
$infoLivre = $stmtSelectLivre->fetch();


/*
    On prépare ici les requètes qui permettront de tester la disponibilité du livre, le nombre d'exemplaires, le nombre de réservations et la disponibilité de l'ebook.
*/

$stmtTestDisponibilite = $conn->prepare("SELECT * FROM emprunter e Join exemplaire ex ON e.id_exemplaire = ex.id_exemplaire Join isbn i ON ex.num_isbn = i.num_isbn WHERE id_livre = {$_SESSION['idLivreActuel']}");
$stmtTestDisponibilite->execute();
$disponibilite = $stmtTestDisponibilite->fetchAll();
$stmtSelectNbExemplaires = $conn->prepare("SELECT count(*) as nb_exemplaires FROM exemplaire ex join isbn i on ex.num_isbn = i.num_isbn WHERE i.id_livre = {$_SESSION['idLivreActuel']}");
$stmtSelectNbExemplaires->execute();
$nbExemplaires = $stmtSelectNbExemplaires->fetch();
$stmtTestReservation = $conn->prepare("SELECT * FROM reserver r Join isbn i ON r.num_isbn = i.num_isbn WHERE id_livre = {$_SESSION['idLivreActuel']}");
$stmtTestReservation->execute();
$est_reserver = $stmtTestReservation->fetch();


/*
    On prépare les requètes qui permettront de récupérer les informations sur les auteurs, les avis, les caractéristiques du livre, l'édition, le genre, la langue, le public cible et la date de parution.
*/

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
            <p>
            <?php 
                $stmtSelectAuteur->execute();
                $auteurs = [];
                foreach($stmtSelectAuteur as $rows){
                    $auteurs[] = $rows['nom_auteur'].' '.$rows['prenom_auteur'];
                }
                /* On affiche ici les auteurs possibles du livre */
                echo implode(', ', $auteurs);

                        $stmtSelectAllAvis->execute();
                        $allAvis = $stmtSelectAllAvis->fetchAll();
                        $totalAvis = count($allAvis);
                        $sumAvis = 0;

                        /* Calcul de la somme et de la moyenne des avis */

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
                        echo '</div>';

                        /* on teste ici si il est possible de d'emprunter directement un exemplaire, sans file d'attente. */
                        if (($stmtTestDisponibilite->rowCount() < $nbExemplaires['nb_exemplaires']) && $nbExemplaires['nb_exemplaires']- $stmtTestDisponibilite->rowCount() > $stmtTestReservation->rowCount()){
                            echo '<p class="green"> Disponible en bibliothèque </p>';
                        } 
                        /* On se place ici dans le cas où tous les exemplaires sont soit empruntés soit réservés. C'est ici que l'on permet à l'utilsiateur d'entrer dans la file d'attente, le 3* illustre le fait que il est possible d'avoir au maximum 3 fois la quantité de livres dans la base de donnée */
                        elseif(($stmtTestDisponibilite->rowCount() <= 3*$nbExemplaires['nb_exemplaires']) && (3*$nbExemplaires['nb_exemplaires']- $stmtTestDisponibilite->rowCount() > $stmtTestReservation->rowCount())){
                            $stmtSelectDateFinEmprunt = $conn->prepare("SELECT date_fin_emprunt FROM emprunter e Join exemplaire ex ON e.id_exemplaire = ex.id_exemplaire JOIN isbn i ON ex.num_isbn = i.num_isbn Join livre l ON i.id_livre = l.id_livre WHERE l.id_livre = {$_SESSION['idLivreActuel']}");
                            $stmtSelectDateFinEmprunt->execute();
                            $dateFinEmprunt = $stmtSelectDateFinEmprunt->fetch();
                            /* Si aucun livre n'est réservé il faut prendre la première date de fin d'emprunt*/
                            if ($dateFinEmprunt){
                                echo '<p> Disponible en réservation au maximum le </p>'.$dateFinEmprunt['date_fin_emprunt'];
                            }
                            else{
                                $stmtSelectDateReservation = $conn->prepare("SELECT date_reservation FROM reserver r Join isbn i ON r.num_isbn = i.num_isbn Join livre l ON i.id_livre = l.id_livre WHERE l.id_livre = {$_SESSION['idLivreActuel']} ORDER BY date_reservation DESC LIMIT 1");
                                $stmtSelectDateReservation->execute();
                                $dateReservation = $stmtSelectDateReservation->fetch();

                                if ($dateReservation) {
                                    $dateFinReservation = date('d-m-Y', strtotime($dateReservation['date_reservation'] . ' + 18 days'));
                                    echo '<p> Disponible en réservation au maximum le ' . $dateFinReservation . '</p>';
                                } 
                            }
                        }
                        else{
                            echo '<p> Indisponible pour le moment </p>';
                        }

                        $stmtSelectEbook = $conn->prepare("SELECT * FROM ebook WHERE id_livre = {$_SESSION['idLivreActuel']}");
                        $stmtSelectEbook->execute();
                        $infoEbook = $stmtSelectEbook->fetch();
                        // On teste si l'ebook est disponible
                        if (!empty($infoEbook)){
                            $_SESSION['idEbook'] = $infoEbook['id_ebook'];
                            echo '<p> E-BOOK | '.$infoEbook['prix'].' €';
                            if (isset($_SESSION['id'])){
                                // On récupère le fait que l'utilisateur a potentiellement déjà acheté l'ebook
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
                        if (!empty($infoEbook)) {
                            $isAdmin = isset($_SESSION['admin']) && $_SESSION['admin'] === 1;
                            $hasBoughtEbook = !empty($testEbook);
                            $canBuy = !$isAdmin && !$hasBoughtEbook;

                            echo '<button onclick="popup(' . $_SESSION['idLivreActuel'] . ', 1)" type="submit" name="acheter" id="btn_achat"' . ($canBuy ? '' : ' disabled') . '>Ajouter au panier</button>';
                        }
                        
                        ?>
                        
                        <button type="submit" name="reserver" id="btn_res" <?php 
                            // On teste si l'utilisateur est connecté
                            if (isset($_SESSION['id'])){
                                // Cette requete permet de récupérer les isbn des exemplaires correspondants à l'utilisateur
                                $stmtSelectIsbn = $conn->prepare("SELECT e.num_isbn FROM exemplaire e JOIN isbn i ON e.num_isbn = i.num_isbn WHERE i.id_livre = {$_SESSION['idLivreActuel']}");
                                $stmtSelectIsbn->execute();
                                $isbn = $stmtSelectIsbn->fetchAll();
                                // On récupère le nombre d'emprunts et de réservations de l'utilisateur
                                $stmtNbEmprunts = $conn->prepare("SELECT count(*) as nb_emp FROM emprunter WHERE id_util = ?");
                                $stmtNbEmprunts->bindParam(1, $_SESSION['id']);
                                $stmtNbEmprunts->execute();
                                $nbEmprunts = $stmtNbEmprunts->fetch();
                                // On cast le nombre d'emprunts de l'utilisateur en entier
                                $nbEmprunts['nb_emp'] = (int)$nbEmprunts['nb_emp'];
                                $stmtNbReservations = $conn->prepare("SELECT count(*) as nb_res FROM reserver WHERE id_util = ?");
                                $stmtNbReservations->bindParam(1, $_SESSION['id']);
                                $stmtNbReservations->execute();
                                $nbReservations = $stmtNbReservations->fetch();
                                // On cast le nombre de réservations de l'utilisateur en entier
                                $nbReservations['nb_res'] = (int)$nbReservations['nb_res'];
                                $nbLivresTota = $nbReservations['nb_res'] + $nbEmprunts['nb_emp'];
                                // On recupère les exemplaires réservés par l'utilisateur
                                $stmtAiReserver = $conn->prepare("SELECT * FROM reserver WHERE id_util = ?");
                                $stmtAiReserver->bindParam(1, $_SESSION['id']);
                                $stmtAiReserver->execute();
                                $reservations = $stmtAiReserver->fetchAll();
                                
                                $testReserver = false;
                                /*
                                    On parcourt toutes les réservations de l'utilsaiteur et on test si il a déjà réservé le livre en question.
                                */
                                foreach($reservations as $rows){
                                    foreach($isbn as $row){
                                        if($rows['num_isbn'] === $row['num_isbn']){
                                            $testReserver = true;
                                        }
                                    }
                                }
                                // On teste si l'utilisateur a réserver un exemplaire du livre en question
                                if ($testReserver){
                                    echo 'disabled';
                                }
                                // On teste si l'utilisateur est un admin
                                elseif(isset($_SESSION['admin']) && $_SESSION['admin'] === 1){
                                    echo 'disabled';
                                }
                                // On teste si l'utilisateur a déjà emprunté ou réservé 6 livres et que le livre est disponible en emprunt ou en réservation selon la liste d'attente
                                elseif ((3*$nbExemplaires['nb_exemplaires']- $stmtTestDisponibilite->rowCount()  >= $stmtTestReservation->rowCount()) && $nbLivresTota < 6){ 
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
    <section id ="res_carac">
        <div id="Resume">
            <input type="checkbox" id="btnRe" hidden/>
            <label for="btnRe"><h2>Résumé</h2>
            <p><?php echo $infoLivre['resume']?></p></label>
        </div>    
        <div id="Caracteristisques">
        <input type="checkbox" id="btnCa" hidden/>
        <label for="btnCa"><h2>Caractéristiques</h2>
            <?php


                echo '<p> Date de parution..........................'.$infoCaracDate['date_parution'].'</p>';              
                echo '<p> Cote..............................................'.$infoLivre['cote_livre'].'</p>';
                echo '<p> Genre...........................................';
                // On affiche les genres du livre
                foreach($infoCaracGenre as $rows){
                    echo $rows['nom_genre']." ";
                }
                echo '</p>';
                echo '<p> Langue.........................................';
                // On affiche les langues du livre
                foreach($infoCaracLangue as $rows){
                    echo $rows['nom_langue']." ";
                }
                echo '</p>';
                echo '<p> Edition.........................................';
                // On affiche les éditions du livre
                foreach($infoCaracEdition2 as $rows){
                    echo $rows['nom_edition']." ";
                }
                echo '</p>';
                echo '<p> Type littéraire...............................'.$infoLivre['type_litteraire'].'</p>';
                echo '<p> Public cible..................................';
                // On affiche le ou les public(s) cible(s) du livre
                foreach($infoCaracPublic as $rows){
                    echo $rows['type_public']." ";
                }
                echo '</p>';
            ?></label>
        </div> 
        <div class="affichage_avis">
        <input type="checkbox" id="btnAv" hidden/>
        <label for="btnAv"><h2> Avis : </h2>

            <form  method="post">
                <input type="hidden" name="form" value="avis">
                <h3>Laisser un avis :</h3>
                <div class="note" required>
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
        
            <?php
                $stmtSelectAllAvis->execute();
                // on parcours tous les avis et on affiche les informations de l'utilisateur qui a laissé l'avis
                foreach ($stmtSelectAllAvis as $rows){
                    $_SESSION['idUtilAvis'] = $rows['id_util'];
                    $sql = "SELECT * from utilisateur where id_util = {$_SESSION['idUtilAvis']}";
                    $result = $conn->query($sql);
                    $row2 = $result->fetch();
                    $pseudo_util = $row2['pseudo'];
                    echo '<div class="affichage_avis">';
                    echo '<div><img src= "'.$row2['img_profil'].'" alt="Image de profil"></div>';
                    echo '<div class="description_utilisateur"> <p>' . $pseudo_util . '</p><p>' . $rows['date_avis']. '</p> </div>';
                    echo '<div class="star-rating">';
                    // on affiche les étoiles correspondant à la note de l'avis
                    for ($i = 1; $i <= 5; $i++) {
                        if ($i <= $rows['note_avis']) {
                            echo '<span class="star filled">★</span>';
                        } 
                    }
                    echo '</div>';
                    echo "<div><p>" . $rows['comment_avis']."</p></div>";
                    echo '</div>';
                }
            ?></label>
        </div>
    </section>

       
<script>

    // Cette fonction est appellée lorsqu'on clique sur le bouton réserver ou ajouter au panier, elle permet de créer une popup.
    // cette popup empêche le formulaire de se soumettre et permet de confirmer à l'utilisateur l'ajout au panier ou la réservation.
    function popup(id_livre, test) {
        event.preventDefault();
        let popup = document.createElement('div');
        popup.className = 'popupGestion hidden';
        // si la variable test vaut 1 alors on affiche un message de confirmation d'ajout au panier, sinon on affiche un message de confirmation de réservation.
        if(test == 1){
            popup.innerHTML = `
            <div class="popupGestion-content">
                <h2>Confirmation d'ajout au panier</h2>
                <p>Le livre a bien été ajouter au panier.</p>
                <form method="POST" >
                    <input type="hidden" name="form" value="res/acha">
                    <input type="hidden" name="id_livre">
                    <button type="submit" name="acheter" id="btn_achat">OK</button>
                </form>
                <br>
            </div>
        `;
        }
        else{
            popup.innerHTML = `
            <div class="popupGestion-content">
                <h2>Confirmation de réservation</h2>
                <p>Le livre a bien été réservé</p>
                <form method="POST">
                    <input type="hidden" name="form" value="edition" >
                    <input type="hidden" name="id_livre">
                    <button type="submit" name="edition" id="btn_res" value="${id_livre}">OK</button>
                </form>
                <br>
            </div>
        `;
        }
        const main = document.querySelector('main');
        main.appendChild(popup);
    }
</script>    


<?php
    require_once 'footer.php';
?>