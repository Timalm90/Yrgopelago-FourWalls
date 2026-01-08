<?php

declare(strict_types=1);
require __DIR__ . '/loadData.php';
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Four Walls</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Notable&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Noticia+Text:ital,wght@0,400;0,700;1,400;1,700&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="frontend/styles/styles.css">



</head>

<body>

    <section class="hotelWrapper">

        <div id="hotel"></div>

        <form action="/backend/booking.php" method="post" class="booking">

            <!-- ROOMS -->
            <div class="room">

                <div class="room-type">
                    <h2>Room Type</h2>

                    <?php foreach ($rooms as $room): ?>
                        <label class="room-option">
                            <input
                                type="radio"
                                name="room"
                                value="<?= (int)$room['id'] ?>"
                                data-room-id="<?= (int)$room['id'] ?>"
                                data-room-tier="<?= htmlspecialchars($room['tier']) ?>"
                                data-room-price="<?= (float)$room['price_per_night'] ?>"
                                <?= isset($old['room']) && (int)$old['room'] === (int)$room['id'] ? 'checked' : '' ?>
                                required>

                            <span class="room-name"><?= ucfirst($room['tier']) ?>
                                $<?= number_format($room['price_per_night'], 2) ?></span>

                        </label>
                    <?php endforeach; ?>

                    <p class="room-description" id="roomDescription"></p>
                </div>

                <!-- CALENDAR -->

                <div class="calendarWrapper">

                    <div class="calendar">
                        <h2>January</h2>
                        <div class="calendar-grid" id="calendarGrid"></div>
                    </div>

                    <label>Arrival</label>
                    <input type="date" name="arrival"
                        value="<?= htmlspecialchars($old['arrival'] ?? '') ?>"
                        min="2026-01-01" max="2026-01-31" required>

                    <label>Departure</label>
                    <input type="date" name="departure"
                        value="<?= htmlspecialchars($old['departure'] ?? '') ?>"
                        min="2026-01-02" max="2026-02-01" required>
                </div>

            </div>

            <!-- FEATURES -->
            <section class="featureWrapper">
                <?php foreach ($featuresByCategory as $category => $features): ?>
                    <div class="featureCategory feature-category-<?= htmlspecialchars($category) ?>">
                        <h3 class="feature-title"><?= ucfirst($category) ?></h3>

                        <?php foreach ($features as $feature): ?>
                            <label class="feature-text feature-tier-<?= htmlspecialchars($feature['tier']) ?>">
                                <input
                                    type="checkbox"
                                    name="features[]"
                                    value="<?= (int)$feature['id'] ?>"
                                    data-price="<?= (float)$feature['price'] ?>"
                                    data-feature-name="<?= htmlspecialchars($feature['feature_name']) ?>"
                                    <?= in_array((int)$feature['id'], $old['features'] ?? [], true) ? 'checked' : '' ?>>

                                <?= ucfirst(htmlspecialchars($feature['feature_name'])) ?>
                                (<?= ucfirst($feature['tier']) ?>,
                                $<?= number_format($feature['price'], 1) ?>)
                            </label>
                        <?php endforeach; ?>
                    </div>
                <?php endforeach; ?>
            </section>

            <!-- USER INPUT -->
            <div class="input">
                <label>
                    <h4>Name</h4>
                </label>
                <input type="text" name="guestId" required
                    value="<?= htmlspecialchars($old['guestId'] ?? '') ?>">

                <label>
                    <h4>Transfer Code</h4>
                </label>
                <input type="text" name="transferCode" required>

            </div>

            <!-- SUM / BOOKING-->

            <h2>Total price: <strong id="livePrice">$0.00</strong></h2>

            <button type="submit">Book now</button>

        </form>

        <!-- MODAL (Messages) -->
        <?php if ($showModal): ?>
            <div class="modal-overlay" onclick="this.remove()">
                <div class="modal-box" onclick="event.stopPropagation()">
                    <span class="modal-close" onclick="this.closest('.modal-overlay').remove()">✕</span>

                    <h2><?= htmlspecialchars($statusMessages[$status]) ?></h2>

                    <?php if ($status === 'success' && isset($_SESSION['receipt'])): ?>
                        <p><strong>Guest:</strong> <?= htmlspecialchars($_SESSION['receipt']['guest'] ?? '') ?></p>
                        <p><strong>Room:</strong> <?= htmlspecialchars($_SESSION['receipt']['room'] ?? '') ?></p>
                        <p><strong>Nights:</strong> <?= (int)($_SESSION['receipt']['nights'] ?? 1) ?></p>

                        <?php if (!empty($_SESSION['receipt']['features'])): ?>
                            <p><strong>Features:</strong>
                                <?= htmlspecialchars(implode(', ', $_SESSION['receipt']['features'])) ?>
                            </p>
                        <?php endif; ?>

                        <?php if (!empty($_SESSION['receipt']['discount'])): ?>
                            <p><strong>Discount:</strong>
                                -$<?= number_format((float)$_SESSION['receipt']['discount'], 2) ?>
                            </p>
                        <?php endif; ?>

                        <p><strong>Total:</strong>
                            $<?= number_format((float)$_SESSION['receipt']['total'], 2) ?>
                        </p>
                    <?php endif; ?>

                </div>
            </div>
            <?php unset($_SESSION['receipt']); ?>
        <?php endif; ?>

    </section>

    <script>
        const ROOM_DESCRIPTIONS = <?= json_encode(
                                        array_column($rooms, 'description', 'tier'),
                                        JSON_THROW_ON_ERROR
                                    ) ?>;
    </script>

    <script src="frontend/scripts/hotel.js"></script>

</body>
<footer>
    <p class="marquee">
        <span>Returning guests get a 10% discount after checkout. &nbsp;</span>
    </p>
    <p class="marquee marquee2">
        <span>Returning guests get a 10% discount after checkout. &nbsp;</span>
    </p>
</footer>

</html>