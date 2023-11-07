<?php
restrict_dev();
page("Test ovh");

$ovh = ovh_api();

// FORMS
$logout_form = new Validator(action: "logout");
$credentials_form = new Validator(action: "credentials");

if ($logout_form->valid()) {
    $ovh->logOut();
}

// display new credentials
if ($credentials_form->valid()):
    $auth_response = $ovh->logIn(); ?>

    <h3>New credentials</h3>
    <p>Consumer key: <code><?= $auth_response["consumerKey"] ?></code>. Paste this in <code>.env</code></p>
    <p><a href="<?= $auth_response["validationUrl"] ?>" target="_blank">Validation url</a>. Click to validate the new token
    </p>
    <a role="button" href="/dev/ovh">Refresh page</a>

    <?php return;
endif;

$needNewCredential = false;

try {
    $currentCredential = $ovh->currentAuth();
} catch (\Exception $e) {
    // If the request failed, the credentials are not valid
    if ($e->getCode() == 403) {
        $needNewCredential = true;
    } else {
        // Handle other exceptions
        echo "An error occurred: {$e->getMessage()}\n";
    }
}

?>

<?php // new credentials
if ($needNewCredential): ?>
    <p>Your credentials are not valid anymore</p>
    <form method="post">
        <?= $credentials_form->render_validation() ?>
        <input type="submit" value="refresh credentials">
    </form>
<?php else: ?>
    <form method="post">
        <?= $logout_form->render_validation() ?>
        <p>You are logged in!</p>
        <input type="submit" value="logout">
    </form>
    <nav>
        <ul>
            <li><a href="/dev/ovh/mailing-lists">Mailing lists</a></li>
            <li><a href="/dev/ovh/redirections">Redirections</a></li>
            <li><a href="/dev/ovh/test-api">test API</a></li>
        </ul>
    </nav>
    <h4>Login info</h4>
    <pre><?= print_r($currentCredential, true) ?></pre>
<?php endif ?>