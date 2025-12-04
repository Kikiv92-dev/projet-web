<?php
// Démarrage de la session et vérification des droits (essentiel)
session_start();

// Si l'utilisateur n'est pas connecté ou n'est pas admin, on le redirige
// (Vous devez adapter cette vérification à votre propre logique de connexion)
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: login.php");
    exit;
}

require_once "config.php";

$pdo = null; 
$message_suppression = "";

try {
    // Connexion à la base de données
    $pdo = new PDO("mysql:host=$db_host;dbname=$db_name;charset=utf8", $db_user, $db_pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
} catch (PDOException $e) {
    die("Erreur de connexion à la base de données : " . $e->getMessage());
}

// ==============================================
// GESTION DE LA SUPPRESSION D'UTILISATEUR
// ==============================================
if (isset($_GET['delete_user_id']) && is_numeric($_GET['delete_user_id'])) {
    $id_a_supprimer = $_GET['delete_user_id'];
    $current_admin_id = $_SESSION["id"]; // Assurez-vous que l'ID de l'admin actuel est stocké en session

    // VÉRIFICATION DE SÉCURITÉ CRITIQUE : Interdire à l'admin de s'auto-supprimer
    if ($id_a_supprimer == $current_admin_id) {
        $message_suppression = "<p class='error-message'>❌ Erreur de sécurité : Vous ne pouvez pas supprimer votre propre compte utilisateur.</p>";
    } else {
        // Requête de suppression
        $sql_delete = "DELETE FROM utilisateurs WHERE id = :id";
        
        try {
            $stmt_delete = $pdo->prepare($sql_delete);
            $stmt_delete->bindParam(':id', $id_a_supprimer, PDO::PARAM_INT);
            $stmt_delete->execute();

            if ($stmt_delete->rowCount() > 0) {
                $message_suppression = "<p class='success-message'>✅ Utilisateur ID **$id_a_supprimer** supprimé avec succès.</p>";
            } else {
                $message_suppression = "<p class='error-message'>⚠️ Erreur : Aucun utilisateur trouvé avec l'ID **$id_a_supprimer**.</p>";
            }
        } catch (PDOException $e) {
            $message_suppression = "<p class='error-message'>❌ Erreur de base de données lors de la suppression : " . $e->getMessage() . "</p>";
        }
    }
}


// ==============================================
// RÉCUPÉRATION DE LA LISTE DES UTILISATEURS
// ==============================================
$users = [];
$sql_users = "SELECT id, username, role FROM utilisateurs ORDER BY id ASC";
$stmt_users = $pdo->query($sql_users);
$users = $stmt_users->fetchAll(PDO::FETCH_ASSOC);

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BDE Admin - Gestion Utilisateurs</title>
    <link rel="stylesheet" href="admin-styles.css"> 
    <style>
        body { background-color: #000000; }
        /* Réutilisez les styles .success-message et .error-message de admin-styles.css */
        /* Ajoutez des styles spécifiques ici si nécessaire */
    </style>
</head>
<body>

    <canvas id="matrixCanvas"></canvas>
    <div id="admin-container">
        <aside id="sidebar">
            <div class="logo">BDE Admin Panel</div>
            <nav>
                <ul>
                    <li><a href="admin.php"> ./Dashboard</a></li>
                    <li><a href="manage_users.php" class="active"> ./Gestion Utilisateurs</a></li>
                    <li><a href="deconnexion.php"> ./Déconnexion</a></li>
                </ul>
            </nav>
        </aside>

        <main id="content">
            <header>
                <h2>Gestion des Comptes Administrateurs</h2>
                <p>Créer, visualiser et supprimer les utilisateurs qui ont accès au panneau d'administration.</p>
            </header>
            
            <section id="main-view">
                <h3>>> LISTE DES UTILISATEURS (Table users)</h3>
                
                <?php echo $message_suppression; ?> 

                <table class="hacker-table">
                    <thead>
                        <tr> 
                            <th>ID</th>
                            <th>Nom d'utilisateur</th>
                            <th>Rôle</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $user): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($user['id']); ?></td>
                            <td><?php echo htmlspecialchars($user['username']); ?></td>
                            <td><?php echo htmlspecialchars($user['role']); ?></td>
                            <td>
                                <a href="manage_users.php?delete_user_id=<?php echo $user['id']; ?>" 
                                   onclick="return confirm('Êtes-vous SÛR de vouloir supprimer l\'utilisateur <?php echo addslashes($user['username']); ?> ?')" 
                                   class="delete-btn <?php echo ($user['id'] == $_SESSION['id']) ? 'disabled' : ''; ?>">
                                    [x] Supprimer
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (empty($users)): ?>
                        <tr>
                            <td colspan="4" style="text-align:center; opacity: 0.7;">[ Aucune utilisateur trouvé. ]</td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </section>
        </main>

    <script src="js1.js"></script>
    
</body>
</html>