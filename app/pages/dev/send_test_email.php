<?php
restrict_staging();
restrict_access([Permission::ROOT]);

$v = new Validator();
$address = $v->text("address")->placeholder("Destinataire")->required()->autocomplete("email");
$address2 = $v->text("address2")->placeholder("Destinataire 2 (optionnel)")->autocomplete("email");
$address3 = $v->text("address3")->placeholder("Destinataire 3 (optionnel)")->autocomplete("email");
$subject = $v->text("subject")->placeholder("Object")->required();
$content = $v->textarea("content")->placeholder("Contenu")->required();

if ($v->valid()) {
    if ($address2->value) {
        $addresses = [$address->value => "addresse 1", $address2->value => "addresse 2"];
        $address3->value ? $addresses += [$address3->value => "addresse 3"] : '';
        $result = Mailer::create()
            ->createBulkEmails($addresses, $subject->value, $content->value)
            ->send();
    } else {
        $result = Mailer::create()
            ->createEmail($address->value, $subject->value, $content->value)
            ->send();
    }
    if ($result->success) {
        $v->set_success('Message has been sent');
    } else {
        $v->set_error($result->message);
    }
}

page("Test email") ?>
<form method="post">
    <?= $v->render_validation() ?>
    <?= $address->render() ?>
    <?= $address2->render() ?>
    <?= $address3->render() ?>
    <?= $subject->render() ?>
    <?= $content->render() ?>
    <button type="submit"><i class="fa fa-paper-plane"></i> Envoyer</button>
</form>