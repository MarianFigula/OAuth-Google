<?php

require_once('config.php');

try {
    $db = new PDO("mysql:host=$hostname;dbname=$dbname", $username, $password);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);









    $query_oh_winners = "SELECT p.name, p.surname, g.year, g.city, g.type, r.discipline FROM person AS p JOIN ranking as r ON p.id=r.person_id JOIN game as g ON g.id=r.game_id WHERE r.placing=1";
    $stmt = $db->query($query_oh_winners);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $query_top_ten = "SELECT concat(p.name, ' ', p.surname) AS 'name', COUNT(r.placing) AS 'počet zlatých medajlí' FROM person AS p JOIN ranking as r ON p.id=r.person_id WHERE r.placing = 1 GROUP BY p.id ORDER BY COUNT(*) DESC LIMIT 10";
    $stmt2 = $db->query($query_top_ten);
    $results2 = $stmt2->fetchAll(PDO::FETCH_ASSOC);


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

    <title>Zadanie1</title>
</head>
<body>


<div class="d-flex pt-2 justify-content-center"><h1>Zadanie 1</h1></div>

<header class="mb-3 mt-2" >
    <nav class="navbar navbar-expand-lg bg-body-tertiary" data-bs-theme="dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="#" data-bs-toggle="modal" data-bs-target="#exampleModal">Domov</a>
            <button class="navbar-toggler bg-light" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNavDropdown" aria-controls="navbarNavDropdown" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNavDropdown">
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link" aria-current="page" href="login.php">Prihlásenie</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" aria-current="page" href="register.php">Registrácia</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
</header>

<div class="container-md d-flex rounded-2 p-2 justify-content-center mb-3 mt-4 bg-secondary text-light">
    <h2>Tabuľka olympijských víťazov</h2>
</div>

<div class="container-md border pt-2 mb-3 table-responsive">
    <table class="table table-bordered table-striped" id="sk-oh-winners">
        <thead>
        <tr>
            <th>MENO</th>
            <th>PRIEZVISKO</th>
            <th>ROK</th>
            <th>MIESTO OH</th>
            <th>TYP OH</th>
            <th>DISCIPLÍNA</th>
            <!--<td></td>-->
        </tr>
        </thead>
        <tbody>
        <?php
        foreach ($results as $result){

            echo "<tr><td>" . $result["name"] . "</td><td>" . $result["surname"] . "</td><td>" . $result["year"] .
                "</td><td>" . $result["city"] . "</td><td>" . $result["type"] . "</td><td>" .
                $result["discipline"] . "</td></tr>";
        }
        ?>
        </tbody>
    </table>
</div>

<div class="container-md d-flex rounded-2 p-2 justify-content-center p-1 mb-3 mt-5 bg-secondary text-light">
    <h2>Tabuľka 10 najúspešnejších olympijonikov podľa počtu zlatých medajlí</h2>
</div>


<div class="container-md table-responsive border pt-2 mb-4">
<table class="table table-bordered table-striped" id="top-ten-by-gold-medals">
    <thead>
    <tr>
        <th>MENO</th>
        <th>POČET ZLATÝCH MEDAJLÍ</th>
    </tr>
    </thead>
    <tbody>
<?php
    foreach ($results2 as $result){
        $name = $result["name"];
        echo "<tr><td><a href='person-detail.php?varname=$name' class='link-dark' >" . $name . "</a></td><td>" . $result["počet zlatých medajlí"] . "</td></tr>";

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
<script src="scripts/script.js"></script>

</body>
</html>
