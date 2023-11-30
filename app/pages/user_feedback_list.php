<?php
restrict_access([Permission::ROOT]);

if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    // Extract the ID from the URL
    $id = get_route_param("user_id");

    // Delete the user_feedback with the given ID
    $user_feedback = em()->getRepository(UserFeedback::class)->find($id);
    if ($user_feedback) {
        em()->remove($user_feedback);
        em()->flush();
        // Send a response
        http_response_code(200);
        Toast::success("Le feedback a été supprimé");
        return;
    }
}

// Get all the bug reports
$user_feedbacks = em()->getRepository(UserFeedback::class)->findAll();

page("Feedbaks")->css('user_feedback_list.css') ?>

<article hx-confirm="Are you sure?" hx-target="closest div#row" hx-swap="outerHTML">
    <?php foreach ($user_feedbacks as $user_feedback): ?>

        <div id="row">
            <div class="grid">
                <kbd>
                    #
                    <?= $user_feedback->id ?>
                </kbd>
                <b>
                    <i class="fas fa-user"></i>
                    <?= $user_feedback->user->first_name ?>
                    <?= $user_feedback->user->last_name ?>
                </b>
                <a role=button href="" class="destructive" hx-delete="/feedback-list/supprimer/<?= $user_feedback->id ?>">
                    <i class="fas fa-trash"></i>
                </a>
            </div>
            <p>
                <?= $user_feedback->description ?>
            </p>
            <hr>
        </div>

    <?php endforeach ?>

</article>