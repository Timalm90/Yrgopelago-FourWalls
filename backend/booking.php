<?php



if (isset(
    $_POST['guestId'],
    $_POST['transferCode'],
    $_POST['arrival'],
    $_POST['departure'],
    $_POST['room'],
)) {

    $guestId = htmlspecialchars($_POST['guestId'] ?? '');
    $transferCode = htmlspecialchars($_POST['transferCode'] ?? '');
    $arrival = ($_POST['arrival'] ?? '');
    $departure = ($_POST['departure'] ?? '');
    $room = $_POST['room'] ?? '';

    $featuresUsed = $_POST['features'] ?? [];



    $database = new PDO('sqlite:database/database.db');
    $statement = $database->query('SELECT * FROM rooms');
    $rooms = $statement->fetchAll(PDO::FETCH_ASSOC);


    var_dump($rooms);

    // foreach ($rooms as $room) {

    //     echo $room['tier'] . "<br>";
    // }
};






// var_dump($_POST['features']);
