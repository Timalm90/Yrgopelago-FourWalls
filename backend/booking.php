<?php

declare(strict_types=1);


$guestName    = trim($_POST['guestId']);
$transferCode = trim($_POST['transferCode']);
$arrival      = $_POST['arrival'] ?? '';
$departure    = $_POST['departure'] ?? '';
$roomId       = isset($_POST['room']) ? (int)$_POST['room'] : null;
$featuresUsed = $_POST['features'] ?? [];


// Validate guest

if (!preg_match('/^[a-zA-Z0-9 _-]{1,50}$/', $guestName)) {
    die("Invalid guest name");
}

// Database

$database = new PDO('sqlite:database/database.db');
$database->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);


// Check if guest exists

$statement = $database->prepare(
    'SELECT id, visits FROM guests WHERE name = :name LIMIT 1'
);
$statement->execute([':name' => $guestName]);
$guest = $statement->fetch(PDO::FETCH_ASSOC);

$guestId = null;
$visits  = 0;

if ($guest) {
    $guestId = (int)$guest['id'];
    $visits  = (int)$guest['visits'];
}


// calculation of number of nights stayed 

$nights = 0;

if (!empty($arrival) && $roomId !== null) {
    if (!$departure) {
        $departure = date('Y-m-d', strtotime($arrival . ' +1 day'));
    }

    if ($arrival >= $departure) {
        die('Departure date must be after arrival date');
    }

    $dateArrive = new DateTime($arrival);
    $dateDepart = new DateTime($departure);
    $nights = max(1, (int)$dateArrive->diff($dateDepart)->format('%a'));
}

// Price calculation

$basePrice = 0;

// Room cost
if ($nights > 0 && $roomId !== null) {
    $statement = $database->prepare(
        'SELECT price_per_night FROM rooms WHERE id = :room_id'
    );
    $statement->execute([':room_id' => $roomId]);

    $roomPricePerNight = (float)$statement->fetchColumn();
    $basePrice += $roomPricePerNight * $nights;
}

// Features cost
foreach ($featuresUsed as $featureId) {

    if ((int)$featureId === 16) {
        continue;
    }
    $statement = $database->prepare(
        'SELECT price FROM features WHERE id = :id'
    );
    $statement->execute([':id' => (int)$featureId]);
    $basePrice += (float)$statement->fetchColumn();
}

//Checks if guest gets free butler. nights need to be 3 or more

if ($nights >= 3) {
    if (!in_array(16, $featuresUsed, true)) {
        $featuresUsed[] = 16;
    }
}

// TODO::::
// --------------------
// 7. Validate transfer code (pseudo)
// --------------------
function validateTransferCode(string $code, float $amount): bool
{
    // TODO: replace with real API call
    return true;
}

if (!validateTransferCode($transferCode, $basePrice)) {
    die('Invalid transfer code');
}

//-------------------------



// Returning guest discount (if nights > 0)

$totalPrice = $basePrice;

if ($visits >= 1 && $nights > 0) {
    $totalPrice *= 0.9;
}


// Update or add new guest


if ($guest) {
    if ($nights > 0) {
        $visits += 1;
    }
    $statement = $database->prepare(
        'UPDATE guests SET visits = :visits WHERE id = :id'
    );
    $statement->execute([
        ':visits' => $visits,
        ':id'     => $guestId
    ]);
} else {
    $initialVisits = $nights > 0 ? 1 : 0;
    $statement = $database->prepare(
        'INSERT INTO guests (name, visits) VALUES (:name, :visits)'
    );
    $statement->execute([
        ':name'   => $guestName,
        ':visits' => $initialVisits
    ]);
    $guestId = (int)$database->lastInsertId();
}


// Insert booking

try {
    $statement = $database->prepare(
        'INSERT INTO bookings (
        room_id,
        guest_id,
        arrival_date,
        departure_date,
        transfer_code,
        price,
        creation_time
    ) VALUES (
        :room_id,
        :guest_id,
        :arrival,
        :departure,
        :transfer_code,
        :price,
        DATE("now")
    )'
    );

    $statement->execute([
        ':room_id'       => $roomId,
        ':guest_id'      => $guestId,
        ':arrival'       => $arrival ?: null,
        ':departure'     => $departure ?: null,
        ':transfer_code' => $transferCode,
        ':price'         => $totalPrice
    ]);

    $bookingId = (int)$database->lastInsertId();
} catch (PDOException $error) {
    if ($error->getCode() === '23000') {
        header('Location: /index.php?status=transfer_expired');
        exit;
    }

    // Unknown database error
    echo "<script>alert('Something went wrong. Please try again later.');</script>";
    exit;
}

// booking features

if (!empty($featuresUsed)) {
    $statement = $database->prepare(
        'INSERT INTO bookings_features (booking_id, feature_id) VALUES (:booking_id, :feature_id)'
    );
    foreach ($featuresUsed as $featureId) {
        $statement->execute([
            ':booking_id' => $bookingId,
            ':feature_id' => (int)$featureId
        ]);
    }
}



header('Location: /index.php?status=success');
exit;
