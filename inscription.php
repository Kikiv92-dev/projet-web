<?php
// =================================================================
// 1. GESTION DES ERREURS (CRITIQUE EN PRODUCTION !)
// =================================================================
// Désactiver l'affichage des erreurs en production pour ne pas exposer d'informations.
// Les erreurs seront enregistrées dans les logs du serveur.
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
error_reporting(E_ALL);

// 2. INCLURE LA CONFIGURATION DE LA BDD
require_once "config.php";

$username = $password = $confirm_password = "";
$username_err = $password_err = $confirm_password_err = "";

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
        
        // Sécurité : Forcer un schéma alphanumérique pour l'username (empêche les injections)
        // Accepte lettres, chiffres et underscore (_), entre 3 et 15 caractères.
        if (!preg_match('/^[a-zA-Z0-9_]{3,15}$/', $input_username)) {
            $username_err = "Le nom d'utilisateur doit contenir entre 3 et 15 caractères (lettres, chiffres et _).";
        } else {
            // Vérification de l'existence du nom d'utilisateur dans la base de données
            $sql = "SELECT id FROM $table_name WHERE username = ?";
            
            // Utilisation de l'approche Orientée Objet pour la préparation (plus moderne)
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
                    // Sécurité : Enregistrement de l'erreur interne, message générique à l'utilisateur
                    error_log("Erreur SQL lors de la vérification de l'utilisateur : " . $stmt->error);
                    $username_err = "Oops! Une erreur interne est survenue. Veuillez réessayer plus tard.";
                }
                $stmt->close();
            }
        }
    }
    
    // =================================================================
    // B. VALIDATION DU MOT DE PASSE ET CONFIRMATION
    // =================================================================
    if(empty(trim($_POST["password"]))){
        $password_err = "Veuillez entrer un mot de passe.";
    } elseif(strlen(trim($_POST["password"])) < 6){
        $password_err = "Le mot de passe doit contenir au moins 6 caractères.";
    } else{
        $password = trim($_POST["password"]);
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
    // =================================================================
    if(empty($username_err) && empty($password_err) && empty($confirm_password_err)){
        
        $sql = "INSERT INTO $table_name (username, password) VALUES (?, ?)";
          
        // Utilisation de l'approche Orientée Objet pour l'insertion
        if($stmt = $conn->prepare($sql)){
            $stmt->bind_param("ss", $param_username, $param_password);
            
            $param_username = $username;
            // Sécurité : Hachage du mot de passe avec PASSWORD_DEFAULT (méthode standard)
            $param_password = password_hash($password, PASSWORD_DEFAULT);
            
            if($stmt->execute()){
                // SUCCÈS : REDIRECTION ET ARRÊT DU SCRIPT
                header("location: login.php?status=success_inscription");
                exit;
            } else{
                // Sécurité : Enregistrement de l'erreur interne, message générique à l'utilisateur
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