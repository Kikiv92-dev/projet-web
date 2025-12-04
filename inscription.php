<?php
// =================================================================
// 1. GESTION DES ERREURS (CRITIQUE EN PRODUCTION !)
// =================================================================
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
error_reporting(E_ALL);

// 2. INCLURE LA CONFIGURATION DE LA BDD
require_once "config.php";

// AJOUTÉ : Variables pour le champ e-mail
$username = $email = $password = $confirm_password = "";
$username_err = $email_err = $password_err = $confirm_password_err = "";

// 3. TRAITEMENT DU FORMULAIRE
if($_SERVER["REQUEST_METHOD"] == "POST"){

    $table_name = "utilisateurs";
    
    // =================================================================
    // A. VALIDATION DU NOM D'UTILISATEUR (avec Regex pour l'assainissement)
    // =================================================================
    if(empty(trim($_POST["username"]))){
        $username_err = "Veuillez entrer un nom d'utilisateur.";
    } else{
        $input_username = trim($_POST["username"]);
        
        // Sécurité : Forcer un schéma alphanumérique pour l'username
        if (!preg_match('/^[a-zA-Z0-9_]{3,15}$/', $input_username)) {
            $username_err = "Le nom d'utilisateur doit contenir entre 3 et 15 caractères (lettres, chiffres et _).";
        } else {
            // Vérification de l'existence du nom d'utilisateur
            $sql = "SELECT id FROM $table_name WHERE username = ?";
            
            if($stmt = $conn->prepare($sql)){
                $stmt->bind_param("s", $param_username);
                $param_username = $input_username;
                
                if($stmt->execute()){
                    $stmt->store_result();
                    if($stmt->num_rows == 1){
                        $username_err = "Ce nom d'utilisateur est déjà pris.";
                    } else{
                        $username = $input_username;
                    }
                } else{
                    error_log("Erreur SQL (vérif username) : " . $stmt->error);
                    $username_err = "Oops! Une erreur interne est survenue. Veuillez réessayer plus tard.";
                }
                $stmt->close();
            }
        }
    }
    
    // =================================================================
    // NOUVEAU : D. VALIDATION DE L'EMAIL
    // =================================================================
    if(empty(trim($_POST["email"]))){
        $email_err = "Veuillez entrer une adresse e-mail.";
    } elseif (!filter_var(trim($_POST["email"]), FILTER_VALIDATE_EMAIL)) {
        $email_err = "Le format de l'adresse e-mail n'est pas valide.";
    } else {
        $input_email = trim($_POST["email"]);

        // Vérification de l'existence de l'e-mail dans la base de données
        $sql = "SELECT id FROM $table_name WHERE email = ?";
        
        if($stmt = $conn->prepare($sql)){
            $stmt->bind_param("s", $param_email);
            $param_email = $input_email;
            
            if($stmt->execute()){
                $stmt->store_result();
                if($stmt->num_rows == 1){
                    $email_err = "Cette adresse e-mail est déjà utilisée.";
                } else{
                    $email = $input_email;
                }
            } else{
                error_log("Erreur SQL (vérif email) : " . $stmt->error);
                $email_err = "Oops! Une erreur interne est survenue. Veuillez réessayer plus tard.";
            }
            $stmt->close();
        }
    }

    // =================================================================
    // B. VALIDATION DU MOT DE PASSE ET CONFIRMATION
    // =================================================================
    if(empty(trim($_POST["password"]))){
        $password_err = "Veuillez entrer un mot de passe.";
    } else{
        $input_password = trim($_POST["password"]);
        
        // --- 1. Règle de Longueur ---
        if(strlen($input_password) < 10){
            $password_err = "Le mot de passe doit contenir au moins 10 caractères.";
        // --- 2. Règle de Complexité
        } elseif (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[^a-zA-Z\d]).{10,}$/', $input_password)) {
            $password_err = "Le mot de passe doit contenir au moins une majuscule, une minuscule, un chiffre et un caractère spécial.";
        } else {
            $password = $input_password;
        }
    }

    // Validation de la confirmation
    if(empty(trim($_POST["confirm_password"]))){
        $confirm_password_err = "Veuillez confirmer le mot de passe.";
    } else{
        $confirm_password = trim($_POST["confirm_password"]);
        if(empty($password_err) && ($password != $confirm_password)){
            $confirm_password_err = "Les mots de passe ne correspondent pas.";
        }
    }
    
    // =================================================================
    // C. INSCRIPTION DANS LA BASE DE DONNÉES (après validation)
    // ATTENTION : 'email' ajouté dans la requête SQL et dans bind_param
    // =================================================================
    if(empty($username_err) && empty($email_err) && empty($password_err) && empty($confirm_password_err)){
        
        $sql = "INSERT INTO $table_name (username, email, password) VALUES (?, ?, ?)";
          
        if($stmt = $conn->prepare($sql)){
            // Modifié : Ajout du 's' pour l'email et de la variable $param_email
            $stmt->bind_param("sss", $param_username, $param_email, $param_password);
            
            $param_username = $username;
            $param_email = $email; // Le nouvel e-mail
            $param_password = password_hash($password, PASSWORD_DEFAULT);
            
            if($stmt->execute()){
                // SUCCÈS : REDIRECTION ET ARRÊT DU SCRIPT
                header("location: login.php?status=success_inscription");
                exit;
            } else{
                error_log("Erreur SQL lors de l'insertion de l'utilisateur : " . $stmt->error);
                echo "Une erreur s'est produite lors de l'inscription. Veuillez réessayer plus tard.";
            }
            $stmt->close();
        }
    }
    
    // Fermer la connexion
    $conn->close();
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
                value="<?php echo htmlspecialchars($username ?? ''); ?>"
                class="<?php echo (!empty($username_err)) ? 'is-invalid' : ''; ?>" required>
            <?php
            if(!empty($username_err)){
                echo '<span class="invalid-feedback">' . htmlspecialchars($username_err) . '</span>';
            }
            ?>

            <label for="email">E-mail :</label>
            <input type="email" id="email" name="email"
                value="<?php echo htmlspecialchars($email ?? ''); ?>"
                class="<?php echo (!empty($email_err)) ? 'is-invalid' : ''; ?>" required>
            <?php
            if(!empty($email_err)){
                echo '<span class="invalid-feedback">' . htmlspecialchars($email_err) . '</span>';
            }
            ?>

            <label for="password">Mot de passe :</label>
            <input type="password" id="password" name="password"
                class="<?php echo (!empty($password_err)) ? 'is-invalid' : ''; ?>" required>
            <?php
            if(!empty($password_err)){
                echo '<span class="invalid-feedback">' . htmlspecialchars($password_err) . '</span>';
            }
            ?>
            
            <label for="confirm_password">Confirmer le mot de passe :</label>
            <input type="password" id="confirm_password" name="confirm_password"
                class="<?php echo (!empty($confirm_password_err)) ? 'is-invalid' : ''; ?>" required>
            <?php
            if(!empty($confirm_password_err)){
                echo '<span class="invalid-feedback">' . htmlspecialchars($confirm_password_err) . '</span>';
            }
            ?>

            <button type="submit">S'inscrire</button>
            <a href="login.php" class="register-link">Se connecter</a>
            
        </form>
    </main>
    
    <script src="js1.js"></script>

</body>
</html>