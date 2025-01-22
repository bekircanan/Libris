<?php
require_once "header.php";

$stmt = $conn->prepare("SELECT id_livre,titre_livre,resume,img_couverture FROM livre");
$stmt->execute();
$livres = $stmt->fetchAll(PDO::FETCH_ASSOC);
if($_SERVER['REQUEST_METHOD'] == 'POST'){
    if(isset($_POST['form']) && $_POST['form']==='supprimeLivre'){
        try{
            $conn->beginTransaction();
            $stmt = $conn->prepare("Delete from exemplaire join isbn on exemplaire.id_isbn = isbn.id_isbn where id_livre = :id_livre");
            $stmt->bindParam(':id_livre', $_POST['id_livre']);
            $stmt->execute();
            $stmt = $conn->prepare("Delete from isbn where id_livre = :id_livre");
            $stmt->bindParam(':id_livre', $_POST['id_livre']);
            $stmt->execute();
            $stmt = $conn->prepare("DELETE FROM livre WHERE id_livre = :id_livre");
            $stmt->bindParam(':id_livre', $_POST['id_livre']);
            $stmt->execute();
            $conn->commit();
        }catch(Exception $e){
            $conn->rollBack();

        }

    }elseif(isset($_POST['form'],$_POST['id_livre'], $_POST['titre_livre'], $_POST['resume']) && $_POST['form']==='ModifieLivre'){
        if(isset($_FILES['img_couverture']) && !empty($_FILES['img_couverture']['name'])){
            $imageFileName = "{$_POST['titre_livre']}.png";
            $imageFileName = str_replace(' ', '_', $imageFileName);
            $targetDir = "./img/img_couv/";
            $targetFile = $targetDir . basename($imageFileName);
            if (file_exists($targetFile)) {
                unlink($targetFile);
            }
            if (move_uploaded_file($_FILES["img_couverture"]["tmp_name"], $targetFile)){
                $stmt = $conn->prepare("UPDATE livre SET titre_livre = :titre_livre, resume = :resume,img_couverture = :img_couverture WHERE id_livre = :id_livre");
                $stmt->bindParam(':titre_livre', $_POST['titre_livre']);
                $stmt->bindParam(':resume', $_POST['resume']);
                $stmt->bindParam(':img_couverture', $targetFile);
                $stmt->bindParam(':id_livre', $_POST['id_livre']);
                $stmt->execute();
            }
        }else{
            $stmt = $conn->prepare("UPDATE livre SET titre_livre = :titre_livre, resume = :resume WHERE id_livre = :id_livre");
            $stmt->bindParam(':titre_livre', $_POST['titre_livre']);
            $stmt->bindParam(':resume', $_POST['resume']);
            $stmt->bindParam(':id_livre', $_POST['id_livre']);
            $stmt->execute();
        }
    }
}
// Check if image file is an actual image or fake image
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['form']) && $_POST['form'] === 'ajout_livre') {
    if (isset($_POST["envoyer"])) {
        if (empty($_FILES["fileToUpload"]["name"])) {
            echo "<script>document.addEventListener('DOMContentLoaded', function() {  popup(); });</script>";
        }
        else if (empty($_FILES["folderToUpload"]["name"])) {
            echo "<script>document.addEventListener('DOMContentLoaded', function() {  popup(); });</script>";
        }
        else{
            $fileType = strtolower(pathinfo($_FILES["fileToUpload"]["name"], PATHINFO_EXTENSION));
            if ($fileType === "csv") {
                $content = file_get_contents($_FILES["fileToUpload"]["tmp_name"]);
                $lines = explode(";", $content);
                foreach ($lines as $line) {
                    if (empty($line)) {
                        break;
                    }
                    $test = explode("\"", $line);
                    $titre = $test[1];
                    $resume = $test[3];
                    $fields = explode(" ", $test[0]);
                    $num_isbn = $fields[0];
                    $num_isbn = str_replace(" ", "", $num_isbn);
                    $cote = $fields[1];
                    $type_litteraire = $test[2];
                    $field = explode(" ", $test[4]);
                    $genres = explode(",", $field[1]);
                    $langue = $field[2];
                    $public_cibles = explode(",", $field[3]);
                    $edition = $field[4];
                    $nb_pages = $field[5];
                    $nom_auteurs = explode(",", $field[6]);;
                    $prenom_auteurs = explode(",", $field[7]);;
                    $date_parution = date('Y-m-d', strtotime($field[8]));
                    
                    $stmtTestIsbn = $conn->prepare("SELECT * FROM isbn WHERE num_isbn = '{$num_isbn}'");
                    $stmtTestIsbn->execute();
                    $testIsbn = $stmtTestIsbn->fetch();
                    $stmtTestCote = $conn->prepare("SELECT * FROM livre WHERE cote_livre = '{$cote}'");
                    $stmtTestCote->execute();
                    $testCote = $stmtTestCote->fetch();

                    if(empty($testCote)){
                        $stmtInsertLivre = $conn->prepare("
                        INSERT INTO livre (cote_livre, titre_livre, type_litteraire, resume)
                        VALUES (:cote_livre, :titre_livre, :type_litteraire, :resume)");
                        $stmtInsertLivre->bindParam(':cote_livre', $cote);
                        $stmtInsertLivre->bindParam(':titre_livre', $titre);
                        $stmtInsertLivre->bindParam(':type_litteraire', $type_litteraire);
                        $stmtInsertLivre->bindParam(':resume', $resume);
                        $stmtInsertLivre->execute();

                        
                        $stmtSelectIdLivre = $conn->prepare("
                            SELECT id_livre
                            FROM livre
                            WHERE cote_livre = '{$cote}'");
                        $stmtSelectIdLivre->execute();
                        $id_livre = $stmtSelectIdLivre->fetch(PDO::FETCH_ASSOC);
                        foreach($genres as $genre) {
                            $stmtSelectIdGenre = $conn->prepare("
                                SELECT id_genre
                                FROM genre
                                WHERE nom_genre = '{$genre}'");
                            $stmtSelectIdGenre->execute();
                            $id_genre = $stmtSelectIdGenre->fetch();
                            if(empty($id_genre)){
                                $stmtInsertGenre = $conn->prepare("
                                    INSERT INTO genre (nom_genre)
                                    VALUES (:nom_genre)");
                                $stmtInsertGenre->bindParam(':nom_genre', $genre);
                                $stmtInsertGenre->execute();
                                $stmtSelectIdGenre = $conn->prepare("
                                    SELECT id_genre
                                    FROM genre
                                    WHERE nom_genre = '{$genre}'");
                                $stmtSelectIdGenre->execute();
                                $id_genre = $stmtSelectIdGenre->fetch();
                            }
                            $stmtInsertGenre = $conn->prepare("
                                INSERT INTO livre_genre (id_genre, id_livre)
                                VALUES (:id_genre, :id_livre)");
                            $stmtInsertGenre->bindParam(':id_livre', $id_livre['id_livre']);
                            $stmtInsertGenre->bindParam(':id_genre', $id_genre['id_genre']);
                            $stmtInsertGenre->execute();
                        }
                        foreach($public_cibles as $public_cible) {
                            $stmtSelectIdPublicCible = $conn->prepare("
                                SELECT id_public
                                FROM public_cible
                                WHERE type_public = '{$public_cible}'");
                            $stmtSelectIdPublicCible->execute();
                            $id_public_cible = $stmtSelectIdPublicCible->fetch();
                            if(empty($id_public_cible)){
                                $stmtInsertPublicCible = $conn->prepare("
                                    INSERT INTO public_cible (type_public)
                                    VALUES (:type_public)");
                                $stmtInsertPublicCible->bindParam(':type_public', $public_cible);
                                $stmtInsertPublicCible->execute();
                                $stmtSelectIdPublicCible = $conn->prepare("
                                    SELECT id_public
                                    FROM public_cible
                                    WHERE type_public = '{$public_cible}'");
                                $stmtSelectIdPublicCible->execute();
                                $id_public_cible = $stmtSelectIdPublicCible->fetch();
                            }
                            $stmtInsertPublicCible = $conn->prepare("
                                INSERT INTO livre_public (id_public, id_livre)
                                VALUES (:id_public, :id_livre)");
                            $stmtInsertPublicCible->bindParam(':id_livre', $id_livre['id_livre']);
                            $stmtInsertPublicCible->bindParam(':id_public', $id_public_cible['id_public']);
                            $stmtInsertPublicCible->execute();
                        }

                        
                        $stmtSelectLangue = $conn->prepare("
                            SELECT id_langue
                            FROM langue
                            WHERE nom_langue = '{$langue}'");
                        $stmtSelectLangue->execute();
                        $id_langue = $stmtSelectLangue->fetch();
                        if(empty($id_langue)){
                            $stmtInsertLangue = $conn->prepare("
                                INSERT INTO langue (nom_langue)
                                VALUES (:nom_langue)");
                            $stmtInsertLangue->bindParam(':nom_langue', $langue);
                            $stmtInsertLangue->execute();
                            $stmtSelectIdLangue = $conn->prepare("
                                SELECT id_langue
                                FROM langue
                                WHERE nom_langue = '{$langue}'");
                            $stmtSelectIdLangue->execute();
                            $id_langue = $stmtSelectIdLangue->fetch();
                        }

                        $stmtSelectIdEdition = $conn->prepare("
                            SELECT id_edition
                            FROM edition
                            WHERE nom_edition = '{$edition}'");
                        $stmtSelectIdEdition->execute();
                        $id_edition = $stmtSelectIdEdition->fetch();
                        if(empty($id_edition)){
                            $stmtInsertEdition = $conn->prepare("
                                INSERT INTO edition (nom_edition)
                                VALUES (:nom_edition)");
                            $stmtInsertEdition->bindParam(':nom_edition', $edition);
                            $stmtInsertEdition->execute();
                            $stmtSelectIdEdition = $conn->prepare("
                                SELECT id_edition
                                FROM edition
                                WHERE nom_edition = '{$edition}'");
                            $stmtSelectIdEdition->execute();
                            $id_edition = $stmtSelectIdEdition->fetch();
                        }
                        if(empty($testIsbn)){
                            $stmtInsertIsbn = $conn->prepare("
                                INSERT INTO isbn (num_isbn, id_livre, id_langue, id_edition, nb_pages)
                                VALUES (:num_isbn, :id_livre, :id_langue, :id_edition, :nb_pages)");
                            $stmtInsertIsbn->bindParam(':num_isbn', $num_isbn);
                            $stmtInsertIsbn->bindParam(':id_livre', $id_livre['id_livre']);
                            $stmtInsertIsbn->bindParam(':id_langue', $id_langue['id_langue']);
                            $stmtInsertIsbn->bindParam(':id_edition', $id_edition['id_edition']);
                            $stmtInsertIsbn->bindParam(':nb_pages', $nb_pages);
                            $stmtInsertIsbn->execute();
                        }
                        
                        foreach($nom_auteurs as $nom_auteur){
                            $prenom_auteur = array_shift($prenom_auteurs);
                            $stmtTestAuteur = $conn->prepare("
                                SELECT id_auteur
                                FROM auteur
                                WHERE nom_auteur = '{$nom_auteur}' and prenom_auteur = '{$prenom_auteur}'");
                            $stmtTestAuteur->execute();
                            $id_auteur = $stmtTestAuteur->fetch();
                            if($id_auteur === false) {
                                $stmtInsertAuteur = $conn->prepare("
                                    INSERT INTO auteur (nom_auteur, prenom_auteur)
                                    VALUES (:nom_auteur, :prenom_auteur)");
                                $stmtInsertAuteur->bindParam(':nom_auteur', $nom_auteur);
                                $stmtInsertAuteur->bindParam(':prenom_auteur', $prenom_auteur);
                                $stmtInsertAuteur->execute();
                                $stmtSelectIdAuteur = $conn->prepare("
                                    SELECT id_auteur
                                    FROM auteur
                                    WHERE nom_auteur = '{$nom_auteur}' and prenom_auteur = '{$prenom_auteur}'");
                                $stmtSelectIdAuteur->execute();
                                $id_auteur = $stmtSelectIdAuteur->fetch();
                            }
                            
                            $stmtInsertAEcrit = $conn->prepare("
                                INSERT INTO a_ecrit (id_auteur, id_livre, date_parution)
                                VALUES (:id_auteur, :id_livre, :date_parution)");
                            $stmtInsertAEcrit->bindParam(':id_auteur', $id_auteur['id_auteur']);
                            $stmtInsertAEcrit->bindParam(':id_livre', $id_livre['id_livre']);
                            $stmtInsertAEcrit->bindParam(':date_parution', $date_parution);
                            $stmtInsertAEcrit->execute();

                        }

                        
                        $imageFileName = "{$titre}.png";
                        $imageFileName = str_replace(' ', '_', $imageFileName);
                        $targetDir = "./img/img_couv/";
                        $targetFile = $targetDir . basename($imageFileName);
                        if (file_exists($targetFile)) {
                            unlink($targetFile);

                        }
                        foreach($_FILES["folderToUpload"]["name"] as $key => $name){
                            if($imageFileName === $name){
                                if (move_uploaded_file($_FILES["folderToUpload"]["tmp_name"][$key], $targetFile)){
                                    $stmtUpdateLivre = $conn->prepare("
                                    UPDATE livre
                                    SET img_couverture = :image_link
                                    WHERE id_livre = :id_livre");
                                    $stmtUpdateLivre->bindParam(':image_link', $targetFile);
                                    $stmtUpdateLivre->bindParam(':id_livre', $id_livre['id_livre']);
                                    $stmtUpdateLivre->execute();
                                }
                            }
                        }
                    }
                    

                    
                }
            }
        }
    }
}
?>

<div class="ajout_livre">
    <h1>Ajout de livres :</h1>
    <div class ="info-style-container">
        <div class="info-style-fichier">
            <p>Le fichier doit être au format .csv et doit respecter la structure suivante :</p>
            <p>ISBN Cote Titre Type Littéraire "Résumé" Genre(s) Langue Public_cible(s) Edition Nombre_de_pages Auteur(s) Date_de_parution;</p>
            <ul>
                <li> Tous les paramètres doivent être séparés par un espace, le résumé sera encadré par des guillemets.</li>    
                <li> Ne pas mettre de guillemets à l'intérieur de celui-ci.</li> 
                <li> Ne pas inclure de ligne de test comme celle ci-dessus.</li>
                <li> Les genres, public cibles et auteurs doivent être séparés par des virgules, si il y en a plusieurs.</li>
            </ul> 
            <p>Le dossier d'images doit respecter les conditions suivantes :</p>
            <ul>
                <li> Les images doivent être au format .png.</li>
                <li> Le nom de l'image doit correspondre au titre du livre, en remplacant les espaces par le caractère "_".</li>
                <li> Dans le cas où plusieurs livres d'éditions différentes sont ajoutés, une seule image est nécessaire pour l'ensemble.</li>
            </ul>
            <a class="btn_ex_csv" href="Exemple/exemple.csv" download = "exemple.csv"> <button>Télecharger Exemple Fichier </button></a>
        </div>
        
    </div>

    <form method="post" enctype="multipart/form-data">
        <input type="hidden" name="form" value="ajout_livre">
        <div class="drop-zones-container">
            <div class="drop-zone" id="drop-zone-file">
                <img src="img/nouveau-fichier.png" alt="UploadFich">
                <label for="fileToUpload">Veuillez déposer ici le fichier (.csv) :</label>
                <input type="file" name="fileToUpload" id="fileToUpload">
            </div>
            <div class="drop-zone" id="drop-zone-folder">
                <img src="img/dossier.png" alt="UploadDoss">
                <label for="folderToUpload[]">Veuillez déposer ici le dossier contenant les images (.png) :</label>
                <input type="file" name="folderToUpload[]" id="folderToUpload" multiple directory="" webkitdirectory="" mozdirectory="">
            </div>
        </div>
        <div class="btn_valider_ajout container">
            <button type="submit" value="Valider" name="envoyer" class="btn_valider_ajout"> Valider </button>

        </div>
    </form>
    <div class="gestion_livre">
        <h1>Livres :</h1>
        <input type="text" id="search-reservations-input" placeholder="Rechercher une livre..." onkeyup="search()">
        <table class="table-emprunts-reservations table-gestion" id="table-reservations">
            <thead>
            <tr>
                <th>Titre</th>
                <th>Resume</th>
                <th>Actions</th>
            </tr>
            </thead>
            <tbody>
            <?php
            foreach ($livres as $livre) {
                echo "<tr>";
                echo "<td>". $livre["titre_livre"] ."</td>";
                echo "<td>". $livre["resume"]. "</td>";
                echo "<td>
                    <button onclick=\"popupModifie('".$livre["id_livre"]."','".$livre["titre_livre"]."','".$livre["resume"]."')\">Modifie</button>
                    <button onclick=\"popupSupprime('".$livre["id_livre"]."')\">Supprime</button>";
                echo "</td>";
                echo "</tr>";
            }
            ?>
            </tbody>
        </table>
    </div>
</div>

<script>
function search() {
        let input = document.getElementById('search-reservations-input').value.toLowerCase();

        let table = document.querySelector('#table-reservations tbody');
        let rows = table.getElementsByTagName('tr');
        for (let i = 0; i < rows.length; i++) {
            let row = rows[i];
            let cells = row.getElementsByTagName('td');
            let rowText = "";
            for (let j = 0; j < cells.length; j++) {
                rowText += cells[j].innerText.toLowerCase() + " ";
            }
            if (rowText.includes(input)) {
                row.style.display = "";
            } else {
                row.style.display = "none";
            }
        }
    }

document.addEventListener('DOMContentLoaded', function () {
    var dropZoneFile = document.getElementById('drop-zone-file');
    var fileInput = document.getElementById('fileToUpload');

    dropZoneFile.addEventListener('click', function () {
        fileInput.click();
    });

    dropZoneFile.addEventListener('dragover', function (e) {
        e.preventDefault();
        e.stopPropagation();
        dropZoneFile.classList.add('dragover');
    });

    dropZoneFile.addEventListener('dragleave', function (e) {
        e.preventDefault();
        e.stopPropagation();
        dropZoneFile.classList.remove('dragover');
    });

    dropZoneFile.addEventListener('drop', function (e) {
        e.preventDefault();
        e.stopPropagation();
        dropZoneFile.classList.remove('dragover');

        if (e.dataTransfer.files.length) {
            fileInput.files = e.dataTransfer.files;
            dropZoneFile.querySelector('p').textContent = fileInput.files[0].name;
        }
    });

    fileInput.addEventListener('change', function () {
        if (fileInput.files.length) {
            dropZoneFile.querySelector('p').textContent = fileInput.files[0].name;
        }
    });

    var dropZoneFolder = document.getElementById('drop-zone-folder');
    var folderInput = document.getElementById('folderToUpload');

    dropZoneFolder.addEventListener('click', function () {
        folderInput.click();
    });

    dropZoneFolder.addEventListener('dragover', function (e) {
        e.preventDefault();
        e.stopPropagation();
        dropZoneFolder.classList.add('dragover');
    });

    dropZoneFolder.addEventListener('dragleave', function (e) {
        e.preventDefault();
        e.stopPropagation();
        dropZoneFolder.classList.remove('dragover');
    });

    dropZoneFolder.addEventListener('drop', function (e) {
        e.preventDefault();
        e.stopPropagation();
        dropZoneFolder.classList.remove('dragover');

        var reader = e.dataTransfer.items[0].webkitGetAsEntry();
        reader.createReader().readEntries(function(entries) {
            var files = [];
            for (var i = 0; i < entries.length; i++) {
                if (entries[i].isFile) {
                    entries[i].file(function(file) {
                        files.push(file);   
                        if (files.length === entries.length) {
                            var dataTransfer = new DataTransfer();
                            files.forEach(function(file) {
                                dataTransfer.items.add(file);
                            });
                            folderInput.files = dataTransfer.files;
                        }
                    });
                }
            }
        });
    });

    folderInput.addEventListener('change', function () {
        if (folderInput.files.length) {
            dropZoneFolder.querySelector('label').textContent = folderInput.files[0].name;
        }
    });
});

function popupSupprime(id_livre) {
        let popup = document.createElement('div');
        popup.className = 'popupGestion hidden';
        popup.innerHTML = `
            <div class="popupGestion-content">
                <h2>Confirmation de suppression</h2>
                <p>Êtes-vous sûr de vouloir supprime cette livre ?</p>
                <form method="POST" action="gestion-livres.php">
                    <input type="hidden" name="form" value="supprimeLivre">
                    <input type="hidden" name="id_livre" value="${id_livre}">
                    <button type="submit">OK</button>
                </form>
                <br>
                <button class='popupGestionAnnuler'>Annuler</button>
            </div>
        `;
        const main = document.querySelector('main');
        main.appendChild(popup);

        const cancelButton = popup.querySelector('.popupGestionAnnuler');
        cancelButton.addEventListener('click', () => {
            main.removeChild(popup);
        });
    }

function popupModifie(id_livre,titre_livre,resume) {
    let popup = document.createElement('div');
    popup.className = 'popupGestion hidden';
    popup.innerHTML = `
        <div class="popupGestion-content">
            <h2>Modification livre</h2>
            <form method="POST" action="gestion-livres.php">
                <input type="hidden" name="form" value="ModifieLivre">
                <input type="hidden" name="id_livre" value="${id_livre}">
                <label for="titre_livre">Titre</label>
                <input type="text" name="titre_livre" value="${titre_livre}" size="${titre_livre.length}">
                <label for="resume">Resume</label>
                <textarea name="resume" cols="40" rows="5">${resume}</textarea>
                <label for="img_couverture">Image</label>
                <input type="file" name="img_couverture" id="img_couverture">
                <br><br>
                <button type="submit">OK</button>
            </form>
            <br>
            <button class='popupGestionAnnuler'>Annuler</button>
        </div>
    `;
    const main = document.querySelector('main');
    main.appendChild(popup);

    const cancelButton = popup.querySelector('.popupGestionAnnuler');
    cancelButton.addEventListener('click', () => {
        main.removeChild(popup);
    });
}

function popup() {
            let popup = document.createElement('div');
            popup.className = 'popupGestion hidden';
            popup.innerHTML = `
                <div class="popupGestion-content">
                    <h2>Erreur de Saisie</h2>
                    <p>Veuillez séletionner un fichier ET un dossier d'images.......</p>
                    <button class='popupGestionAnnuler'>Okay</button>
                </div>
            `;
            const main = document.querySelector('main');
            main.appendChild(popup);

            const cancelButton = popup.querySelector('.popupGestionAnnuler');
            cancelButton.addEventListener('click', () => {
                main.removeChild(popup);
            });
        }
</script>

<?php
require_once "footer.php"
?>