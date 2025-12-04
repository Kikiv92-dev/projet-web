<?php
// =================================================================
// 1. GESTION DES ERREURS 
// =================================================================
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
error_reporting(E_ALL);

// =================================================================
// 2. FONCTIONS D'ENVOI (SMS SIMULÉ & EMAIL RÉEL)
// =================================================================

function envoyer_sms_confirmation(string $telephone, string $message): bool {
    // SIMULATION - À remplacer par une API réelle (Twilio, Vonage...)
    error_log("SIMULATION SMS envoyé à $telephone : $message");
    return true; 
}

function envoyer_email_confirmation(string $destinataire_email, string $nom_prenom, string $evenement): bool {
    
    $sujet = "✅ Confirmation d'inscription à l'événement : " . $evenement;
    $expediteur_email = "mailt5573@gmail.com"; // REMPLACER par votre adresse réelle
    $expediteur_nom = "BDE Guardia";

    $corps_html = "
    <html>
    <head><title>$sujet</title></head>
    <body style='font-family: Arial, sans-serif; line-height: 1.6;'>
        <div style='max-width: 600px; margin: 20px auto; padding: 20px; border: 1px solid #ddd; border-radius: 5px;'>
            <h2 style='color: #007bff;'>Bonjour $nom_prenom,</h2>
            <p>Votre inscription à l'événement <b>$evenement</b> est confirmée.</p>
            <p>Un récapitulatif des informations vous sera envoyé à l'approche de l'événement.</p>
            <p>À très vite !</p>
            <p>L'équipe du BDE Guardia</p>
        </div>
    </body>
    </html>";

    $headers = "MIME-Version: 1.0" . "\r\n";
    $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
    $headers .= "From: $expediteur_nom <$expediteur_email>" . "\r\n";
    $headers .= "Reply-To: $expediteur_nom <$expediteur_email>" . "\r\n";

    // Envoi de l'e-mail
    if (@mail($destinataire_email, $sujet, $corps_html, $headers)) {
        error_log("Email envoyé à $destinataire_email pour l'événement $evenement");
        return true;
    } else {
        error_log("ÉCHEC ENVOI EMAIL : La fonction mail() de PHP a échoué.");
        return false;
    }
}

// =================================================================
// 3. LOGIQUE D'INSCRIPTION, VALIDATION ET SAUVEGARDE
// =================================================================

$evenements_autorises = ["Raclette Party", "Soirée de Noël", "Fête de la galette des rois", "BDE Santa Secret"]; 
require_once "config.php"; 

$nom = $prenom = $telephone = $email = $evenement = "";
$erreur = "";
$email_succes = true;

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Récupération des données (y compris le nouvel EMAIL)
    $nom = trim(filter_input(INPUT_POST, "nom", FILTER_SANITIZE_SPECIAL_CHARS) ?? '');
    $prenom = trim(filter_input(INPUT_POST, "prenom", FILTER_SANITIZE_SPECIAL_CHARS) ?? '');
    $telephone = trim(filter_input(INPUT_POST, "telephone", FILTER_SANITIZE_NUMBER_INT) ?? ''); 
    $email = trim(filter_input(INPUT_POST, "email", FILTER_SANITIZE_EMAIL) ?? ''); // NOUVEAU
    $evenement_input = trim(filter_input(INPUT_POST, "evenement", FILTER_SANITIZE_SPECIAL_CHARS) ?? '');

    // Validation (mise à jour pour inclure l'email)
    if (!in_array($evenement_input, $evenements_autorises)) {
        $erreur = "Erreur de validation de l'événement.";
    } elseif (empty($nom) || empty($prenom) || empty($telephone) || empty($evenement_input) || empty($email)) {
        $erreur = "Veuillez remplir tous les champs du formulaire, y compris l'e-mail.";
    } elseif (strlen($telephone) < 10) {
        $erreur = "Le numéro de téléphone est invalide ou trop court.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) { // Validation de l'adresse e-mail
        $erreur = "L'adresse e-mail fournie est invalide.";
    } else {
        $evenement = $evenement_input;
    }

    // 4. Si aucune erreur, procéder à l'insertion et à l'envoi
    if (empty($erreur)) {
        
        // Requête SQL mise à jour : inclusion de la colonne 'email'
        $sql = "INSERT INTO inscriptions_evenements (nom, prenom, email, telephone, evenement) VALUES (?, ?, ?, ?, ?)"; 

        if ($stmt = $conn->prepare($sql)) {
            // Lier les variables (email est le 3ème 's')
            $stmt->bind_param("sssss", $param_nom, $param_prenom, $param_email, $param_telephone, $param_evenement); 
            
            $param_nom = $nom;
            $param_prenom = $prenom;
            $param_email = $email; // NOUVEAU PARAMÈTRE
            $param_telephone = $telephone;
            $param_evenement = $evenement; 

            if ($stmt->execute()) {
                
                // --- ENVOI DES NOTIFICATIONS ---
                $nom_complet = $prenom . " " . $nom;
                $email_succes = envoyer_email_confirmation($email, $nom_complet, $evenement);
                $sms_succes = envoyer_sms_confirmation($telephone, "Bonjour $prenom, votre inscription à '$evenement' est confirmée !"); 
                
                
                // Redirection avec indication du succès/échec des envois
                $redirect_url = "evenement.html?success=true&event=" . urlencode($evenement);
                if (!$email_succes) {
                    $redirect_url .= "&email_error=true";
                }
                if (!$sms_succes) {
                    $redirect_url .= "&sms_error=true";
                }
                
                header("location: " . $redirect_url);
                exit();

            } else {
                error_log("Erreur SQL lors de l'inscription : " . $stmt->error);
                $erreur = "Oups! Une erreur interne est survenue. Veuillez réessayer plus tard.";
            }

            $stmt->close();
        } else {
            error_log("Erreur de préparation SQL: " . $conn->error);
            $erreur = "Oups! Erreur de préparation de la requête.";
        }
    }
    
    if (!empty($erreur)) {
        header("location: evenement.html?error=" . urlencode($erreur));
        exit();
    }
}
if (isset($conn)) {
    $conn->close();
}
?>