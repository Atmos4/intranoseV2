<?php
use Minishlink\WebPush\WebPush;
use Minishlink\WebPush\Subscription;

restrict(dev_or_staging());
restrict_access([Permission::ROOT]);

$subscriptions = em()->getRepository('NotificationSubscription')->findAll();
$last_subscription = end($subscriptions);

$auth = array(
    'VAPID' => array(
        'subject' => 'Intranose',
        'publicKey' => env("PUBLIC_VAPID_KEY"),
        'privateKey' => env("PRIVATE_VAPID_KEY"),
    ),
);

$webPush = new WebPush($auth);

$v = new Validator();
$title = $v->text("title")->placeholder("Titre")->required();
$message = $v->textarea("message")->placeholder("Message")->required();
$url = $v->url("url")->placeholder("url");

if ($v->valid()) {
    logger()->info("Sending message");

    // Initialize an empty array to store the subscriptions
    $subscriptionsArray = [];

    // Iterate through each subscription entity
    foreach ($subscriptions as $subscriptionEntity) {
        // Create a new Subscription object with the required data
        $subscription = Subscription::create([
            "endpoint" => $subscriptionEntity->endpoint,
            "keys" => [
                'p256dh' => $subscriptionEntity->p256dh,
                'auth' => $subscriptionEntity->auth,
            ]
        ]);

        // Add the Subscription object to the array
        $subscriptionsArray[] = $subscription;
    }

    // send multiple notifications with payload
    foreach ($subscriptionsArray as $subscription) {
        $webPush->queueNotification(
            $subscription,
            '{"title":"' . $title->value . '","message":"' . $message->value . '","url":"' . $url->value . '"}',
        );
    }

    /**
     * Check sent results
     */
    foreach ($webPush->flush() as $report) {
        $endpoint = $report->getRequest()->getUri()->__toString();

        if ($report->isSuccess()) {
            logger()->info("[v] Message sent successfully for subscription {$endpoint}.");
        } else {
            logger()->error("[x] Message failed to sent for subscription {$endpoint}: {$report->getReason()}");
        }
    }
}

page("Test Service Worker and Push Notifications");
?>

<h4>Load Informations</h4>
<nav>
    <ul>
        <li><a role=button id="reg-btn" onclick="subscriptionsInformations()">Load</a></li>
    </ul>
</nav>
<div id=msg></div>
<h4>Test Service Worker</h4>
<nav>
    <ul>
        <li><a role=button onclick="sw_register()">Register</a></li>
        <li><a role=button onclick="sw_unregister()">Unregister</a></li>
        <li><a role=button onclick="sw_update()">Update</a></li>
    </ul>
</nav>
<h4>Test Notifications subscription</h4>
<nav>
    <ul>
        <li><a role=button onclick="pn_updateSubscription()">Subscribe</a></li>
        <li><a role=button onclick="pn_unsubscribe()">Delete Subscription</a></li>
        <li><a role="button" onclick="pn_getSubscription()">Get Subscription Status</a></li>
    </ul>
</nav>
<div id=pn></div>
<h3>Send Notification</h3>
<form method="post">
    <?= $v->render_validation() ?>
    <?= $title->render() ?>
    <?= $message->render() ?>
    <?= $url->render() ?>
    <button type="submit"><i class="fa fa-paper-plane"></i> Envoyer</button>
</form>