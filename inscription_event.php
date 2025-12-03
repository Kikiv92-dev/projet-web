<?php
// =================================================================
// 1. GESTION DES ERREURS (CRITIQUE EN PRODUCTION !)
// =================================================================
// Désactiver l'affichage des erreurs en production.
ini_set('display_errors', 0); 
ini_set('display_startup_errors', 0);
error_reporting(E_ALL);

// =================================================================
// 2. LISTE BLANCHE DES ÉVÉNEMENTS (CRITIQUE !)
// =================================================================
// Définir une liste de valeurs autorisées pour le champ caché 'evenement'
$evenements_autorises = ["Raclette Party", "Soirée de Noël", "Fête de la galette des rois"]; 
// Ajoutez ici tous les noms d'événements valides

// Inclure le fichier de connexion à la base de données
require_once "config.php";
// Note : Le fichier config.php doit utiliser l'approche Orientée Objet ($conn = new mysqli(...))

// Définir les variables et initialiser les erreurs
$nom = $prenom = $telephone = $evenement = "";
$erreur = "";

// Traitement après la soumission du formulaire
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // 1. Validation et récupération des données
    // Sécurité : Utilisation de FILTER_SANITIZE_STRING (maintenant FILTER_SANITIZE_SPECIAL_CHARS en PHP 8.1+)
    $nom = trim(filter_input(INPUT_POST, "nom", FILTER_SANITIZE_SPECIAL_CHARS) ?? '');
    $prenom = trim(filter_input(INPUT_POST, "prenom", FILTER_SANITIZE_SPECIAL_CHARS) ?? '');
    // Sécurité : On nettoie le numéro de téléphone pour ne garder que les chiffres
    $telephone = trim(filter_input(INPUT_POST, "telephone", FILTER_SANITIZE_NUMBER_INT) ?? '');
    
    // C'EST LA CLÉ : on récupère et on nettoie le champ caché 'evenement'
    $evenement_input = trim(filter_input(INPUT_POST, "evenement", FILTER_SANITIZE_SPECIAL_CHARS) ?? '');
    
    // =================================================================
    // A. VALIDATION CRITIQUE : VÉRIFICATION DE LA LISTE BLANCHE (Whitelist)
    // =================================================================
    if (!in_array($evenement_input, $evenements_autorises)) {
        // Alerte critique : un attaquant a modifié le champ caché !
        $erreur = "Erreur de validation de l'événement. Tentative de soumission non autorisée.";
        error_log("Tentative d'injection sur le champ evenement: " . $evenement_input);
        
    } elseif (empty($nom) || empty($prenom) || empty($telephone) || empty($evenement_input)) {
        // Validation des champs obligatoires
        $erreur = "Veuillez remplir tous les champs du formulaire.";
        
    } elseif (strlen($telephone) < 10) {
        // Validation spécifique du téléphone (ex: minimum 10 chiffres)
        $erreur = "Le numéro de téléphone est invalide ou trop court.";
        
    } else {
        // Si toutes les validations passent, on assigne la valeur nettoyée
        $evenement = $evenement_input;
    }

    // 2. Si aucune erreur, procéder à l'insertion
    if (empty($erreur)) {
        
        $sql = "INSERT INTO inscriptions_evenements (nom, prenom, telephone, evenement) VALUES (?, ?, ?, ?)";

        // Sécurité : Utilisation de l'approche Orientée Objet pour la compatibilité avec config.php
        if ($stmt = $conn->prepare($sql)) {
            // Lier les variables à la requête préparée
            $stmt->bind_param("ssss", $param_nom, $param_prenom, $param_telephone, $param_evenement);
            
            // Définir les paramètres (après la validation)
            $param_nom = $nom;
            $param_prenom = $prenom;
            $param_telephone = $telephone;
            $param_evenement = $evenement; 

            // Exécuter la requête
            if ($stmt->execute()) {
                // Redirection après succès
                header("location: evenement.html?success=true&event=" . urlencode($evenement));
                exit();
            } else {
                // Sécurité : Ne pas afficher l'erreur SQL
                error_log("Erreur SQL lors de l'inscription : " . $stmt->error);
                $erreur = "Oups! Une erreur interne est survenue. Veuillez réessayer plus tard.";
            }

            $stmt->close();
        } else {
            // Gérer les erreurs de préparation de la requête
            error_log("Erreur de préparation SQL: " . $conn->error);
            $erreur = "Oups! Erreur de préparation de la requête.";
        }
    }
    
    // Affichage de l'erreur si elle existe
    if (!empty($erreur)) {
        // En cas d'erreur, on peut rediriger vers la page d'événement avec un paramètre d'erreur.
        // Pour l'exemple, affichons-la ici
        echo "Erreur d'inscription : " . htmlspecialchars($erreur); 
    }
}
// Fermer la connexion à la base de données
if (isset($conn)) {
    $conn->close();
}
?>