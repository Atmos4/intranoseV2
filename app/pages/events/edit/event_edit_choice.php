<?php
restrict_access(Access::$ADD_EVENTS);

page("Création d'événement")->enableHelp();
?>
<?= actions()?->back("/evenements", "Annuler", "fas fa-xmark") ?>
<div class="row center"
    data-intro="Vous pouvez créer deux types d'événement : <b>simple</b>, avec une seule activité ou <b>complexe</b>, avec plusieurs activités.">
    <p>Combien d'activités votre nouvel événement va-t-il comporter ?</p>
    <div class="col-auto">
        <a role="button" href="/evenements/nouveau/simple">
            <i class="fas fa-hand-point-up"></i> Une seule
        </a>
    </div>
    <div class="col-auto">
        <a role="button" href="/evenements/nouveau/complexe"
            data-intro="Attention, une fois créé, vous pouvez passer d'un événement simple (une seule activité) à complexe (plusieurs) mais pas l'inverse">
            <i class="fas fa-hand-peace"></i> Plusieurs
        </a>
    </div>
</div>