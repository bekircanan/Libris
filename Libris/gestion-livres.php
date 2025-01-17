<?php
require_once "header.php";

// Check if image file is an actual image or fake image
print_r($_POST);
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['form']) && $_POST['form'] === 'ajout_livre') {
    if (isset($_POST["envoyer"])) {
        if (empty($_FILES["fileToUpload"]["name"])) {
            echo "Veuillez sélectionner un fichier .csv";
        }
        else if (empty($_FILES["folderToUpload"]["name"])) {
            echo "Veuillez sélectionner un dossier d'images";
        }
        else{
            $fileType = strtolower(pathinfo($_FILES["fileToUpload"]["name"], PATHINFO_EXTENSION));
            $FileTypeImg = strtolower(pathinfo($_FILES["folderToUpload"]["name"], PATHINFO_EXTENSION));
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
                    $cote = $fields[1];
                    $type_litteraire = $test[2];
                    $field = explode(" ", $test[4]);
                    $genres = explode(",", $field[0]);
                    $langue = $field[0];
                    $public_cibles = explode(",", $field[1]);
                    $edition = $field[2];
                    $nb_pages = $field[3];
                    $nom_auteurs = explode(",", $field[4]);;
                    $prenom_auteurs = explode(",", $field[5]);;
                    $date_parution = date('Y-m-d', strtotime($field[6]));
                    
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
                        $targetDir = "img_couv/";
                        $targetFile = $targetDir . basename($imageFileName);
                        echo $targetFile;
                        echo $imageFileName;

                        if (file_exists($_FILES["folderToUpload"]["tmp_name"] . "/" . $imageFileName)) {
                            
                            move_uploaded_file($_FILES["folderToUpload"]["tmp_name"] . "/" . $imageFileName, $targetFile);

                        
                            $stmtUpdateLivre = $conn->prepare("
                                UPDATE livre
                                SET img_couverture = :image_link
                                WHERE id_livre = :id_livre");
                            $stmtUpdateLivre->bindParam(':image_link', $targetFile);
                            $stmtUpdateLivre->bindParam(':id_livre', $id_livre);
                            $stmtUpdateLivre->execute();
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
        </div>
    </div>
    <form  method="post" enctype="multipart/form-data">
        <input type="hidden" name="form" value="ajout_livre">
        <img src="img/nouveau-fichier.png" alt="UploadFich">
        <p>Veuillez déposer ici le fichier (.csv) :</p>
        <input type="file" name="fileToUpload" id="fileToUpload">

        <img src="img/dossier.png" alt="UploadDoss">
                
        <p>Veuillez déposer ici le dossier contenant les images (.png) :</p>
        <input type="file" name="folderToUpload" id="folderToUpload" webkitdirectory directory multiple>
        <button type="submit" value="Valider" name="envoyer" class="btn_valider_ajout">
    </form>
</div>

<script>
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

        if (e.dataTransfer.files.length) {
            folderInput.files = e.dataTransfer.files;
        }
    });

    folderInput.addEventListener('change', function () {
        if (folderInput.files.length) {
            dropZoneFolder.querySelector('p').textContent = folderInput.files[0].name;
        }
    });
});
</script>









































<?php
require_once "footer.php"
?>