<?php

require_once('config.php');
require_once 'insert_edit.php';

session_start();

$_SESSION["show_message"] = false;
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("Location: login.php");
    exit;
}

if (!isset($_GET["id"])) {
    exit("id not exist!");
}
$success = null;
try {
    $db = new PDO("mysql:host=$hostname;dbname=$dbname", $username, $password);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    if (isset($_POST["btn-edit-person"])){
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            $errmsg = "";
            // Validacia username
            if (checkFullname($_POST['name'], $_POST['surname']) == false) {
                $errmsg .= "<p class='text-danger'>Meno alebo priezvisko je zadané nesprávne.</p>";
            } elseif (checkTwoDates(date($_POST["birth_day"]), date("Y-m-d")) === false) {
                $errmsg .= "<p class='text-danger'>Dátum narodenia musí byť menši ako dnešný dátum</p>";
            } elseif ($_POST["death_day"] !== "" && checkTwoDates(date($_POST["birth_day"]), date($_POST["death_day"])) === false) {
                $errmsg .= "<p class='text-danger'>Dátum narodenia musí byť menši ako dátum úmrtia</p>";
            }
        }
        if (empty($errmsg)) {
            if (!empty($_POST) && !empty($_POST["name"])) {
                $deathDay = $_POST['death_day'];
                $deathPlace = $_POST['death_place'];
                $deathCountry = $_POST['death_country'];
                if ($_POST['death_day'] == "" || $_POST['death_day'] == null) {
                    $queryChange = "UPDATE person SET name='{$_POST['name']}',surname='{$_POST['surname']}',birth_day='{$_POST['birth_day']}',birth_place='{$_POST['birth_place']}',birth_country='{$_POST['birth_country']}',death_day=NULL ,death_place='$deathPlace',death_country='$deathCountry' WHERE id=" . intval($_POST['person-id']);
                } else {
                    $queryChange = "UPDATE person SET name='{$_POST['name']}',surname='{$_POST['surname']}',birth_day='{$_POST['birth_day']}',birth_place='{$_POST['birth_place']}',birth_country='{$_POST['birth_country']}',death_day='$deathDay',death_place='$deathPlace',death_country='$deathCountry' WHERE id=" . intval($_POST['person-id']);
                }
                $stmt2 = $db->prepare($queryChange);
                $success = $stmt2->execute();
                if ($success) {
                    insertUserActivity($db, $queryChange, $_SESSION["email"]);

                }
            }
        }
    }



    $query = "SELECT * FROM person where id=?";
    $stmt = $db->prepare($query);
    $stmt->execute([$_GET["id"]]);
    $person = $stmt->fetch(PDO::FETCH_ASSOC);

    if (isset($_POST["del-placement-id"])) {
        $sql = "DELETE FROM ranking WHERE id=?";
        $stmt4 = $db->prepare($sql);
        $success4 = $stmt4->execute([intval($_POST["del-placement-id"])]);
        if ($success4) {
            insertUserActivity($db, "DELETE FROM ranking WHERE id=" . $_POST["del-placement-id"], $_SESSION["email"]);
            $_SESSION["show_message"] = true;
        }
    }

    $query2 = "SELECT ranking.*, game.city, game.type from ranking join game on ranking.game_id=game.id where ranking.person_id=?";
    $stmt3 = $db->prepare($query2);
    $stmt3->execute([$_GET["id"]]);
    $placements = $stmt3->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
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
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.3/jquery.min.js"
            integrity="sha512-STof4xm1wgkfm7heWqFJVn58Hm3EtS31XFaagaa8VMReCXAkQnJZ+jEy8PCC/iT18dFy95WcExNHFTqLyp72eQ=="
            crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <script src="http://code.jquery.com/jquery-1.9.1.min.js"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/2.0.1/css/toastr.css" rel="stylesheet"/>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/2.0.1/js/toastr.js"></script>
    <script src="scripts/admin-edit-person.js"></script>

    <title>Editácia Športovca</title>
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
                        <a class="nav-link" aria-current="page" href="admin-add.php">Pridať športovca | Pridať
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
        <div class="border p-4 bg-light mb-5">
            <h2 class="justify-content-center center-text">Editácia Športovca</h2>
            <hr class="mb-4 mt-3">
            <!-- spravit name ako column TODO upload.php to action na novu stranku sa mi to prehodi -->
            <form action="#" method="post">
                <div class="mb-3 row">
                    <input type="hidden" name="person-id" value="<?php echo $person["id"]; ?>">
                    <div class="col form-group">
                        <label for="name" class="form-label">Meno*</label><input type="text" name="name"
                                                                                id="name" class="form-control"
                                                                                value="<?php echo $person["name"] ?>"
                                                                                required>
                    </div>
                    <div class="col form-group">
                        <label for="surname" class="form-label">Priezvisko*</label>
                        <input type="text" name="surname"
                               id="surname"
                               class="form-control"
                               value="<?php echo $person["surname"] ?>"
                               required>
                    </div>
                </div>


                <div class="mb-3 row">
                    <div class="col form-group">
                        <label for="birth_day" class="form-label">Dátum narodenia*</label>
                        <input type="date"
                               name="birth_day"
                               id="birth_day"
                               class="form-control"
                               value="<?php echo $person["birth_day"] ?>"
                               required>
                    </div>
                </div>

                <div class="mb-3 row">
                    <div class="col form-group">
                        <label for="birth_place" class="form-label">Miesto narodenia*</label><input type="text"
                                                                                                   name="birth_place"
                                                                                                   id="birth_place"
                                                                                                   class="form-control"
                                                                                                   value="<?php echo $person["birth_place"] ?>"
                                                                                                   required>
                    </div>
                    <div class="col form-group">
                        <label for="birth_country" class="form-label">Krajina narodenia*</label><input type="text"
                                                                                                      name="birth_country"
                                                                                                      id="birth_country"
                                                                                                      class="form-control"
                                                                                                      value="<?php echo $person["birth_country"] ?>"
                                                                                                      required>
                    </div>
                </div>

                <div class="mb-3 row">
                    <div class="col form-group">
                        <label for="death_day" class="form-label">Dátum úmrtia</label><input type="date"
                                                                                             name="death_day"
                                                                                             id="death_day"
                                                                                             class="form-control"
                                                                                             value="<?php echo $person["death_day"] ?>">
                    </div>
                </div>

                <div class="mb-3 row">
                    <div class="col form-group">
                        <label for="death_place" class="form-label">Miesto úmrtia</label><input type="text"
                                                                                                name="death_place"
                                                                                                id="death_place"
                                                                                                class="form-control"
                                                                                                value="<?php echo $person["death_place"] ?>">
                    </div>
                    <div class="col form-group">
                        <label for="death_country" class="form-label">Krajina úmrtia</label><input type="text"
                                                                                                   name="death_country"
                                                                                                   id="death_country"
                                                                                                   class="form-control"
                                                                                                   value="<?php echo $person["death_country"] ?>">
                    </div>
                </div>

                <div class="mb-3 row">
                    <div class="col form-group">
                        <button type="submit" class="btn btn-primary" name="btn-edit-person" id="btn-edit-person">
                            Zmeniť
                        </button>
                    </div>
                </div>
                <?php
                if (!empty($errmsg)) {
                    echo $errmsg;
                } ?>
            </form>
        </div>

        <form action="#" method="post">
            <div class="modal fade" id="exampleModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
                 aria-hidden="true">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="exampleModalLabel">Vymazanie umiestnenia</h5>
                            <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <p>Naozaj chcete vymazať toto umiestnenie ?</p>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Zavrieť</button>
                            <button type="submit" class="btn btn-danger" name="del-placement-id">Vymazať</button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="border p-4 bg-light table-responsive">
                <h2 class="justify-content-center center-text">Zoznam umiestnení Športovca</h2>
                <hr class="mb-4 mt-3">
                <table class="table table-bordered table-striped" id="admin-edit-placement">
                    <thead>
                    <tr>
                        <th>Umiestnenie</th>
                        <th>Disciplína</th>
                        <th>OH</th>
                        <th>Mesto</th>
                        <th>Editácia / Vymazanie</th>
                    </tr>
                    </thead>
                    <tbody>

                    <?php
                    foreach ($placements as $placement) {
                        echo "<tr><td>" . $placement["placing"] . ".</td><td>" . $placement["discipline"] . "</td><td>" . $placement["type"] .
                            "</td><td>" . $placement["city"] . "</td><input type='hidden' name='del-placement-id' id='clicked_button' value=" . $placement["id"] . "'><td class='dt-body-center'><a type='submit' role='button' class='btn btn-warning edit-btn me-2' href='admin-edit-rank.php?id=" . $placement["id"] . "' ></a><button type='button' data-bs-toggle='modal' data-bs-target='#exampleModal' class='btn btn-danger del-btn'></button></td>";
                    }
                    ?>

                    </tbody>
                </table>
            </div>
        </form>
    </div>
</div>

<footer class="d-flex justify-content-center py-3 my-4 mt-4 border-top border-dark-subtle">
    <span class="text-muted">© 2023 Marián Figula</span>
</footer>
<?php
if ($success) {
    echo "<script type='text/javascript'>toastr.success('Zmena prebehla úspešne')</script>";
}

if ($_SESSION["show_message"] == true) {
    echo "<script type='text/javascript'>toastr.success('Umiestnenie úspešne odstránené')</script>";
    $_SESSION["show_message"] = false; // unset the session variable to prevent it from showing again
}

?>

</body>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-w76AqPfDkMBDXo30jS1Sgez6pr3x5MlQ1ZAGC+nuZB+EYdgRZgiwxhTBTkF7CXvN"
        crossorigin="anonymous"></script>
<script src="https://cdn.datatables.net/1.13.2/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.2/js/dataTables.bootstrap5.min.js"></script>


</html>