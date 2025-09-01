<?php
session_start();
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: index.php");
    exit();
}

if (!isset($_SESSION['email'])) {
    header("Location: index.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>strona główna</title>
    <link rel="stylesheet" href="style.css" />
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
    <button>##1##</button>
    <button>##2##</button>
    <button>##3##</button>
    <button>##4##</button>
    <button>##5##</button>
    <button>##6##</button>
    <button>##7##</button>
</nav>

<main>
    <p>Treść strony tutaj...</p>
    <div style="height:1500px;"></div>
    <div>
        <strong>Nick:</strong> <?php echo htmlspecialchars($_SESSION['nick']); ?><br>
        <strong>Płeć:</strong> <?php echo htmlspecialchars($_SESSION['plec'] ?? ''); ?><br>
        <strong>Data urodzenia:</strong> <?php echo htmlspecialchars($_SESSION['data_urodzenia'] ?? ''); ?><br>
    </div>
</main>

<nav id="pasek_informujący">
    <img id="hidden-ranga" src="<?php
    $ranga = isset($_SESSION['ranga']) ? htmlspecialchars($_SESSION['ranga']) : 'ERROR404';
    echo "img/{$ranga}.png";
    ?>" alt="ranga" style="display: none;" />

    <div class="informacyjny-wrapper">
        <div class="lewa">
            <div id="rangaLogoMini" class="logo_mini_placeholder"></div>
        </div>
        <div class="srodek" id="nickUzytkownika">
            <?php echo isset($_SESSION['nick']) ? htmlspecialchars($_SESSION['nick']) : 'Nieznany'; ?>
        </div>
        <div class="prawa">
            <form method="post" action="logout.php">
                <button type="submit" class="logowanie1">Wyloguj</button>
            </form>
        </div>
    </div>
</nav>

<footer class="footer_dolny">
    <div class="footer-lewo">
        <?php
        if (isset($_SESSION['ranga'])) {
            $ranga = $_SESSION['ranga'];
            $sciezka = "img/" . htmlspecialchars($ranga) . ".png";
            if (file_exists($sciezka)) {
                echo '<img class="logo1" src="' . $sciezka . '" alt="ranga ' . htmlspecialchars($ranga) . '" />';
            } else {
                echo '<img class="logo1" src="img/ERROR404.png" alt="Brak pliku dla rangi" />';
            }
        } else {
            echo '<img class="logo1" src="img/ERROR404.png" alt="Brak rangi w sesji" />';
        }
        ?>
    </div>


    <div class="footer-srodek">
        <?php if (isset($_SESSION['nick'])) : ?>
            <p><?php echo htmlspecialchars($_SESSION['nick']); ?></p>
        <?php endif; ?>
    </div>


    <div class="footer-prawo">
        <?php if (isset($_SESSION['email'])) : ?>
            <p><?php echo htmlspecialchars($_SESSION['email']); ?></p>
        <?php endif; ?>
        <?php if (isset($_SESSION['uid'])) : ?>
            <p>#<?php echo htmlspecialchars($_SESSION['uid']); ?></p>
        <?php endif; ?>
    </div>
</footer>

<script src="script.js"></script>
</body>
</html>