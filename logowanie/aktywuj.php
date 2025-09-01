<?php
$token = $_GET['token'] ?? '';

if ($token) {
    $db = new mysqli("localhost", "root", "", "auth");
    if ($db->connect_error) {
        die("Błąd połączenia z bazą: " . $db->connect_error);
    }

    $stmt = $db->prepare("UPDATE user SET is_verified = 1, token = NULL WHERE token = ?");
    $stmt->bind_param("s", $token);
    $stmt->execute();

    if ($stmt->affected_rows > 0) {
        $message = "Konto zostało aktywowane! Możesz się teraz zalogować.";
    } else {
        $message = "Nieprawidłowy lub już użyty token aktywacyjny.";
    }

    $stmt->close();
    $db->close();
} else {
    $message = "Brak tokenu aktywacyjnego.";
}
?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <link rel="icon" href="img/przestawiene.png" type="image/x-icon">
    <meta charset="UTF-8">
    <title>Aktywacja konta</title>
    <style>
        body {
            margin: 0;
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            background-color: black;
            background-image: url('img/logo_wodne.png');
            background-repeat: no-repeat;
            background-position: center;
            background-size: 80%;
            font-family: Arial, sans-serif;
            color: white;
        }
        .overlay {
            background-color: rgba(0, 0, 0, 0.6);
            padding: 40px;
            border-radius: 10px;
            text-align: center;
            max-width: 500px;
        }
        h2, p {
            margin: 0 0 15px 0;
        }
        .btn {
            display: inline-block;
            margin-top: 20px;
            padding: 14px 24px;
            font-size: 16px;
            color: white;
            background-color: #00cc88;
            text-decoration: none;
            border-radius: 5px;
        }
        .btn:hover {
            background-color: #00aa70;
        }
    </style>
</head>
<body>
<div class="overlay">
    <h2>Aktywacja konta</h2>
    <p><?php echo htmlspecialchars($message); ?></p>
    <a href="index.php" class="btn">Przejdź do logowania</a>
</div>
</body>
</html>


