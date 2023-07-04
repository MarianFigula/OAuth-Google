<?php

require_once('config.php');
require_once 'glogin/vendor/autoload.php';
require_once "PHPGangsta/GoogleAuthenticator.php";

try {
    $db = new PDO("mysql:host=$hostname;dbname=$dbname", $username, $password);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
}catch (PDOException $e){
    echo $e->getMessage();
}

session_start();

$client = new Google\Client();

try {
    $client->setAuthConfig('client_secret.json');
} catch (\Google\Exception $e) {
    echo $e->getMessage();
}
$client->setRedirectUri("https://site87.webte.fei.stuba.sk/zadanie1/index-admin.php");
$client->addScope("email");
$client->addScope("profile");


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

    <title>Prihlásenie</title>
</head>
<body>

<div class="d-flex pt-2 justify-content-center"><h1>Prihlásenie</h1></div>
<header class="mb-3 mt-2" >
    <nav class="navbar navbar-expand bg-body-tertiary" data-bs-theme="dark">
        <div class="container-fluid">
            <div class="collapse navbar-collapse justify-content-center" id="navbarNavDropdown">
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link" aria-current="page" href="register.php">Registrácia</a>
                    </li>
            </div>
        </div>
    </nav>
</header>

<div class="container border rounded bg-secondary">
    <div class="width-50 p-5">
        <div class="border p-4 bg-light">
            <h2 class="justify-content-center center-text">Zadajte Vaše údaje</h2>
            <hr class="mb-4 mt-3">
            <!-- spravit name ako column TODO upload.php to action na novu stranku sa mi to prehodi -->
            <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="post">
                <div class="mb-3 row">
                    <!--<input type="hidden" name="id" value=" ">-->
                    <div class="col form-group">
                        <label for="login" class="form-label">Login*</label><input type="text" name="login" id="login" class="form-control" required>
                    </div>
                </div>
                <div class="mb-3 row">
                    <div class="col form-group">
                        <label for="password" class="form-label">Heslo*</label><input type="password" name="password" id="password" class="form-control" required>
                    </div>
                </div>
                <div class="mb-3 row">
                    <div class="col form-group">
                        <label for="2fa" class="form-label">Google Authenticator Kód*</label><input type="number" name="2fa" id="2fa" class="form-control" required>
                    </div>
                </div>
                <div class="mb-3 row">
                    <div class="col form-group">
                        <button type="submit" class="btn btn-primary" name="btn-login" id="btn-login">Prihlásiť sa</button>
                    </div>
                </div>
            </form>
            <div class="mb-3 row">
                <div class="col form-group">
                    <a type="button" class="btn btn-danger" href="<?php echo $client->createAuthUrl(); ?>">Prihlásiť sa pomocou Google</a>
                </div>
            </div>
            <?php
            if ($_SERVER["REQUEST_METHOD"] == "POST"){

                $sql = "SELECT fullname, email, login, password, created_at, 2fa_code FROM users WHERE login = :login";

                $stmt = $db->prepare($sql);
                $stmt->bindParam(":login", $_POST["login"], PDO::PARAM_STR);

                if ($stmt->execute()){
                    if ($stmt->rowCount() == 1){
                        $row = $stmt->fetch();
                        $hashed_password = $row["password"];

                        if (password_verify($_POST["password"], $hashed_password)){
                            //spravne heslo
                            $g2fa = new PHPGangsta_GoogleAuthenticator();
                            if ($g2fa->verifyCode($row["2fa_code"],  $_POST["2fa"], 2)){
                                // Uloz data pouzivatela do session.
                                $_SESSION["loggedin"] = true;
                                //$_SESSION["login"] = $row['login'];
                                $_SESSION["fullname"] = $row['fullname'];
                                $_SESSION["email"] = $row['email'];
                                $_SESSION["created_at"] = $row['created_at'];
                                $_SESSION["login"] = $row['login'];

                                $sql = "INSERT INTO user_login (user_email, login_type) VALUES (:user_email,:login_type)";
                                $login_type = "Účet na stránke";
                                $stmt = $db->prepare($sql);
                                $stmt->bindParam(":user_email",$row['email'], PDO::PARAM_STR);
                                $stmt->bindParam(":login_type",$login_type,PDO::PARAM_STR);
                                $stmt->execute();


                                header("location: index-admin.php");
                            }else{
                                echo "<p class='text-danger'>Neplatný kód 2FA.</p>";
                            }
                        }else{
                            echo "<p class='text-danger'>Nesprávny login alebo heslo</p>";
                        }
                    }else{
                        echo "<p class='text-danger'>Nesprávny login alebo heslo.</p>";
                    }
                }else{
                    echo "<p class='text-danger'>Niečo sa pokazilo!</p>";
                }
                unset($stmt);
                unset($db);
            }
            ?>
        </div>
    </div>
</div>
<footer class="d-flex justify-content-center py-3 my-4 border-top border-dark-subtle">
    <span class="text-muted">© 2023 Marián Figula</span>
</footer>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js" integrity="sha384-w76AqPfDkMBDXo30jS1Sgez6pr3x5MlQ1ZAGC+nuZB+EYdgRZgiwxhTBTkF7CXvN" crossorigin="anonymous"></script>
<script src="https://cdn.datatables.net/1.13.2/js/dataTables.bootstrap5.min.js"></script>
</body>
</html>

