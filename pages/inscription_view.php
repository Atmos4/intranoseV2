<?php
restrict_access();

require_once "database/inscriptions_data.php";
$deplacement = get_deplacement_by_id(get_route_param('id_depl', true));
$courses = get_courses_by_deplacement_id($deplacement['did']);

page($deplacement['nom'], "inscription_view.css");
?>
<main class="container">
    <a href="/mes-inscriptions" class="secondary return-link"><i class="fas fa-caret-left"></i> Retour</a>
    <article>
        <header>
            <h3>Déplacement : <?= $deplacement['nom'] ?></h3>
            <div class="row">
                <div class="col-sm-auto">
                    <?php include "components/start_icon.php" ?>
                    <span data-tooltip="Départ"><?= " " . $deplacement['depart'] ?></span>
                </div>
                <div class="col-sm-auto">
                    <?php include "components/finish_icon.php" ?>
                    <span data-tooltip="Arrivée"><?= " " . $deplacement['arrivee'] ?></span>
                </div>
                <div class="col-sm-auto">
                    <i class="fas fa-warning"></i>
                    <span data-tooltip="Date limite"><?= " " . $deplacement['limite'] ?></span>
                </div>
            </div>
        </header>
        <h3 class="center">Courses</h3>
        <table>
            <?php foreach ($courses as $course) : ?>
                <tr>
                    <td class="course-name"><b><?= $course['nom'] ?></b></td>
                    <td class="course-date"><?= $course['date'] ?></td>
                    <td class="course-place"><?= $course['lieu'] ?></td>
                </tr>
            <?php endforeach ?>
        </table>
    </article>
</main>