<?php

class MockMailer extends Mailer
{
    public MockedEmail $mail;

    // Only allow new instance with create() function
    function __construct()
    {
        $this->mail = new MockedEmail();
    }

    function createEmail($address, $subject, $content): self
    {
        $this->mail->address = $address;
        $this->mail->subject = $subject;
        $this->mail->content = $content;
        return $this;
    }

    function send(): MailResult
    {
        logger()->debug("MockMailer: sent email to {address} with subject {subject}", [
            "address" => $this->mail->address,
            "subject" => $this->mail->subject,
            "content" => $this->mail->content,
        ]);
        return MailResult::success();
    }
}

class MockedEmail
{
    public string $address;
    public string $subject;
    public string $content;
}