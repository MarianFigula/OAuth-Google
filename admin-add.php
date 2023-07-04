<?php

require_once('config.php');
require_once 'insert_edit.php';

session_start();

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("Location: login.php");
    exit;
}

try {
    $db = new PDO("mysql:host=$hostname;dbname=$dbname", $username, $password);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo $e->getMessage();
}

$success = false;
$success2 = false;

$queryAllGames = "SELECT * from game";
$stmt = $db->query($queryAllGames);
$allGames = $stmt->fetchAll(PDO::FETCH_ASSOC);


if (isset($_POST["btn-add-person"])) {
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $errmsg = "";
        // Validacia username
        if (checkFullname($_POST['name'], $_POST['surname']) == false) {
            $errmsg .= "<p class='text-danger'>Meno alebo priezvisko je zadané nesprávne.</p>";
        } elseif (checkTwoDates(date($_POST["birth_date"]), date("Y-m-d")) === false) {
            $errmsg .= "<p class='text-danger'>Dátum narodenia musí byť menši ako dnešný dátum</p>";
        }
    }
    if (empty($errmsg)){
        $personName = $_POST['name'];
        $personSurname = $_POST['surname'];
        $sqlSelect = "Select * FROM person AS p WHERE concat(p.name, ' ', p.surname) LIKE '$personName $personSurname'";
        $stmt2 = $db->query($sqlSelect);
        $rows = $stmt2->fetchAll(PDO::FETCH_ASSOC);

        if ($rows) {
            echo '<script>alert("Športovec už existuje")</script>';
        } else {
            $sql = "INSERT INTO person (name,surname,birth_day,birth_place,birth_country) VALUES (?,?,?,?,?)";
            $stmt = $db->prepare($sql);
            $success = $stmt->execute([$_POST['name'], $_POST['surname'], $_POST['birth_date'], $_POST['birth_place'], $_POST['birth_country']]);
            if ($success) {
                insertUserActivity($db, "INSERT INTO person (name,surname,birth_day,birth_place,birth_country) VALUES (" . $_POST['name'] . "," . $_POST["surname"] . "," . $_POST['birth_date'] . "," . $_POST['birth_place'] . "," . $_POST['birth_country'] . ")", $_SESSION["email"]);
            }
        }
    }
}
if (isset($_POST["btn-add-placement"])) {

    $checkDiscipline = $_POST['discipline'];
    $personId = $_POST["person_id"];
    $gameId = $_POST["game"];
    $placing = $_POST["placing"];
    $sqlSelect = "Select * FROM ranking WHERE person_id=" . $personId . " AND discipline='$checkDiscipline' AND game_id=$gameId";
    $stmt = $db->query($sqlSelect);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if ($rows) {
        echo '<script>alert("Športovec a jeho umiestnenie už existuje")</script>';
    } else {
        $sql1 = "INSERT INTO `ranking`(`person_id`,`game_id`,`placing`,`discipline`) VALUES (?, ?, ?, ?)";
        $stmt2 = $db->prepare($sql1);
        $success2 = $stmt2->execute([$_POST['person_id'], $_POST["game"], intval($_POST['placing']), $_POST['discipline']]);

        if ($success2) {
            insertUserActivity($db, "INSERT INTO `ranking`(`person_id`,`game_id`,`placing`,`discipline`) VALUES (" . $_POST['person_id'] . "," . $_POST["game"] . "," . $_POST['placing'] . "," . $_POST['discipline'] . ")", $_SESSION["email"]);
        }
    }
}

