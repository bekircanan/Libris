<?php
    require_once 'header.php';

    // Récupérer les genres, langues et publics cibles
    $resultGenre = $conn->query("SELECT id_genre, nom_genre FROM genre");
    $resultLangue = $conn->query("SELECT id_langue, nom_langue FROM langue");
    $resultCible = $conn->query("SELECT id_public, type_public FROM public_cible");

?>
<div class="contenaire-recherche-avance">
    
    <form method="GET" action="./catalogue.php">
        
        <section class="recherche-avance">
            <h1>Recherche avancée</h1>
            <div class="exemple-recherche">
                <select name="critaireOption[]">
                    <option value="ET">ET</option>
                    <option value="OU">OU</option>
                    <option value="SAUF">SAUF</option>
                </select>
                <select name="critaireValue[]">
                    <option value="titre">Titre</option>
                    <option value="auteur">Auteur</option>
                    <option value="edition">Edition</option>
                    <option value="sujet">Sujet</option>
                    <option value="isbn">ISBN</option>
                </select>
                <input type="text" name="recherche-avance[]" placeholder="Rechercher...">
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
                    // Afficher les genres
                    foreach ($resultGenre as $genre){
                        echo '<div class="groupe-checkbox"><input id="' . $genre['id_genre'] . '" type="checkbox" name="genres[]" value="' . $genre['nom_genre'] . '"><label for="' . $genre['id_genre'] . '">' . $genre['nom_genre'] . '</label></div>';
                    }
                ?>
                </div>

                <h4>Langues :</h4>
                <div class="groupe-langue">
                <?php
                    // Afficher les langues
                    foreach ($resultLangue as $langue){
                        echo '<div class="groupe-checkbox"><input type="checkbox" name="langues[]" value="' . $langue['nom_langue'] . '"><label for="' . $langue['nom_langue'] . '">' . $langue['nom_langue'] . '</label></div>';
                    }
                ?>
                </div>
            
                <h4>Public cible :</h4>
                <div class="groupe-public">
                    <?php
                        // Afficher les publics cibles
                        foreach ($resultCible as $public){
                            echo '<div class="groupe-checkbox"><input type="checkbox" name="public[]" value="' . $public['type_public'] . '"><label for="' . $public['type_public'] . '">' . $public['type_public'] . '</label></div>';
                        }
                    ?>
                </div>
                <h4>E-book :</h4>
                <div class="groupe-ebook">
                    <div class="groupe-checkbox">
                        <input id="ebook" type="checkbox" name="ebook"><label for="ebook">E-book</label>
                    </div>
                </div>
                <div class="groupe-prix">
                    <div class="groupe-checkbox">
                        <input type="checkbox" name="prix"> (prendre en compte le prix)
                    </div>  
                        <label for="prix-min">Prix min <span id="valeurMin">0</span>:</label>
                        <input id="prix-min" name="prix-min" type="range" value="0" min="0" max="25"/>
                        <label for="prix-max">Prix max <span id="valeurMax">25</span>:</label>
                        <input id="prix-max" name="prix-max" type="range" value="25" min="25" max="50"/>
                        <!-- <div class="wrapper">
                            <div class="container">
                                <div class="slider-track"></div>
                                <input type="range" min="0" max="100" value="30" id="slider-1" oninput="slideOne()">
                                <input type="range" min="0" max="100" value="70" id="slider-2" oninput="slideTwo()">
                            </div>
                        </div> -->
                </div>
            </div>
        
        <div class="conteneur-button">
            <button type="submit" class="button-recherche-avance">Chercher</button>
        </div>
        </section>
    </form>
</div>

<script>
    // Recuperer les elements
    let bouton = document.querySelector(".fa-plus");
    let slideMin = document.querySelector("#prix-min");
    let slideMax = document.querySelector("#prix-max");
    let valeurSlideMin = document.querySelector("#valeurMin");
    let valeurSlideMax = document.querySelector("#valeurMax");

    // Ajouter un critaire de recherche
    bouton.addEventListener('click',() =>{
        let parent = document.querySelector(".recherche-avance");
        let newEnfant = document.querySelector(".exemple-recherche").cloneNode(true);
        let boutonAdd = newEnfant.querySelector(".fa-plus");
        newEnfant.removeChild(boutonAdd);
        let boutonSupp = document.createElement("i");
        boutonSupp.setAttribute("class", "fa-solid fa-xmark");
        boutonSupp.addEventListener('click', () => {
            parent.removeChild(newEnfant);
        });
        newEnfant.appendChild(boutonSupp);
        parent.appendChild(newEnfant);

    });

    // Afficher la valeur des sliders
    slideMax.addEventListener('input', () =>{
        valeurSlideMax.innerHTML = slideMax.value;
    })
    slideMin.addEventListener('input', () =>{
        valeurSlideMin.innerHTML = slideMin.value;
    })

    // Supprimer un critaire de recherche
    function supprimer(){
        let parent = document.querySelector(".recherche-avance");
        
    }
</script>
<?php
    require_once 'footer.php';
?>
