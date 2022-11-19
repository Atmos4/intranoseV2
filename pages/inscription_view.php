<?php
require_once "database/inscriptions_data.php";

$id_depl = get_route_param('id_depl');

$user_id = $_SESSION["user_id"];

page("Deplacement" . $id_depl . " " . $user_id);
?>
<main class="container">
    <a href="/mes-inscriptions" class="link">Retour aux inscriptions</a>
    <article>
        <h2>Inscription aux course</h2>
    </article>
    <article>
        <h2>Deplacement avec le club</h2>
    </article>
    <article>
        <h2>Hebergement avec le club</h2>
    </article>
</main>