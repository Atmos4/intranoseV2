<?php

restrict_access([Permission::ROOT]);

// only serve POST request containing valid json data
if (isset($_SERVER['CONTENT_TYPE']) && trim(strtolower($_SERVER['CONTENT_TYPE']) == 'application/json')) {
    //decode json passed through the http request
    $json = json_decode(trim(file_get_contents('php://input')), true);
    if ($json === false) {
        logger()->error("invalid JSON data!");
        return;
    }

    $method = $_SERVER['REQUEST_METHOD'];
    $state = false;

    switch ($method) {
        case 'POST':
            $subscription = new NotificationSubscription();
            $subscription->endpoint = $json['endpoint'];
            $subscription->p256dh = $json['keys']['p256dh'];
            $subscription->auth = $json['keys']['auth'];
            em()->persist($subscription);
            $state = true;
            break;
        case 'PUT':
            // update the key and token of subscription corresponding to the endpoint
            $subscription = em()->getRepository('NotificationSubscription')->findOneBy(['endpoint' => $json['endpoint']]);
            if ($subscription) {
                $subscription->p256dh = $json['keys']['p256dh'];
                $subscription->auth = $json['keys']['auth'];
                em()->persist($subscription);
                $state = true;
            } else {
                logger()->error("subscription {$subscription->id} not found!");
            }
            break;
        case 'DELETE':
            // delete the subscription corresponding to the endpoint
            $subscription = em()->getRepository('NotificationSubscription')->findOneBy(['endpoint' => $json['endpoint']]);
            if ($subscription) {
                em()->remove($subscription);
                $state = true;
            } else {
                logger()->error("subscription {$subscription->id} not found!");
            }
            break;
        default:
            logger()->error("http method not handled");
            return;
    }
    if ($state) {
        em()->flush();
        logger()->info("subscription {$subscription->id} saved on server!");
    }
}
