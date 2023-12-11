<?php

class MockMailer extends Mailer
{
    public MockedEmail $mockedMail;

    // Only allow new instance with create() function
    function __construct()
    {
        $this->mockedMail = new MockedEmail();
    }

    function createEmail($address, $subject, $content): self
    {
        $this->mockedMail->address = $address;
        $this->mockedMail->subject = $subject;
        $this->mockedMail->content = $content;
        return $this;
    }

    function send(): MailResult
    {
        logger()->debug("MockMailer: sent email to {address} with subject {subject}", [
            "address" => $this->mockedMail->address,
            "subject" => $this->mockedMail->subject,
            "content" => $this->mockedMail->content,
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