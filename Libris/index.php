<?php
    require_once 'header.php';
    // Récupérer les derniers livres
    $stmt = $conn->prepare("SELECT l.id_livre, l.titre_livre, l.img_couverture, GROUP_CONCAT(CONCAT(a.prenom_auteur, ' ', a.nom_auteur) SEPARATOR ', ') AS auteurs
                                FROM livre l
                                JOIN a_ecrit ae ON l.id_livre = ae.id_livre
                                JOIN auteur a ON a.id_auteur = ae.id_auteur
                                GROUP BY l.id_livre, l.titre_livre, l.img_couverture
                                ORDER BY ae.date_parution DESC LIMIT 25;");
    $stmt->execute();
    $livres = $stmt->fetchAll();
    // Récupérer les livres les mieux notés
    $stmt = $conn->prepare("SELECT l.id_livre, l.titre_livre, l.img_couverture, 
            GROUP_CONCAT(DISTINCT CONCAT(a.prenom_auteur, ' ', a.nom_auteur) SEPARATOR ', ') AS auteurs, 
            AVG(av.note_avis) AS moy
                FROM livre l
                JOIN a_ecrit ae ON l.id_livre = ae.id_livre
                JOIN auteur a ON a.id_auteur = ae.id_auteur
                LEFT JOIN avis av ON l.id_livre = av.id_livre
                GROUP BY l.id_livre, l.titre_livre, l.img_couverture
                having moy>1
                ORDER BY moy DESC LIMIT 25;");
    $stmt->execute();
    $livresnote = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<div class ="pre-container">
    <div class="text-container">
        <h1>Derniers ouvrages</h1>
        <button class="background-violet"><a href="catalogue.php">Parcourir ></a></button>
    </div>
    <div class="slide-container">
        <div class="slide">
        <?php 
        if($livres){
            // Afficher les livres
            foreach($livres as $liv){ 
                echo '<div class="pre-livre"><a href="./info_livre.php?id_livre=' . htmlspecialchars($liv['id_livre']). '">'; 
                echo '<img src="' . htmlspecialchars($liv['img_couverture']) . '" alt="' . htmlspecialchars($liv['titre_livre']) . '">';
                echo '<h2>' . htmlspecialchars($liv['titre_livre']) . '</h2>';
                echo '<p>' . htmlspecialchars($liv['auteurs']) . '</p>';
                echo '</a></div>'; 
            }
        } else {
            echo '<p>Aucun livre trouvé.</p>';
        }
        ?>

        </div>
        <button class="scroll-right background-violet" style="display: none;">></button>
    </div>
</div>

<div class="pre-container">
    <div class="text-container">
        <h1>Les mieux notés</h1>
        <button class="background-violet"><a href="catalogue.php">Parcourir ></a></button>
    </div>
    <div class="slide-container">
        <div class="slide">
            <?php 
            if ($livres) {
                // Afficher les livres les mieux notés
                foreach ($livresnote as $liv) {
                        echo '<div class="pre-livre">';
                        echo '<a href="./info_livre.php?id_livre=' . htmlspecialchars($liv['id_livre']) . '">';
                        echo '<img src="' . htmlspecialchars($liv['img_couverture']) . '" alt="' . htmlspecialchars($liv['titre_livre']) . '">';
                        echo '<h2>' . htmlspecialchars($liv['titre_livre']) . '</h2>';
                        echo '<p>' . htmlspecialchars($liv['auteurs']) . '</p>';
                        echo '</a></div>';
                 }
            } else {
                echo '<p>No books found with sufficient ratings.</p>';
            }
            ?>
        </div>
        <button class="scroll-right background-violet" style="display: none;">></button>
    </div>
</div>

<script>
    // Fonction pour faire défiler les livres
    document.querySelectorAll('.slide').forEach(container => {
        const items = container.children;
        const scrollButton = container.nextElementSibling;
        let isScrolling = false;

        // Afficher le bouton de défilement si plus de 4 livres
        if (items.length > 4) {
            scrollButton.style.display = 'block';
        }

        // défilement progressif des livres
        const debounce = (func, delay) => {
            let inDebounce;
            return function() {
                const context = this;
                const args = arguments;
                clearTimeout(inDebounce);
                inDebounce = setTimeout(() => func.apply(context, args), delay);
            };
        };

        //défilement vers la droite ou la gauche selon le bouton cliqué 
        const handleScroll = (direction) => {
            if (isScrolling) return;
            isScrolling = true;
            
            if (direction === 'forward') {
                const firstItem = items[0];
                container.scrollLeft += firstItem.offsetWidth;
                setTimeout(() => {
                    container.appendChild(firstItem);
                    container.scrollLeft -= firstItem.offsetWidth;
                    isScrolling = false;
                }, 300);
            } else {
                const lastItem = items[items.length - 1];
                container.scrollLeft -= lastItem.offsetWidth;
                setTimeout(() => {
                    container.insertBefore(lastItem, items[0]);
                    container.scrollLeft += lastItem.offsetWidth;
                    isScrolling = false;
                }, 300);
            }
        };

        scrollButton.addEventListener('click', debounce(() => handleScroll('forward'), 300));
    });
</script>
<?php
    require_once 'footer.php';
?>