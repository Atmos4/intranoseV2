<?php
use GuzzleHttp\Promise\Utils;

page("Test de l'API");
$v = new Validator();
$from = $v->email("from_email")->label("From");
$to = $v->email("to_email")->label("To");

$redirectionId = $v->number("redirection")->label("Redirection");

$api_result = [];
if ($v->valid()) {
    $api_result = match ($_POST['action']) {
        // It's so ugly i love it
        "checkAll" => (function () use ($from, $to) {
                $client = ovh_api();
                $promises = [
                "redirection" => $client->getRedirectionAsync($from->value, $to->value),
                "mailing" => $client->getMailingListSubscriberAsync("nose", $to->value),
                ];
                $response = Utils::settle($promises)->wait(true); // The true here makes it throw on exception
                return [
                "redirection" => $response["redirection"]["value"]->getBody()->getContents(),
                "mailing" => $response["mailing"]["value"]->getBody()->getContents(),
                ];
            })(),
        "getRedirectionById" => ovh_api()->getRedirectionById($redirectionId->value),
        "createRedirection" => ovh_api()->addRedirection($from->value, $to->value),
        "deleteRedirection" => ovh_api()->removeRedirection($from->value, $to->value),
        "checkSubscription" => ovh_api()->getMailingListSubscriber("nose", $to->value),
        "addToList" => ovh_api()->addSubscriberToMailingList("nose", $to->value),
        "removeFromList" => ovh_api()->removeSubscriberFromMailingList("nose", $to->value),
    };
}
?>
<form method="post">
    <?= $v->render_validation() ?>
    <div class="row">
        <div class="col-sm-12 col-md-6">
            <?= $from->render() ?>
        </div>
        <div class="col-sm-12 col-md-6">
            <?= $to->render() ?>
        </div>
    </div>

    <button name="action" value="checkAll">Check redirection and mailing</button><br>
    <button name="action" value="createRedirection">Créer redirection</button>
    <button name="action" value="deleteRedirection">Supprimer redirection</button><br>
    <button name="action" value="addToList">Ajouter à <code>nose</code></button>
    <button name="action" value="removeFromList">Supprimer de <code>nose</code></button>

    <?= $redirectionId->render() ?>
    <button name="action" value="getRedirectionById">Get redirection id</button>
</form>

<pre>
<?= print_r($api_result, true) ?>
</pre>