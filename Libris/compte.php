<?php
    require_once 'header.php';
    ini_set('file_uploads', '1');
    if(!isset($_SESSION['user'])){
        header('Location: index.php');
        exit;
    }
    if($_SERVER['REQUEST_METHOD'] == 'POST'){
        if(isset($_POST['pseudo']) && !empty($_POST['pseudo'])){
            $newPseudo = $_POST['pseudo'];
            $stmt = $conn->prepare("UPDATE utilisateur SET pseudo = ? WHERE id_util  = ?");
            $stmt->bindParam(1,$newPseudo);
            $stmt->bindParam(2,$_SESSION['id']);
            $stmt->execute();
            $_SESSION['user'] = $newPseudo;
        }if(isset($_POST['email']) && !empty($_POST['email'])){
            $newEmail = $_POST['email'];
            $stmt = $conn->prepare("UPDATE utilisateur SET email = ? WHERE id_util  = ?");
            $stmt->bindParam(1,$newEmail);
            $stmt->bindParam(2,$_SESSION['id']);
            $stmt->execute();
            $_SESSION['email'] = $newEmail;
        }if(isset($_POST['amdp']) && !empty($_POST['amdp']) && isset($_POST['nmdp']) && !empty($_POST['nmdp'])){ 
            $newMdp = $_POST['nmdp'];
            $newMdp = password_hash($newMdp, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("SELECT mdp FROM utilisateur WHERE id_util  = ?");
            $stmt->bindParam(1,$_SESSION['id']);
            $stmt->execute();
            $mdp = $stmt->fetch();
            if(password_verify($_POST['amdp'],$mdp['mdp'])){
                $stmt = $conn->prepare("UPDATE utilisateur SET mdp = ? WHERE id_util  = ?");
                $stmt->bindParam(1,$newMdp);
                $stmt->bindParam(2,$_SESSION['id']);
                $stmt->execute();
            }else{
                $errlog = "Mot de passe incorrect.";
            }
        }if(isset($_FILES["profil"]) && $_FILES["profil"]["error"] == 0){
            echo 'test';
            $imageFileType = strtolower(pathinfo($_FILES["profil"]["name"],PATHINFO_EXTENSION));
            $target_file =  "img/profil/" . $_SESSION['id'] . "." . $imageFileType;
            $uploadOk = 1;
            if ($_FILES["profil"]["size"] > 500000) {
                $errlog = "Désolé, votre fichier est trop volumineux.";
                $uploadOk = 0;
            }elseif(!in_array($imageFileType, ['jpg', 'png', 'jpeg'])) {
                $errlog = "Désolé, seuls les fichiers JPG, JPEG, PNG sont autorisés.";
                $uploadOk = 0;
            }elseif($uploadOk == 0) {
                $errlog = "Désolé, votre fichier n'a pas été téléchargé.";
            }else{
                if (file_exists($target_file)) {
                    unlink($target_file);
                }
                if(move_uploaded_file($_FILES["profil"]["tmp_name"], $target_file)) { 
                    $stmt = $conn->prepare("UPDATE utilisateur SET img_profil = ? WHERE id_util  = ?");
                    $stmt->bindParam(1,$target_file);
                    $stmt->bindParam(2,$_SESSION['id']);
                    $stmt->execute();
                }else {
                    $errlog = "Désolé, une erreur s'est produite lors du téléchargement de votre fichier.";
                }
            }
        }
    }
    
?>
<br>
<div class="inscrire">
    <?php echo '<p style="color:red">'.$errlog ?>
    <form method="post" enctype="multipart/form-data">
        <input type="hidden" name="form" value="c">

        <h2>Pseudo</h2>
        <input type="text" name="pseudo" placeholder="<?php echo $_SESSION['user']; ?>" size="<?php echo strlen($_SESSION['user']); ?>">

        <h2>Email</h2>
        <input type="text" name="email" placeholder="<?php echo $_SESSION['email']; ?>" size="<?php echo strlen($_SESSION['email']); ?>">

        <h2>Mot de passe</h2>
        <input type="password" name="amdp" placeholder="Actuel Mot de passe">
        <input type="password" name="nmdp" placeholder="Nouveau Mot de passe">

        <h2>Image profil</h2>
        
        <input type="file" accept="image/*" name="profil" id="profil">
        <button type="submit">Valider</button>
        <br>
    </form>
</div>
<br>
<?php
    require_once 'footer.php';

?>