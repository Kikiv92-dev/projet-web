<?php
// ... (Initialisation et config.php)

// 3. TRAITEMENT DU FORMULAIRE (DOIT ÊTRE AU DÉBUT !)
if($_SERVER["REQUEST_METHOD"] == "POST"){

    $table_name = "utilisateurs"; 

    if(empty(trim($_POST["username"]))){
        $username_err = "Veuillez entrer un nom d'utilisateur.";
    } else {
        // 2. Préparer l'instruction SELECT uniquement si le champ n'est PAS vide
        $sql = "SELECT id FROM $table_name WHERE username = ?";

        if($stmt = mysqli_prepare($conn, $sql)){
            // Assigner et lier le paramètre pour la vérification d'existence
            $param_username = trim($_POST["username"]);
            mysqli_stmt_bind_param($stmt, "s", $param_username);
            
            if(mysqli_stmt_execute($stmt)){
                mysqli_stmt_store_result($stmt);
                
                if(mysqli_stmt_num_rows($stmt) == 1){
                    // L'utilisateur existe déjà
                    $username_err = "Ce nom d'utilisateur est déjà pris.";
                } else {
                    // L'utilisateur est unique, on assigne la variable pour l'INSERT
                    $username = $param_username;
                }
            } else{
                $username_err = "Oops! Quelque chose a mal tourné. Veuillez réessayer plus tard.";
            }
            mysqli_stmt_close($stmt);
        }
    }
    // ************ FIN DE CORRECTION ************
    
    // Valider le mot de passe
    $min_password_length = 8; // Meilleure pratique : utiliser 8 au lieu de 6
    if(empty(trim($_POST["password"]))){
        $password_err = "Veuillez entrer un mot de passe.";     
    } elseif(strlen(trim($_POST["password"])) < $min_password_length){
        $password_err = "Le mot de passe doit contenir au moins " . $min_password_length . " caractères.";
    } else{
        $password = trim($_POST["password"]);
    }
    
    // Vérifier les erreurs avant d'insérer dans la base de données
    if(empty($username_err) && empty($password_err)){
        
        $sql = "INSERT INTO $table_name (username, password) VALUES (?, ?)";
          
        if($stmt = mysqli_prepare($conn, $sql)){
            mysqli_stmt_bind_param($stmt, "ss", $param_username, $param_password);
            
            // Les variables sont assignées APRES le bind_param, c'est l'ordre correct pour mysqli
            $param_username = $username;
            $param_password = password_hash($password, PASSWORD_DEFAULT);
            
            // Tenter d'exécuter l'instruction préparée
            if(mysqli_stmt_execute($stmt)){
                header("location: login.php?status=success_inscription");
                exit;
            } else{
                // Dans un environnement de production, n'affichez JAMAIS mysqli_error() à l'utilisateur !
                error_log("DB Error: " . mysqli_error($conn)); 
                echo "Une erreur interne s'est produite lors de l'enregistrement.";
            }
            mysqli_stmt_close($stmt);
        }
    }
    
    // Fermer la connexion
    mysqli_close($conn);
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
 <meta charset="UTF-8">
 <meta name="viewport" content="width=device-width, initial-scale=1.0">
 <title>Inscription - Guardia</title>
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
    <h1>S'inscrire</h1>
    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST" class="login-form">
        
        <label for="username">Nom d'utilisateur :</label>
        <input type="text" id="username" name="username" 
            value="<?php echo htmlspecialchars($username, ENT_QUOTES, 'UTF-8'); ?>"
            class="<?php echo (!empty($username_err)) ? 'is-invalid' : ''; ?>" required>
        <?php 
        if(!empty($username_err)){
            echo '<span class="invalid-feedback">' . $username_err . '</span>';
        }
        ?>

        <label for="password">Mot de passe :</label>
        <input type="password" id="password" name="password" 
            class="<?php echo (!empty($password_err)) ? 'is-invalid' : ''; ?>" required>
        <?php 
        if(!empty($password_err)){
            echo '<span class="invalid-feedback">' . $password_err . '</span>';
        }
        ?>

        <button type="submit">S'inscrire</button>
        <a href="login.php" class="register-link">Se connecter</a>
    </form>
</main>

 <script src="js1.js"></script>

</body>
</html>