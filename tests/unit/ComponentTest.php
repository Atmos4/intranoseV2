<?php declare(strict_types=1);

final class ComponentTest extends BaseTestCase
{
    public function testSimpleComponent(): void
    {
        $simple = component(__DIR__ . "/components/simpleComp.php")->render(["hello" => "world"]);
        $this->assertSame("<p>world</p>", $simple);
    }
    public function testComponentWithChildren(): void
    {
        $compWithChildren = new Component(__DIR__ . "/components/compWithChildren.php", ["hello" => "world"]);
        $compWithChildren->open();
        echo "children";
        $this->assertSame("<p><i>world</i>children</p>", $compWithChildren->close());
    }

    public function testRealExampleComponent(): void
    {
        $comp = component(__DIR__ . "/components/realExampleDemo.php")->render();
        $this->assertStringStartsWith("<main>", $comp);
        $this->assertStringContainsString("<h2>Hello world</h2>", $comp);
        $this->assertStringContainsString("<p>Welcome to my blog</p>", $comp);
    }
}