<?php declare(strict_types=1);

final class OvhServiceTest extends BaseTestCase
{
    public function testRedirectionAndMailingAreAddedIfMissing(): void
    {
        $ovhClient = $this->createMock(OvhMock::class);
        $ovhClient->expects($this->once())->method('getMailingListSubscriber')->willReturn(false);
        $ovhClient->expects($this->once())->method('getRedirection')->willReturn(false);
        $ovhClient->expects($this->once())->method('addSubscriberToMailingList')->willReturn(true);
        $ovhClient->expects($this->once())->method('addRedirection')->willReturn(true);

        $service = new OvhService($ovhClient);
        $service->addUser("test@example.com", "test@example.com");

        $toasts = Toast::$toasts;
        $this->assertEquals(count($toasts), 2);
        $this->assertEquals($toasts[0]->message, "Redirection créée");
    }

    public function testRedirectionAndMailingAreSkippedIfAlreadyPresent(): void
    {
        $ovhClient = $this->createMock(OvhMock::class);
        $ovhClient->expects($this->once())->method('getMailingListSubscriber')->willReturn(true);
        $ovhClient->expects($this->once())->method('getRedirection')->willReturn(true);
        # should never be called if the user already already has a redirection
        $ovhClient->expects($this->never())->method('addSubscriberToMailingList');
        $ovhClient->expects($this->never())->method('addRedirection');

        $service = new OvhService($ovhClient);
        $service->addUser("test@example.com", "test@example.com");

        $toasts = Toast::$toasts;
        $this->assertEquals(count($toasts), 0);
    }
}