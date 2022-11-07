<?php
require_once "database/licencie_data.php";
$licencie = get_licencie(get_route_param('licencie_id'));
page($licencie['nom'] . " " . $licencie['prenom']);
?>
<main class="container">
    <article>
        <h1><?= $licencie['prenom'] . " " . $licencie['nom'] ?></h1>
    </article>
</main>