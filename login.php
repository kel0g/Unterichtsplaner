<?php
session_start();

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $name = htmlspecialchars(trim($_POST['name']));
    $password = htmlspecialchars(trim($_POST['password']));

    if (!empty($name) && !empty($password)) {
        $file = "users.txt";

        if (file_exists($file)) {
            $users = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            $login_success = false;

            foreach ($users as $user) {
                // Name: Max | Email: max@test.de | Passwort: 1234 | Code: 123456
                $parts = explode("|", $user);
                $userData = [];

                foreach ($parts as $part) {
                    list($key, $value) = array_map('trim', explode(":", $part));
                    $userData[$key] = $value;
                }

                if (
                    isset($userData['Name']) &&
                    isset($userData['Passwort']) &&
                    $userData['Name'] === $name &&
                    $userData['Passwort'] === $password
                ) {
                    $login_success = true;
                    $_SESSION['username'] = $name;
                    break;
                }
            }

            if ($login_success) {
                header("Location: dashboard.php");
                exit();
            } else {
                $error = "Name oder Passwort ist falsch.";
            }
        } else {
            $error = "Keine Benutzerdatei gefunden.";
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
