<?php
    require_once 'header.php';
    
    use PHPMailer\PHPMailer\PHPMailer;
    require '.\vendor\autoload.php';
    $errlog = '';

    function smtp($email, $subject, $body){
        $mail = new PHPMailer();
        $mail->IsSMTP(); 
        $mail->SMTPAuth = true;
        $mail->SMTPSecure = "tls";
        $mail->Host = "smtp.gmail.com";
        $mail->Port = 587;
        $mail->isHTML(true);
        $mail->CharSet = 'UTF-8';
        $mail->Username = "Libris.supp@gmail.com";
        $mail->Password = "ajjulessggrafgrq";
        $mail->AddAddress($email);
        $mail->SetFrom("Libris-supp@gmail.com");
        $mail->Subject = $subject;
        $mail->Body = $body;
        $mail->SMTPOptions=array('ssl'=>array(
            'verify_peer'=>false,
            'verify_peer_name'=>false,
            'allow_self_signed'=>false
        ));
        if(!$mail->Send()){
            return 'Erreur: '.$mail->ErrorInfo;
        }else{
            return 'Mail envoyé';
        }
    }

    $stmtAfficheEbook = $conn->prepare("SELECT DISTINCT l.id_livre, l.titre_livre, e.prix, achat.id_achat, e.lien_PDF, l.img_couverture
                                        FROM achat_ebook achat JOIN ebook e ON e.id_ebook = achat.id_ebook 
                                                JOIN livre l ON e.id_livre = l.id_livre 
                                                JOIN a_ecrit ae ON ae.id_livre = l.id_livre 
                                                JOIN auteur a ON ae.id_auteur = a.id_auteur 
                                        WHERE id_util LIKE :idUtilisateur AND regle = 0");
        
    $stmtAfficheEbook->execute([':idUtilisateur' => $_SESSION['id']]);
    $achatEbook = $stmtAfficheEbook->fetchAll();

    $stmtAuteur = $conn->prepare("SELECT DISTINCT a.nom_auteur, a.prenom_auteur 
                                    FROM auteur a LEFT OUTER JOIN a_ecrit ae ON a.id_auteur = ae.id_auteur
                                    WHERE id_livre = :id_livre");
    
    $prixTotal = 0;
    $listeLienEbook = '';
    foreach($achatEbook as $ebook){
        $prixTotal = $prixTotal + $ebook['prix'];
        $listeLienEbook = $listeLienEbook . ' ' . $ebook['titre_livre'] . ' : ' . $ebook['lien_PDF']; //"\n"
    }

    if(!isset($_SESSION['id'])){
        header("Location: ./index.php");
        exit;
    }else{
        if($_SERVER['REQUEST_METHOD'] === 'POST'){
            if ($_POST['form']=== 'supprimer' || $_POST['form']=== 'payer'){
                if(isset($_POST['supprimer']) && !isset($_POST['payer'])){
                    $stmtDeleteEbook = $conn->prepare("DELETE FROM achat_ebook WHERE id_achat = :idAchat");
                    $stmtDeleteEbook->execute([':idAchat' => $_POST['supprimer']]);
        
                }else if(isset($_POST['payer']) && !isset($_POST['supprimer']) && !isset($achatEbook)){
                    $stmtRecupEmail = $conn->prepare("SELECT email FROM utilisateur WHERE id_util = :idUtilisateur");
                    $stmtRecupEmail->execute([':idUtilisateur' => $_SESSION['id']]); 
                    $user = $stmtRecupEmail->fetch();
        
                    $stmtModifDateAchat = $conn->prepare("UPDATE achat_ebook SET regle = 1, date_achat = NOW() WHERE id_util = :idUtilisateur");
                    $stmtModifDateAchat->execute([':idUtilisateur' => $_SESSION['id']]);
                    echo "<script> openPopUp() </script>";
                    header("Refresh:0");
        
                    // si l'utilisateur existe, on crée un token et on l'envoie par mail
                    if($user){
                        $token = bin2hex(random_bytes(16));
                        $expire = date('Y-m-d H:i:s', time() + 60 * 15);
                        $stmt = $conn->prepare("INSERT INTO token (nom_token, expire, id_util) VALUES (:nomToken, :expire, :idUtilisateur)");
                        $stmt->execute([':expire' => $expire, ':nomToken' => $token, ':idUtilisateur' => $_SESSION['id']]);
                        $errlog = smtp($user['email'], 'Merci pour votre achat sur Libris !', 'Merci pour cette transaction sur Libris. Voici le lien de téléchargement de chaques livres : '. "\n" . $listeLienEbook);
                    } else {
                        $errlog ='Aucun compte n\'est associé à cet email.';
                    }
                }
            }
        }
        
        
    }
    
?>

    <h1 id="mon_panier">Mon panier</h1>
    <div class="prix_section">
    
        <section id="prix_total">
            
            <div id="prix">
                <p>Prix Total</p>
                <p><?php echo ($prixTotal === 0 ? "-" : $prixTotal . "€");?></p>
            </div>
            <hr> 
            <div id="payer">   
                <div>    
                    <a href=".\catalogue.php"> < Retour au catalogue</a> 
                </div>
                <form method="POST">
                    <input type="hidden" name="form" value="payer">
                    <button type="sumbit" name="payer" class="bouton_payer" <?php echo (empty($achatEbook)?  "disabled" : "")?>>Payer</button>
                </form>

            </div>

        </section>
        
        <!-- affichage du fond noire derrière la pop up -->
        <div class="mask"></div>
        <!-- pop up -->
        <div class="modal">
            <p>Un mail a été envoyé</p>
            <i class="fa-solid fa-xmark fa-2xl close"></i>
        </div>
        
        <?php
            if ($prixTotal === 0){
                echo '<h1>Votre panier est vide</h1>';
                echo '<a href=".\catalogue.php">Choisir des ebooks</a>';
            }else{
                foreach($achatEbook as $ebook){
                    $lesAuteurs = '';
                    echo '<div class="ebook_acheter">'; 
                    echo '<img src='. $ebook['img_couverture'] . '></img>';
                    echo '<div class="info_ebook_acheter">';
                    echo '<p>' . $ebook['titre_livre'] . '</p>';
                    
                    $stmtAuteur->execute([':id_livre' => $ebook['id_livre']]);
                    $Auteurs = $stmtAuteur->fetchAll();
                    foreach($Auteurs as $aut){
                        $lesAuteurs = $lesAuteurs . $aut['prenom_auteur'] . " " . $aut['nom_auteur']. " ";
                    }
                    echo '<p>' . $lesAuteurs . '</p>';
                    echo '<p>' . $ebook['prix'] . '€</p>';
                    echo '</div>'; 
                    echo '<form method="POST"><input type="hidden" name="form" value="supprimer"><button name="supprimer" value =" '. $ebook['id_achat'] . '">Supprimer <i class="fa-solid fa-trash"></i> </button> </form>';
                    echo '</div>'; 
                } 
            }
        ?>
    </div>

    <!-- script qui permet d'afficher une pop up confirmant l'envoie du mail -->
    <script>
            
            let mask = document.querySelector(".mask");
            let modal = document.querySelector(".modal");
            let body = document.querySelector("body");

            let boutonFermer = document.querySelector(".close");
            boutonFermer.addEventListener('click',closePopUp);
            
            let boutonOuvrir = document.querySelector(".bouton_payer");
            

            if(localStorage.getItem("popUp")){
                openPopUp()
            }

            function openPopUp() {
                
                modal.classList.toggle("modal_change");
                mask.classList.toggle("mask_change");
                localStorage.setItem("popUp", true);
                body.style.overflow = hidden;
            }

            function closePopUp() {
               
                modal.classList.toggle("modal_change");
                mask.classList.toggle("mask_change");
                localStorage.removeItem("popUp");  
                body.style.overflow = scroll;
            }
    </script>
    
<?php
    require_once 'footer.php';
?>