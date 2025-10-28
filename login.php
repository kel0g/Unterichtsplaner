<?php
session_start();

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $name = htmlspecialchars(trim($_POST['name']));
    $password = trim($_POST['password']);

    if (!empty($name) && !empty($password)) {
        $dbFile = __DIR__ . DIRECTORY_SEPARATOR . 'textdateien' . DIRECTORY_SEPARATOR . 'users.db';
        $dsn = 'sqlite:' . $dbFile;

        if (file_exists($dbFile)) {
            try {
                $pdo = new PDO($dsn);
                $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

                $stmt = $pdo->prepare('SELECT username, password, verified FROM users WHERE username = :username LIMIT 1');
                $stmt->execute([':username' => $name]);
                $user = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($user && password_verify($password, $user['password'])) {
                    // Optional: Check verified status
                    $_SESSION['username'] = $user['username'];
                    header("Location: dashboard.php");
                    exit();
                } else {
                    $error = "Name oder Passwort ist falsch.";
                }

            } catch (Exception $e) {
                error_log('Login DB-Fehler: ' . $e->getMessage());
                $error = 'Ein Fehler ist aufgetreten. Versuche es später erneut.';
            }
        } else {
            $error = "Keine Benutzerdatenbank gefunden.";
        }
    } else {
        $error = "Bitte alle Felder ausfüllen!";
    }
}
?>

<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="styles/style.css">
</head>
<body>
    <div class="container">
        <h1>Anmeldung</h1>
        <form method="POST" action="">
            <input type="text" name="name" placeholder="Name" required><br><br>
            <input type="password" name="password" placeholder="Passwort" required><br><br>
            <button type="submit" class="btn">Login</button>
        </form>
        <?php if (!empty($error)) echo "<p style='color:red;'>$error</p>"; ?>
    </div>
</body>
</html>