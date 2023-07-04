<?php

require_once('config.php');
require_once 'insert_edit.php';

session_start();

if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header("Location: login.php");
    exit;
}

if (!isset($_GET["id"])){
    exit("id not exist!");
}
$success = null;
try {
    $db = new PDO("mysql:host=$hostname;dbname=$dbname", $username, $password);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    if (!empty($_POST)) {
            //$queryChange = "UPDATE person SET name='{$_POST['name']}',surname='{$_POST['surname']}',birth_day='{$_POST['birth_day']}',birth_place='{$_POST['birth_place']}',birth_country='{$_POST['birth_country']}',death_day=NULL ,death_place='$deathPlace',death_country='$deathCountry' WHERE id=" . intval($_POST['person-id']);
        $queryChangeGame = "UPDATE ranking SET game_id={$_POST['game']}, discipline='{$_POST['discipline']}', placing={$_POST['placing']} where id={$_POST["placement_id"]}";

        $stmt2 = $db->prepare($queryChangeGame);
        $success = $stmt2->execute();
        if ($success){
            insertUserActivity($db,$queryChangeGame,$_SESSION["email"]);
        }
    }

    $queryAllGames = "Select * FROM game";
    $stmt2 = $db->prepare($queryAllGames);
    $stmt2->execute();
    $allGames = $stmt2->fetchAll(PDO::FETCH_ASSOC);
    //var_dump($allGames);

    $query2 = "SELECT ranking.*, game.city, game.type, game.country from ranking join game on ranking.game_id=game.id where ranking.id=?";
    $stmt3 = $db->prepare($query2);
    $stmt3->execute([$_GET["id"]]);
    $placement = $stmt3->fetch(PDO::FETCH_ASSOC);
    //var_dump($placement);

    $query = "SELECT name,surname FROM person where id=?";
    $stmt = $db->prepare($query);
    $stmt->execute([$placement["person_id"]]);
    $person = $stmt->fetch(PDO::FETCH_ASSOC);

}catch (PDOException $e) {
    echo $e->getMessage();
}
?>


<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">

    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet"
          integrity="sha384-GLhlTQ8iRABdZLl6O3oVMWSktQOp6b7In1Zl3/Jr59b6EGGoI1aFkw7cmDA6j6gD" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.2/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="css/utility.css">
    <link rel="stylesheet" href="css/icons.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.3/jquery.min.js"
            integrity="sha512-STof4xm1wgkfm7heWqFJVn58Hm3EtS31XFaagaa8VMReCXAkQnJZ+jEy8PCC/iT18dFy95WcExNHFTqLyp72eQ=="
            crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.3/jquery.min.js" integrity="sha512-STof4xm1wgkfm7heWqFJVn58Hm3EtS31XFaagaa8VMReCXAkQnJZ+jEy8PCC/iT18dFy95WcExNHFTqLyp72eQ==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <script src="http://code.jquery.com/jquery-1.9.1.min.js"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/2.0.1/css/toastr.css" rel="stylesheet"/>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/2.0.1/js/toastr.js"></script>
    <script src="scripts/admin-edit-person.js"></script>

    <title>Editácia umiestnenia</title>
</head>
<body>

<div class="d-flex pt-2 justify-content-center"><h1>Admin Panel</h1></div>

<header class="mb-3 mt-2">
    <nav class="navbar navbar-expand-lg bg-body-tertiary" data-bs-theme="dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="#"><?php echo $_SESSION["login"] ?></a>
            <button class="navbar-toggler bg-light" type="button" data-bs-toggle="collapse"
                    data-bs-target="#navbarNavDropdown" aria-controls="navbarNavDropdown" aria-expanded="false"
                    aria-label="Toggle navigation">
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


<div class="container border rounded bg-secondary">
    <div class="width-50 p-5">
        <div class="border p-4 bg-light mb-5">
            <h2 class="justify-content-center center-text"><?php echo $person["name"] . " " . $person["surname"] ?> - Umiestnenie</h2>
            <hr class="mb-4 mt-3">
            <form action="#" method="post">
                <div class="mb-3 row">
                    <input type="hidden" >
                    <div class="col form-group">
                        <label for="game" class="form-label">Olympíjske hry</label>
                        <select name="game" id="game" class="form-control">
                            <?php
                                foreach ($allGames as $game){
                                    //var_dump($game);
                                    if ($game["id"] == $placement["game_id"]){
                                        echo "<option selected name='game_id' value=".$game['id'].">".$game["type"]. " " . $game["year"]." ". $game["city"] . ", " . $game["country"] . "</option>";
                                        continue;
                                    }
                                    echo "<option name='game_id' value=".$game['id'].">".$game["type"]. " " . $game["year"]." ". $game["city"] . ", " . $game["country"] . "</option>";
                                }
                            ?>
                        </select>
                    </div>
                </div>
                <div class="mb-3 row">
                    <input type="hidden" name="placement_id" value="<?php echo $placement["id"]; ?>">
                    <div class="col form-group">
                        <label for="discipline" class="form-label">Disciplína*</label>
                        <input type="text" name="discipline"
                               id="discipline" class="form-control"
                               value="<?php echo $placement["discipline"] ?>"
                               required>
                    </div>
                </div>
                <div class="mb-3 row">
                    <div class="col form-group">
                        <label for="placing" class="form-label">Umiestnenie*</label>
                        <input type="number"
                               name="placing"
                               id="placing"
                               class="form-control"
                               value="<?php echo $placement["placing"] ?>"
                               required>
                    </div>
                </div>

                <div class="mb-3 row">
                    <div class="col form-group">
                        <button type="submit" class="btn btn-primary" name="btn-edit-person" id="btn-edit-person">
                            Zmeniť
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<footer class="d-flex justify-content-center py-3 my-4 mt-4 border-top border-dark-subtle">
    <span class="text-muted">© 2023 Marián Figula</span>
</footer>

<?php
if ($success){
    echo "<script type='text/javascript'>toastr.success('Zmena prebehla úspešne')</script>";
}
?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-w76AqPfDkMBDXo30jS1Sgez6pr3x5MlQ1ZAGC+nuZB+EYdgRZgiwxhTBTkF7CXvN"
        crossorigin="anonymous"></script>
<script src="https://cdn.datatables.net/1.13.2/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.2/js/dataTables.bootstrap5.min.js"></script>

</body>
</html>