<?php
    // Démarrer la session si elle n'est pas déjà démarrée
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    // Connexion à la base de données
    try {
        $conn = new PDO("mysql:host=localhost;dbname=libris", 'root', '');
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    } catch (PDOException $e) {
        die($e->getMessage());
    }

    // Afficher les erreurs
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);

    // Définir les pages actuelles et les pages administratives
    $pageActuelle = basename($_SERVER['PHP_SELF']);
    $pageadmin = ['gestion-emprunts-reservations.php', 'gestion-livres.php', 'gestion-utilisateurs.php'];
    $pc = $_SERVER['HTTP_USER_AGENT'];
    $typePc = ['Windows', 'Mac'];
    $pageUtil = ['mes-reservations.php', 'mes-ebooks.php', 'panier.php', 'compte.php'];
    $allow=true;
    if((stripos($pc, $typePc[1]) || stripos($pc, $typePc[0]) )){
        $allow=false;
    }
    // Rediriger les utilisateurs non autorisés
    if ((!isset($_SESSION['user']) || $_SESSION['admin'] !== 1 || $allow) && in_array($pageActuelle, $pageadmin) ) {
        header('Location: index.php');
        exit();
    }elseif((!isset($_SESSION['user']) || $_SESSION['admin'] !== 0) && in_array($pageActuelle, $pageUtil)){
        header('Location: index.php');
        exit();
    }elseif(isset($_SESSION['user']) && $pageActuelle === 'inscrire.php'){
        header('Location: index.php');
        exit();
    }elseif(isset($_SESSION['user'])){
        $stmt = $conn->prepare("SELECT regle from achat_ebook where id_util = ? and regle = 0");
        $stmt->bindParam(1, $_SESSION['id']);
        $stmt->execute();
        $regle = $stmt->fetch();
        if($regle){
            $notif='style = "color: red;"';
        }
    }

    $errlog = "<p style='color:red;'>";
    // Traitement du formulaire de connexion
    if ($_SERVER["REQUEST_METHOD"] == "POST" && $_POST['form'] === 'connect' ) {
        // Vérifier si les champs sont remplis
        if (isset($_POST['mdp']) && isset($_POST['email'])) {
            // Récupérer l'utilisateur
            $stmt = $conn->prepare("SELECT id_util, pseudo, email, mdp FROM utilisateur WHERE email like ? OR pseudo like ?");
            $stmt->bindParam(1, $_POST['email']);
            $stmt->bindParam(2, $_POST['email']);
            $stmt->execute();
            $user = $stmt->fetch();
            // Vérifier si l'utilisateur existe
            if ($user) {
                // Vérifier le mot de passe
                if (password_verify($_POST['mdp'], $user['mdp'])) {
                    // Créer la session
                    $_SESSION['user'] = $user['pseudo'];
                    $_SESSION['email'] = $user['email'];
                    $_SESSION['id'] = $user['id_util'];
                    $stmt = $conn->prepare("SELECT id_util FROM bibliotecaire WHERE id_util = ?");
                    $stmt->bindParam(1, $user['id_util']);
                    $stmt->execute();
                    $iduser = $stmt->fetch();
                    // Vérifier si l'utilisateur est un administrateur
                    if ($iduser) {
                        $_SESSION['admin'] = 1;
                    } else {
                        $_SESSION['admin'] = 0;
                    }
                    header('Location: index.php');
                    exit();
                } else {
                    $errlog .= "mot de passe incorrect.</p>";
                    echo "<script>document.addEventListener('DOMContentLoaded', function() { ouvreNav(); popup(); });</script>";
                }
            } else {
                $errlog .= "Utilisateur non trouvé.</p>";
                echo "<script>document.addEventListener('DOMContentLoaded', function() { ouvreNav(); popup(); });</script>";
            }
        } else {
            $errlog .= "Veuillez remplir tous les champs.</p>";
            echo "<script>document.addEventListener('DOMContentLoaded', function() { ouvreNav(); popup(); });</script>";
        }
    }
?>
<!DOCTYPE html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Libris</title>
    <link rel="icon" href="img/logo.png">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <script src="js/icon.js"></script>
