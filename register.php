<?php
session_start();

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $name = htmlspecialchars(trim($_POST['name']));
    $email = htmlspecialchars(trim($_POST['email']));
    $password = trim($_POST['password']); // Passwort nicht mit htmlspecialchars hashen

    if (!empty($name) && !empty($email) && !empty($password)) {
        // Random Code
        $code = rand(100000, 999999);

        // SQLite DB
        $dbFile = __DIR__ . DIRECTORY_SEPARATOR . 'textdateien' . DIRECTORY_SEPARATOR . 'users.db';
        $dsn = 'sqlite:' . $dbFile;

        try {
            $pdo = new PDO($dsn);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            // Tabelle erstellen, falls nicht vorhanden
            $createSql = "CREATE TABLE IF NOT EXISTS users (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                username TEXT NOT NULL UNIQUE,
                email TEXT NOT NULL,
                password TEXT NOT NULL,
                verified INTEGER DEFAULT 0,
                verify_code TEXT,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP
            )";
            $pdo->exec($createSql);

            // Passwort hashen
            $hashed = password_hash($password, PASSWORD_DEFAULT);

            // Insert (verifiziert = 0)
            $stmt = $pdo->prepare('INSERT INTO users (username, email, password, verified, verify_code) VALUES (:username, :email, :password, 0, :code)');
            $stmt->execute([
                ':username' => $name,
                ':email' => $email,
                ':password' => $hashed,
                ':code' => $code
            ]);

            // Direkt als eingeloggt markieren und zum Dashboard weiterleiten
            $_SESSION['username'] = $name;
            header("Location: dashboard.php");
            exit();

        } catch (Exception $e) {
            // Fehler (z.B. Duplicate username)
            if (strpos($e->getMessage(), 'UNIQUE') !== false) {
                $error = 'Benutzername ist bereits vergeben.';
            } else {
                error_log('Register DB-Fehler: ' . $e->getMessage());
                $error = 'Ein Fehler ist aufgetreten. Versuche es später erneut.';
            }
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
    <title>Registrieren</title>
    <link rel="stylesheet" href="styles/style.css">
</head>
<body>
    <div class="container">
        <h1>Registrieren</h1>
        <form method="POST" action="">
            <input type="text" name="name" placeholder="Name" required><br><br>
            <input type="email" name="email" placeholder="E-Mail" required><br><br>
            <input type="password" name="password" placeholder="Passwort" required><br><br>
            <button type="submit" class="btn">Registrieren</button>
        </form>
        <?php if (!empty($error)) echo "<p style='color:red;'>$error</p>"; ?>
    </div>
</body>
</html>