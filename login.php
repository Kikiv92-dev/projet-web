<?php

require_once "connexion.php"

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion - Guardia</title>
    <link rel="stylesheet" href="login.css">
</head>
<body>

    <canvas id="matrixCanvas"></canvas>

    <header class="navbar">
        <nav>
            <ul>
                <li><a href="index.html" class="nav-link">./accueil</a></li>
                <li><a href="bde.html" class="nav-link">./BDE</a></li>
                <li><a href="evenement.html" class="nav-link">./evenement</a></li>
                <li><a href="contact.html" class="nav-link">./contact</a></li>
                <li><a href="login.php" class="nav-link">./login</a></li>
            </ul>
        </nav>
    </header>

    <main class="content-section">
        <div class="logo-container">
            <img src="image.png" alt="Logo du Bureau des Étudiants ERROR" class="site-logo logo-bde">
            <img src="guardia-logo-104x100.png.webp" alt="Logo de l'école GUARDIA" class="site-logo logo-guardia">
        </div>

    <main class="content-interior">
        <h1>Connexion</h1>
        
        <?php
        // 1. AFFICHAGE DES MESSAGES DE SUCCÈS (après inscription)
        if (isset($_GET['status']) && $_GET['status'] == 'success_inscription') {
            echo '<div class="alert success-message">Inscription réussie ! Vous pouvez maintenant vous connecter.</div>';
        }
        ?>
        
        <?php 
        if(!empty($login_err)){
            echo '<div class="alert error-message">' . $login_err . '</div>';
        }        
        ?>

        <form action="login.php" method="POST" class="login-form">
            
            <label for="username">Nom d'utilisateur :</label>
            <input type="text" id="username" name="username" 
                   value="<?php echo $username ?? ''; ?>" required>
            
            <label for="password">Mot de passe :</label>
            <input type="password" id="password" name="password" required>
            
            <button type="submit">Se connecter</button>

            <a href="inscription.php" class="register-link">S'inscrire</a>
        </form>
    </main>

    <script src="js1.js"></script>

</body>
</html>