</head>
<body>
    <header>
        <?php
            if(isset($notif)){
                $notif2='style = "background-color: red;"';
            }
        ?>
            <span class="open" onclick="ouvreNav()">
                <span <?php echo isset($notif2) ? $notif2 : ''; ?>></span>
                <span <?php echo isset($notif2) ? $notif2 : ''; ?>></span>
                <span <?php echo isset($notif2) ? $notif2 : ''; ?>></span>
            </span>
        <div class="search-bar-container">
            <div class="search-bar">
                <form method="get" action="./catalogue.php">
                    <input type="hidden" name="form" value="search">
                    <input type="text" name="recherche" placeholder="Rechercher un livre...">
                    <button type="submit"><i class="fa-solid fa-magnifying-glass"></i></button>
                </form>
                <a class="advanced-search" href="./recherche_avance.php">Recherche avancé</a>
            </div>
        </div>
        <div>
            <a href="index.php"><img src="img/logo.png" alt="Logo"></a>
        </div>
    </header>
    <div id="Sidebar" class="sidebar">
        <ul>
            <a href="javascript:void(0)" class="closebtn" onclick="fermeNav()">&times;</a>
            <a href="index.php" class="active"><i class="fa-sharp fa-regular fa-house"></i> Decouvrir</a>
            <a href="catalogue.php"><i class="fa-regular fa-book-open"></i> Catalogue</a>
            <?php 
            // Afficher les liens en fonction de l'utilisateur
            if (isset($_SESSION['user'])) {
                if (isset($_SESSION['admin']) && $_SESSION['admin'] === 1) {
                    echo '<li><a href="./gestion-utilisateurs.php"><i class="fa-sharp fa-regular fa-scroll"></i> Gestion des comptes</a></li>';
                    echo '<li><a href="./gestion-livres.php"><i class="fa-sharp fa-thin fa-books"></i> Gestion des livres</a></li>';
                    echo '<li><a href="./gestion-emprunts-reservations.php"><i class="fa-sharp fa-thin fa-books"></i> Gestion des emprunts/reservations</a></li>';
                } else {
                    echo '<li><a href="./mes-reservations.php"><i class="fa-sharp fa-regular fa-scroll"></i> Mes réservations</a></li>';
                    echo '<li><a href="./mes-ebooks.php"><i class="fa-sharp fa-thin fa-books"></i> Mes e-books</a></li>';
                    echo '<li><a href="./panier.php"><i class="fa-regular fa-basket-shopping"'.(isset($notif) ? $notif : '').'></i> Mon panier</a></li>';
                }    
            }?>
        </ul>
        <br>
        <ul>
            <?php if (isset($_SESSION['user'])) { 
                // Afficher les liens en fonction de l'utilisateur
                if (isset($_SESSION['admin']) && $_SESSION['admin'] === 0) {
                    echo '<li><a href="compte.php"><i class="fa-regular fa-gear"></i> Paramètres du compte</a></li>';
                }
                echo '<li><a href="deconnexion.php"><i class="fa-solid fa-sign-out-alt"></i> Se déconnecter</a></li>';
            } else {
                echo '<li class="sign-buttons"><a href="inscrire.php"><i class="fa-sharp fa-light fa-user-plus"></i> S\'inscrire</a></li>';
                echo '<li class="sign-buttons popup">
                        <a href="#" onclick="popup()"><i class="fa-solid fa-sign-in-alt"></i> Se connecter</a>
                        <form class="popuptext" id="popup" method="POST">
                            <input type="hidden" name="form" value="connect">
                            Connectez-vous :
                            '.$errlog.'
                            <input type="text" name="email" placeholder="Email/Pseudo">
                            <input type="password" name="mdp" placeholder="Mot de passe">
                            <button type="submit">Valider</button>
                        </form>
                    </li>';
            } ?>
        </ul>
    </div>
    
    <script>
        // Fonctions pour ouvrir et fermer la barre de navigation
        function ouvreNav() {
            document.getElementById("Sidebar").style.width = "300px";
        }
        
        function fermeNav() {
            document.getElementById("Sidebar").style.width = "0";
        }

        // Fonction pour ouvrir et fermer la popup
        function popup() {
            var popup = document.getElementById("popup");
            popup.classList.toggle("show");
        }
    </script>
    <main>