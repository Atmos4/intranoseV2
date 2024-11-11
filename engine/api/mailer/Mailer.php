<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;

class MailResult
{
    public bool $success = false;
    public string $message = "";
    function __construct($success, $message)
    {
        $this->success = $success;
        $this->message = $message;
    }
    static function success($message = "")
    {
        return new MailResult(true, $message);
    }

    static function error($message = "")
    {
        return new MailResult(false, $message);
    }
}

class Mailer extends FactoryDependency
{
    public PHPMailer $mail;

    // Only allow new instance with create() function
    function __construct()
    {
        $this->mail = new PHPMailer();
        $this->mail->CharSet = "UTF-8";
        $this->mail->SMTPDebug = SMTP::DEBUG_OFF;
        $this->mail->isSMTP();
        $this->mail->isHTML(true);
        $this->mail->Host = env("MAIL_HOST");
        $this->mail->SMTPAuth = true;
        $this->mail->Username = env("MAIL_USER");
        $this->mail->Password = env("MAIL_PASSWORD");
        $this->mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        $this->mail->Port = 465;
        $this->mail->setFrom(env("MAIL_USER"), "Intranose");

        // DKIM - not using for now
        if (env("USE_DKIM")) {
            $this->mail->DKIM_domain = env("DKIM_DOMAIN");
            $this->mail->DKIM_private = base_path() . "/.secrets/" . env("DKIM_FILENAME"); // Make sure to protect the key from being publicly accessible!
            $this->mail->DKIM_selector = env("DKIM_SELECTOR");
            $this->mail->DKIM_passphrase = env("DKIM_PASSPHRASE");
            $this->mail->DKIM_identity = $this->mail->From;
        }
    }

    static function getGlobalAddress()
    {
        return env("MAIL_GLOBAL_ADDRESS") ?? "devs@nose42.fr";
    }

    function createEmail($address, $subject, $content): self
    {
        $this->mail->addAddress($address);
        $this->mail->Subject = $subject;
        $this->mail->Body = $content;
        return $this;
    }

    function createBulkEmails($addresses, $subject, $content): self
    {
        foreach ($addresses as $email => $name) {
            $this->mail->addAddress($email, $name);
        }
        $this->mail->Subject = $subject;
        $this->mail->Body = $content;
        return $this;
    }

    function send(): MailResult
    {
        try {
            $this->mail->send();
            return MailResult::success();
        } catch (Exception $e) {
            return MailResult::error("Message could not be sent. Mailer Error: {$this->mail->ErrorInfo}");
        }
    }
}

class MailHelper
{
    // Shamelessly taken from StackOverflow
    static function obfuscate($email)
    {
        $em = explode("@", $email);
        $name = implode('@', array_slice($em, 0, count($em) - 1));
        $len = floor(strlen($name) / 2);

        return substr($name, 0, $len) . str_repeat('*', $len) . "@" . end($em);
    }
}

class MailerFactory
{
    static function createActivationEmail(string $address, string $token)
    {
        $base_url = env("BASE_URL");
        $subject = "Activation du compte NOSE";
        $content = "Voici le lien pour activer ton compte: $base_url/activation?token=$token";
        return Mailer::create()->createEmail($address, $subject, $content);
    }

    static function createEventPublicationEmail(Event $event)
    {
        $base_url = env("BASE_URL");
        $subject = (env("STAGING") ? "[STAGING] " : "") . "Nouvel événement sur l'intranose";
        $event_date = $event->deadline->format('d/m/Y');
        $content = "<h3>Un nouvel événement a été publié sur l'intranose !</h3>
        Nom de l'événement : <b>$event->name</b><br>
        La deadline pour s'inscrire est le $event_date.<br>
        Pour voir les infos : <a href = '$base_url/evenements/$event->id' >Lien de l'événement</a>.<br>
        Pour s'inscrire : <a href = '$base_url/evenements/$event->id/inscription' >Inscription</a>.<br>
        <br>
        A bientôt pour de nouveaux événements !<br>
        Le Nose<br>
        <a href = 'www.nose42.fr' >www.nose42.fr</a>";
        $mailer = Mailer::create();
        return $mailer->createEmail(Mailer::getGlobalAddress(), $subject, $content);
    }
}