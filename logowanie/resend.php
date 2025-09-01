<?php
session_start();
require 'vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;


$host = "localhost";
$dbname = "auth";
$username = "root";
$password = "";

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Błąd połączenia z bazą: " . $e->getMessage());
}


$message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = trim($_POST['email']);

    if (!empty($email)) {

        $stmt = $pdo->prepare("SELECT id, nick, token, is_verified FROM user WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user) {
            if ($user['is_verified'] == 1) {
                $message = "To konto jest już aktywne.";
            } else {
                $token = $user['token'];
                $nick = $user['nick'];

                $link = "http://localhost/projekt_ogolny/aktywuj.php?token=" . urlencode($token);

                $tresc = '
<html>
<head>
  <meta charset="UTF-8">
  <style>
    body {
      background-color: #000000;
      font-family: Arial, sans-serif;
      margin: 0;
      padding: 0;
      color: #ffffff;
    }
    .header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 15px;
      background-color: #000000;
    }
    .header img {
      height: 50px;
    }
    .container {
      max-width: 600px;
      margin: 30px auto;
      background: #111111;
      padding: 30px;
      border-radius: 10px;
      text-align: center;
      color: #ffffff;
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

  <div class="header">
    <!-- <img src="<?php echo $logoFirmyUrl; ?>" alt="Logo Strony"> -->
    <!-- <img src="<?php echo $logoRangiUrl; ?>" alt="Logo Rangi"> -->
  </div>
  <div class="container">
    <h2>Cześć ' . htmlspecialchars($nick) . '!</h2>
    <p>Dziękujemy za rejestrację w <strong>strona testowa</strong>.</p>
    <p>Kliknij poniższy przycisk, aby przejść do strony aktywacji konta:</p>
    <a href="' . $link . '" class="btn">Przejdź do aktywacji</a>
    <p style="margin-top:30px; font-size: 0.9em; color: #aaa;">Jeśli to nie Ty się rejestrowałeś, zignoruj tę wiadomość.</p>
  </div>
</body>
</html>
';


                $mail = new PHPMailer(true);
                try {
                    $mail->CharSet = 'UTF-8';
                    $mail->isSMTP();
                    $mail->Host = 'smtp.gmail.com';
                    $mail->SMTPAuth = true;
                    $mail->Username = 'awadon.studio@gmail.com';
                    $mail->Password = 'qdeb cnhd kbik bpno';
                    $mail->SMTPSecure = 'tls';
                    $mail->Port = 587;

                    $mail->setFrom('awadon.studio@gmail.com', 'AWADON');
                    $mail->addAddress($email);
                    $mail->isHTML(true);
                    $mail->Subject = "Ponowna aktywacja konta AWADON";
                    $mail->Body = $tresc;

                    $mail->send();
                    $message = "Mail aktywacyjny został wysłany ponownie.";
                } catch (Exception $e) {
                    $message = "Błąd przy wysyłaniu maila: " . $mail->ErrorInfo;
                }
            }
        } else {
            $message = "Nie znaleziono konta dla tego adresu email.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <title>Wyślij ponownie e-mail aktywacyjny</title>
    <link rel="icon" href="img/przestawiene.png" type="image/x-icon">
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
            background-color: rgba(0, 0, 0, 0.7);
            padding: 40px;
            border-radius: 10px;
            text-align: center;
            max-width: 400px;
            width: 100%;
        }

        h1 {
            margin-bottom: 20px;
        }

        label {
            display: block;
            margin-bottom: 8px;
            text-align: left;
        }

        input[type="email"] {
            width: 100%;
            padding: 10px;
            border: none;
            border-radius: 5px;
            margin-bottom: 15px;
        }

        input[type="submit"] {
            padding: 12px 24px;
            background-color: #00cc88;
            border: none;
            border-radius: 5px;
            color: white;
            font-size: 16px;
            cursor: pointer;
        }

        input[type="submit"]:hover {
            background-color: #00aa70;
        }

        .message {
            margin-top: 15px;
            font-size: 0.9em;
            color: #ff4444;
        }

        .back-link {
            margin-top: 20px;
            display: block;
            color: darkred;
            text-decoration: none;
        }

        .back-link:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
<div class="overlay">
    <h1>Wyślij ponownie e-mail</h1>
    <form action="resend.php" method="post">
        <label for="email">Podaj adres e-mail:</label>
        <input type="email" id="email" name="email" required>
        <input type="submit" value="Wyślij ponownie">
    </form>
    <?php if ($message): ?>
        <div class="message"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>
    <a href="index.php" class="back-link">⬅ Wróć do logowania</a>
</div>
</body>
</html>