$query_all = "SELECT * from person";
$stmt = $db->query($query_all);
$persons = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
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
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.3/jquery.min.js"
            integrity="sha512-STof4xm1wgkfm7heWqFJVn58Hm3EtS31XFaagaa8VMReCXAkQnJZ+jEy8PCC/iT18dFy95WcExNHFTqLyp72eQ=="
            crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <script src="http://code.jquery.com/jquery-1.9.1.min.js"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/2.0.1/css/toastr.css" rel="stylesheet"/>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/2.0.1/js/toastr.js"></script>
    <title>Pridať športovca | Pridať umiestnenie</title>
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
                        <a class="nav-link active" aria-current="page" href="admin-add.php">Pridať športovca | Pridať
                            umiestnenie</a>
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
        <div class="border p-4 bg-light">
            <h2 class="justify-content-center center-text">Pridanie Športovca</h2>
            <hr class="mb-4 mt-3">
            <form action="#" method="post">
                <div class="mb-3 row">
                    <div class="col form-group">
                        <label for="inputName" class="form-label">Meno*</label><input type="text" name="name"
                                                                                     id="inputName" class="form-control"
                                                                                     required>
                    </div>
                    <div class="col form-group">
                        <label for="inputSurname" class="form-label">Priezvisko*</label><input type="text" name="surname"
                                                                                              id="inputSurname"
                                                                                              class="form-control"
                                                                                              required>
                    </div>
                </div>
                <div class="mb-3 row">
                    <div class="col form-group">
                        <label for="inputDate" class="form-label">Dátum narodenia*</label><input type="date"
                                                                                                name="birth_date"
                                                                                                id="inputDate"
                                                                                                class="form-control"
                                                                                                required>
                    </div>
                </div>

                <div class="mb-3 row">
                    <div class="col form-group">
                        <label for="inputPlace" class="form-label">Miesto narodenia*</label><input type="text"
                                                                                                  name="birth_place"
                                                                                                  id="inputPlace"
                                                                                                  class="form-control"
                                                                                                  required>
                    </div>
                    <div class="col form-group">
                        <label for="inputCountry" class="form-label">Krajina narodenia*</label><input type="text"
                                                                                                     name="birth_country"
                                                                                                     id="inputCountry"
                                                                                                     class="form-control"
                                                                                                     required>
                    </div>
                </div>
                <div class="mb-3 row">
                    <div class="col form-group">
                        <button type="submit" class="btn btn-primary" name="btn-add-person" id="btn-add-person">Pridať
                        </button>
                    </div>
                    <?php
                    if (!empty($errmsg)) {
                        echo $errmsg;
                    } ?>
                </div>
            </form>
            <!-- to iste len nad vsetkymi olympijskymi hramy - games to bude -
            ten isty form najprv to bude skryta a potom ked posle prvy tak sa objavi dalsi formular -->
        </div>
    </div>


    <div class="width-50 p-5">
        <div class="border p-4 bg-light">
            <h2 class="justify-content-center center-text">Pridanie umiestnenia k športovcovi</h2>
            <hr class="mb-4 mt-3">
            <form action="#" method="post">
                <div class="mb-3 row">
                    <div class="form-group col">
                        <label for="person_select" class="pb-2">Vyberte športovca:</label>
                        <select class="form-control" name="person_id" id="person_select">
                            <?php
                            foreach ($persons as $person)
                                echo "<option name='person_id' value=" . $person["id"] . ">" . $person["name"] . " " . $person["surname"] . "</option>"
                            ?>
                        </select>
                    </div>
                </div>
                <div class="mb-3 row">
                    <div class="col form-group">
                        <label for="discipline" class="form-label">Disciplína*</label><input type="text"
                                                                                            name="discipline"
                                                                                            id="discipline"
                                                                                            class="form-control"
                                                                                            required>
                    </div>
                    <div class="col form-group col-lg-2">
                        <label for="placing" class="pb-2">Umiestnenie*</label>
                        <input type="number" name="placing" id="placing" class="form-control" required>
                    </div>
                </div>
                <div class="mb-3 row">
                    <div class="col form-group">
                        <label for="game" class="form-label">Olympíjske hry</label>
                        <select name="game" id="game" class="form-control">
                            <?php
                            foreach ($allGames as $game) {
                                //var_dump($game);
                                echo "<option name='game_id' value=" . $game['id'] . ">" . $game["type"] . " " . $game["year"] . " " . $game["city"] . ", " . $game["country"] . "</option>";
                            }
                            ?>
                        </select>
                    </div>
                </div>
                <div class="mb-3 row">
                    <div class="col form-group">
                        <button type="submit" class="btn btn-primary" name="btn-add-placement" id="btn-add-placement">
                            Pridať
                        </button>
                    </div>
                </div>
                <?php
                if (!empty($errmsg)) {
                    echo $errmsg;
                } ?>
            </form>
        </div>
    </div>

</div>

<footer class="d-flex justify-content-center py-3 my-4 mt-4 border-top border-dark-subtle">
    <span class="text-muted">© 2023 Marián Figula</span>
</footer>

<?php

if ($success) {
    $success2 = false;

    echo "<script type='text/javascript'>toastr.success('Športovec úspešne pridaný')</script>";

}
if ($success2) {
    $success1 = false;
    echo "<script type='text/javascript'>toastr.success('Pridanie umiestnenia k športovcovi prebehlo úspešne')</script>";
}
?>


</body>
</html>