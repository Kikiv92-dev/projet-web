<?php
// 1. Démarrer la session
session_start();
 
// 2. VÉRIFICATION DE SÉCURITÉ ET CONTRÔLE D'ACCÈS
// Si l'utilisateur n'est PAS connecté OU si le rôle stocké n'est PAS "administrateur"
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || $_SESSION["role"] !== "administrateur"){
    // Rediriger immédiatement vers la page de connexion
    // On suppose que l'utilisateur non autorisé ne devrait pas savoir qu'une page admin existe.
    header("location: login.php"); // Rediriger vers votre page de connexion
    exit;
}

// Les variables de session que nous allons afficher sont maintenant disponibles :
$admin_username = htmlspecialchars($_SESSION["username"]);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BDE Admin Dashboard</title>
    <link rel="stylesheet" href="admin-styles.css"> 
    <style>
        body { 
            background-color: #000000;
        }
    </style>
</head>
<body>

    <canvas id="matrixCanvas"></canvas>
    <div id="admin-container">
        <aside id="sidebar">
            <div class="logo">BDE Admin Panel</div>
            <nav>
                <ul>
                    <li><a href="admin.php" class="active" data-content="dashboard"> ./Dashboard</a></li>
                    <li><a href="deconnexion.php"> ./Déconnexion</a></li>
                </ul>
            </nav>
        </aside>

        <main id="content">
            <header>
                <h2>Bienvenue, Administrateur BDE</h2>
                  <div id="user-info">
                    <p>Connecté en tant que: <span class="username-display"><?php echo $admin_username; ?></span></p>
                  </div>
                <p>Gérez les tournois et la communauté étudiante.</p>
            </header>
            
            <section id="main-view">
                <h3>Aperçu Rapide</h3>
                <div class="stats-grid">
                    <div class="stat-card">Total Inscrits: <span>145</span></div>
                    <div class="stat-card">Événements Actifs: <span>3</span></div>
                    <div class="stat-card">CTF Prévu: <span>Capture the Flag Hiver</span></div>
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