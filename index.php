<?php

declare(strict_types=1);
require(__DIR__ . "/backend/vendor/autoload.php");
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Yrgopelago</title>
    <link rel="stylesheet" href="frontend/styles/styles.css">


</head>

<body>
    <?php

    ?>

    <form action="/backend/booking.php" method="post" class="booking">

        <label for="guestId" class="block mt-3">Your name (guest_id)</label>
        <input type="text" name="guestId" class="form-input" required="">

        <label for="transferCode" class="block mt-3">transferCode</label>
        <input type="text" name="transferCode" class="form-input" required="">


        <label for="arrival" class="block mt-3">Arrival</label>
        <input type="date" name="arrival" class="form-input" min="2026-01-01" max="2026-01-31">

        <label for="departure" class="block mt-3">Departure</label>
        <input type="date" name="departure" class="form-input" min="2026-01-01" max="2026-01-31">

        <label for="room" class="block mt-3">Room</label>
        <select name="room" id="" class="form-input pr-12">
            <option value="1">Economy</option>
            <option value="2">Standard</option>
            <option value="3">Luxury</option>
        </select>


        <br>
        <label for="features" class="block mt-6">Features</label>

        <section class="featureWrapper">
            <div class="featureCategory">
                <p>Water</p>

                <label class="block ml-2">
                    <input class="mr-2" type="checkbox" name="features[]" value="1">
                    Pool (Economy, $0.5)
                </label>

                <label class="block ml-2">
                    <input class="mr-2" type="checkbox" name="features[]" value="2">
                    Scuba Diving (Basic, $1)
                </label>

                <label class="block ml-2">
                    <input class="mr-2" type="checkbox" name="features[]" value="3">
                    Olympic Pool (Premium, $1.5)
                </label>

                <label class="block ml-2">
                    <input class="mr-2" type="checkbox" name="features[]" value="4">
                    waterpark with fire and minibar (superior, $2)
                </label>
            </div>
            <div class="featureCategory">
                <p>Games</p>

                <label class="block ml-2">
                    <input class="mr-2" type="checkbox" name="features[]" value="5">
                    Yahtzee (Economy, $0.5)
                </label>

                <label class="block ml-2">
                    <input class="mr-2" type="checkbox" name="features[]" value="6">
                    Ping pong table (Basic, $1)
                </label>

                <label class="block ml-2">
                    <input class="mr-2" type="checkbox" name="features[]" value="7">
                    PS5 (Premium, $1.5)
                </label>

                <label class="block ml-2">
                    <input class="mr-2" type="checkbox" name="features[]" value="8">
                    Casino (superior, $2)
                </label>
            </div>
            <div class="featureCategory">
                <p>Wheels</p>

                <label class="block ml-2">
                    <input class="mr-2" type="checkbox" name="features[]" value="9">
                    Unicycle (Economy, $0.5)
                </label>

                <label class="block ml-2">
                    <input class="mr-2" type="checkbox" name="features[]" value="10">
                    Bicycle (Basic, $1)
                </label>

                <label class="block ml-2">
                    <input class="mr-2" type="checkbox" name="features[]" value="11">
                    Trike (Premium, $1.5)
                </label>

                <label class="block ml-2">
                    <input class="mr-2" type="checkbox" name="features[]" value="12">
                    Four-wheeled motorized beast (superior, $2)
                </label>
            </div>
            <div class="featureCategory">
                <p>Hotel-specific</p>

                <label class="block ml-2">
                    <input class="mr-2" type="checkbox" name="features[]" value="13">
                    Carpet (Economy, $0.5)
                </label>

                <label class="block ml-2">
                    <input class="mr-2" type="checkbox" name="features[]" value="14">
                    Good Dog (Basic, $1)
                </label>

                <label class="block ml-2">
                    <input class="mr-2" type="checkbox" name="features[]" value="15">
                    Fireplace (Premium, $1.5)
                </label>

                <label class="block ml-2">
                    <input class="mr-2" type="checkbox" name="features[]" value="16">
                    Butler (superior, $2)
                </label>
            </div>
        </section>


        <button name="submit" type="submit">Book your visit now!</button>


        <?php
        if (isset($_GET['status'])) {
            if ($_GET['status'] === 'success') {
                echo '<p class="success-message">Booking saved successfully!</p>';
            }

            if ($_GET['status'] === 'transfer_expired') {
                echo '<p class="error-message">Transfer code expired. Please enter a valid code.</p>';
            }

            if ($_GET['status'] === 'error') {
                echo '<p class="error-message">Something went wrong. Please try again.</p>';
            }
        }
        ?>












        <scrpt src="/frontend/scripts/main.js"></scrpt>
</body>

</html>