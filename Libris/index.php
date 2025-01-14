<?php
    require_once 'header.php';
    
    $stmt = $conn->prepare("SELECT l.id_livre, l.titre_livre, l.img_couverture, GROUP_CONCAT(CONCAT(a.prenom_auteur, ' ', a.nom_auteur) SEPARATOR ', ') AS auteurs
                                FROM livre l
                                JOIN a_ecrit ae ON l.id_livre = ae.id_livre
                                JOIN auteur a ON a.id_auteur = ae.id_auteur
                                GROUP BY l.id_livre, l.titre_livre, l.img_couverture
                                ORDER BY ae.date_parution DESC LIMIT 25;");
    $stmt->execute();
    $livres = $stmt->fetchAll();
?>
<div class ="pre-container">
    <div class="text-container">
        <h1>Derniers ouvrages</h1>
        <button class="background-violet">Parcourir ></button>
    </div>
    <div class="slide-container">
        <div class="slide">
        <?php 
        if($livres){
            foreach($livres as $liv){
                echo '<div class="pre-livre"><a href="./info_livre.php?id_livre=' . $liv['id_livre']. '">'; 
                echo '<img src="' . $liv['img_couverture'] . '" alt="' . $liv['titre_livre'] . '">';
                echo '<h2>' . $liv['titre_livre'] . '</h2>';
                echo '<p>by ' . $liv['auteurs'] . '</p>';
                echo '</a></div>'; 
            }
        } else {
            echo '<p>Aucune Livre trouve</p>';
        }
        ?>

        </div>
        <button class="scroll-right background-violet" style="display: none;">></button>
    </div>
</div>

<?php
$stmt = $conn->prepare("
SELECT l.id_livre, l.titre_livre, l.img_couverture, 
       GROUP_CONCAT(DISTINCT CONCAT(a.prenom_auteur, ' ', a.nom_auteur) SEPARATOR ', ') AS auteurs, 
       AVG(av.note_avis) AS moy
        FROM livre l
        JOIN a_ecrit ae ON l.id_livre = ae.id_livre
        JOIN auteur a ON a.id_auteur = ae.id_auteur
        LEFT JOIN avis av ON l.id_livre = av.id_livre
        GROUP BY l.id_livre, l.titre_livre, l.img_couverture
        HAVING AVG(av.note_avis) >= (SELECT AVG(note_avis) FROM avis)
        ORDER BY moy DESC LIMIT 25
");
$stmt->execute();
$livres = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<div class="pre-container">
    <div class="text-container">
        <h1>Les mieux notés</h1>
        <button class="background-violet">Parcourir ></button>
    </div>
    <div class="slide-container">
        <div class="slide">
            <?php 
            if ($livres) {
                foreach ($livres as $liv) { 
                    echo '<div class="pre-livre">';
                    echo '<a href="./info_livre.php?id_livre=' . $liv['id_livre'] . '">';
                    echo '<img src="' . $liv['img_couverture'] . '" alt="' . $liv['titre_livre'] . '">';
                    echo '<h2>' . $liv['titre_livre'] . '</h2>';
                    echo '<p>by ' . $liv['auteurs'] . '</p>';
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
    document.querySelectorAll('.slide').forEach(container => {
        const items = container.children;
        const scrollButton = container.nextElementSibling;
        let isScrolling = false;

        if (items.length > 3) {
            scrollButton.style.display = 'block';
        }

        const debounce = (func, delay) => {
            let inDebounce;
            return function() {
                const context = this;
                const args = arguments;
                clearTimeout(inDebounce);
                inDebounce = setTimeout(() => func.apply(context, args), delay);
            };
        };

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

        // container.addEventListener('wheel', debounce((evt) => {
        //     evt.preventDefault();
        //     if (evt.deltaY > 0) {
        //         handleScroll('forward');
        //     } else {
        //         handleScroll('backward');
        //     }
        // }, 300));
    });
</script>
<?php
    require_once 'footer.php';
?>