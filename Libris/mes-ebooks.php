<?php
require('header.php');


$stmtEbooks = $conn->prepare("
    SELECT l.id_livre, l.img_couverture, l.titre_livre
    FROM achat_ebook ae
    INNER JOIN ebook e ON ae.id_ebook = e.id_ebook
    INNER JOIN livre l ON e.id_livre = l.id_livre
    WHERE ae.id_util = :idUtilisateur and ae.regle = 1
");
$stmtEbooks->execute([':idUtilisateur' => $_SESSION['id']]);
$ebooks = $stmtEbooks->fetchAll();
?>

<!DOCTYPE html>
<html lang="FR">
<head>
    <title>Mes e-books</title>
    <meta charset="UTF-8">
</head>
<body>

<div class="reservations-ebooks">
    <h1>Mes e-books</h1>
    <?php
    /* Afficher les e-books possédés par l'utiliateur */
    echo '<div class="mes-ebooks">';
    echo '<div class="livres">';
    foreach ($ebooks as $ebook) {
        echo '<div class="livre">';
        echo '<a href="info_livre.php?id_livre=' . $ebook['id_livre'] . '">'; /* Lien vers la page info-livre */
        echo '<img class="imgCouverture" src="'.$ebook['img_couverture'].'" alt="Couverture du livre">';
        echo '</a>';
        echo '<h3>'.$ebook['titre_livre'].'</h3>';
        echo '</div>';
    }
    echo '</div>';
    echo '</div>';
    ?>
</div>
</body>
</html>

<?php require("footer.php"); ?>