<?php
    require_once 'header.php';

    $resultGenre = $conn->query("SELECT id_genre, nom_genre FROM genre");
    $resultLangue = $conn->query("SELECT id_langue, nom_langue FROM langue");
    $resultCible = $conn->query("SELECT id_public, type_public FROM public_cible");

?>
        <div class="contenaire-recherche-avance">
           
            <form method="GET" action="./catalogue.php">
                
                <section class="recherche-avance">
                    <h1>Recherche avancée</h1>
                    <div class="exemple-recherche">
                        <select id="critaire">
                            <option value="titre">ET</option>
                            <option value="auteur">OU</option>
                            <option value="genre">SAUF</option>
                        </select>
                        <select id="critaire">
                            <option value="titre">Titre</option>
                            <option value="auteur">Auteur</option>
                            <option value="genre">Genre</option>
                        </select>
                        <input type="text" name="recherche-avance" placeholder="Rechercher...">
                        <i class="fa-solid fa-plus"></i>
                    </div>
                    
                </section>
                
                
                <section class="limter-recherche">
                    <h1>Limiter la recherche</h1>
                    <div class="groupe-annee">
                        <h4>Année de publication :</h4>
                        <p>De</p>
                        <input type="number" name="anneeDebut" placeholder="aaaa" min="1000" max="2025"> <p>à</p> 
                        <input type="number" name="anneeFin" placeholder="aaaa">
                    </div>
                    <div class="groupe">
                        <h4>Genres :</h4>
                        <div class="groupe-genre">
                        <?php
                            foreach ($resultGenre as $genre){
                                echo '<div class="groupe-checkbox"><input type="checkbox" name="genres[]" value="' . $genre['nom_genre'] . '"><label for="' . $genre['nom_genre'] . '">' . $genre['nom_genre'] . '</label></div>';
                            }
                        ?>
                        </div>
                    </div>
                    <div class="groupe-langue">
                        <h4>Langues :</h4>
                        <?php
                            foreach ($resultLangue as $langue){
                                echo '<div class="groupe-checkbox"><input type="checkbox" name="langues[]" value="' . $langue['nom_langue'] . '"><label for="' . $langue['nom_langue'] . '">' . $langue['nom_langue'] . '</label></div>';
                            }
                        ?>
                    </div>
                    <div class="groupe-public">
                        <h4>Public cible :</h4>
                        <?php
                            foreach ($resultCible as $public){
                                echo '<input type="checkbox" name="public[]" value="' . $public['type_public'] . '"><label for="' . $public['type_public'] . '">' . $public['type_public'] . '</label>';
                            }
                        ?>
                    </div>
                    <div class="groupe-ebook">
                        <h4>E-book :</h4>
                        <label><input type="checkbox" name="ebook" value="1"> E-book</label>

                        <div class="groupe-prix">
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
