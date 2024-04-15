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

    public function testSectionComponent(): void
    {
        $comp = component(__DIR__ . "/components/compWithSectionsDemo.php")->render(["header"]);
        $this->assertStringStartsWith("<main>", $comp);
        $this->assertStringContainsString("<h1>Hello world</h1>", $comp);
        $this->assertStringContainsString("<p>Main content</p>", $comp);
    }

    public function testComponentWithEmptySection(): void
    {
        $comp = component(__DIR__ . "/components/compWithSectionsDemo.php")->render(["noHeader" => true]);
        $this->assertStringStartsWith("<main>", $comp);
        $this->assertStringNotContainsString("<h1>Hello world</h1>", $comp);
        $this->assertStringContainsString("<p>Main content</p>", $comp);
    }
}