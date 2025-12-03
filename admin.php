<?php
// ... VOTRE CODE EXISTANT (session_start(), vérification d'accès, etc.) ...
session_start();
// ...
$admin_username = htmlspecialchars($_SESSION["username"]);


// ==============================================
// 1. CONFIGURATION DE LA BASE DE DONNÉES (À MODIFIER)
// ==============================================
$db_host = 'localhost';      // Généralement 'localhost'
$db_name = 'guardia_bde';    // Nom de votre base de données
$db_user = 'root';           // Votre nom d'utilisateur MySQL
$db_pass = 'zb6[)M8s/u]*FqQA'; // Votre mot de passe MySQL (si vous en avez un)

$pdo = null; // Initialisation de la variable de connexion

try {
    // Connexion à la base de données via PDO
    $pdo = new PDO("mysql:host=$db_host;dbname=$db_name;charset=utf8", $db_user, $db_pass);
    // Configuration pour afficher les erreurs
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
} catch (PDOException $e) {
    // En cas d'erreur de connexion, affiche un message d'erreur et arrête le script
    die("Erreur de connexion à la base de données : " . $e->getMessage());
}

// ==============================================
// 2. RÉCUPÉRATION DES DONNÉES DU DASHBOARD
// ==============================================

// --- a) Total Inscrits ---
$total_inscrits = 0;
$stmt_count = $pdo->query("SELECT COUNT(*) FROM inscriptions_evenements");
$total_inscrits = $stmt_count->fetchColumn();

// --- b) Inscriptions Récentes (Top 10) ---
$recent_inscriptions = [];
// Requête pour les 10 dernières inscriptions, triées par date la plus récente
$sql_recent = "SELECT id_inscription, prenom, nom, telephone, evenement, date_inscription 
               FROM inscriptions_evenements 
               ORDER BY date_inscription DESC 
               LIMIT 10";
               
$stmt_recent = $pdo->prepare($sql_recent);
$stmt_recent->execute();
$recent_inscriptions = $stmt_recent->fetchAll(PDO::FETCH_ASSOC);


// --- c) Événements Actifs (Si vous avez une colonne 'statut' ou 'date_evenement' > NOW())
// EXEMPLE SIMPLIFIÉ :
$evenements_actifs = 3; // Laissez statique ou écrivez la requête SQL correspondante
// $ctf_name = "Capture the Flag Hiver"; // Laissez statique ou écrivez la requête SQL correspondante

// On ferme la connexion PDO pour les bonnes pratiques (optionnel car PHP la ferme à la fin du script)
// $pdo = null;

// ==============================================

// --- d) Événement le Plus Populaire ---
$evenement_populaire = "N/A";
$sql_populaire = "SELECT evenement, COUNT(evenement) as total 
                  FROM inscriptions_evenements 
                  GROUP BY evenement 
                  ORDER BY total DESC 
                  LIMIT 1";
                  
$stmt_populaire = $pdo->query($sql_populaire);
$resultat_populaire = $stmt_populaire->fetch(PDO::FETCH_ASSOC);

if ($resultat_populaire) {
    // Stocke le nom de l'événement le plus populaire
    $evenement_populaire = $resultat_populaire['evenement'];
}
// La variable $evenement_populaire est maintenant prête à être utilisée.
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
        <div class="stat-card">Total Inscrits: <span class="data-flicker"><?php echo $total_inscrits; ?></span></div>
        
        <div class="stat-card">Événements Actifs: <span class="data-flicker"><?php echo $evenements_actifs; ?></span></div>
        
        <div class="stat-card">Nouvelles Inscriptions (24h): <span class="data-flicker">?</span></div>
        
        <div class="stat-card">Événement Populaire: <span><?php echo htmlspecialchars($evenement_populaire); ?></span></div>
    </div>
<div class="dashboard-grid">
    
    <div class="recent-inscriptions">
        <h3>>> FLUX D'INSCRIPTIONS </h3>
        <table class="hacker-table">
            <thead>
                </thead>
            <tbody>
                <?php foreach ($recent_inscriptions as $inscription): ?>
                <tr>
                    <td><?php echo htmlspecialchars($inscription['id_inscription']); ?></td>
                    <td><?php echo htmlspecialchars($inscription['prenom']); ?></td>
                    <td><?php echo htmlspecialchars($inscription['nom']); ?></td>
                    <td class="col-event"><?php echo htmlspecialchars($inscription['evenement']); ?></td>
                    <td><?php echo htmlspecialchars(date('Y-m-d H:i', strtotime($inscription['date_inscription']))); ?></td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($recent_inscriptions)): ?>
                <tr>
                    <td colspan="5" style="text-align:center; opacity: 0.7;">[ Aucune inscription trouvée pour le moment. ]</td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    
    </div>

                <div id="dynamic-data-placeholder">
                    </div>
            </section>
        </main>

    <script src="js1.js"></script>
    
</body>
</html>