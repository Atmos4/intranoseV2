<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;

class Singleton
{
    private static $instances = [];

    /**
     * The Singleton's constructor should always be private to prevent direct
     * construction calls with the `new` operator.
     */
    protected function __construct()
    {
    }
    protected function __clone()
    {
    }

    public static function getInstance(): static
    {
        $cls = static::class;
        self::$instances[$cls] ??= new static();
        return self::$instances[$cls];
    }
}

class Page extends Singleton
{
    public string|false $title = "";
    public string $description = "";
    public string $css = "";
    public bool $nav = true;
    public string|false $heading = "";
    public string $content = "";
    public bool $controlled = false;

    public function css(string $css)
    {
        $this->css = "/assets/css/" . $css;
        return $this;
    }
    public function description(string $description)
    {
        $this->description = $description;
        return $this;
    }
    public function heading(string|false $heading)
    {
        $this->heading = $heading;
        return $this;
    }
    public function disableNav()
    {
        $this->nav = false;
        return $this;
    }
    public function title(string $title)
    {
        $this->title = $title;
        return $this;
    }
    public function controlled()
    {
        $this->controlled = true;
        return $this;
    }
    public function setContent($content)
    {
        $this->content = $content;
    }
}

class Env extends Singleton
{
    private $hashmap;

    protected function __construct()
    {
        $this->hashmap = include("env.php");
    }

    function getValue($key)
    {
        return $this->hashmap[$key] ?? "";
    }
}

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