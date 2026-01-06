<?php

declare(strict_types=1);

$database = new PDO('sqlite:' . __DIR__ . '/backend/database/database.db');
$database->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

/*
 Fetch only unlocked features
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
 Group features by category
*/

$featuresByCategory = [];

foreach ($features as $feature) {
    $category = $feature['category'];
    $featuresByCategory[$category][] = $feature;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>BronxLand</title>
    <link rel="stylesheet" href="frontend/styles/styles.css">
</head>

<body>

    <section class="hotelWrapper">

        <!-- Graphics container-->

        <div id="hotel"></div>

        <!-- Booking-->


        <form action="/backend/booking.php" method="post" class="booking">

            <label>Your name</label>
            <input type="text" name="guestId" required>

            <label>Transfer code</label>
            <input type="text" name="transferCode" required>

            <label>Arrival</label>
            <input type="date" name="arrival">

            <label>Departure</label>
            <input type="date" name="departure">

            <label>Room</label>
            <select name="room">
                <option value="1">Economy $2.0</option>
                <option value="2">Standard $4.0</option>
                <option value="3">Luxury $8.0</option>
            </select>


            <br>
            <label class="block mt-6">
                <h3>Features</h3>
            </label>

            <!-- Puts features in correct categories-->

            <section class="featureWrapper">

                <?php foreach ($featuresByCategory as $category => $features): ?>

                    <div class="featureCategory feature-category-<?= htmlspecialchars($category) ?>">
                        <p class="feature-title"><?= ucfirst($category) ?></p>

                        <?php foreach ($features as $feature): ?>
                            <label class="block ml-2 feature-tier-<?= htmlspecialchars($feature['tier']) ?>">
                                <input
                                    class="mr-2"
                                    type="checkbox"
                                    name="features[]"
                                    value="<?= (int)$feature['id'] ?>">
                                <?= ucfirst(htmlspecialchars($feature['feature_name'])) ?>
                                (<?= ucfirst($feature['tier']) ?>, $<?= number_format($feature['price'], 1) ?>)
                            </label>
                        <?php endforeach; ?>

                    </div>

                <?php endforeach; ?>

            </section>

            <div class="Button-message">
                <button type="submit" class="Submit-button">Book now</button>

                <div class="message">
                    <?php
                    $status = $_GET['status'] ?? '';
                    $messages = [
                        'success' => 'Booking successful!',
                        'transfer_expired' => 'Transfer code expired.',
                        'transfer_invalid' => 'Transfer code invalid.',
                        'departure_date_error' => 'Departure cannot be before arrival.',
                        'error' => 'Something went wrong.'
                    ];
                    if (isset($messages[$status])) {
                        echo "<p> {$messages[$status]}</p>";
                    }
                    ?>
                </div>
            </div>
        </form>

    </section>



    <script src="frontend/scripts/hotel.js"></script>
</body>


</html>