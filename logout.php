<?php
session_start();        // Session starten, um sie beenden zu können
session_unset();        // Alle Session-Variablen löschen
session_destroy();      // Die Session selbst zerstören

// Optional: Cookie löschen, falls gesetzt
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Zurück zur Startseite
header("Location: index.php");
exit();
?>
