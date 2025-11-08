<?php
restrict_access();

$open = Component::prop('open') ? "open" : "";

$user = em()->find(User::class, Component::prop('user_id') ?? get_route_param('user_id'));
if (!$user) {
    force_404("this user doesn't exist");
}

$isNotMe = $user != User::getMain();
$can_edit_users = check_auth(Access::$EDIT_USERS) && $isNotMe;
$is_root = check_auth([Permission::ROOT]);

$profile_picture = $user->getPicture();

$use_nose_email = !!env("INTRANOSE");

$email = $use_nose_email ? $user->nose_email : $user->real_email;

// HTMX - replace url
$hxUrl = explode("?", get_header("HX-Current-URL") ?? "")[0] ?: $_SESSION['request_url'];
header("HX-Replace-Url: $hxUrl?user=$user->id");
// HTMX - show user modal
if (!Component::mounted()) {
    UserModal::triggerShowModal("userViewDialog");
}
?>
<script>
    function start_modal_intro() {
        var dropdown = document.getElementById('actions-dropdown');
        dropdown.setAttribute('open', '');
        introJs("#userViewDialog").addSteps(
            [
                {
                    intro: "Bienvenue sur la vue d'un utilisateur ! Ici, vous pouvez voir ses informations et effectuer des actions sur son profil."
                },
                {
                    element: document.getElementById('groups'),
                    intro: "Ici vous pouvez voir les groupes auquels l'utilisateur appartient"
                },
                {
                    element: document.getElementById('user-control'),
                    intro: "En contrôlant un utilisateur, vous pouvez par exemple l'inscrire à des événements à sa place."
                },
                {
                    element: document.getElementById('user-modify'),
                    intro: "Modifiez les informations de l'utilisateur ici."
                },
                {
                    element: document.getElementById('user-family'),
                    intro: "Un utilisateur peut être ajouté à une famille !"
                },
                {
                    element: document.getElementById('user-deactivate'),
                    intro: "Un utilisateur désactivé ne peut plus accéder à son compte, mais peut être réactivé plus tard."
                }
            ]
        ).start();
    }
</script>
<dialog id="userViewDialog" <?= $open ?> onclick="event.target=== this && htmx.trigger(this, 'close-modal')"
    hx-on:close-modal="this.classList.add('closing');
    this.addEventListener('animationend', () => {
        this.close();
    }, {once:true})" hx-on:close="this.close();this.classList.remove('closing');
        history.replaceState({htmx:true}, '', '<?= $hxUrl ?>')">
    <article>
        <header>
            <button class="close secondary" role="link" onclick="htmx.trigger(this, 'close-modal')">
            </button>
            <b>
                <?= "$user->first_name $user->last_name" ?>
            </b>
        </header>

        <?php if ($can_edit_users): ?>
            <div class="help-button-modal" onclick="start_modal_intro()" id="help-button"><i class="fas fa-question"></i>
            </div>
        <?php endif ?>

        <div class="row center">
            <section>
                <figure class="col">
                    <img class="profile-picture" src="<?= $profile_picture ?>">
                </figure>
            </section>
            <?= GroupService::renderTags($user->groups) ?>
            <section>
                <a class="contrast" href="mailto:<?= $email ?>">
                    <?= $email ?>
                </a>
            </section>
            <?php if ($user->phone): ?>
                <section>
                    📞 <a class="contrast" href="tel:<?= $user->phone ?>">
                        <?= $user->phone ?>
                    </a>
                </section>
            <?php endif ?>
            <section>
                <?= $user->birthdate ? "🎂 " . date_format($user->birthdate, "d/m/Y") : "" ?>
            </section>
            <?php if ($isNotMe && FeatureService::enabled(Feature::Messages)): ?>
                <section>
                    <a role="button" href="/messages/direct/<?= $user->id ?>">Message</a>
                </section>
            <?php endif ?>
        </div>

        <?php if ($can_edit_users): ?>
            <footer>
                <nav al-center>
                    <li><a href="/user-control/<?= $user->id ?>" class="outline" id="user-control">Contrôler</a>
                    </li>
                    <li>
                        <details class="dropdown" id="actions-dropdown">
                            <summary class="contrast">Actions</summary>
                            <ul dir="rtl" data-placement="top">
                                <?php if (check_auth(Access::$ROOT)): ?>
                                    <li><a href="/licencies/<?= $user->id ?>/debug"><i class="fa fa-bug"></i>
                                            Debug</a>
                                    </li>
                                <?php endif ?>
                                <li><a href="/licencies/<?= $user->id ?>/modifier" id="user-modify">Modifier</a>
                                </li>
                                <li>
                                    <a href="<?= $user->family ? "/famille/{$user->family->id}" : "/licencies/$user->id/creer-famille" ?>"
                                        id="user-family">
                                        <i class="fa fa-<?= $user->family ? "users" : "plus" ?>"></i> Famille
                                    </a>
                                </li>
                                <li>
                                    <a href="/licencies/<?= $user->id ?>/desactiver" class="destructive"
                                        id="user-deactivate">
                                        <i class="fas fa-trash"></i>
                                        Désactiver
                                    </a>
                                </li>
                            </ul>
                        </details>
                    </li>
                </nav>
            </footer>
        <?php endif ?>
    </article>
</dialog>