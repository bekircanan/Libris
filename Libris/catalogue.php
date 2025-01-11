<?php
    require_once 'header.php';   

    $order = 'l.titre_livre ASC'; // Ordre par défaut (A-Z)
    $ListeConditions = [];
    $ListeParametres = [];

    if ($_SERVER['REQUEST_METHOD'] == 'POST'){
        // vérifie quel trie est choisie
        if ($_POST['form']=== 'tri' || $_POST['form'] === 'filtre'){
            if (isset($_POST['tri'])) {
                if ($_POST['tri'] == 'z-a') {
                    $order = 'l.titre_livre DESC'; // Z-A
                } elseif ($_POST['tri'] == 'prix-croissant') {
                    $order = 'eb.prix ASC'; // Prix croissant
                } elseif ($_POST['tri'] == 'prix-decroissant') {
                    $order = 'eb.prix DESC'; // Prix décroissant
                } 
            }
            // Filtre par genres
            if (isset($_POST['genres']) && is_array($_POST['genres'])) {
                $ListeParametresVides = implode(',', array_fill(0, count($_POST['genres']), '?'));
                $ListeConditions[] = "g.nom_genre IN ($ListeParametresVides)";
                $ListeParametres = array_merge($ListeParametres, $_POST['genres']);
            }
            // Filtre par E-book
            if (isset($_POST['ebook']) && $_POST['ebook'] == '1') {
                $ListeConditions[] = "eb.id_livre IS NOT NULL";
            }
            // Filtre par prix
            if (isset($_POST['price']) && is_numeric($_POST['price'])) {
                $ListeConditions[] = "eb.prix <= ?";
                $params[] = $_POST['price'];
            } 
        }
               
    }

    // Combinez les conditions dans la requête SQL
    $whereClause = '';
    if (!empty($ListeConditions)) {
        $whereClause = 'WHERE ' . implode(' AND ', $ListeConditions);
    }
    else{
        if($_SERVER['REQUEST_METHOD'] == 'GET' && isset($_GET['recherche'])){
            $whereClause = 'WHERE pc.type_public LIKE "%' .$_GET['recherche']. '%"
                                OR g.nom_genre LIKE "%' .$_GET['recherche']. '%" 
                                OR l.titre_livre LIKE "%' .$_GET['recherche']. '%"  
                                OR l.type_litteraire LIKE "%' .$_GET['recherche']. '%" 
                                OR lang.nom_langue LIKE "%' .$_GET['recherche']. '%" 
                                OR ed.nom_edition LIKE "%' .$_GET['recherche']. '%" 
                                OR a.nom_auteur LIKE "%' .$_GET['recherche']. '%"
                                OR l.cote_livre LIKE "%' .$_GET['recherche']. '%"';
        }else{
            $whereClause = '';
        }
    }
                                    
    $stmtRecherche = $conn->prepare("SELECT DISTINCT l.id_livre, l.titre_livre, eb.prix, l.img_couverture
                                        FROM livre l LEFT OUTER JOIN ebook eb ON l.id_livre = eb.id_livre
                                            LEFT OUTER JOIN a_ecrit ae ON l.id_livre = ae.id_livre
                                            LEFT OUTER JOIN auteur a ON ae.id_auteur = a.id_auteur
                                            LEFT OUTER JOIN livre_genre lg ON lg.id_livre = l.id_livre
                                            LEFT OUTER JOIN genre g ON lg.id_genre = g.id_genre    
                                            LEFT OUTER JOIN livre_public lp ON lp.id_livre = l.id_livre
                                            LEFT OUTER JOIN public_cible pc ON pc.id_public = lp.id_public
                                            LEFT OUTER JOIN isbn i ON i.id_livre = l.id_livre
                                            LEFT OUTER JOIN langue lang ON i.id_langue = lang.id_langue
                                            LEFT OUTER JOIN edition ed ON ed.id_edition = ed.id_edition
                                        $whereClause
                                        ORDER BY $order"); 
    $stmtRecherche->execute($ListeParametres);
    $livreRecherche = $stmtRecherche->fetchAll();

    $stmtNbAvis = $conn->prepare("SELECT COUNT(id_avis) as avis
                                    FROM avis
                                    WHERE id_livre = :id_livre");
                                                    
    $stmtAuteur = $conn->prepare("SELECT DISTINCT a.nom_auteur, a.prenom_auteur 
                                    FROM auteur a LEFT OUTER JOIN a_ecrit ae ON a.id_auteur = ae.id_auteur
                                    WHERE id_livre = :id_livre");

    $resultGenre = $conn->query("SELECT nom_genre FROM genre");
?>

<div id="page_catalogue">
    <section class="filters">
        <h1>Filtre</h1>
        <hr>
        <form id="filters-form" method="POST">
            <input type="hidden" name="form" value="filtre">
            <section class="info-ebook>
                <h3>E-book</h3>
                <input type="checkbox" id="ebook" name="ebook" value="1">
                <label for="ebook">E-book</label>
                <label for="price">Prix :</label>
                <input type="range" name="prix" id="price" min="0" max="50" value="25">
            </section>
            <section>
                <h3>Genre de livres</h3>
                <ul>
                    <?php
                        foreach ($resultGenre as $genre){
                            echo '<li><input type="checkbox" name="genres[]" value="' . $genre['nom_genre'] . '"><label for="' . $genre['nom_genre'] . '">' . $genre['nom_genre'] . '</label></li>';
                        }
                    ?>
                </ul>
            </section>
            <!-- <section>
                <h3>Note</h3>
                <input type="checkbox" id="plus3etoiles" name="note">
                <label for="plus3etoiles">Plus de 3 étoiles</label>
                <input type="checkbox" id="moins3etoiles" name="note">
                <label for="moins3etoiles">Moins de 3 étoiles</label>
            </section> -->
            <!-- <section>
                <label for="parution">Date de parution :</label>
                <input type="range" id="parution" min="0" max="50" value="10">
            </section> -->
            <button type="submit">Appliquer les filtres</button>
        </form>
    </section>
    <section id="catalogue">
         
        <form method="POST">
            <input type="hidden" name="form" value="tri">
            <label for="tri-select">Trier par :</label>
            <select name="tri" id="tri-select" onchange="this.form.submit()">
                <option value="a-z" <?php 
                                        if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['tri'])){
                                            if ($_POST['tri'] == 'a-z') echo 'selected';
                                        }
                                    ?>> A-Z </option>
                <option value="z-a" <?php 
                                        if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['tri'])){
                                            if ($_POST['tri'] == 'z-a') echo 'selected';
                                        }
                                    ?>> Z-A </option>
                <option value="prix-croissant" <?php 
                                                    if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['tri'])){
                                                        if ($_POST['tri'] == 'prix-croissant') echo 'selected';
                                                    }
                                                ?>> Prix croissant </option>
                <option value="prix-decroissant" <?php 
                                                    if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['tri'])){
                                                        if ($_POST['tri'] == 'prix-decroissant') echo 'selected';
                                                    }
                                                ?>> Prix décroissant </option>
            </select>
        </form>

        <div class="liste-ebook">
            <?php
                foreach($livreRecherche as $livre){
                    $lesAuteurs = '';
                    echo '<div class="book-item"><a href="./info_livre.php?id_livre=' . $livre['id_livre']. '">';
                    echo '<img src="' . $livre['img_couverture'] . '" >';
                    echo '<h3>' . $livre['titre_livre'] . '</h3>';
                    
                    $stmtAuteur->execute([':id_livre' => $livre['id_livre']]);
                    $Auteurs = $stmtAuteur->fetchAll();
                    foreach($Auteurs as $aut){
                        $lesAuteurs = $lesAuteurs . $aut['prenom_auteur'] . " " . $aut['nom_auteur'];
                    }
                    echo '<p>' . $lesAuteurs . '</p>';

                    if($livre['prix'] != null){
                        echo '<p><span class="price"> Prix : ' . $livre['prix'] . '</span></p>';
                    }

                    $stmtNbAvis->execute([':id_livre' => $livre['id_livre']]);
                    $NbAvis = $stmtNbAvis->fetch();
                    echo '<p> avis : ' . $NbAvis['avis'] . '</p>';
                    
                    echo '</a></div>';
                }
            ?>
        </div>
    </section>
</div>

<script>
    function filtre_recherche(requestData){
            fetch("catalogue.php", {
            method: "POST",
            headers: {
                "Content-Type": "application/json"
            },
            body: JSON.stringify(requestData)
            })
            .then(response => response.json())
            .then(data => {
            if (data.success) {
                button.textContent = action === "add" ? "remove" : "add";
            } else {
                console.error(data.message);
            }
            })
            .catch(error => {
            console.error("Error:", error);
            });
            window.location.reload();
        }


</script>
<?php
    require_once 'footer.php';
?>