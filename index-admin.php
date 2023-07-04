<?php

require_once('config.php');
require_once 'glogin/vendor/autoload.php';
require_once 'PHPGangsta/GoogleAuthenticator.php';
require_once 'insert_edit.php';

session_start();

$success = false;
$_SESSION["show_message"] = false;
$client = new Google\Client();

try {
    $db = new PDO("mysql:host=$hostname;dbname=$dbname", $username, $password);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
}catch (Exception $e){
    echo $e->getMessage();
}

$client = new Google\Client();

function generatePassword(): string{
    $alphabet = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890';
    $pass = array(); //remember to declare $pass as an array
    $alphaLength = strlen($alphabet) - 1; //put the length -1 in cache
    for ($i = 0; $i < 16; $i++) {
        $n = rand(0, $alphaLength);
        $pass[] = $alphabet[$n];
    }
    return implode($pass); //turn the array into a string
}

function userExist($db, $email): bool{
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


try {
    $client->setAuthConfig('client_secret.json');
} catch (\Google\Exception $e) {
    echo $e->getMessage();
}
$client->setRedirectUri("https://site87.webte.fei.stuba.sk/zadanie1/index-admin.php");
$client->addScope("email");
$client->addScope("profile");

if (isset($_GET['code'])) {
    //TODO: FETCH
    $token = $client->fetchAccessTokenWithAuthCode($_GET['code']);
    $client->setAccessToken($token);

    $gauth = new Google\Service\Oauth2($client);

    $google_info = $gauth->userinfo->get();
    $email = $google_info->email;
    $name = $google_info->name;
    $_SESSION["login"] = $name;
    $_SESSION["password"] = password_hash(generatePassword(), PASSWORD_ARGON2ID);
    $_SESSION["email"] = $email;
    $_SESSION["login"] = $name;
    // TODO alebo znovu vytvorit authURL
    if (userExist($db, $email) == false) {
        $g2fa = new PHPGangsta_GoogleAuthenticator();
        try {
            $user_secret = $g2fa->createSecret();
        } catch (Exception $e) {
        }
        $sql = "INSERT INTO users (fullname, login, email, password, 2fa_code) VALUES (:fullname, :login, :email, :password, :2fa_code)";

        // Bind parametrov do SQL
        $stmt = $db->prepare($sql);

        $stmt->bindParam(":fullname", $name, PDO::PARAM_STR);
        $stmt->bindParam(":login", $name, PDO::PARAM_STR);
        $stmt->bindParam(":email", $email, PDO::PARAM_STR);
        $stmt->bindParam(":password", $_SESSION["password"], PDO::PARAM_STR);
        $stmt->bindParam(":2fa_code", $user_secret, PDO::PARAM_STR);
        $stmt->execute();

    }

    $sql = "INSERT INTO user_login (user_email, login_type) VALUES (:user_email,:login_type)";
    $login_type = "Google";
    $stmt = $db->prepare($sql);
    $stmt->bindParam(":user_email",$email, PDO::PARAM_STR);
    $stmt->bindParam(":login_type",$login_type,PDO::PARAM_STR);
    $stmt->execute();

    $_SESSION["loggedin"] = true;

    header("location: index-admin.php");
}


if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header("Location: login.php");
    exit;
}

try {
    if (!empty($_POST)) {
        if (isset($_POST['clicked_button'])) {

            $clicked_button_name = array_keys($_POST)[2] . "";
            $position = strpos($clicked_button_name, '-');
            $id = intval(substr($clicked_button_name, $position + 1, strlen($clicked_button_name)));


            $sql = "DELETE FROM person WHERE person.id = $id";
            $stmt = $db->prepare($sql);
            $success = $stmt->execute();

            if ($success) {
                insertUserActivity($db,$sql,$_SESSION["email"]);
                $_SESSION["show_message"] = true;
            }
        }
    }

    $query_oh_winners = "SELECT * FROM person AS p";
    $stmt = $db->query($query_oh_winners);
    $stmt->execute();
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    echo $e->getMessage();
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
    <link rel="stylesheet" href="css/icons.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.3/jquery.min.js" integrity="sha512-STof4xm1wgkfm7heWqFJVn58Hm3EtS31XFaagaa8VMReCXAkQnJZ+jEy8PCC/iT18dFy95WcExNHFTqLyp72eQ==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/2.0.1/css/toastr.css" rel="stylesheet"/>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/2.0.1/js/toastr.js"></script>
    <title>Index Admin</title>
</head>
<body>


<div class="d-flex pt-2 justify-content-center"><h1>Admin Panel</h1></div>

<header class="mb-3 mt-2" >
    <nav class="navbar navbar-expand-lg bg-body-tertiary" data-bs-theme="dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="#"><?php echo $_SESSION["login"] ?></a>
            <button class="navbar-toggler bg-light" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNavDropdown" aria-controls="navbarNavDropdown" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNavDropdown">
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link active" aria-current="page" href="index-admin.php">Domov</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" aria-current="page" href="admin-add.php">Pridať športovca | Pridať umiestnenie</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="admin-history-log.php">História prihlásení</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="logout.php">Odhlásiť sa</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
</header>

<div class="container-md d-flex rounded-2 p-2 justify-content-center mb-3 mt-4 bg-secondary text-light">
    <h2>Tabuľka olympijských športovcov</h2>
</div>


<div class="container-md border pt-2 mb-3 table-responsive">
    <form action="#" method="post">
        <table class="table table-bordered table-striped" id="admin-oh-sportsmen">
            <thead>
            <tr>
                <th>MENO</th>
                <th>PRIEZVISKO</th>
                <th>ROK NARODENIA</th>
                <th>MIESTO NARODENIA</th>
                <th>KRAJINA NARODENIA</th>
                <th>ROK ÚMRTIA</th>
                <th>MIESTO ÚMRTIA</th>
                <th>KRAJINA ÚMRTIA</th>
                <th>EDITOVAŤ / VYMAZAŤ</th>
            </tr>
            </thead>
            <tbody>
            <?php
            foreach ($results as $result){

                echo "<tr><td>" . $result["name"] . "</td><td>" . $result["surname"] . "</td><td>" . $result["birth_day"] .
                    "</td><td>" . $result["birth_place"] . "</td><td>" . $result["birth_country"] . "</td><td>" .
                    $result["death_day"] . "</td><td>" . $result["death_place"] ."</td><td>" . $result["death_country"].
                    "</td><td class='dt-body-center'><input type='hidden' name='clicked_button' id='clicked_button' value=''><a type='submit' role='button' class='btn btn-warning edit-btn' href='admin-edit-person.php?id=". $result["id"] ."' ></a>  <button type='submit' class='btn btn-danger ms-2 del-btn' name=del-".$result["id"]."></button></td></tr>";
            }
            ?>
            </tbody>
        </table>
    </form>
</div>

<footer class="d-flex justify-content-center py-3 my-4 mt-4 border-top border-dark-subtle">
    <span class="text-muted">© 2023 Marián Figula</span>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js" integrity="sha384-w76AqPfDkMBDXo30jS1Sgez6pr3x5MlQ1ZAGC+nuZB+EYdgRZgiwxhTBTkF7CXvN" crossorigin="anonymous"></script>
<script src="https://cdn.datatables.net/1.13.2/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.2/js/dataTables.bootstrap5.min.js"></script>
<script src="scripts/admin.js"></script>

<?php
if ($_SESSION["show_message"] == true) {
    echo "<script type='text/javascript'>toastr.success('Športovec úspešne odstránený')</script>";
    $_SESSION["show_message"]=false; // unset the session variable to prevent it from showing again
}
?>

</body>
</html>
