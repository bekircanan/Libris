<?php
require_once "header.php";

// Check if image file is an actual image or fake image
if ($_SERVER['REQUEST_METHOD'] == 'POST' && $_POST['form'] === 'ajout_livre') {
    $fileType = strtolower(pathinfo($_FILES["fileToUpload"]["name"], PATHINFO_EXTENSION));
    $FileTypeImg = strtolower(pathinfo($_FILES["folderToUpload"]["name"], PATHINFO_EXTENSION));
    if ($fileType === "csv") {
        $content = file_get_contents($_FILES["fileToUpload"]["tmp_name"]);
        $lines = explode(";", $content);
        
        foreach ($lines as $line) {
            $test = explode("\"", $line);
            $titre = $test[1];
            $resume = $test[4];
            $fields = explode(" ", $test[0]);
            $num_isbn = $fields[0];
            $cote = $fields[1];
            $type_litteraire = $test[3];
            $field = explode(" ", $test[5]);
            $genres = explode(",", $field[0]);
            $langue = $field[1];
            $public_cibles = explode(",", $field[2]);
            $edition = $field[3];
            $nb_pages = $field[4];
            $nom_auteur = $field[5];
            $prenom_auteur = $field[6];
            $date_parution = $field[7];

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
                WHERE cote_livre = {$cote} and titre_livre = {$titre}");
            $stmtSelectIdLivre->execute();
            $id_livre = $stmtSelectIdLivre->fetch();
            foreach($genres as $genre) {
                $stmtInsertGenre = $conn->prepare("
                    INSERT INTO livre_genre (id_genre, id_livre)
                    VALUES (:nom_genre, :id_livre)");
                $stmtInsertGenre->bindParam(':id_livre', $id_livre);
                $stmtInsertGenre->bindParam(':nom_genre', $genre);
                $stmtInsertGenre->execute();
            }
            foreach($public_cibles as $public_cible) {
                $stmtInsertPublicCible = $conn->prepare("
                    INSERT INTO livre_public_cible (id_public_cible, id_livre)
                    VALUES (:nom_public_cible, :id_livre)");
                $stmtInsertPublicCible->bindParam(':id_livre', $id_livre);
                $stmtInsertPublicCible->bindParam(':nom_public_cible', $public_cible);
                $stmtInsertPublicCible->execute();
            }
            $stmtSelectLangue = $conn->prepare("
                SELECT id_langue
                FROM langue
                WHERE nom_langue = {$langue}");
            $stmtSelectIdLangue->execute();
            $id_langue = $stmtSelectIdLangue->fetch();

            $stmtInsertLangue = $conn->prepare("
                INSERT INTO livre_langue (id_langue, id_livre)
                VALUES (:id_langue, :id_livre)");
            $stmtInsertLangue->bindParam(':id_livre', $id_livre);
            $stmtInsertLangue->bindParam(':id_langue', $id_langue);
            $stmtInsertLangue->execute();
            $stmtSelectIdEdition = $conn->prepare("
                SELECT id_edition
                FROM edition
                WHERE nom_edition = {$edition}");
            $stmtSelectIdEdition->execute();
            $id_edition = $stmtSelectIdEdition->fetch();
            $stmtInsertEdition = $conn->prepare("
                INSERT INTO livre_edition (id_edition, id_livre)
                VALUES (:id_edition, :id_livre)");
            $stmtInsertEdition->bindParam(':id_livre', $id_livre);
            $stmtInsertEdition->bindParam(':id_edition', $id_edition);
            $stmtInsertEdition->execute();
            $stmtInsertIsbn = $conn->prepare("
                INSERT INTO livre_isbn (num_isbn, id_livre, id_langue, id_edition, nb_pages)
                VALUES (:num_isbn, id_langue, :id_livre, :id_edition, :nb_pages)");
            $stmtInsertIsbn->bindParam(':num_isbn', $num_isbn);
            $stmtInsertIsbn->bindParam(':id_livre', $id_livre);
            $stmtInsertIsbn->bindParam(':id_langue', $id_langue);
            $stmtInsertIsbn->bindParam(':id_edition', $id_edition);
            $stmtInsertIsbn->bindParam(':nb_pages', $nb_pages);
            $stmtInsertIsbn->execute();

            $stmtTestAuteur = $conn->prepare("
                SELECT id_auteur
                FROM auteur
                WHERE nom_auteur = {$nom_auteur} and prenom_auteur = {$prenom_auteur}");
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
                    WHERE nom_auteur = {$nom_auteur} and prenom_auteur = {$prenom_auteur}");
                $stmtSelectIdAuteur->execute();
                $id_auteur = $stmtSelectIdAuteur->fetch();
            }
            $stmtInsertAEcrit = $conn->prepare("
                INSERT INTO a_ecrit (id_auteur, id_livre, date_parution)
                VALUES (:id_auteur, :id_livre, :date_parution)");
            $stmtInsertAEcrit->bindParam(':id_auteur', $id_auteur);
            $stmtInsertAEcrit->bindParam(':id_livre', $id_livre);
            $stmtInsertAEcrit->bindParam(':date_parution', $date_parution);
            $stmtInsertAEcrit->execute();

            
            $imageFileName = "{$titre}.png";
            $imageFileName = str_replace(' ', '_', $imageFileName);
            $targetDir = "img_couv/";
            $targetFile = $targetDir . basename($imageFileName);

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
?>

<div class="ajout_livre">
    <h1>Ajout de livres :</h1>
    <h2>Déposez votre fichier ci-dessous : (.csv)</h2>
    <form action="ajout_livre.php" method="post" enctype="multipart/form-data">
        <input type="hidden" name="form" value="ajout_livre">
        Select file to upload:
        <input type="file" name="fileToUpload" id="fileToUpload">
        <input type="file" name="folderToUpload" id="folderToUpload" webkitdirectory directory multiple>
        <input type="submit" value="Valider" name="envoyer">
    </form>
</div>











































<?php
require_once "footer.php"
?>