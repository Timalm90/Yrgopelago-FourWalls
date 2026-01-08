<?php

declare(strict_types=1);

session_start();

require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/services/CentralBankService.php';

use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

/* =======================
   DATABASE
======================= */
$db = new PDO('sqlite:' . __DIR__ . '/database/database.db');
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

/* =======================
   INPUT
======================= */
$guestName    = trim($_POST['guestId'] ?? '');
$transferCode = trim($_POST['transferCode'] ?? '');
$arrival      = $_POST['arrival'] ?? '';
$departure    = $_POST['departure'] ?? '';
$roomId       = isset($_POST['room']) ? (int)$_POST['room'] : null;
$features     = $_POST['features'] ?? [];

if (!$guestName || !$transferCode || !$arrival || !$departure || !$roomId) {
    header('Location: /index.php?status=error');
    exit;
}

/* =======================
   NIGHTS
======================= */
$start = new DateTime($arrival);
$end   = new DateTime($departure);

if ($end <= $start) {
    header('Location: /index.php?status=departure_date_error');
    exit;
}

$nights = (int)$start->diff($end)->days;
if ($nights < 1) $nights = 1;

/* =======================
   OVERLAP CHECK
======================= */
$stmt = $db->prepare("
    SELECT COUNT(*) FROM bookings
    WHERE room_id = :room
      AND arrival_date < :departure
      AND departure_date > :arrival
");
$stmt->execute([
    ':room' => $roomId,
    ':arrival' => $arrival,
    ':departure' => $departure,
]);

if ((int)$stmt->fetchColumn() > 0) {
    header('Location: /index.php?status=room_unavailable');
    exit;
}

/* =======================
   ROOM PRICE
======================= */
$stmt = $db->prepare('SELECT tier, price_per_night FROM rooms WHERE id = :id');
$stmt->execute([':id' => $roomId]);
$room = $stmt->fetch(PDO::FETCH_ASSOC);

$bankAmount = $room['price_per_night'] * $nights;
$finalPrice = $bankAmount;

/* =======================
   FEATURES 
======================= */
$featureNames = [];

if ($features) {
    $in = implode(',', array_fill(0, count($features), '?'));
    $stmt = $db->prepare("
        SELECT id, feature_name, price
        FROM features
        WHERE id IN ($in)
    ");
    $stmt->execute(array_map('intval', $features));

    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $f) {
        $finalPrice += (float)$f['price']; // 
        $featureNames[] = $f['feature_name'];
    }
}

/* =======================
    DISCOUNT
======================= */
$stmt = $db->prepare('SELECT id, visits FROM guests WHERE name = :n');
$stmt->execute([':n' => $guestName]);
$guest = $stmt->fetch(PDO::FETCH_ASSOC);

$discount = 0;

if ($guest) {
    $guestId = (int)$guest['id'];
    $visits  = (int)$guest['visits'];
    $db->prepare('UPDATE guests SET visits = visits + 1 WHERE id = :i')
        ->execute([':i' => $guestId]);

    if ($visits >= 1) {
        $discount = $finalPrice * 0.1;
        $finalPrice -= $discount;
    }
} else {
    $db->prepare('INSERT INTO guests (name, visits) VALUES (:n,1)')
        ->execute([':n' => $guestName]);
    $guestId = (int)$db->lastInsertId();
}

/* =======================
   CENTRAL BANK 
======================= */
$bank = new CentralBankService();

if (!$bank->validateTransfer($transferCode, $bankAmount)) {
    header('Location: /index.php?status=transfer_invalid');
    exit;
}

/* =======================
   SAVE BOOKING
======================= */
$stmt = $db->prepare("
    INSERT INTO bookings
    (room_id, guest_id, arrival_date, departure_date, transfer_code, price, creation_time)
    VALUES (:room,:guest,:arrival,:departure,:code,:price,DATE('now'))
");
$stmt->execute([
    ':room' => $roomId,
    ':guest' => $guestId,
    ':arrival' => $arrival,
    ':departure' => $departure,
    ':code' => $transferCode,
    ':price' => round($finalPrice, 2),
]);

/* =======================
   DEPOSIT
======================= */
$bank->deposit($transferCode);

/* =======================
   RECEIPT SESSION
======================= */
$_SESSION['receipt'] = [
    'guest'    => $guestName,
    'room'     => ucfirst($room['tier']),
    'nights'   => $nights,
    'features' => $featureNames,
    'discount' => round($discount, 2),
    'total'    => round($finalPrice, 2),
];

header('Location: /index.php?status=success');
exit;
