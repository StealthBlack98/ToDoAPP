<?php
$host = 'localhost';
$dbname = 'DbProgetto';
$username = 'root';
$password = ''; // Password vuota

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
} catch (PDOException $e) {
    die("Errore di connessione al database: " . $e->getMessage());
}
?>