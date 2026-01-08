
<?php
session_start();
$old = $_SESSION['old'] ?? [];

$database = new PDO('sqlite:' . __DIR__ . '/backend/database/database.db');
$database->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

/*
 Fetch rooms
*/
$roomsStmt = $database->query("
    SELECT id, tier, price_per_night, description
    FROM rooms
    ORDER BY price_per_night ASC
");
$rooms = array_reverse($roomsStmt->fetchAll(PDO::FETCH_ASSOC));

/*
 Fetch unlocked features
*/
$stmt = $database->query("
    SELECT 
        f.id,
        f.category,
        f.tier,
        f.feature_name,
        f.price
    FROM features f
    JOIN hotel_features hf ON hf.feature_id = f.id
    ORDER BY f.category, f.id
");
$features = $stmt->fetchAll(PDO::FETCH_ASSOC);

/*
 Group features
*/
$featuresByCategory = [];
foreach ($features as $feature) {
    $featuresByCategory[$feature['category']][] = $feature;
}

/*
 Status messages
*/
$status = $_GET['status'] ?? null;

$statusMessages = [
    'success' => 'Booking successful!',
    'transfer_expired' => 'Transfer code expired.',
    'transfer_invalid' => 'Transfer code invalid.',
    'departure_date_error' => 'Departure cannot be before arrival.',
    'room_unavailable' => 'Selected room is not available for those dates.',
    'error' => 'Something went wrong.'
];

$showModal = $status && isset($statusMessages[$status]);
