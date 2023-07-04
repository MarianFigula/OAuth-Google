<?php

require_once('config.php');

session_start();

if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header("Location: login.php");
    exit;
}

try {
    $db = new PDO("mysql:host=$hostname;dbname=$dbname", $username, $password);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $sqlLogin = "SELECT * from user_login where user_email='{$_SESSION["email"]}'";
    $stmt = $db->query($sqlLogin);
    $loginResults = $stmt->fetchAll(PDO::FETCH_ASSOC);


    $sqlActivity = "SELECT * from user_activity where user_email='{$_SESSION["email"]}'";
    $stmt = $db->query($sqlActivity);
    $activityResults = $stmt->fetchAll(PDO::FETCH_ASSOC);

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
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.3/jquery.min.js" integrity="sha512-STof4xm1wgkfm7heWqFJVn58Hm3EtS31XFaagaa8VMReCXAkQnJZ+jEy8PCC/iT18dFy95WcExNHFTqLyp72eQ==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js" integrity="sha384-w76AqPfDkMBDXo30jS1Sgez6pr3x5MlQ1ZAGC+nuZB+EYdgRZgiwxhTBTkF7CXvN" crossorigin="anonymous"></script>

    <title>História Logov</title>
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
                        <a class="nav-link" aria-current="page" href="index-admin.php">Domov</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" aria-current="page" href="admin-add.php">Pridať športovca | Pridať umiestnenie</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="admin-history-log.php">História prihlásení</a>
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
    <h2>História prihlásení</h2>
</div>

<div class="container-md border pt-2 mb-3 table-responsive">
    <table class="table table-bordered table-striped" id="login-table">
        <thead>
        <tr>
            <th>TYP PRIHLÁSENIA</th>
            <th>ČAS PRIHLÁSENIA</th>
        </tr>
        </thead>
        <tbody>
        <?php
        foreach ($loginResults as $result){
            echo "<tr><td>" . $result["login_type"] . "</td><td>" . $result["logged_at"] . "</td></tr>";
        }
        ?>
        </tbody>
    </table>
</div>

<div class="container-md d-flex rounded-2 p-2 justify-content-center mb-3 mt-4 bg-secondary text-light">
    <h2>Vykonané zmeny</h2>
</div>

<div class="container-md border pt-2 mb-3 table-responsive">
    <table class="table table-bordered table-striped" id="activity-table">
        <thead>
        <tr>
            <th>ZMENA</th>
            <th>ČAS ZMENY</th>
        </tr>
        </thead>
        <tbody>
        <?php
        foreach ($activityResults as $result){
            echo "<tr><td>" . $result["edit"] . "</td><td>".$result["edited_at"]."</td></tr>";
        }
        ?>
        </tbody>
    </table>
</div>

<footer class="d-flex justify-content-center py-3 my-4 mt-4 border-top border-dark-subtle">
    <span class="text-muted">© 2023 Marián Figula</span>
</footer>

<script src="https://cdn.datatables.net/1.13.2/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.2/js/dataTables.bootstrap5.min.js"></script>
<script src="scripts/history_log.js"></script>

</body>
</html>
