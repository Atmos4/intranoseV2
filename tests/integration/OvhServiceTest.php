<?php declare(strict_types=1);
use PHPUnit\Framework\TestCase;

final class OvhServiceTest extends TestCase
{
    private function initTestOvhService(): OvhService
    {
        $mock = new class extends OvhMock {
            function getMailingListSubscriber($list, $subscriberEmail)
            {
                return false;
            }
            function addSubscriberToMailingList($list, $subscriberEmail)
            {
                return true;
            }
            function getRedirection($from = "", $to = "")
            {
                return false;
            }
            function addRedirection($from = "", $to = "")
            {
                return true;
            }
        };
        MainLogger::init(new MainLogger(new Monolog\Logger("")));
        return new OvhService($mock);
    }
    public function testMethodWorks(): void
    {
        $service = $this->initTestOvhService();
        $service->addUser("test@example.com", "test@example.com");

        // Assert
        $toasts = Toast::$toasts;
        $this->assertEquals(count($toasts), 2);
        $this->assertEquals($toasts[0]->message, "Redirection créée");
    }
}