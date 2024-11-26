<?php
    require_once 'header.php';

    // if($_SERVER['REQUEST_METHOD'] == 'GET'){
    //     $infoRecherche = $_GET['recherche'];
    // }    
    
    // if ($_SERVER["REQUEST_METHOD"] === "POST") {
    //     if ($_POST['form']=== 'trie'){
    //         $valueTri = $_POST["tri"];
    //         if ($valueTri === "z-a") {
    //             $_SESSION['trie'] = 'DESC';
                
    //         } elseif ($valueTri === "a-z"){
    //             echo 'test';
    //             unset($_SESSION['trie']);
                
    //         }
    //     }
        
    // }

    $stmtRecherche = $conn->prepare("SELECT DISTINCT l.id_livre, l.titre_livre, eb.prix
                                    FROM livre l LEFT OUTER JOIN ebook eb ON l.id_livre = eb.id_livre
                                        LEFT OUTER JOIN a_ecrit ae ON l.id_livre = ae.id_livre
                                        LEFT OUTER JOIN auteur a ON ae.id_auteur = a.id_auteur
                                        LEFT OUTER JOIN livre_genre lg ON lg.id_livre = l.id_livre
                                        LEFT OUTER JOIN genre g ON lg.id_genre = g.id_genre    
                                        LEFT OUTER JOIN livre_edition le ON l.id_livre = le.id_livre
                                        LEFT OUTER JOIN edition ed ON ed.id_edition = le.id_edition
                                        LEFT OUTER JOIN livre_langue ll ON ll.id_livre = l.id_livre
                                        LEFT OUTER JOIN langue lang ON lang.id_langue = ll.id_langue
                                        LEFT OUTER JOIN livre_public lp ON lp.id_livre = l.id_livre
                                        LEFT OUTER JOIN public_cible pc ON pc.id_public = lp.id_public
                                    WHERE :recherche LIKE pc.type_public
                                        OR :recherche LIKE g.nom_genre
                                        OR :recherche LIKE l.titre_livre
                                        OR :recherche LIKE l.type_litteraire
                                        OR :recherche LIKE lang.nom_langue
                                        OR :recherche LIKE ed.nom_edition
                                        OR :recherche LIKE a.nom_auteur
                                        OR :recherche LIKE l.cote_livre
                                    ORDER BY l.titre_livre "); 

    $stmtRecherche->execute([':recherche' => 'roman']);//$infoRecherche]);
    $livreRecherche = $stmtRecherche->fetchAll();

    $stmtNbAvis = $conn->prepare("SELECT COUNT(id_avis) as avis
                                    FROM avis
                                    WHERE id_livre = :id_livre ");
                                                    
    $stmtAuteur = $conn->prepare("SELECT DISTINCT a.nom_auteur, a.prenom_auteur 
                                    FROM auteur a LEFT OUTER JOIN a_ecrit ae ON a.id_auteur = ae.id_auteur
                                    WHERE id_livre = :id_livre ");
    
    // $index = 0;
    // foreach($livreRecherche as $livre){
    //     $listeIdLivre[]
    //     $index = $index + 1
    // }

    $resultGenre = $conn->query("SELECT nom_genre FROM genre");

        

?>

<div id="page_catalogue">
    <section class="filters">
        <h1>Filtre</h1>
        <hr>
        <section>
            <h3>E-book</h3>
            <input type="checkbox" id="ebook" name="ebook">
            <label for="ebook">E-book</label>
            <label for="price">Prix :</label>
            <input type="range" id="price" min="0" max="50" value="25">
        </section>
        <section>
            <h3>Genre de livres</h3>
            <ul>
                <?php
                    foreach ($resultGenre as $genre){
                        echo '<li><input type="checkbox" id="' . $genre['nom_genre'] . '"><label for="' . $genre['nom_genre'] . '">' . $genre['nom_genre'] . '</label></li>';
                    }
                ?>
            </ul>
        </section>
        <section>
            <h3>Note</h3>
            <input type="checkbox" id="plus3etoiles" name="note">
            <label for="plus3etoiles">Plus de 3 étoiles</label>
            <input type="checkbox" id="moins3etoiles" name="note">
            <label for="moins3etoiles">Moins de 3 étoiles</label>
        </section>
        <section>
            <label for="parution">Date de parution :</label>
            <input type="range" id="parution" min="0" max="50" value="10">
        </section>
    </section>
    <section id="catalogue">
        <?php echo 'la' . $_SESSION['trie']; ?>
        <form method="POST" action="">
            <input type="hidden" name="form" value="trie">
            <label for="tri-select">Trier par :</label>
            <select name="tri" id="tri-select" onchange="this.form.submit()">
                <option value="a-z">A-Z</option>
                <option value="z-a">Z-A</option>
            </select>
        </form>
        <div class="liste-ebook">
            <?php
                foreach($livreRecherche as $livre){
                    $lesAuteurs = '';
                    echo '<div class="book-item"><a href="./info_livre.php?id_livre=' . $livre['id_livre']. '">';
                    echo '<h3>' . $livre['titre_livre'] . '</h3>';
                    
                    $stmtAuteur->execute([':id_livre' => $livre['id_livre']]);
                    $Auteurs = $stmtAuteur->fetchAll();
                    foreach($Auteurs as $aut){
                        $lesAuteurs = $lesAuteurs . $aut['prenom_auteur'] . " " . $aut['nom_auteur'];
                    }
                    echo '<p>' . $lesAuteurs     . '</p>';

                    if($livre['prix'] != null){
                        echo '<p><span class="price"> Prix : ' . $livre['prix'] . '</span></p>';
                    }

                    $stmtNbAvis->execute([':id_livre' => $livre['id_livre']]);
                    $NbAvis = $stmtNbAvis->fetchAll();
                    foreach($NbAvis as $avis){
                        echo '<p> avis : ' . $avis['avis'] . '</p>';
                    }
                    
                    echo '</a></div>';
                }
            ?>
        </div>
    </section>
</div>
<?php
    require_once 'footer.php';
?>