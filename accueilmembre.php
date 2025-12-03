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
<?php
// ... VOTRE CODE EXISTANT (session_start(), vérification d'accès, etc.) ...
$user_username = htmlspecialchars($_SESSION["username"]);


// ==============================================
// 1. CONFIGURATION ET CONNEXION BDD (À MODIFIER)
// ==============================================
$db_host = 'localhost';
$db_name = 'guardia_bde';
$db_user = 'root';
$db_pass = getenv('DB_PASSWORD');

$pdo = null;

try {
    $pdo = new PDO("mysql:host=$db_host;dbname=$db_name;charset=utf8", $db_user, $db_pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
} catch (PDOException $e) {
    die("Erreur de connexion à la base de données : " . $e->getMessage());
}

// ==============================================
// 2. RÉCUPÉRATION DES DONNÉES DU DASHBOARD MEMBRE
// ==============================================

// NOTE: Votre table n'a pas de colonne pour lier l'inscription à un ID utilisateur
// Nous allons donc filtrer ici sur le 'nom' et 'prénom' pour simuler la personnalisation.
// EN PRODUCTION, VOUS DEVRIEZ UTILISER un 'user_id' stocké dans la session !

$user_prenom = isset($_SESSION['prenom']) ? $_SESSION['prenom'] : null; // Si vous stockez le prénom en session
$user_nom = isset($_SESSION['nom']) ? $_SESSION['nom'] : null; // Si vous stockez le nom en session

if (!$user_prenom && !$user_nom) {
    // Si les infos ne sont pas en session, on utilise le username comme prénom/nom (solution temporaire)
    // C'est une grosse simplification, car 'username' est souvent un email/pseudo.
    $user_prenom = $user_username;
    $user_nom = $user_username;
}


// --- a) Événements Futurs Inscrits ---
$evenements_futurs = [];
$sql_futurs = "SELECT evenement, date_inscription 
               FROM inscriptions_evenements 
               WHERE prenom = :prenom AND nom = :nom 
               ORDER BY evenement ASC";
               
$stmt_futurs = $pdo->prepare($sql_futurs);
$stmt_futurs->execute([':prenom' => $user_prenom, ':nom' => $user_nom]);
$evenements_futurs = $stmt_futurs->fetchAll(PDO::FETCH_ASSOC);

$total_evenements_futurs = count($evenements_futurs);

// --- b) Prochain Événement ---
$prochain_event = "Aucun";
if (!empty($evenements_futurs)) {
    // Simplement le premier de la liste triée (on pourrait filtrer sur la date future la plus proche si on avait une date_evenement)
    $prochain_event = $evenements_futurs[0]['evenement'];
}
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
                    <li><a href="index.html" > ./Accueil</a></li>
                    <li><a href="accueilmembre.php" class="active"> ./Dashboard</a></li>
                    <li><a href="calendar.php"> ./Calendrier</a></li>
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
        <div class="stat-card">Événements Inscrits : <span class="data-flicker"><?php echo $total_evenements_futurs; ?></span></div>
        <div class="stat-card">Prochain Événement : <span><?php echo htmlspecialchars($prochain_event); ?></span></div>
        <div class="stat-card">Votre Rôle : <span><?php echo htmlspecialchars($_SESSION["role"]); ?></span></div>
    </div>

    <div class="dashboard-grid">
        <div class="recent-inscriptions" style="width: 100%; margin-top: 30px; text-align: left;">
            <h3>>> VOS INSCRIPTIONS EN COURS { ACTIF }</h3>
            <table class="hacker-table">
                <thead>
                    <tr>
                        <th style="width: 50%;">Événement</th>
                        <th style="width: 50%;">Date d'Inscription</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($evenements_futurs)): ?>
                        <?php foreach ($evenements_futurs as $event): ?>
                        <tr>
                            <td class="col-event"><?php echo htmlspecialchars($event['evenement']); ?></td>
                            <td><?php echo htmlspecialchars(date('Y-m-d H:i', strtotime($event['date_inscription']))); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="2" style="text-align:center; opacity: 0.7;">[ Vous n'êtes inscrit à aucun événement pour le moment. ]</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    
    <div id="dynamic-data-placeholder"></div>
</section>
        
    <script src="js1.js"></script>

</body>
</html>