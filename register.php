<?php
session_start();

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $name = htmlspecialchars(trim($_POST['name']));
    $email = htmlspecialchars(trim($_POST['email']));
    $password = htmlspecialchars(trim($_POST['password']));

    if (!empty($name) && !empty($email) && !empty($password)) {
        // random Code
        $code = rand(100000, 999999);

        // Speicherung in der Textdatei
        $data = "Name: $name | Email: $email | Passwort: $password | Code: $code" . PHP_EOL;
        file_put_contents("textdateien/users.txt", $data, FILE_APPEND | LOCK_EX);

        // Code per E-Mail senden
        $subject = "Dein Bestätigungscode";
        $message = "Hallo $name,\n\nDein Bestätigungscode lautet: $code\n\nGib diesen Code auf der Webseite ein, um deine Registrierung abzuschließen.";
        $headers = "From: noreply@unterrichtsplan.de";

        // funktionirt nur auf server
        mail($email, $subject, $message, $headers);

        // Code und E-Mail in Session speichern, um sie in verify.php zu prüfen
        $_SESSION['email'] = $email;
        $_SESSION['code'] = $code;

        // Weiterleitung zu Verifizierung
        header("Location: verify.php");
        exit();
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
