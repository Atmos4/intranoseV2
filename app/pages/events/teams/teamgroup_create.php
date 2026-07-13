<?php
restrict_access(Access::$ADD_EVENTS);
$event_id = get_route_param('event_id');
$event = em()->find(Event::class, $event_id);

$v = new Validator();
$name = $v->text("name")->required()->placeholder("Nom")->label("Nom du pool d'équipe");

if ($v->valid()) {
    // Create new TeamGroup
    $team_group = new TeamGroup();
    $team_group->event = $event;
    $team_group->name = $name->value;

    em()->persist($team_group);
    em()->flush();

    Toast::success("Pool d'équipes créé avec succès");
    redirect("/evenements/$event_id/pool/$team_group->id");
}

page("Créer un Pool d'Équipes");
?>

<?= actions()->back("/evenements/$event_id?tab=pools") ?>

<div class="container">
    <article>
        <header>
            <h2>Créer un nouveau Pool d'Équipes</h2>
        </header>
        <form method="post">
            <?= $v->render_validation() ?>

            <?= $name->render() ?>
            <p>
                Un pool d'équipes vous permet de regrouper plusieurs équipes pour un même contexte
                (ex: équipes de relais, équipes de vaisselle, équipes de nettoyage, etc.)
            </p>
            <p>
                Vous pourrez ensuite créer et gérer les équipes dans ce pool.
            </p>

            <button type="submit">
                <i class="fa fa-plus"></i> Créer le Pool
            </button>
        </form>
    </article>
</div>