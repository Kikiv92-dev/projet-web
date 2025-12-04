<?php

session_start();
// Vérifier si l'utilisateur est déjà connecté
if (isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true) {
    // Rediriger si déjà connecté
    if ($_SESSION["role"] == "administrateur") {
        header("location: admin.php");
    } else {
        header("location: accueilmembre.php");
    }
    exit;
}

require_once "config.php"; // Pour charger $pdo
require_once "connexion.php"; // Votre fichier de connexion à la base

$username = $password = $login_err = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // Récupération et nettoyage des entrées
    $username = trim($_POST["username"]);
    $password = $_POST["password"];

    // 1. Requête préparée pour récupérer l'utilisateur
    $sql = "SELECT id, username, password_hash, role FROM users WHERE username = :username"; 
    
    if ($stmt = $pdo->prepare($sql)) {
        $stmt->bindParam(":username", $username, PDO::PARAM_STR);

        if ($stmt->execute()) {
            if ($stmt->rowCount() == 1) {
                
                // Un utilisateur trouvé
                if ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    $hashed_password = $row['password_hash'];
                    
                    // 2. Vérification du mot de passe
                    // Utilisez password_verify() pour une vérification sécurisée du HASH
                    if (password_verify($password, $hashed_password)) {
                        
                        // Mot de passe correct, Démarrer/Mettre à jour la session
                        $_SESSION["loggedin"] = true;
                        $_SESSION["id"] = $row["id"];
                        $_SESSION["username"] = $row["username"];
                        $_SESSION["role"] = $row["role"]; 
                        
                        // Redirection en fonction du rôle
                        if ($_SESSION["role"] == "administrateur") {
                            header("location: admin.php");
                        } else {
                            header("location: accueilmembre.php");
                        }
                        exit;

                    } else {
                        $login_err = "Nom d'utilisateur ou mot de passe invalide.";
                    }
                }
            } else {
                $login_err = "Nom d'utilisateur ou mot de passe invalide.";
            }
        } else {
            $login_err = "Oups! Quelque chose a mal tourné. Veuillez réessayer plus tard.";
        }
        unset($stmt);
    }
    // Fermer la connexion PDO (si elle n'est pas réutilisée)
    unset($pdo);
}

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
            // AJOUT DE htmlspecialchars() pour prévenir le XSS
            echo '<div class="alert error-message">' . htmlspecialchars($login_err) . '</div>';
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