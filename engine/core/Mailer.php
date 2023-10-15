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

class Mailer
{
    public PHPMailer $mail;

    // Only allow new instance with create() function
    private function __construct()
    {
    }

    static function create(): self
    {
        $instance = new Mailer();
        $instance->mail = new PHPMailer();
        $instance->mail->CharSet = "UTF-8";
        $instance->mail->SMTPDebug = SMTP::DEBUG_OFF;
        $instance->mail->isSMTP();
        $instance->mail->Host = env("MAIL_HOST");
        $instance->mail->SMTPAuth = true;
        $instance->mail->Username = env("MAIL_USER");
        $instance->mail->Password = env("MAIL_PASSWORD");
        $instance->mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        $instance->mail->Port = 465;
        $instance->mail->setFrom(env("MAIL_USER"));
        return $instance;
    }

    function to($address, $subject, $content): self
    {
        $this->mail->addAddress($address);
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
        return Mailer::create()->to($address, $subject, $content);
    }
}