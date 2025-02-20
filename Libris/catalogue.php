<?php
    require_once 'header.php';  

    // initialisation des variables
    $order = 'l.titre_livre ASC'; // Ordre par défaut (A-Z)
    $whereClause = '';
    $ListeConditions = [];
    $ListeConditionsRechercheAvance = [];
    $ListeParametres = [];
    $Argument = 0;
    
    // Vérifie si une requête POST a été envoyée
    if ($_SERVER['REQUEST_METHOD'] == 'POST'){
        // vérifie quel trie est choisie
        if ($_POST['form']=== 'tri' || $_POST['form'] === 'filtre'){
            if (isset($_POST['tri'])) {
                // Vérifie si une recherche avancée a été effectuée
                if($_POST['listcond'] === 1){
                    $ListeParametres = explode(';',$_POST['listePram']);
                    $Argument = 1;
                }else{
                    $ListeParametres = [];
                    $Argument = 0;
                }
                
                $whereClause = $_POST['whereCause'];
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
            // Filtre par langues
            if(isset($_POST['langues']) && is_array($_POST['langues'])) {
                $ListeParametresVides = implode(',', array_fill(0, count($_POST['langues']), '?'));
                $ListeConditions[] = "lang.nom_langue IN ($ListeParametresVides)";
                $ListeParametres = array_merge($ListeParametres, $_POST['langues']);
            }
            // Filtre par public
            if(isset($_POST['public']) && is_array($_POST['public'])) {
                $ListeParametresVides = implode(',', array_fill(0, count($_POST['public']), '?'));
                $ListeConditions[] = "pc.type_public IN ($ListeParametresVides)";
                $ListeParametres = array_merge($ListeParametres, $_POST['public']);
            }
            // Filtre par E-book
            if (isset($_POST['ebook'])) {
                $ListeConditions[] = "eb.id_livre IS NOT NULL";
            }
            // Filtre par prix
            if (isset($_POST['prix']) && isset($_POST['prix-min']) && isset($_POST['prix-max'])){
                $ListeConditions[] = "eb.prix BETWEEN ? AND ?";
                array_push($ListeParametres, $_POST['prix-min'], $_POST['prix-max']);
                
            }
        }
            
    }

    // Vérifie si une requête GET a été envoyée
    if($_SERVER['REQUEST_METHOD'] == 'GET'){
            // Filtre par genres
            if(isset($_GET['genres']) && is_array($_GET['genres'])) {
                $ListeParametresVides = implode(',', array_fill(0, count($_GET['genres']), '?'));
                $ListeConditions[] = "g.nom_genre IN ($ListeParametresVides)";
                $ListeParametres = array_merge($ListeParametres, $_GET['genres']);
            }
            // Filtre par langues
            if(isset($_GET['langues']) && is_array($_GET['langues'])) {
                $ListeParametresVides = implode(',', array_fill(0, count($_GET['langues']), '?'));
                $ListeConditions[] = "lang.nom_langue IN ($ListeParametresVides)";
                $ListeParametres = array_merge($ListeParametres, $_GET['langues']);
            }
            // Filtre par public
            if(isset($_GET['public']) && is_array($_GET['public'])) {
                $ListeParametresVides = implode(',', array_fill(0, count($_GET['public']), '?'));
                $ListeConditions[] = "pc.type_public IN ($ListeParametresVides)";
                $ListeParametres = array_merge($ListeParametres, $_GET['public']);
            } 
            // Filtre par E-book
            if (isset($_GET['ebook'])) {
                $ListeConditions[] = "eb.id_livre IS NOT NULL";
            }
            // Filtre par prix
            if (isset($_GET['prix']) && isset($_GET['prix-min']) && isset($_GET['prix-max'])){
                $ListeConditions[] = "eb.prix BETWEEN ? AND ?";
                array_push($ListeParametres, $_GET['prix-min'], $_GET['prix-max']);
                
            }
            
            // Filtre par annee
            if (isset($_GET['anneeDebut']) && isset($_GET['anneeFin'])) {
                if (!empty($_GET['anneeDebut']) && !empty($_GET['anneeFin'])){
                    $ListeConditions[] = "ae.date_parution BETWEEN ? AND ?";
                    array_push($ListeParametres, $_GET['anneeDebut'], $_GET['anneeFin']);
                }
            } 
            // Filtre par recherche-avance
            if (isset($_GET['recherche-avance'])){
                if($_GET['recherche-avance'][0] !== ''){
                    $conditionCritaire = ''; 
                    $conditions = '';
                    // Boucle pour chaque critaire de recherche avancée
                    for($i = 0; $i < sizeof($_GET['recherche-avance']); $i++){
                        switch(strtolower($_GET['critaireValue'][$i])){
                            case "titre":
                                $conditionCritaire = "l.titre_livre";
                                break;
                            case "auteur":
                                $conditionCritaire = "a.nom_auteur";
                                break;
                            case "edition":
                                $conditionCritaire = "ed.nom_edition";
                                break;
                            case "isbn":
                                $conditionCritaire = "i.num_isbn";
                                break;
                            case "sujet":
                                $conditionCritaire = "l.resume";
                                break;
                            default:
                                break;
                        }
                        // Vérifie si le critaire est différent de la première valeur
                        if ($i != 0 OR strtolower($_GET['critaireOption'][0]) === "sauf"){
                            switch(strtolower($_GET['critaireOption'][$i])){
                                case "et":
                                    $conditions = "AND" .' '. $conditionCritaire . " LIKE '%".$_GET['recherche-avance'][$i]."%'";
                                    break;
                                case "ou":
                                    $conditions = "OR" .' '. $conditionCritaire . " LIKE '%".$_GET['recherche-avance'][$i]."%'";
                                    break;
                                case "sauf":
                                    if($i===0){
                                        $conditions = $conditionCritaire . " NOT LIKE '%".$_GET['recherche-avance'][$i]."%'";
                                    }else{
                                        $conditions = ' AND ' . $conditionCritaire . " NOT LIKE '%".$_GET['recherche-avance'][$i]."%'";

                                    }
                                    break;

                                default:
                                    break;
                            }
                        }else{
                            $conditions = $conditionCritaire . " LIKE '%".$_GET['recherche-avance'][$i]."%'";
                        }
                        array_push($ListeConditionsRechercheAvance, $conditions);
                    }
                
                }
                
            }
        } 
    if ($whereClause === ''){       
        // Vérifie si une condition de recherche avancée a été effectuée                                                                                                          
        if (!empty($ListeConditions) && empty($ListeConditionsRechercheAvance)) {
            $whereClause = 'WHERE ' . implode(' OR ', $ListeConditions);
        }else if(empty($ListeConditions) && !empty($ListeConditionsRechercheAvance)){
            $whereClause = 'WHERE ' . implode(' ', $ListeConditionsRechercheAvance);
        }else if(!empty($ListeConditions) && !empty($ListeConditionsRechercheAvance)){
            $whereClause = 'WHERE ' . implode(' ', $ListeConditionsRechercheAvance);
            $whereClause = $whereClause . ' OR ' . implode(' OR ', $ListeConditions);
        }else{
            if($_SERVER['REQUEST_METHOD'] == 'GET'){
                if(isset($_GET['recherche'])){
                    $whereClause = 'WHERE pc.type_public LIKE "%' .$_GET['recherche']. '%"
                                        OR g.nom_genre LIKE "%' .$_GET['recherche']. '%" 
                                        OR l.titre_livre LIKE "%' .$_GET['recherche']. '%"  
                                        OR l.type_litteraire LIKE "%' .$_GET['recherche']. '%" 
                                        OR lang.nom_langue LIKE "%' .$_GET['recherche']. '%" 
                                        OR ed.nom_edition LIKE "%' .$_GET['recherche']. '%" 
                                        OR a.nom_auteur LIKE "%' .$_GET['recherche']. '%"
                                        OR l.cote_livre LIKE "%' .$_GET['recherche']. '%"';
                    $ListeParametres = [];
                }else{
                    $whereClause = '';
                    $ListeParametres = [];
                } 
            }
        }
    }
    
    if(empty($whereClause)){
        $ListeParametres = [];
    }
    if(empty($ListeParametres) && $Argument){
        $whereClause = '';
    }  
     
    // Requête pour récupérer les livres
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

    // Requête pour récupérer les avis
    $stmtSelectAllAvis = $conn->prepare("SELECT * 
                                         FROM avis
                                         WHERE id_livre = :id_livre");
                                                    
    // Requête pour récupérer les auteurs
    $stmtAuteur = $conn->prepare("SELECT DISTINCT a.nom_auteur, a.prenom_auteur 
                                    FROM auteur a LEFT OUTER JOIN a_ecrit ae ON a.id_auteur = ae.id_auteur
                                    WHERE id_livre = :id_livre");

    $resultGenre = $conn->query("SELECT nom_genre FROM genre");
?>

<div id="page_catalogue">
    <section class="filters">
    <input type="checkbox" id="btnfil" hidden/><label for="btnfil">
        <h1>Filtre</h1>
        <hr>
        <form id="filters-form" method="POST">
            <input type="hidden" name="form" value="filtre">
            <section class="info-ebook">
                <h3>E-book</h3> 
                <ul>
                    <div class="groupe-checkbox">
                        <input type="checkbox" id="ebook" name="ebook">
                        <label for="ebook">E-book</label>
                    </div>
                    <div class="groupe-checkbox">
                        <input type="checkbox" name="prix"> 
                        
                    </div>
                    <label for="prix-min">Prix min <span id="valeurMin">0</span>:</label>
                    <input id="prix-min" name="prix-min" type="range" value="0" min="0" max="25"/>
                    <br>
                    <label for="prix-max">Prix max <span id="valeurMax">50</span>:</label>
                    <input id="prix-max" name="prix-max" type="range" value="50" min="25" max="50"/>
                </ul>
            </section>
            <section>
                <h3>Genre de livres</h3>
                <ul>
                    <?php
                        // Afficher les genres
                        foreach ($resultGenre as $genre){
                            echo '<div class="groupe-checkbox"><input type="checkbox" name="genres[]" value="' . $genre['nom_genre'] . '"><label for="' . $genre['nom_genre'] . '">' . $genre['nom_genre'] . '</label></div>';
                        }
                    ?>
                </ul>
            </section>
            <button type="submit">Appliquer les filtres</button>
        </form>
    </label>
    </section>
    
    <section id="catalogue">
         
        <form method="POST">
            <input type="hidden" name="form" value="tri">
            <input type="hidden" name="whereCause" value="<?php echo empty($whereClause) ? "" : $whereClause ?>"></label>
            <input type="hidden" name="listcond" value="<?php echo empty($ListeConditionsRechercheAvance) ? 1 : 0 ?>"></label>
            <input type="hidden" name="listePram" value=
            <?php 
                // Boucle pour chaque paramètre de recherche avancée
                $value = ""; 
                for($i = 0; $i < sizeof($ListeParametres); $i++){
                    ($i===0)? $value = $ListeParametres[$i] :$value = $value . ";" .$ListeParametres[$i];
                }
                echo $value;
            ?>></label>
            <label for="tri-select">Trier par :</label>
            <select name="tri" id="tri-select" onchange="this.form.submit()">
                <!--Ajout des options de tri -->
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
            if (empty($livreRecherche)){
                echo '<p>Aucun Résultat</p>';
            }else{
                // Afficher les livres
                foreach($livreRecherche as $livre){
                    $lesAuteurs = '';
                    echo '<div class="book-item"><a href="./info_livre.php?id_livre=' . $livre['id_livre']. '">';
                    echo '<img src="' . $livre['img_couverture'] . '" >';
                    echo '<h3>' . $livre['titre_livre'] . '</h3>';
                    
                    $stmtAuteur->execute([':id_livre' => $livre['id_livre']]);
                    $Auteurs = $stmtAuteur->fetchAll();
                    for($i =0; $i < sizeOf($Auteurs); $i++){
                        if($i ===0){
                            $lesAuteurs = $Auteurs[$i]['prenom_auteur'] . " " . $Auteurs[$i]['nom_auteur'];
                        }else{
                            $lesAuteurs = $lesAuteurs .' | '. $Auteurs[$i]['prenom_auteur'] . " " . $Auteurs[$i]['nom_auteur'];
                        }
                    }
                    echo '<p>' . $lesAuteurs . '</p>';

                    // Calculer la moyenne des avis
                    $stmtSelectAllAvis->execute([':id_livre' => $livre['id_livre']]);
                    $allAvis = $stmtSelectAllAvis->fetchAll();
                    $totalAvis = count($allAvis);
                    $sumAvis = 0;
                    foreach ($allAvis as $avis) {
                        $sumAvis += $avis['note_avis'];
                    }

                    $averageAvis = $totalAvis ? $sumAvis / $totalAvis : 0;
                    $averageAvis = round($averageAvis, 1);
                    $etoiles = '';

                    echo '<div class="star-rating etoile">';
                    // Afficher les étoiles
                    for ($i = 1; $i <= 5; $i++) {
                        if ($i <= $averageAvis) {
                            $etoiles =  $etoiles.'<span class="star filled">★</span>';
                        }
                    }
                    echo $etoiles;

                    echo '</div>';
                    
                    
                    // Afficher le prix
                    if($livre['prix'] != null){
                        echo '<p><span class="price"> E-BOOK | ' . $livre['prix'] . '€</span></p>';
                    }

                    echo '</a></div>';
                }
            }
            ?>
        </div>
    </section>
</div>

<script>
    // Récupérer les éléments
    let slideMin = document.querySelector("#prix-min");
    let slideMax = document.querySelector("#prix-max");
    let valeurSlideMin = document.querySelector("#valeurMin");
    let valeurSlideMax = document.querySelector("#valeurMax");

    // Afficher la valeur des sliders
    slideMax.addEventListener('input', () =>{
        valeurSlideMax.innerHTML = slideMax.value;
    })
    slideMin.addEventListener('input', () =>{
        valeurSlideMin.innerHTML = slideMin.value;
    })
</script>

<?php
    require_once 'footer.php';
?>