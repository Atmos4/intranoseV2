<?php
restrict_access();

require_once "database/inscriptions_data.php";
$deplacement = get_deplacement_by_id(get_route_param('id_depl', true), $_SESSION['user_id']);
$courses = get_courses_by_deplacement_id($deplacement['did'], $_SESSION['user_id']);

$has_entry = isset($deplacement['present']) && $deplacement['present'] == 1;
$is_transported = isset($deplacement['transport']) && $deplacement['transport'] == 1;
$is_hosted = isset($deplacement['heberg']) && $deplacement['heberg'] == 1;

page($deplacement['nom'], "inscription_view.css");
?>
<main class="container">
    <a href="/mes-inscriptions" class="secondary return-link"><i class="fas fa-caret-left"></i> Retour</a>
    <article>
        <header class="center">
            <h3><?= $deplacement['nom'] ?></h3>
            <div class="row">
                <div class="col-auto">
                    <?php include "components/start_icon.php" ?>
                    <span data-tooltip="Départ"><?= " " . $deplacement['depart'] ?></span>
                </div>
                <div class="col-auto">
                    <?php include "components/finish_icon.php" ?>
                    <span data-tooltip="Arrivée"><?= " " . $deplacement['arrivee'] ?></span>
                </div>
                <div class="col-auto">
                    <i class="fas fa-warning"></i>
                    <span data-tooltip="Date limite"><?= " " . $deplacement['limite'] ?></span>
                </div>
            </div>
        </header>
        <div class="grid">
            <p>
                <?php if (isset($deplacement['present']) && $deplacement['present'] == 1) : ?>
                    <ins><i class="fas fa-check"></i></ins>
                    <span>Participe</span>
                <?php else : ?>
                    <del><i class="fas fa-xmark"></i></del>
                    <span><?= isset($deplacement['present']) ? "Ne participe pas" : "Pas inscrit" ?></span>
                <?php endif;  ?>
            </p>
            <?php if ($has_entry) : ?>
                <p>
                    <?php if ($is_transported) : ?>
                        <ins><i class="fas fa-check"></i></ins>
                    <?php else : ?>
                        <del><i class="fas fa-xmark"></i></del>
                    <?php endif;  ?>
                    <span>Transport avec le club</span>
                </p>
                <p>
                    <?php if ($is_hosted) : ?>
                        <ins><i class="fas fa-check"></i></ins>
                    <?php else : ?>
                        <del><i class="fas fa-xmark"></i></del>
                    <?php endif;  ?>
                    <span>Hébergement avec le club</span>
                </p>
            <?php endif; ?>
        </div>
        <h4>Courses : </h4>
        <table role="grid">
            <?php foreach ($courses as $course) : ?>
                <tr>
                    <td class="course-entry">
                        <?php if (isset($course['present']) && $course['present'] == 1) : ?>
                            <ins><i class="fas fa-check"></i></ins>
                        <?php else : ?>
                            <del><i class="fas fa-xmark"></i></del>
                        <?php endif;  ?>
                    </td>
                    <td class="course-name"><b><?= $course['nom'] ?></b></td>
                    <td class="course-date"><?= $course['date'] ?></td>
                    <td class="course-place"><?= $course['lieu'] ?></td>


                </tr>
            <?php endforeach ?>
        </table>
    </article>
</main>