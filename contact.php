<?php
// =================================================================
// 1. GESTION DES ERREURS
// =================================================================
// Désactive l'affichage des erreurs en production (0)
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
error_reporting(E_ALL);

// 2. INCLURE LA CONFIGURATION (pour des raisons de cohérence)
// Assurez-vous que ce fichier ne fait qu'inclure les paramètres SMTP si la BDD n'est pas nécessaire ici.
require_once "config.php"; 

// Initialisation des variables pour les erreurs et les messages
$nom = $email = $message = "";
$nom_err = $email_err = $message_err = "";
$success_message = $error_message = "";

// 3. TRAITEMENT DU FORMULAIRE
if($_SERVER["REQUEST_METHOD"] == "POST"){
    
    // =================================================================
    // A. VALIDATION DES CHAMPS
    // =================================================================
    
    // Validation du Nom
    if(empty(trim($_POST["nom"]))){
        $nom_err = "Veuillez entrer votre nom.";
    } else {
        // Utilisation de htmlspecialchars pour prévenir les attaques XSS
        $nom = htmlspecialchars(trim($_POST["nom"]));
    }
    
    // Validation de l'Email
    if(empty(trim($_POST["email"]))){
        $email_err = "Veuillez entrer une adresse e-mail.";
    } elseif (!filter_var(trim($_POST["email"]), FILTER_VALIDATE_EMAIL)) {
        $email_err = "Le format de l'adresse e-mail n'est pas valide.";
    } else {
        $email = htmlspecialchars(trim($_POST["email"]));
    }
    
    // Validation du Message
    if(empty(trim($_POST["message"]))){
        $message_err = "Veuillez entrer un message.";
    } else {
        $message = htmlspecialchars(trim($_POST["message"]));
    }

    // =================================================================
    // B. ENVOI DE L'EMAIL
    // =================================================================
    if(empty($nom_err) && empty($email_err) && empty($message_err)){
        
        // --- Paramètres d'envoi ---
        // REMPLACEZ CETTE LIGNE PAR L'EMAIL DU BDE
        $to = "mail5573@gmail.com"; 
        
        $subject = "Nouveau message de contact BDE Guardia (De: $nom)";
        
        // Création du corps du message en texte brut
        $body = "Nom: $nom\n";
        $body .= "Email: $email\n";
        $body .= "Message:\n$message";
        
        // Configuration des entêtes pour le "From" et le "Reply-To"
        $headers = "From: $email" . "\r\n";
        $headers .= "Reply-To: $email" . "\r\n";
        
        // Envoi de l'e-mail (utilise la configuration sendmail.ini corrigée)
        if(mail($to, $subject, $body, $headers)){
            $success_message = "Votre message a été envoyé avec succès ! Nous vous recontacterons bientôt.";
            // Réinitialiser les champs après succès
            $nom = $email = $message = ""; 
        } else {
            error_log("Erreur lors de l'envoi du formulaire de contact.");
            $error_message = "Une erreur s'est produite lors de l'envoi. Veuillez réessayer plus tard.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact - Guardia</title>
    <link rel="stylesheet" href="contact.css">
</head>
<body>

    <canvas id="matrixCanvas"></canvas>

    <header class="navbar">
        <nav>
            <ul>
                <li><a href="index.html" class="nav-link">./accueil</a></li>
                <li><a href="bde.html" class="nav-link">./BDE</a></li>
                <li><a href="evenement.html" class="nav-link">./evenement</a></li>
                <li><a href="contact.php" class="nav-link">./contact</a></li>
                <li><a href="login.php" class="nav-link">./login</a></li>
            </ul>
        </nav>
    </header>

    <main class="content-section">
        <div class="logo-container">
            <img src="image.png" alt="Logo du Bureau des Étudiants ERROR" class="site-logo logo-bde">
            <img src="guardia-logo-104x100.png.webp" alt="Logo de l'école GUARDIA" class="site-logo logo-guardia">
        </div>

        <div class="content-interior">
            <h1>Nous Contacter</h1>
            
            <?php 
            // Affichage des messages de succès ou d'erreur
            if(!empty($success_message)){
                echo '<div class="alert-success">' . htmlspecialchars($success_message) . '</div>';
            }
            if(!empty($error_message)){
                echo '<div class="alert-error">' . htmlspecialchars($error_message) . '</div>';
            }
            ?>
            
            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST" class="contact-form">
                
                <label for="nom">Nom :</label>
                <input type="text" id="nom" name="nom" 
                    value="<?php echo htmlspecialchars($nom ?? ''); ?>"
                    class="contact-input <?php echo (!empty($nom_err)) ? 'is-invalid' : ''; ?>" required>
                <?php
                if(!empty($nom_err)){
                    echo '<span class="invalid-feedback">' . htmlspecialchars($nom_err) . '</span>';
                }
                ?>
                
                <label for="email">Email :</label>
                <input type="email" id="email" name="email" 
                    value="<?php echo htmlspecialchars($email ?? ''); ?>"
                    class="contact-input <?php echo (!empty($email_err)) ? 'is-invalid' : ''; ?>" required>
                <?php
                if(!empty($email_err)){
                    echo '<span class="invalid-feedback">' . htmlspecialchars($email_err) . '</span>';
                }
                ?>
                
                <label for="message">Message :</label>
                <textarea id="message" name="message" rows="5"
                    class="contact-input <?php echo (!empty($message_err)) ? 'is-invalid' : ''; ?>" required><?php echo htmlspecialchars($message ?? ''); ?></textarea>
                <?php
                if(!empty($message_err)){
                    echo '<span class="invalid-feedback">' . htmlspecialchars($message_err) . '</span>';
                }
                ?>
                
                <button type="submit">Envoyer le Message</button>
            </form>
        </div>
        
    </main>
    
    <script src="js1.js"></script>

</body>
</html>