<?php
require_once "database/inscriptions_data.php";

page("Mes inscriptions", "inscriptions.css");
check_auth("USER");

$deplacements = get_deplacements();

setlocale(LC_TIME, "fr_FR");

$formatter = new IntlDateFormatter('fr_FR', IntlDateFormatter::LONG, IntlDateFormatter::NONE);
$formatter2 = new IntlDateFormatter('fr_FR', IntlDateFormatter::SHORT, IntlDateFormatter::NONE);

$user_id = $_SESSION["user_id"];
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
            <?php if (is_registered($deplacement, $user_id)) : ?>
                <div class="col-md-1"><img src="/assets/icon/check-32x32.png" /></div>
            <?php else : ?>
                <div class="col-md-1"><img src="/assets/icon/cross-32x32.png" /></div>
            <?php endif ?>
            <div class="col-md-3 row-title"><b><?= $deplacement['nom'] ?></b></div>
            <div class=col-md-3><?= $formatter->format(strtotime($deplacement['depart'])) ?></div>
            <div class=col-md-3><?= $formatter->format(strtotime($deplacement['arrivee'])) ?></div>
            <div class=col-md-2><?= $formatter2->format(strtotime($deplacement['limite'])) ?></div>
        </div>
    <?php endforeach ?>
</main>