<?php
/**
 * Builds and registers all guardian fields on a Validator.
 *
 * Single source of truth for guardian field definitions. Used by settings.php
 * (POST processing) and guardian_form.php (HTMX rendering).
 *
 * @param  string $prefix  e.g. "guardian[0]" for existing, "new_guardians[0]" for new
 * @return array{ first_name: Field, last_name: Field, phone: Field, email: Field }
 */
function build_guardian_validator(Validator $v, string $prefix): array
{
    return [
        'first_name' => $v->text("{$prefix}[first_name]")->label("Prénom")->required(),
        'last_name' => $v->text("{$prefix}[last_name]")->label("Nom")->required(),
        'phone' => $v->phone("{$prefix}[phone]")->label("Téléphone"),
        'email' => $v->email("{$prefix}[email]")->label("Email")->required(),
    ];
}

function render_guardian_fieldset(
    array $fields,
    string $title,
    ?string $delete_href = null,
    bool $js_delete = false,
): void { ?>
    <fieldset class="guardian-row">
        <h4 style="margin:0;"><?= $title ?></h4>
        <div class="row">
            <div class="col-sm-12 col-md-6"><?= $fields['first_name']->render() ?></div>
            <div class="col-sm-12 col-md-6"><?= $fields['last_name']->render() ?></div>
            <div class="col-sm-12 col-md-6"><?= $fields['phone']->render() ?></div>
            <div class="col-sm-12 col-md-6"><?= $fields['email']->render() ?></div>
        </div>
        <?php if ($delete_href || $js_delete): ?>
            <div class="row">
                <?php if ($js_delete): ?>
                    <div class="col-auto">
                        <button type="link" class="destructive outline" onclick="this.closest('fieldset').remove()">
                            <i class="fas fa-trash"></i> Supprimer
                        </button>
                    </div>
                <?php else: ?>
                    <div class="col-auto">
                        <a type="button" href="<?= $delete_href ?>" class="destructive outline">
                            <i class="fas fa-trash"></i> Supprimer
                        </a>
                    </div>
                <?php endif ?>
            </div>
        <?php endif ?>
    </fieldset>
<?php }

function render_guardian_form(
    array $guardian_rows,
    array $new_guardian_rows,
    Validator $v,
    User $user,
): void { ?>
    <h2 id="guardians"
        data-intro="Vous pouvez ajouter des tuteurs à un utilisateur. Ces tuteurs seront inclus dans toutes les communications.">
        Tuteurs</h2>
    <form method="post" hx-swap="innerHTML show:#guardians:top" class="row">
        <?= $v->render_validation() ?>

        <div id="guardian-list" class="col-12">
            <?php foreach ($guardian_rows as $index => $fields):
                $guardian = $user->guardians->get($index); ?>
                <?php render_guardian_fieldset(
                    $fields,
                    "Tuteur " . ($index + 1),
                    delete_href: "/licencies/{$user->id}/tuteur/{$guardian->id}/supprimer",
                ); ?>
            <?php endforeach ?>

            <div id="new-guardian-list">
                <?php foreach ($new_guardian_rows as $i => $fields): ?>
                    <?php render_guardian_fieldset(
                        $fields,
                        "Nouveau tuteur " . ($i + 1),
                        js_delete: true,
                    ); ?>
                <?php endforeach ?>
            </div>
        </div>

        <div class="col-auto">
            <button type="button" class="outline contrast" hx-get="/licencies/<?= $user->id ?>/tuteur/form"
                hx-target="#new-guardian-list" hx-swap="beforeend"
                hx-vals='js:{"index": document.querySelectorAll("#new-guardian-list .guardian-row").length}'>
                <i class="fa fa-plus"></i> Ajouter un tuteur
            </button>
        </div>
        <div>
            <input type="submit" class="outline" value="Mettre à jour les tuteurs">
        </div>
    </form>
<?php }
