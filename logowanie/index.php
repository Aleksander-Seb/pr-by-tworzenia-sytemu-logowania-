<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
require __DIR__ . '/vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

session_start();

$loginError = "";
$loginSuccess = "";

$registerError = "";
$registerSuccess = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] == "login") {
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'];
    $db = new mysqli("localhost", "root", "", "auth");

    $q = $db->prepare("SELECT * FROM user WHERE email = ? LIMIT 1");
    $q->bind_param("s", $email);
    $q->execute();
    $result = $q->get_result();
    $userRow = $result->fetch_assoc();

    if ($userRow === null || !password_verify($password, $userRow['passwordHash'])) {
        $loginError = "Błędny login lub hasło";
    } elseif ($userRow['is_verified'] != 1) {
        $loginError = "Konto nie jest aktywowane. Sprawdź e-mail i kliknij link aktywacyjny.";
    } else {
        $_SESSION['email'] = $userRow['email'];
        $_SESSION['ranga'] = $userRow['ranga'];
        $_SESSION['nick'] = $userRow['nick'];
        $_SESSION['uid'] = $userRow['uid'];
        $_SESSION['plec'] = $userRow['plec'];
        $_SESSION['data_urodzenia'] = $userRow['data_urodzenia'];
        $loginSuccess = "Zalogowano poprawnie";
        header("Location: main.php");
        exit();
    }
}



if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] == "register") {
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'];
    $passwordRepeat = $_POST['passwordRepeat'];
    $nick = $_POST['nick'] ?? '';
    $plec = $_POST['plec'] ?? 'inny';
    $data_urodzenia = $_POST['data_urodzenia'] ?? null;

    $wiekOK = false;
    if ($data_urodzenia) {
        $today = new DateTime();
        $birthdate = new DateTime($data_urodzenia);
        $age = $today->diff($birthdate)->y;
        if ($age >= 18) {
            $wiekOK = true;
        }
    }

    if ($password !== $passwordRepeat) {
        $registerError = "Hasła nie są zgodne - spróbuj ponownie!";
    } elseif (!$wiekOK) {
        $registerError = "Musisz mieć co najmniej 18 lat!";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $registerError = "Nieprawidłowy format adresu e-mail!";
    } else {
        $domain = substr(strrchr($email, "@"), 1);


        if (!checkdnsrr($domain, "MX")) {
            $registerError = "Domena e-maila nie istnieje!";
        } else {
            $db = new mysqli("localhost", "root", "", "auth");


            $checkEmail = $db->prepare("SELECT id FROM user WHERE email = ?");
            $checkEmail->bind_param("s", $email);
            $checkEmail->execute();
            $emailResult = $checkEmail->get_result();


            $checkNick = $db->prepare("SELECT id FROM user WHERE nick = ?");
            $checkNick->bind_param("s", $nick);
            $checkNick->execute();
            $nickResult = $checkNick->get_result();

            if ($emailResult->num_rows > 0) {
                $registerError = "Ten e-mail już jest zarejestrowany!";
            } elseif ($nickResult->num_rows > 0) {
                $registerError = "Ten nick jest już zajęty!";
            } else {

                do {
                    $uid = str_pad(strval(mt_rand(0, 999999999)), 9, "0", STR_PAD_LEFT);
                    $check = $db->prepare("SELECT id FROM user WHERE uid = ?");
                    $check->bind_param("s", $uid);
                    $check->execute();
                    $checkResult = $check->get_result();
                } while ($checkResult->num_rows > 0);

                $token = bin2hex(random_bytes(16));
                $passwordHash = password_hash($password, PASSWORD_ARGON2ID);

                $q = $db->prepare("INSERT INTO user (email, passwordHash, ranga, uid, nick, plec, data_urodzenia, token, is_verified) VALUES (?, ?, 'D1', ?, ?, ?, ?, ?, 0)");
                $q->bind_param("sssssss", $email, $passwordHash, $uid, $nick, $plec, $data_urodzenia, $token);
                $result = $q->execute();

                if ($result) {
                    $link = "http://localhost/projekt_ogolny/aktywuj.php?token=$token";

                    $userId = $db->insert_id;

                    $query = "SELECT ranga FROM user WHERE id = $userId";
                    $resultRanga = $db->query($query);
                    $rowRanga = $resultRanga->fetch_assoc();
                    $ranga = $rowRanga['ranga'] ?? 'D1';
                    $logoRangiUrl = "https://twojadomena.pl/img/" . $ranga . ".png";
                    $logoFirmyUrl = "https://twojadomena.pl/img/przestawiene.png";





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
                        $mail->Encoding = 'base64';
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
                        $mail->Subject = "Aktywacja konta AWADON";
                        $mail->Body = $tresc;

                        $mail->send();
                        $registerSuccess = "Konto utworzono! Sprawdź e-mail i kliknij link aktywacyjny.";
                    } catch (Exception $e) {
                        $registerError = "Błąd przy wysyłaniu e-maila aktywacyjnego: " . $mail->ErrorInfo;
                    }
                }

            }
        }
    }

}

