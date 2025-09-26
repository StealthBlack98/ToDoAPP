<?php
session_start();

// Cancella tutte le variabili di sessione
$_SESSION = [];

// Distrugge la sessione
session_destroy();

// Cancella il cookie di sessione
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 3600,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Reindirizza alla dashboard o login
header("Location: dashboard.php");
exit;
?>