<?php
restrict_access([Permission::ROOT]);

require_once app_path() . "/components/conditional_icon.php";

$user_id = get_route_param("user_id");
$user = em()->find(User::class, $user_id);
if (!$user) {
    echo "This user doesn't exist";
    return;
}

$ovh = ovh_api();

$realEmailIsSubscribed = $ovh->getMailingListSubscriber("nose", $user->real_email);
$noseEmailIsSubscribed = $ovh->getMailingListSubscriber("nose", $user->nose_email);
$redirections = $ovh->getRedirection(to: $user->real_email);
?>
<h4>OVH</h4>
<p>
    <?= ConditionalIcon(!!$realEmailIsSubscribed) ?>Membre de <code>nose</code> avec
    <?= $user->real_email ?>
</p>
<p>
    <?= ConditionalIcon(!!$noseEmailIsSubscribed) ?>Membre de <code>nose</code> avec
    <?= $user->nose_email ?>
</p>
<p>
    <?= ConditionalIcon(!!$redirections) ?>Redirection vers
    <?= $user->real_email ?>
</p>