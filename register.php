<?php

require_once('config.php');
require_once 'glogin/vendor/autoload.php';
require_once 'PHPGangsta/GoogleAuthenticator.php';
require_once 'insert_edit.php';

try {
    $db = new PDO("mysql:host=$hostname;dbname=$dbname", $username, $password);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
}catch (PDOException $e){
    echo $e->getMessage();
}

function checkEmpty($field) {
    if (empty(trim($field))) {
        return true;
    }
    return false;
}
function checkLength($field, $min, $max) {
    $string = trim($field);
    $length = strlen($string);
    if ($length < $min || $length > $max) {
        return false;
    }
    return true;
}
function checkUsername($username) {
    // Funkcia pre kontrolu, ci username obsahuje iba velke, male pismena, cisla a podtrznik.
    if (!preg_match('/^[a-zA-Z0-9_]+$/', trim($username))) {
        return false;
    }
    return true;
}
function checkGmail($email) {
    // Funkcia pre kontrolu, ci zadany email je gmail.
    if (!preg_match('/^[\w.+\-]+@gmail\.com$/', trim($email))) {
        return false;
    }
    return true;
}

function userExist($db, $email) {
    $exist = false;

    $param_email = trim($email);

    $sql = "SELECT id FROM users WHERE email = :email";
    $stmt = $db->prepare($sql);
    $stmt->bindParam(":email", $param_email, PDO::PARAM_STR);

    $stmt->execute();

    if ($stmt->rowCount() == 1) {
        $exist = true;
    }
    unset($stmt);
    return $exist;
}
$client = new Google\Client();

try {
    $client->setAuthConfig('client_secret.json');
} catch (\Google\Exception $e) {
    echo $e->getMessage();
}

$client->setRedirectUri("https://site87.webte.fei.stuba.sk/zadanie1/index-admin.php");
$client->addScope("email");
$client->addScope("profile");


if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $errmsg = "";

    // Validacia username
    if (checkEmpty($_POST['login']) === true) {
        $errmsg .= "<p class='text-danger'>Zadajte login.</p>";
    } elseif (checkLength($_POST['login'], 6,32) === false) {
        $errmsg .= "<p class='text-danger'>Login musí mať 6 až 32 znakov.</p>";
    }elseif (checkLength($_POST['password'], 6,32) === false) {
        $errmsg .= "<p class='text-danger'>Heslo musí mať 6 až 32 znakov.</p>";
    } elseif (checkUsername($_POST['login']) === false) {
        $errmsg .= "<p class='text-danger'>Login môže obsahovať iba veľké, malé písmená, číslice a podtržník.</p>";
    }elseif (checkFullname($_POST['firstname'], $_POST['lastname']) == false){
        $errmsg .= "<p class='text-danger'>Meno alebo priezvisko je zadané nesprávne.</p>";
    }

    if (userExist($db, $_POST['email']) === true) {
        $errmsg .= "<p class='text-danger'>Používateľ s týmto e-mailom už existuje.</p>";

    }
    if (checkGmail($_POST['email']) && userExist($db, $_POST['email']) === false) {
        echo "<script>alert('S Google účtom sa prihláste pomocou tlačidla Prihlásiť sa pomocou Google');
                window.location.href='login.php';
            </script>";
        //header("Location: login.php");
        exit();
    }

    if (empty($errmsg)) {
        $sql = "INSERT INTO users (fullname, login, email, password, 2fa_code) VALUES (:fullname, :login, :email, :password, :2fa_code)";

        $fullname = $_POST['firstname'] . ' ' . $_POST['lastname'];
        $email = $_POST['email'];
        $login = $_POST['login'];
        $hashed_password = password_hash($_POST['password'], PASSWORD_ARGON2ID);

        // 2FA pomocou PHPGangsta kniznice
        $g2fa = new PHPGangsta_GoogleAuthenticator();
        try {
            $user_secret = $g2fa->createSecret();
        } catch (Exception $e) {
        }
        $codeURL = $g2fa->getQRCodeGoogleUrl('Olympic Games', $user_secret);

        $stmt = $db->prepare($sql);

        $stmt->bindParam(":fullname", $fullname, PDO::PARAM_STR);
        $stmt->bindParam(":login", $login, PDO::PARAM_STR);
        $stmt->bindParam(":email", $email, PDO::PARAM_STR);
        $stmt->bindParam(":password", $hashed_password, PDO::PARAM_STR);
        $stmt->bindParam(":2fa_code", $user_secret, PDO::PARAM_STR);

        if ($stmt->execute()) {
            // qrcode je premenna, ktora sa vykresli vo formulari v HTML.
            $qrcode = $codeURL;
        } else {
            echo "Ups. Nieco sa pokazilo";
        }

        unset($stmt);
    }
    unset($pdo);
}
?>

