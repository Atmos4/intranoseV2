<?php
require_once "database/data_inscriptions.php";

page("Mes inscriptions", "inscriptions.css");
check_auth("USER");

$deplacements = get_deplacements();
?>

<main class="container">
    <div class="row">
        <div class=col-md-3>Nom</div>
        <div class=col-md-3>Debut</div>
        <div class=col-md-3>Fin</div>
        <div class=col-md-2>Limite d'inscription</div>
    </div>
    <hr>
    <?php foreach ($deplacements as $deplacement) : ?>
        <div class="row row-events">
            <div class="col-md-1"><img src="/assets/icon/check-32x32.png" /></div>
            <div class="col-md-3 row-title"><b><?= $deplacement['nom'] ?></b></div>
            <div class=col-md-3><?= date_format(date_create($deplacement['depart']), "l d F Y") ?></div>
            <div class=col-md-3><?= date_format(date_create($deplacement['arrivee']), "l d F Y") ?></div>
            <div class=col-md-2><?= $deplacement['limite'] ?></div>
        </div>
    <?php endforeach ?>
</main>