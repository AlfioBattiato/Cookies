<?php
session_start();

// Funzione per impostare la lingua preferita
function setLanguage($lang)
{
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
function translate($key)
{
    global $pdo, $language;
    $query = "SELECT translation FROM translations WHERE `key` = :key AND lang = :lang";
    $statement = $pdo->prepare($query);
    $statement->execute(array(':key' => $key, ':lang' => $language));
    $result = $statement->fetch(PDO::FETCH_ASSOC);
    return $result['translation'] ?? $key; // Ritorna la chiave se la traduzione non è disponibile
}

// Aggiungi un nuovo articolo al database se i dati sono stati inviati dal form
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title_it = $_POST['title_it'];
    $content_it = $_POST['content_it'];
    $title_en = $_POST['title_en'];
    $content_en = $_POST['content_en'];

    // Inserisci l'articolo in italiano
    $query_it = "INSERT INTO news (title, content) VALUES (:title_it, :content_it)";
    $statement_it = $pdo->prepare($query_it);
    $statement_it->execute(array(':title_it' => $title_it, ':content_it' => $content_it));

    // Recupera l'id dell'ultimo articolo inserito
    $last_id = $pdo->lastInsertId();

    // Inserisci le traduzioni dell'articolo in italiano
    $query_it_title_translation = "INSERT INTO translations (`key`, translation, lang) VALUES (:key_it_title, :translation_it_title, 'it')";
    $statement_it_title_translation = $pdo->prepare($query_it_title_translation);
    $statement_it_title_translation->execute(array(':key_it_title' => 'news_' . $last_id . '_title', ':translation_it_title' => $title_it));

    $query_it_content_translation = "INSERT INTO translations (`key`, translation, lang) VALUES (:key_it_content, :translation_it_content, 'it')";
    $statement_it_content_translation = $pdo->prepare($query_it_content_translation);
    $statement_it_content_translation->execute(array(':key_it_content' => 'news_' . $last_id . '_content', ':translation_it_content' => $content_it));

    // Inserisci le traduzioni dell'articolo in inglese
    $query_en_title_translation = "INSERT INTO translations (`key`, translation, lang) VALUES (:key_en_title, :translation_en_title, 'en')";
    $statement_en_title_translation = $pdo->prepare($query_en_title_translation);
    $statement_en_title_translation->execute(array(':key_en_title' => 'news_' . $last_id . '_title', ':translation_en_title' => $title_en));

    $query_en_content_translation = "INSERT INTO translations (`key`, translation, lang) VALUES (:key_en_content, :translation_en_content, 'en')";
    $statement_en_content_translation = $pdo->prepare($query_en_content_translation);
    $statement_en_content_translation->execute(array(':key_en_content' => 'news_' . $last_id . '_content', ':translation_en_content' => $content_en));
}
?>

<!DOCTYPE html>
<html lang="<?php echo $language; ?>">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo translate('site_title'); ?></title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
</head>

<body>
    <div class="container">
        <h1><?php echo translate('welcome_message'); ?></h1>

        <div class="row">
            <div class="col">
                <h2><?php echo translate('create_news'); ?></h2>
                <p><?php echo translate('language_selector'); ?>:
                    <a href="?lang=en">English</a> |
                    <a href="?lang=it">Italiano</a>
                </p>
                <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                    <div class="row">
                        <div class="col-6">
                            <div class="mb-3">
                                <label for="title_it" class="form-label"><?php echo translate('title'); ?>
                                    (Italiano)</label>
                                <input type="text" class="form-control" id="title_it" name="title_it" required>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="mb-3">
                                <label for="title_en" class="form-label"><?php echo translate('title'); ?>
                                    (English)</label>
                                <input type="text" class="form-control" id="title_en" name="title_en" required>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="mb-3">
                                <label for="content_it" class="form-label"><?php echo translate('content'); ?>
                                    (Italiano)</label>
                                <textarea class="form-control" id="content_it" name="content_it" rows="3"
                                    required></textarea>
                            </div>
                        </div>

                        <div class="col-6">
                            <div class="mb-3">
                                <label for="content_en" class="form-label"><?php echo translate('content'); ?>
                                    (English)</label>
                                <textarea class="form-control" id="content_en" name="content_en" rows="3"
                                    required></textarea>
                            </div>
                        </div>





                    </div>
                    <button type="submit" class="btn btn-primary"><?php echo translate('submit'); ?></button>
                </form>
            </div>
        </div>

        <hr>

        <h2><?php echo translate('latest_news'); ?></h2>
        <div class="row row-cols-1 row-cols-md-2 g-4">
            <?php
            // Recupera le notizie dal database e le visualizza
            $query = "SELECT * FROM news";
            $statement = $pdo->query($query);
            $news = $statement->fetchAll(PDO::FETCH_ASSOC);

            foreach ($news as $newsItem) {
                echo '<div class="col">';
                echo '<div class="card">';
                echo '<div class="card-body">';
                echo '<h5 class="card-title">' . translate('news_' . $newsItem['id'] . '_title') . '</h5>';
                echo '<p class="card-text">' . translate('news_' . $newsItem['id'] . '_content') . '</p>';
                echo '</div>';
                echo '</div>';
                echo '</div>';
            }
            ?>
        </div>

      <div class="bg-dark p-5 text-white mt-5">
      <p><?php echo translate('footer_message'); ?></p>
      </div>


    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-Hf4y7Ci4oZ6LZzR08LyYR3zkkYoNK1X/uxPapz7D4zK8S5a+V56iFiqs2ZyJhC2A"
        crossorigin="anonymous"></script>
</body>

</html>