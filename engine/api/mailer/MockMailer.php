<?php

class MockMailer extends Mailer
{
    public MockedEmail $mockedMail;

    // Only allow new instance with create() function
    function __construct()
    {
        $this->mockedMail = new MockedEmail();
        $this->global_address = "test@test.fr";
        $this->mockedMail->addresses = [];
    }

    function createEmail($address, $subject, $content): self
    {
        $this->mockedMail->addresses[] = $address;
        $this->mockedMail->subject = $subject;
        $this->mockedMail->content = $content;
        return $this;
    }

    function createBulkEmails($addresses, $subject, $content): self
    {
        $this->mockedMail->addresses = $addresses;
        $this->mockedMail->subject = $subject;
        $this->mockedMail->content = $content;
        return $this;
    }

    function send(): MailResult
    {
        logger()->debug("MockMailer: sent email to {address} with subject {subject}", [
            "address" => $this->mockedMail->addresses,
            "subject" => $this->mockedMail->subject,
            "content" => $this->mockedMail->content,
        ]);
        return MailResult::success();
    }
}

class MockedEmail
{
    public array $addresses;
    public string $subject;
    public string $content;
}