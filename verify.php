<?php
session_start();

if (!isset($_SESSION['email']) || !isset($_SESSION['code'])) {
    header("Location: register.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $userCode = trim($_POST['code']);
 
    if ($userCode == $_SESSION['code']) {
        // Mark verification as successful and redirect to dashboard
        // Keep the session (so user remains logged in) and optionally set a flag
        $_SESSION['verified'] = true;
        // Optionally unset the one-time code
        unset($_SESSION['code']);
        header("Location: dashboard.php");
        exit();
    } else {
        $error = "Der eingegebene Code ist falsch.";
    }
}
?>

<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Code-Verifizierung</title>
    <link rel="stylesheet" href="styles/style.css">
</head>
<body>
    <div class="container">
        <h1>Bestätigungscode eingeben</h1>
        <p>Wir haben dir einen Code an deine E-Mail gesendet.</p>

        <form method="POST" action="">
            <input type="text" name="code" placeholder="6-stelliger Code" required><br><br>
            <button type="submit" class="btn">Bestätigen</button>
        </form>

        <?php
        if (!empty($error)) echo "<p style='color:red;'>$error</p>";
        if (!empty($success)) echo "<p style='color:green;'>$success</p>";
        ?>
    </div>
</body>
</html>
