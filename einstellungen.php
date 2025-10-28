<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}
$username = $_SESSION['username'];
?>

<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <link rel="stylesheet" href="styles/style.css">
    <link rel="stylesheet" href="styles/dashboard.css">
</head>
<body>
    <div class="dashboard-container">
        <h1>Einstellungen</h1>
        <p>Wähle eine Option:</p>

        <div class="main-buttons">
            <a href="logout.php" class="main-btn">Logout</a>
        </div>

        <div class="bottom-nav">
            <a href="dashboard.php" class="nav-btn">Start</a>
            <a href="stundenplan.php" class="nav-btn">Stundenplan</a>
            <a href="mitteilungen.php" class="nav-btn">Mitteilungen</a>
            <a href="einstellungen.php" class="nav-btn active">Einstellungen</a>
        </div>
    </div>
</body>
</html>
