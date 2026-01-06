<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/services/CentralBankService.php';

use Dotenv\Dotenv;

// Load .env
$dotenv = Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

// -----------------------
// Database FIRST
$database = new PDO('sqlite:' . __DIR__ . '/database/database.db');
$database->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// -----------------------
// Input
$guestName    = trim($_POST['guestId'] ?? '');
$transferCode = trim($_POST['transferCode'] ?? '');
$arrival      = $_POST['arrival'] ?? '';
$departure    = $_POST['departure'] ?? '';
$roomId       = isset($_POST['room']) ? (int)$roomId = (int)$_POST['room'] : null;

// -----------------------
// Validate guest name
if (!preg_match('/^[a-zA-Z0-9 _-]{1,50}$/', $guestName)) {
    header('Location: /index.php?status=error');
    exit;
}

// -----------------------
// Fetch unlocked features
$stmt = $database->query('SELECT feature_id FROM hotel_features');
$unlockedFeatureIds = $stmt->fetchAll(PDO::FETCH_COLUMN);

// Only allow unlocked features
$featuresUsed = array_values(array_intersect(
    $_POST['features'] ?? [],
    $unlockedFeatureIds
));

// -----------------------
// Nights calculation
$nights = 1;

if ($arrival) {
    $start = new DateTime($arrival);
    if ($departure) {
        $end = new DateTime($departure);
        if ($end <= $start) {
            header('Location: /index.php?status=departure_date_error');
            exit;
        }
        $nights = max(1, $start->diff($end)->days);
    } else {
        $departure = $start->modify('+1 day')->format('Y-m-d');
        $nights = 1;
    }
}

// -----------------------
// Calculate base price
$basePrice = 0;

if ($roomId) {
    $stmt = $database->prepare('SELECT price_per_night FROM rooms WHERE id = :id');
    $stmt->execute([':id' => $roomId]);
    $basePrice += (float)$stmt->fetchColumn() * $nights;
}

foreach ($featuresUsed as $featureId) {
    if ((int)$featureId === 16) continue;
    $stmt = $database->prepare('SELECT price FROM features WHERE id = :id');
    $stmt->execute([':id' => (int)$featureId]);
    $basePrice += (float)$stmt->fetchColumn();
}

// -----------------------
// Guest lookup
$stmt = $database->prepare('SELECT id, visits FROM guests WHERE name = :name LIMIT 1');
$stmt->execute([':name' => $guestName]);
$guest = $stmt->fetch(PDO::FETCH_ASSOC);

$guestId = $guest['id'] ?? null;
$visits  = (int)($guest['visits'] ?? 0);

// -----------------------
// Free butler
if ($nights >= 3 && !in_array(16, $featuresUsed, true)) {
    $featuresUsed[] = 16;
}

// -----------------------
// CentralBank
$centralBank = new CentralBankService();

$transferResult = $centralBank->validateTransfer($transferCode, $basePrice);

if (!$transferResult['success']) {
    header(
        'Location: /index.php?status=' .
            ($transferResult['error'] === 'invalid_code' ? 'transfer_invalid' : 'transfer_expired')
    );
    exit;
}

// -----------------------
// Discount
$totalPrice = ($visits >= 1) ? $basePrice * 0.9 : $basePrice;

// -----------------------
// Guest update
if ($guestId) {
    $stmt = $database->prepare('UPDATE guests SET visits = visits + 1 WHERE id = :id');
    $stmt->execute([':id' => $guestId]);
} else {
    $stmt = $database->prepare('INSERT INTO guests (name, visits) VALUES (:name, 1)');
    $stmt->execute([':name' => $guestName]);
    $guestId = (int)$database->lastInsertId();
}

// -----------------------
// Booking insert
$stmt = $database->prepare(
    'INSERT INTO bookings (room_id, guest_id, arrival_date, departure_date, transfer_code, price, creation_time)
     VALUES (:room, :guest, :arrival, :departure, :code, :price, DATE("now"))'
);
$stmt->execute([
    ':room'      => $roomId,
    ':guest'     => $guestId,
    ':arrival'   => $arrival,
    ':departure' => $departure,
    ':code'      => $transferCode,
    ':price'     => $totalPrice
]);

$bookingId = (int)$database->lastInsertId();

// -----------------------
// Booking features
$featuresUsed = array_unique($featuresUsed);

$stmt = $database->prepare(
    'INSERT INTO bookings_features (booking_id, feature_id) VALUES (:b, :f)'
);

foreach ($featuresUsed as $featureId) {
    $stmt->execute([
        ':b' => $bookingId,
        ':f' => (int)$featureId
    ]);
}

// -----------------------
// Deposit
if (!$centralBank->deposit($transferCode)) {
    header('Location: /index.php?status=error');
    exit;
}

// -----------------------
// Receipt
function mapFeaturesForReceipt(array $ids): array
{
    $map = [
        1 => ['activity' => 'water', 'tier' => 'economy'],
        2 => ['activity' => 'water', 'tier' => 'basic'],
        3 => ['activity' => 'water', 'tier' => 'premium'],
        4 => ['activity' => 'water', 'tier' => 'superior'],
        5 => ['activity' => 'games', 'tier' => 'economy'],
        6 => ['activity' => 'games', 'tier' => 'basic'],
        7 => ['activity' => 'games', 'tier' => 'premium'],
        8 => ['activity' => 'games', 'tier' => 'superior'],
        9 => ['activity' => 'wheels', 'tier' => 'economy'],
        10 => ['activity' => 'wheels', 'tier' => 'basic'],
        11 => ['activity' => 'wheels', 'tier' => 'premium'],
        12 => ['activity' => 'wheels', 'tier' => 'superior'],
        13 => ['activity' => 'extras', 'tier' => 'economy'],
        14 => ['activity' => 'extras', 'tier' => 'basic'],
        15 => ['activity' => 'extras', 'tier' => 'premium'],
        16 => ['activity' => 'extras', 'tier' => 'superior'],
    ];

    return array_values(array_filter(array_map(fn($id) => $map[$id] ?? null, $ids)));
}

$centralBank->sendReceipt(
    $guestName,
    $arrival,
    $departure,
    mapFeaturesForReceipt($featuresUsed),
    5
);

// -----------------------
header('Location: /index.php?status=success');
exit;
