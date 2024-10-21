<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Libris</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    <header>
        <a class="open" onclick="toggleNav()">
            <span></span>
            <span></span>
            <span></span>
        </a>
        <div class="search-bar-container">
            <div class="search-bar">
                <form method="post">
                    <input type="text" placeholder="Rechercher un livre...">
                    <button type="submit"><i class="fa-solid fa-magnifying-glass"></i></button>
                </form>
                <a class="advanced-search">Recherche avancé</a>
            </div>
        </div>
        <div>
        <a href="index.php"><img src="img/logo.png" alt="Logo"></a>
        </div>
    </header>
    <div id="mySidebar" class="sidebar">
        <ul>
            <li><a href="index.php" class="active"><i class="fa-solid fa-house"></i> Decouvrir</a></li>
            <li><a href="#"><i class="fa-solid fa-book-open"></i> Catalogue</a></li>
        </ul>
        <br>
        <ul>
            <li class="sign-buttons"><a href="#"><i class="fa-solid fa-user-plus"></i> S'inscrire</a></li>
            <li class="sign-buttons"><a href="#"><i class="fa-solid fa-sign-in-alt"></i> Se connecter</a></li>
        </ul>
    </div>
    <script>
        const open = document.querySelector('.open');
        open.addEventListener('click', () => {
            open.classList.toggle('active');
        });
        function toggleNav() {
            const sidebar = document.getElementById("mySidebar");
            if (sidebar.style.width === "250px") {
                sidebar.style.width = "0";
                document.body.style.marginLeft = "0";
            } else {
                sidebar.style.width = "250px";
                document.body.style.marginLeft = "250px";
            }
        }
    </script>
    <main>