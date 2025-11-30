<?php
// Démarrage de la session PHP pour utiliser $_SESSION
session_start();
 
// Initialisation des variables d'erreur et des valeurs du formulaire
$username = $password = "";
$username_err = $password_err = $login_err = "";

// Vérifier si l'utilisateur est déjà connecté, si oui, le rediriger
if(isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true){
    // Redirection par rôle
    if ($_SESSION["role"] == "administrateur") {
        header("location: admin.php");
    } else {
        header("location: accueilmembre.php");
    }
    exit;
}
 
// Inclure le fichier de connexion à la base de données
require_once "config.php";
 
// Traitement des données du formulaire
if($_SERVER["REQUEST_METHOD"] == "POST"){

    // 1. VALIDATION DU NOM D'UTILISATEUR
    if(empty(trim($_POST["username"]))){
        $username_err = "Veuillez entrer un nom d'utilisateur.";
    } else{
        $username = trim($_POST["username"]);
    }
    
    // 2. VALIDATION DU MOT DE PASSE
    if(empty(trim($_POST["password"]))){
        $password_err = "Veuillez entrer votre mot de passe.";
    } else{
        $password = trim($_POST["password"]);
    }
    
    // 3. VÉRIFICATION DES ERREURS AVANT DE CONTINUER
    if(empty($username_err) && empty($password_err)){
        
        // Requête SQL pour récupérer l'utilisateur et son rôle (CORRIGÉE : 'utilisateur')
        $sql = "SELECT id, username, password, role FROM utilisateurs WHERE username = ?";
        
        if($stmt = mysqli_prepare($conn, $sql)){
            mysqli_stmt_bind_param($stmt, "s", $param_username);
            $param_username = $username;
            
            if(mysqli_stmt_execute($stmt)){
                mysqli_stmt_store_result($stmt);
                
                if(mysqli_stmt_num_rows($stmt) == 1){                    
                    // Lier les variables de résultat
                    mysqli_stmt_bind_result($stmt, $id, $username, $hashed_password, $role);
                    
                    if(mysqli_stmt_fetch($stmt)){
                        // VÉRIFICATION DU MOT DE PASSE
                        if(password_verify($password, $hashed_password)){
                            
                            // Succès! Stocker les données de session
                            $_SESSION["loggedin"] = true;
                            $_SESSION["id"] = $id;
                            $_SESSION["username"] = $username;
                            $_SESSION["role"] = $role;                            
                            
                            // REDIRECTION CONDITIONNELLE BASÉE SUR LE RÔLE
                            if ($role == "administrateur") {
                                header("location: admin.php");
                            } else {
                                header("location: accueilmembre.php");
                            }
                            exit;
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
                // Erreur d'exécution de la requête
                echo "Oops! Une erreur de base de données est survenue. Veuillez réessayer plus tard.";
            }
            mysqli_stmt_close($stmt);
        }
    }
    // Fermer la connexion uniquement si elle a été ouverte
    if (isset($conn)) {
        mysqli_close($conn);
    }
}
?>