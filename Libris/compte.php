<?php
    require_once 'header.php';

    if(!isset($_SESSION['user'])){
        header('Location: index.php');
        exit;
    }
        if(isset($_POST['pseudo']) && !empty($_POST['pseudo'])){
            echo $_POST['pseudo'];
            $newPseudo = $_POST['pseudo'];
            $stmt = $conn->prepare("UPDATE utilisateur SET pseudo = ? WHERE id_util  = ?");
            $stmt->bindParam(1,$newPseudo);
            $stmt->bindParam(2,$_SESSION['id']);
            $stmt->execute();
            $_SESSION['user'] = $newPseudo;
        }else if(isset($_POST['email']) && !empty($_POST['email'])){
            $newEmail = $_POST['email'];
            $stmt = $conn->prepare("UPDATE utilisateur SET email = ? WHERE id_util  = ?");
            $stmt->bindParam(1,$newEmail);
            $stmt->bindParam(2,$_SESSION['id']);
            $stmt->execute();
            $_SESSION['email'] = $newEmail;
        }else if(isset($_POST['amdp']) && !empty($_POST['amdp']) && isset($_POST['nmdp']) && !empty($_POST['nmdp'])){ 
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
        }
    
?>
<br>
<div class="inscrire">
    <?php echo '<p style="color:red">'.$errlog ?>
    <form method="post">
        <h2>Modifier Pseudo</h2>
        <input type="hidden" name="form" value="c">
        <input type="text" name="pseudo" placeholder="<?php echo $_SESSION['user']; ?>" size="<?php echo strlen($_SESSION['user']); ?>">
        <button type="submit">Valider</button>
    </form>
    <form method="post">
        <h2>Modifier Email</h2>
        <input type="hidden" name="form" value="c">
        <input type="text" name="email" placeholder="<?php echo $_SESSION['email']; ?>" size="<?php echo strlen($_SESSION['email']); ?>">
        <button type="submit">Valider</button>
    </form>

    <form method="post">
        <h2>Modifier Mot de passe</h2>
        <input type="hidden" name="form" value="c">
        <input type="password" name="amdp" placeholder="Current Mot de passe">
        <input type="password" name="nmdp" placeholder="New Mot de passe">
        <button type="submit">Valider</button>
    </form>
</div>
<br>
<?php
    require_once 'footer.php';

?>