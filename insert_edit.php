<?php

function insertUserActivity($db, $sql,$email){
    $activity = "INSERT INTO user_activity (user_email, edit) VALUES (:user_email,:edit)";
    $stmt2 = $db->prepare($activity);
    $stmt2->bindParam(":user_email",$email,PDO::PARAM_STR);
    $stmt2->bindParam(":edit",$sql,PDO::PARAM_STR);
    $stmt2->execute();
}

function checkFullname($firstname,$lastname) : bool{
    $param_firstname = trim($firstname);
    $param_lastname = trim($lastname);

    if (preg_match('~[0-9]+~', $param_firstname . " " . $param_lastname)) {
        return false;
    }
    return true;
}

function checkTwoDates($date, $date2): bool{
    if ($date < $date2) {
        return true;
    }
    return false;
}