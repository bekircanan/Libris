<?php
    require_once 'header.php';
    
    try {
        $conn = new PDO("mysql:host=localhost;dbname=libris", 'root', '');
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    } catch (PDOException $e) {
        die($e->getMessage());
    }
    //:recherche

        //requette sql pour la barre de recherche
        $stmtRecherche = $conn->prepare("SELECT DISTINCT l.titre_livre, a.prenom_auteur, a.nom_auteur,  eb.prix
                                FROM livre l JOIN ebook eb ON l.id_livre = eb.id_ebook
                                            JOIN a_ecrit ae ON l.id_livre = ae.id_auteur
                                            JOIN auteur a ON ae.id_auteur = a.id_auteur
                                            JOIN livre_genre lg ON lg.id_livre = l.id_livre
                                            JOIN genre g ON lg.id_genre = g.id_genre    
                                            JOIN livre_edition le ON l.id_livre = le.id_livre
                                            JOIN edition ed ON ed.id_edition = le.id_edition
                                            JOIN livre_langue ll ON ll.id_livre = l.id_livre
                                            JOIN langue lang ON lang.id_langue = ll.id_langue
                                            JOIN livre_public lp ON lp.id_livre = l.id_livre
                                            JOIN public_cible pc ON pc.id_public = lp.id_public
                                WHERE :recherche LIKE pc.type_public
                                OR :recherche LIKE g.nom_genre
                                OR :recherche LIKE l.titre_livre
                                OR :recherche LIKE l.type_litteraire
                                OR :recherche LIKE lang.nom_langue
                                OR :recherche LIKE ed.nom_edition
                                OR :recherche LIKE a.nom_auteur"); 
        
        $stmtRecherche->execute([':recherche' => 'Roman']);
        $infoLivres = $stmtRecherche->fetchAll();

        $resultGenre = $conn->query("SELECT nom_genre FROM genre");

        //if($_SERVER['REQUEST_METHOD'] === 'POST'){
        

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
        <label>Trier par :</label><form></form>
    </section>
</div>
<?php
    require_once 'footer.php';
?>