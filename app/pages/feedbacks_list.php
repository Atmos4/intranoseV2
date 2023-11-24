<?php
restrict_access([Permission::ROOT]);

if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    // Extract the ID from the URL
    $id = basename($_SERVER['REQUEST_URI']);

    // Delete the feedback with the given ID
    $feedback = em()->getRepository(Feedback::class)->find($id);
    if ($feedback) {
        em()->remove($feedback);
        em()->flush();
        // Send a response
        http_response_code(200);
        exit;
    }
}

// Get all the feedbaks
$feedbacks = em()->getRepository(Feedback::class)->findAll();

page("Liste des feedbacks") ?>

<article>
    <table>
        <thead>
            <tr>
                <th>Utilisateur</th>
                <th>Description</th>
                <th></th>
            </tr>
        </thead>
        <tbody hx-confirm="Êtes vous sûr ?" hx-target="closest tr" hx-swap="outerHTML swap">
            <?php foreach ($feedbacks as $feedback): ?>
                <tr>
                    <td>
                        <?= $feedback->user->first_name ?>
                        <?= $feedback->user->last_name ?>
                    </td>
                    <td>
                        <?= $feedback->description ?>
                    </td>
                    <td>
                        <a href="" class="destructive" hx-delete="/feedbacks_list/<?= $feedback->id ?>">
                            <i class="fas fa-trash"></i>
                        </a>
                    </td>
                </tr>
            <?php endforeach ?>
        </tbody>
    </table>
</article>