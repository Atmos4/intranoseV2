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
        $instance->mail->SMTPDebug = SMTP::DEBUG_OFF;
        $instance->mail->isSMTP();
        $instance->mail->Host = env("mail_host");
        $instance->mail->SMTPAuth = true;
        $instance->mail->Username = env("mail_user");
        $instance->mail->Password = env("mail_password");
        $instance->mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        $instance->mail->Port = 465;
        $instance->mail->setFrom(env("mail_user"));
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