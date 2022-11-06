<?php
page("Mon profil");
check_auth("USER");

$user_data = fetch("SELECT * FROM licencies WHERE id = ? LIMIT 1;", [$_SESSION['user_id']])[0];
?>

<main class="container">
    <?php include "template/page_title.php"; ?>
    <form>
        <!-- Grid -->
        <div class="grid">

            <!-- Markup example 1: input is inside label -->
            <label for="firstname">
                First name
                <input type="text" id="firstname" name="firstname" placeholder="First name" required>
            </label>

            <label for="lastname">
                Last name
                <input type="text" id="lastname" name="lastname" placeholder="Last name" required>
            </label>

        </div>

        <!-- Markup example 2: input is after label -->
        <label for="email">Email address</label>
        <input type="email" id="email" name="email" placeholder="Email address" required>
        <small>We'll never share your email with anyone else.</small>

        <!-- Button -->
        <button type="submit">Submit</button>

    </form>
</main>