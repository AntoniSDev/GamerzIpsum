<?php
session_start();
$_GET["id"];
if (isset($_GET['id']) && !empty($_GET['id'])) {
    require_once("connect.php");

    $id = strip_tags($_GET['id']);
    $sql = "SELECT * FROM games WHERE game_id = :id";
    $query = $db->prepare($sql);
    $query->bindValue(':id', $id, PDO::PARAM_INT);
    $query->execute();
    $result = $query->fetchAll(PDO::FETCH_ASSOC);
    print_r($result);
    $game = $result[0]["game_name"];


    $sql = "SELECT * FROM screenshots WHERE game = '$game'";
    $query = $db->prepare($sql);
    $query->execute();
    $screen = $query->fetchAll(PDO::FETCH_ASSOC);

    foreach ($result as $result) {
        $cover = $result["game_cover"];
        unlink($cover);
    }
    foreach ($screen as $screen) {
        $source = $screen["images"];
        unlink($source);
    }


    $sql = "DELETE FROM screenshots WHERE game = '$game'";
    $query = $db->prepare($sql);
    $query->execute();

    $sql = "DELETE FROM games WHERE game_id = :id";
    $query = $db->prepare($sql);
    $query->bindValue(':id', $id, PDO::PARAM_INT);
    $query->execute();



    $_SESSION["del"]["toast"] = 1;
    require_once('close.php');
    header("Location: backoffice.php");
}
