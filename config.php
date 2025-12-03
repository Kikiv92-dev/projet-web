<?php
// Paramètres de connexion à la base de données
define('DB_SERVER', 'localhost');
define('DB_USERNAME', 'root'); // Par défaut avec XAMPP
define('DB_PASSWORD', 'zb6[)M8s/u]*FqQA');     // Par défaut avec XAMPP
define('DB_NAME', 'guardia_bde');

$db_host = 'localhost';
$db_name = 'guardia_bde';
$db_user = 'root';
$db_pass = 'zb6[)M8s/u]*FqQA';

/* Tentative de connexion à la base de données MySQL */
$conn = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);

// Vérifier la connexion
if($conn === false){
    die("ERREUR : Impossible de se connecter. " . mysqli_connect_error());
}
?>