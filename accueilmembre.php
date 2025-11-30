<?php
// Démarrer la session
session_start();
 
// VÉRIFICATION DE SÉCURITÉ ET CONTRÔLE D'ACCÈS
// Si l'utilisateur n'est PAS connecté, on le renvoie à la connexion.
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header("location: login.php"); // Rediriger vers votre page de connexion
    exit;
}

// Les variables de session que nous allons afficher sont maintenant disponibles :
$user_username = htmlspecialchars($_SESSION["username"]);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BDE Hacking - Mon Compte</title>
    <link rel="stylesheet" href="user-styles.css"> 
    <style>
        body { 
            background-color: #000000;
        }
    </style>
</head>
<body>

    <canvas id="matrixCanvas"></canvas>
    <div id="user-container">
        <aside id="sidebar">
            <div class="logo">BDE User Panel</div>
            <nav>
                <ul>
                    <li><a href="user.php" class="active"> ./Dashboard</a></li>
                    <li><a href="calendar.html"> ./Calendrier</a></li>
                    <li><a href="deconnexion.php"> ./Déconnexion</a></li>
                </ul>
            </nav>
        </aside>

        <main id="content">
            <header>
                <h2>Mon Profil Étudiant</h2>
                  <div id="user-info">
                    <p>Connecté en tant que: <span class="username-display"><?php echo $user_username; ?></span></p>
                  </div>
                <p>Gérez les tournois et la communauté étudiante.</p>
            </header>
            
            <section id="main-view">
                <h3>Aperçu Rapide</h3>
                <div class="stats-grid">
                    <div class="stat-card">Événements Future : <span>4</span></div>
                    <div class="stat-card">Prochain Événements : <span>BDE SANTA SECRET</span></div>
                    <div class="stat-card">Votre Rôle : <span><?php echo htmlspecialchars($_SESSION["role"]); ?></span></div>
                </div>

                <div id="dynamic-data-placeholder">
                    </div>
            </section>
        </main>
    </div>
    <script src="js1.js"></script>
    <p>Status: <span class="data-flicker">ACTIVE</span></p>
    <div class="processing-container">
        <div class="progress-bar"></div>
        <p>Executing payload...</p>
    </div>
</body>
</html>