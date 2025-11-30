<?php
// Inclure le fichier de connexion à la base de données
require_once "config.php";

// Définir les variables et initialiser les erreurs
$nom = $prenom = $telephone = $evenement = "";
$erreur = "";

// Traitement après la soumission du formulaire
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // 1. Validation et récupération des données
    $nom = trim($_POST["nom"] ?? '');
    $prenom = trim($_POST["prenom"] ?? '');
    $telephone = trim($_POST["telephone"] ?? '');
    // C'EST LA CLÉ : on récupère le champ caché 'evenement'
    $evenement = trim($_POST["evenement"] ?? '');

    // Validation simple (ajoutez-en plus si nécessaire)
    if (empty($nom) || empty($prenom) || empty($telephone) || empty($evenement)) {
        $erreur = "Veuillez remplir tous les champs du formulaire.";
    }

    // 2. Si aucune erreur, procéder à l'insertion
    if (empty($erreur)) {
        
        $sql = "INSERT INTO inscriptions_evenements (nom, prenom, telephone, evenement) VALUES (?, ?, ?, ?)";

        if ($stmt = mysqli_prepare($conn, $sql)) {
            // Lier les variables à la requête préparée
            mysqli_stmt_bind_param($stmt, "ssss", $param_nom, $param_prenom, $param_telephone, $param_evenement);
            
            // Définir les paramètres
            $param_nom = $nom;
            $param_prenom = $prenom;
            $param_telephone = $telephone;
            $param_evenement = $evenement; // 'Raclette', 'Noël', ou 'Galette'

            // Exécuter la requête
            if (mysqli_stmt_execute($stmt)) {
                // Redirection après succès (vers la page de l'événement avec un message)
                header("location: evenement.html?success=true&event=" . urlencode($evenement));
                exit();
            } else {
                $erreur = "Oups! Quelque chose a mal tourné. Veuillez réessayer plus tard.";
            }

            mysqli_stmt_close($stmt);
        }
    }
    
    // Si une erreur se produit, vous pouvez rediriger ici vers la page de l'événement 
    // ou afficher l'erreur sur cette page (dépend de votre architecture).
    if (!empty($erreur)) {
         // Pour l'instant, affichons juste l'erreur
         echo $erreur; 
    }
}
// Fermer la connexion à la base de données
if (isset($conn)) {
    mysqli_close($conn);
}
?>