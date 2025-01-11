<?php
    require_once 'header.php';

    $resultGenre = $conn->query("SELECT id_genre, nom_genre FROM genre");
    $resultLangue = $conn->query("SELECT id_langue, nom_langue FROM langue");
    $resultCible = $conn->query("SELECT id_public, type_public FROM public_cible");

    $genres = '';
    $langues = '';
    $public = '';
    $anneeDebut = '';
    $anneeFin = '';
    $ebook = false;
    $prixMax = "50";
    $prixMin = "0";
    

    if ($_SERVER['REQUEST_METHOD'] == 'POST'){
        // vérifie quel trie est choisie
        if ($_POST['form'] === 'filtre'){
            // Filtre par annee
            if (isset($_POST['anneeDebut'])) {
                $anneeDebut = $anneeDebut . $_POST['anneeDebut'];
            }
            if (isset($_POST['anneeFin'])) {
                $anneeFin = $anneeFin . $_POST['anneeFin'];
            }
            // Filtre par genre
            if (isset($_POST['genres']) && is_array($_POST['genres'])) {
                for($i = 0; $i < count($_POST['genres']); $i++){
                    if($i === count($_POST['genres']) -1){
                        $genres = $genres . $_POST['genres'][$i];
                    }else{
                        $genres = $genres . $_POST['genres'][$i] . ",";
                    }
                }
            }
            // Filtre par langue
            if (isset($_POST['langue']) && is_array($_POST['langue'])) {
                for($i = 0; $i < count($_POST['langue']); $i++){
                    if($i === count($_POST['langue']) -1){
                        $langues = $langues . $_POST['langue'][$i];
                    }else{
                        $langues = $langues . $_POST['langue'][$i] . ",";
                    }
                }
            }
            // Filtre par public cible
            if (isset($_POST['public']) && is_array($_POST['public'])) {
                for($i = 0; $i < count($_POST['public']); $i++){
                    if($i === count($_POST['public']) -1){
                        $public = $public . $_POST['public'][$i];
                    }else{
                        $public = $public . $_POST['public'][$i] . ",";
                    }
                }
            }
            // Filtre par E-book
            if (isset($_POST['ebook']) && $_POST['ebook'] == '1') {
                $ebook = true;
            }
            // Filtre par prix
            if (isset($_POST['prix-max']) ) {
                $prixMax = $_POST['prix-max'];
            } 
            if (isset($_POST['prix-min']) ) {
                $prixMin = $_POST['prix-min'];
            } 
        }
               
    }

    $url = $anneeDebut . ";" . $anneeFin . ";" . $genres . ";" . $langues . ";" . $public . ";" . $ebook . ";" . $prixMin . ";" . $prixMax;
    //ajouter edition, sujet, 
?>
        <div class="contenair-recherche">
            <h1>Recherche avancée</h1>
            
                <section class="recherche-avance">
                    <div class="group-critaire">
                        <label for="criteria1">Et</label>
                        <select id="criteria1">
                            <option value="title">Titre</option>
                            <option value="author">Auteur</option>
                            <option value="genre">Genre</option>
                        </select>
                        <input type="text" placeholder="Rechercher...">
                    </div>
                </section>
                <button class="button-recherche-avance">ajouter...</button>
            <form method="POST">
            <input type="hidden" name="form" value="filtre">
                <section class="filtre">
                    <h2>Limiter la recherche</h2>
                    <div class="filtre-groupe">
                        <label>Année de publication :</label>
                        <input type="number" name="anneeDebut" placeholder="aaaa" min="1000" max="2025"> à 
                        <input type="number" name="anneeFin" placeholder="aaaa">
                    </div>
                    <div class="filtre-groupe">
                        <h3>Genre du document :</h3>
                        <?php
                            foreach ($resultGenre as $genre){
                                echo '<input type="checkbox" name="genres[]" value="' . $genre['id_genre'] . '"><label for="' . $genre['nom_genre'] . '">' . $genre['nom_genre'] . '</label>';
                            }
                        ?>
                    </div>
                    <div class="filtre-groupe">
                        <h3>Langue :</h3>
                        <?php
                            foreach ($resultLangue as $langue){
                                echo '<input type="checkbox" name="langue[]" value="' . $langue['id_langue'] . '"><label for="' . $langue['nom_langue'] . '">' . $langue['nom_langue'] . '</label>';
                            }
                        ?>
                    </div>
                    <div class="filtre-groupe">
                        <h3>Public cible :</h3>
                        <?php
                            foreach ($resultCible as $public){
                                echo '<input type="checkbox" name="public[]" value="' . $public['id_public'] . '"><label for="' . $public['type_public'] . '">' . $public['type_public'] . '</label>';
                            }
                        ?>
                    </div>
                    <div class="filtre-groupe">
                        <h3>E-book :</h3>
                        <label><input type="checkbox" name="ebook" value="1"> E-book</label>

                        <div class="sliders-control">
                            <label for="prix-min">Prix min <span id="valeurMin">0</span>:</label>
                            <input id="prix-min" name="prix-min" type="range" value="0" min="0" max="25"/>
                            <label for="prix-max">Prix max <span id="valeurMax">25</span>:</label>
                            <input id="prix-max" name="prix-max" type="range" value="25" min="25" max="50"/>
                        </div>
                    </div>
                </section>
                <button type="submit" class="button-recherche-avance">Chercher</button>
            </form>
        </div>

        <script>
            let bouton = document.querySelector(".button-recherche-avance");
            let slideMin = document.querySelector("#prix-min");
            let slideMax = document.querySelector("#prix-max");
            let valeurSlideMin = document.querySelector("#valeurMin");
            let valeurSlideMax = document.querySelector("#valeurMax");

            bouton.addEventListener('click',() =>{
                let parent = document.querySelector(".recherche-avance");
                let newEnfant = document.querySelector(".group-critaire").cloneNode(true);
                let boutonSupp = document.createElement("i");
                boutonSupp.setAttribute("class", "fa-solid fa-xmark fa-2xl");
                boutonSupp.addEventListener('click', () => {
                    parent.removeChild(newEnfant);
                });
                newEnfant.appendChild(boutonSupp);
                parent.appendChild(newEnfant);

            });

            slideMax.addEventListener('input', () =>{
                valeurSlideMax.innerHTML = slideMax.value;
            })
            slideMin.addEventListener('input', () =>{
                valeurSlideMin.innerHTML = slideMin.value;
            })

            function supprimer(){
                let parent = document.querySelector(".recherche-avance");
                
            }




        </script>
        <?php
    require_once 'footer.php';
?>