?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Logowanie</title>
    <link rel="stylesheet" href="style.css">
    <link rel="icon" href="img/przestawiene.png" type="image/x-icon">
</head>
<body>

<header class="header_gorny">
    <div class="logo_i_nazwa">
        <img class="logo1" src="img/przestawiene.png" alt="logo" />
        <span class="nazwa">strona testowa</span>
    </div>
</header>

<nav class="pasek_przyciskow" id="pasek_przyciskow">
    <div class="logo_male" id="logo_male_container">
        <img src="img/przestawiene.png" alt="logo" class="logo_male_img" id="logo_male" />
    </div>
</nav>

<main class="rejestracja_2">
    <div class="login">
        <h1>Zaloguj się</h1>
        <form action="index.php" method="post">
            <label for="emailInput">Email:</label>
            <input type="email" name="email" id="emailInput" required>
            <label for="passwordInput">Hasło:</label>
            <div class="password-wrapper">
                <input type="password" name="password" id="passwordInput" required>
                <button type="button" class="toggle-password" onclick="togglePassword('passwordInput', this)">👁</button>
            </div>
            <input type="hidden" name="action" value="login">
            <input type="submit" value="Zaloguj">
        </form>
        <?php if (!empty($loginError)) : ?>
            <div class="error"><?php echo $loginError; ?></div>
        <?php endif; ?>
        <?php if (!empty($loginSuccess)) : ?>
            <div class="success"><?php echo $loginSuccess; ?></div>
        <?php endif; ?>
    </div>

    <div class="login">
        <h1>Zarejestruj się</h1>
        <form action="index.php" method="post">
            <label for="nickInput">Nick:</label>
            <input type="text" name="nick" id="nickInput" required>
            <label for="plecInput">Płeć:</label>
            <select name="plec" id="plecInput" required>
                <option value="mezczyzna">Mężczyzna</option>
                <option value="kobieta">Kobieta</option>
                <option value="inny">Inna</option>
            </select>
            <label for="dataUrodzeniaInput">Data urodzenia:</label>
            <input type="date" name="data_urodzenia" id="dataUrodzeniaInput" required>
            <label for="emailInput2">Email:</label>
            <input type="email" name="email" id="emailInput2" required>
            <label for="passwordInput2">Hasło:</label>
            <div class="password-wrapper">
                <input type="password" name="password" id="passwordInput2" required>
                <button type="button" class="toggle-password" onclick="togglePassword('passwordInput2', this)">👁</button>
            </div>
            <label for="passwordRepeatInput">Hasło ponownie:</label>
            <div class="password-wrapper">
                <input type="password" name="passwordRepeat" id="passwordRepeatInput" required>
                <button type="button" class="toggle-password" onclick="togglePassword('passwordRepeatInput', this)">👁</button>
            </div>
            <input type="hidden" name="action" value="register">
            <input type="submit" value="Zarejestruj">
        </form>
        <?php if (!empty($registerError)) : ?>
            <div class="error"><?php echo $registerError; ?></div>
        <?php endif; ?>
        <?php if (!empty($registerSuccess)) : ?>
            <div class="success"><?php echo $registerSuccess; ?></div>
        <?php endif; ?>
        <div class="mailAktywacyjny" >
            <a href="resend.php" style="color: #ff0000; text-decoration: none;">
                Nie dostałeś maila aktywacyjnego?<br> Wyślij ponownie
            </a>
        </div>
    </div>
</main>

<nav class="pasek_informujący" id="pasek_informujący"></nav>

<footer class="footer_dolny">
    <div class="footer-gify">
        <img class="logo1" src="img/logo D.gif" alt="logo" />
        <img class="logo1" src="img/logo N.gif" alt="logo" />
        <img class="logo1" src="img/logo C.gif" alt="logo" />
    </div>
</footer>

<script src="script.js"></script>
</body>
</html>