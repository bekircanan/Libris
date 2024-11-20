<?php
    require_once 'header.php';

    // faire une constante pour verif si le mec a payer pour garder la pop up afficher apres le rechargement de pages (si true on passe le display en flex, si false on passe le display en none)
    use PHPMailer\PHPMailer\PHPMailer;
    require '..\vendor\autoload.php';
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
        $mail->Username = "Libris@gmail.com";
        $mail->Password = "igmbkulkriqskwey";
        $mail->AddAddress($email);
        $mail->SetFrom("Libris@gmail.com");
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


    try {
        $conn = new PDO("mysql:host=localhost;dbname=libris", 'root', '');
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    } catch (PDOException $e) {
        die($e->getMessage());
    }

    $stmt = $conn->prepare("SELECT DISTINCT l.titre_livre, a.nom_auteur, e.prix, achat.id_achat, e.lien_PDF
                            FROM achat_ebook achat JOIN ebook e ON e.id_ebook = achat.id_ebook 
                                                JOIN livre l ON e.id_livre = l.id_livre 
                                                JOIN a_ecrit ae ON ae.id_livre = l.id_livre 
                                                JOIN auteur a ON ae.id_auteur = a.id_auteur 
                            WHERE id_util LIKE :idUtilisateur "); //AND regle = 0
        
    $stmt->execute([':idUtilisateur' =>3]); //modifier avec l'id de l'utilisateur qui a ouvert la session
    $achatEbook = $stmt->fetchAll();
    
    $prixTotal = 0;
    $listeLienEbook = '';
    foreach($achatEbook as $ebook){
        $prixTotal = $prixTotal + $ebook['prix'];
        $listeLienEbook = $listeLienEbook . ' ' . $ebook['titre_livre'] . ' : ' . $ebook['lien_PDF']; //"\n"
       
    }
    
    if($_SERVER['REQUEST_METHOD'] === 'POST'){
        if(isset($_POST['supprimer']) && !isset($_POST['payer'])){
            $stmtDeleteEbook = $conn->prepare(
                "DELETE FROM achat_ebook WHERE id_ebook = {$_POST['supprimer']}"
            );
            $stmtDeleteEbook->execute();

        }else if(isset($_POST['payer']) && !isset($_POST['supprimer'])){
            $stmt = $conn->prepare("SELECT email FROM utilisateur WHERE id_util = :idUtilisateur");
            //$stmt2 = $conn2->prepare("UPDATE achat_ebook SET regle = 1, date_achat = TO_CHAR(SYSDATE, 'DD/MM/YYYY HH24:MI:SS') WHERE id_util = :idUtilisateur");
            $stmt->execute([':idUtilisateur' =>3]); //modifier avec l'id de l'utilisateur qui a ouvert la session
            $user = $stmt->fetch();

            // si l'utilisateur existe, on crée un token et on l'envoie par mail
            if($user){
                $token = bin2hex(random_bytes(16));
                $expire = date('Y-m-d H:i:s', time() + 60 * 15);
                $stmt = $conn->prepare("INSERT INTO token (nom_token, expire, id_util) VALUES (:nomToken, :expire, :idUtilisateur)");
                $stmt->execute([':expire' => $expire, ':nomToken' => $token, ':idUtilisateur' =>3]);//modifier avec l'id de l'utilisateur qui a ouvert la session
                $errlog = smtp($user['email'], 'Merci pour votre achat sur Libris !', 'Merci pour cette transaction sur Libris.' . $listeLienEbook);
            } else {
                $errlog ='Aucun compte n\'est associé à cet email.';
            }
        }
        
    }
    ?>


    

    <h1 id="mon_panier">Mon panier</h1>
    <div class="prix_section">
    
        <section id="prix_total">
            
            <div id="prix">
                <p>Prix Total</p>
                <p><?php echo $prixTotal. '€'?></p>
            </div>
            <hr> 
            <div id="payer">
                <a href=".\catalogue.php"> < Retour au catalogue</a> 
                <form method="POST">
                    <button name="payer" class="bouton_payer">Payer</button>
                </form>

            </div>

            
                
            
        </section>
        <div class="mask"></div>
        <div class="modal">
            <p>Un mail a été envoyé</p>
            <button href="panier.php"class="close">X</button>
        </div>

        <script>
            let mask = document.querySelector(".mask")
            let modal = document.querySelector(".modal")

            let test = document.querySelector(".close");
            test.addEventListener('click',closePopUp);
            let test2 = document.querySelector(".bouton_payer");
            test2.addEventListener('click',openPopUp);

            function openPopUp() {
                modal.classList.toggle("modal_change");
                mask.classList.toggle("mask_change"); // Show the background overlay
            }

            function closePopUp() {
                modal.classList.toggle("modal");
                mask.classList.toggle("mask"); // Hide the background overlay
            }
        </script>
        
        <?php
            foreach($achatEbook as $ebook){
                echo '<div class="ebook_acheter">'; 
                echo '<p>'. 'imageaaaaaaaa' . '</p>';
                echo '<div class="info_ebook_acheter">';
                echo '<p>' . $ebook['titre_livre'] . '</p>';
                echo '<p>' . $ebook['nom_auteur'] . '</p>';
                echo '<p>' . $ebook['prix'] . '€</p>';
                echo '</div>'; 
                echo '<form method="POST"><button name="supprimer" value =" '. $ebook['id_achat'] . '">Supprimer <img id="poubelle" src="./img/icon_poubelle.png" alt="Icône de poubelle" </button> </form>';
                echo '</div>'; 
            } 
        ?>
    </div>
    
<?php
    require_once 'footer.php';
?>