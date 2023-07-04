<?php

require_once('config.php');
$var_value = $_GET['varname'];

$db = new PDO("mysql:host=$hostname;dbname=$dbname", $username, $password);
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$details = "SELECT r.placing, r.discipline, g.type, g.city, g.year FROM person AS p JOIN ranking as r ON p.id=r.person_id JOIN game as g ON g.id=r.game_id WHERE concat(p.name, ' ', p.surname) LIKE '%$var_value%'";
$stmt = $db->query($details);
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="sk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-GLhlTQ8iRABdZLl6O3oVMWSktQOp6b7In1Zl3/Jr59b6EGGoI1aFkw7cmDA6j6gD" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.2/css/dataTables.bootstrap5.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.3/jquery.min.js" integrity="sha512-STof4xm1wgkfm7heWqFJVn58Hm3EtS31XFaagaa8VMReCXAkQnJZ+jEy8PCC/iT18dFy95WcExNHFTqLyp72eQ==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <script src="scripts/detail.js"></script>
    <script src="scripts/script.js"></script>
    <title>Detail Športovca</title>
</head>
<body>

<div class="d-flex pt-2 justify-content-center"><h1><?php echo $var_value ?></h1></div>

<header class="mb-3 mt-2" >
    <nav class="navbar navbar-expand-lg bg-body-tertiary p-2" data-bs-theme="dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="index.php">Späť</a>d
        </div>
    </nav>
</header>

<div class="container-md d-flex rounded-2 p-2 justify-content-center p-1 mb-3 mt-4 bg-secondary text-light">
    <h2>Detail Športovca</h2>
</div>

<div class="container-md border pt-2 mb-5 table-responsive">
    <table class="table table-bordered table-striped" id="person-detail">
        <thead>
        <tr>
            <td>UMIESTNENIE</td>
            <td>DISCIPLÍNA</td>
            <td>TYP OH</td>
            <td>MIESTO OH</td>
            <td>ROK OH</td>
        </tr>
        </thead>
        <tbody>
        <?php
        foreach ($results as $result){
            echo "<tr><td>" . $result["placing"] . "</td><td>" . $result["discipline"] . "</td><td>" . $result["type"] .
                "</td><td>" . $result["city"] . "</td><td>" . $result["year"] . "</td></tr>";
        }
        ?>
        </tbody>
    </table>
</div>

<footer class="d-flex justify-content-center py-3 my-4 border-top border-dark-subtle">
    <span class="text-muted">© 2023 Marián Figula</span>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js" integrity="sha384-w76AqPfDkMBDXo30jS1Sgez6pr3x5MlQ1ZAGC+nuZB+EYdgRZgiwxhTBTkF7CXvN" crossorigin="anonymous"></script>
<script src="https://cdn.datatables.net/1.13.2/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.2/js/dataTables.bootstrap5.min.js"></script>

</body>
</html>
