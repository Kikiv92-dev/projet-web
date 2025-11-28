<?php
// Initialiser la session
session_start();
 
// Vérifier si l'utilisateur est déjà connecté, si oui, rediriger vers la page d'accueil
if(isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true){
    header("location: accueil_membre.php");
    exit;
}
 
// Inclure le fichier de configuration de la BDD
require_once "config.php";
 
$username = $password = "";
$username_err = $password_err = $login_err = "";
 
// Traitement des données du formulaire
if($_SERVER["REQUEST_METHOD"] == "POST"){
 
    // Valider le nom d'utilisateur
    if(empty(trim($_POST["username"]))){
        $username_err = "Veuillez entrer un nom d'utilisateur.";
    } else{
        $username = trim($_POST["username"]);
    }
    
    // Valider le mot de passe
    if(empty(trim($_POST["password"]))){
        $password_err = "Veuillez entrer votre mot de passe.";
    } else{
        $password = trim($_POST["password"]);
    }
    
    // Vérifier les identifiants
    if(empty($username_err) && empty($password_err)){
        // Préparer une instruction SELECT
        $sql = "SELECT id, username, password FROM utilisateurs WHERE username = ?";
        
        if($stmt = mysqli_prepare($conn, $sql)){
            // Lier les variables à l'instruction préparée comme paramètres
            mysqli_stmt_bind_param($stmt, "s", $param_username);
            
            // Définir les paramètres
            $param_username = $username;
            
            // Tenter d'exécuter l'instruction préparée
            if(mysqli_stmt_execute($stmt)){
                // Stocker le résultat
                mysqli_stmt_store_result($stmt);
                
                // Vérifier si le nom d'utilisateur existe, si oui, vérifier le mot de passe
                if(mysqli_stmt_num_rows($stmt) == 1){                    
                    // Lier les variables de résultat
                    mysqli_stmt_bind_result($stmt, $id, $username, $hashed_password);
                    if(mysqli_stmt_fetch($stmt)){
                        // Utiliser password_verify() pour vérifier le mot de passe haché
                        if(password_verify($password, $hashed_password)){
                            // Mot de passe correct, démarrer une nouvelle session
                            session_start();
                            
                            // Stocker les données dans des variables de session
                            $_SESSION["loggedin"] = true;
                            $_SESSION["id"] = $id;
                            $_SESSION["username"] = $username;                            
                            
                            // Rediriger l'utilisateur vers la page d'accueil après connexion
                            header("location: accueil_membre.php");
                        } else{
                            // Mot de passe invalide
                            $login_err = "Nom d'utilisateur ou mot de passe invalide.";
                        }
                    }
                } else{
                    // Nom d'utilisateur non trouvé
                    $login_err = "Nom d'utilisateur ou mot de passe invalide.";
                }
            } else{
                echo "Oops! Quelque chose a mal tourné. Veuillez réessayer plus tard.";
            }

            // Fermer l'instruction
            mysqli_stmt_close($stmt);
        }
    }
    
    // Fermer la connexion
    mysqli_close($conn);
}
?>