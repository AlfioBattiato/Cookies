<?php
session_start();

// Funzione per impostare la lingua preferita
function setLanguage($lang) {
    $_SESSION['lang'] = $lang;
    setcookie('lang', $lang, time() + (3600 * 24 * 30)); // Cookie scade dopo 30 giorni
}

// Se il cookie della lingua è impostato, utilizza quella lingua, altrimenti utilizza la lingua del browser
if (isset($_COOKIE['lang'])) {
    $language = $_COOKIE['lang'];
} else {
    // Estrarre la lingua preferita dal browser
    $language = substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2);
}

// Impostare la lingua
if (isset($_GET['lang'])) {
    $language = $_GET['lang'];
    setLanguage($language);
}

// Connessione al database
$host = 'localhost';
$dbname = 'cookies';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->exec("SET CHARACTER SET utf8");
} catch (PDOException $e) {
    die("Errore nella connessione al database: " . $e->getMessage());
}

// Funzione per ottenere le traduzioni
function translate($key) {
    global $pdo, $language;
    $query = "SELECT translation FROM translations WHERE `key` = :key AND lang = :lang";
    $statement = $pdo->prepare($query);
    $statement->execute(array(':key' => $key, ':lang' => $language));
    $result = $statement->fetch(PDO::FETCH_ASSOC);
    return $result['translation'] ?? $key; // Ritorna la chiave se la traduzione non è disponibile
}

// Pagina HTML
?>
<!DOCTYPE html>
<html lang="<?php echo $language; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo translate('site_title'); ?></title>
</head>
<body>
    <h1><?php echo translate('welcome_message'); ?></h1>

    <?php
    // Recupera le notizie dal database e le visualizza
    $query = "SELECT * FROM news";
    $statement = $pdo->query($query);
    $news = $statement->fetchAll(PDO::FETCH_ASSOC);

    foreach ($news as $newsItem) {
        echo "<h2>" . translate('news_' . $newsItem['id'] . '_title') . "</h2>";
        echo "<p>" . translate('news_' . $newsItem['id'] . '_content') . "</p>";
    }
    ?>

    <p><?php echo translate('footer_message'); ?></p>

    <p><?php echo translate('language_selector'); ?>:
        <a href="?lang=en">English</a> |
        <a href="?lang=it">Italiano</a>
    </p>
</body>
</html>