<!doctype html>
<html lang="sk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-GLhlTQ8iRABdZLl6O3oVMWSktQOp6b7In1Zl3/Jr59b6EGGoI1aFkw7cmDA6j6gD" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.2/css/dataTables.bootstrap5.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.3/jquery.min.js" integrity="sha512-STof4xm1wgkfm7heWqFJVn58Hm3EtS31XFaagaa8VMReCXAkQnJZ+jEy8PCC/iT18dFy95WcExNHFTqLyp72eQ==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js" integrity="sha384-w76AqPfDkMBDXo30jS1Sgez6pr3x5MlQ1ZAGC+nuZB+EYdgRZgiwxhTBTkF7CXvN" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="css/utility.css">

    <title>Registrácia</title>
</head>
<body>

<div class="d-flex pt-2 justify-content-center"><h1>Registrácia</h1></div>

<header class="mb-3 mt-2" >
    <nav class="navbar navbar-expand bg-body-tertiary" data-bs-theme="dark">
        <div class="collapse navbar-collapse justify-content-center" id="navbarNavDropdown">
            <ul class="navbar-nav">
                <li class="nav-item">
                    <a class="nav-link" aria-current="page" href="login.php">Prihlásenie</a>
                </li>
        </div>
    </nav>
</header>

<div class="container border rounded bg-secondary">
    <div class="width-50 p-5">
        <div class="border p-4 bg-light">
            <h2 class="justify-content-center center-text">Zadajte Vaše údaje</h2>
            <hr class="mb-4 mt-3">
            <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']);?>" method="post">
                <div class="mb-3 row">
                    <input type="hidden" name="id" value=" ">
                    <div class="col form-group">
                        <label for="firstname" class="form-label">Meno*</label><input type="text" name="firstname" id="firstname" class="form-control" required>
                    </div>
                    <div class="col form-group">
                        <label for="lastname" class="form-label">Priezvisko*</label><input type="text" name="lastname" id="lastname" class="form-control" required>
                    </div>
                </div>
                <div class="mb-3 row">
                    <div class="col form-group">
                        <label for="email" class="form-label">Email*</label><input type="email" name="email" id="email" class="form-control" placeholder="name@example.com" required>
                    </div>
                    <div class="col form-group">
                        <label for="login" class="form-label">Login*</label><input type="text" name="login" id="login" class="form-control" required>
                    </div>
                    <div class="col form-group">
                        <label for="password" class="form-label">Heslo*</label><input type="password" name="password" id="password" class="form-control" required>
                    </div>
                </div>
                <div class="mb-3 row">
                    <div class="col form-group">
                        <button type="submit" class="btn btn-primary" name="btn-edit-person" id="btn-edit-person">Registrovať</button>
                    </div>
                </div>
                <?php
                if (!empty($errmsg)) {
                    echo $errmsg;
                }
                if (isset($qrcode)) {
                    $message = '<p>Naskenujte QR kód do aplikácie Authenticator pre 2FA: <br><img src="'.$qrcode.'" alt="qr kod pre aplikaciu authenticator"></p><br>';

                    echo $message;
                    echo '<p>Teraz sa môžete prihlásiť: <a href="login.php" class="btn btn-primary" role="button">Prihlásenie</a></p>';
                }
                ?>
            </form>
        </div>
    </div>
</div>

<footer class="d-flex justify-content-center py-3 my-4 mt-4 border-top border-dark-subtle">
    <span class="text-muted">© 2023 Marián Figula</span>
</footer>

<script src="https://cdn.datatables.net/1.13.2/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.2/js/dataTables.bootstrap5.min.js"></script>

</body>
</html>
