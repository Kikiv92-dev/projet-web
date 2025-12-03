<?php
// ... VOTRE CODE EXISTANT (session_start(), vérification d'accès, etc.) ...
session_start();
$user_username = htmlspecialchars($_SESSION["username"]);


// ==============================================
// 1. CONFIGURATION ET CONNEXION BDD (À MODIFIER)
// ==============================================
$db_host = 'localhost';      
$db_name = 'guardia_bde';    
$db_user = 'root';           
$db_pass = getenv('DB_PASSWORD');

$pdo = null; 
$evenements_json = "[]"; // Initialisation du JSON pour le calendrier

try {
    $pdo = new PDO("mysql:host=$db_host;dbname=$db_name;charset=utf8", $db_user, $db_pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Requête pour tous les événements (simplifiée car pas de date d'événement)
    // NOTE: Idéalement, vous devriez avoir une table 'events' avec 'nom', 'date_debut', 'date_fin'.
    // Puis vous feriez une jointure pour savoir si l'utilisateur y est inscrit.
    
    // Ici, nous simulons les dates d'événement basées sur les dates statiques de evenement.html
    $events_data = [
        ['title' => 'Raclette Party', 'start' => '2025-12-05T19:00:00', 'color' => '#00ff00'],
        ['title' => 'BDE Santa Secret', 'start' => '2025-12-15T20:00:00', 'color' => '#ffcc00'],
        ['title' => 'Soirée de Noël', 'start' => '2025-12-20T21:00:00', 'color' => '#00ff00'],
        ['title' => 'Fête de la galette des rois', 'start' => '2026-01-15T18:30:00', 'color' => '#33ff33'],
    ];

    // Convertir les événements au format JSON
    $evenements_json = json_encode($events_data);

} catch (PDOException $e) {
    // Si la connexion échoue, le calendrier sera vide, pas besoin de mourir.
    error_log("Calendar DB Error: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="fr">
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BDE Hacking - Calendrier des Événements</title>
    <link rel="stylesheet" href="calendar-styles.css"> 
    <link href='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/main.min.css' rel='stylesheet' />
    <style>
        body { 
            background-color: #000000; /* Or your exact dark background color */
        }
    </style>
</head>
<body>

    <canvas id="matrixCanvas"></canvas>
    <script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.js'></script>
    <div id="user-container">
        <aside id="sidebar">
            <div class="logo">BDE User Panel</div>
            <nav>
                <ul>
                    <li><a href="index.html" > ./Accueil</a></li>
                    <li><a href="accueilmembre.php" > ./Dashboard</a></li>
                    <li><a href="#" class="active"> ./Calendrier</a></li>
                    <li><a href="deconnexion.php" id="logout-btn"> ./Déconnexion</a></li>
                </ul>
            </nav>
        </aside>

        <main id="content">
            <header>
                <h2>Calendrier Officiel des Événements</h2>
                  <div id="user-info">
                     <p>Connecté en tant que: <span class="username-display"><?php echo $user_username; ?></span></p>
                  </div>
                <p>Retrouvez tous les CTF, ateliers et réunions du BDE.</p>
            </header>
            
            <section id="main-view">
                <h3>Calendrier des Événements</h3>
                <div id="event-calendar">
        </div>

                <div id="dynamic-data-placeholder">
                    </div>
            </section>
        </main>
    </div>
   <script src="js1.js"></script>

    <script>
        var serverEvents = <?php echo $evenements_json; ?>;
    </script>

    <script src='calendar-init.js'></script>
    
    
</div>
</body>
</html>