<?php

declare(strict_types=1);

namespace dpi\EnhancedDrupalPhpunitResults\tests;

use Behat\Mink\Element\DocumentElement;
use Behat\Mink\Session;
use dpi\EnhancedDrupalPhpunitResults\CombinedEnhancedUiHelperTrait;
use org\bovigo\vfs\vfsStream;
use PHPUnit\Framework\TestCase;

/**
 * @coversDefaultClass \dpi\EnhancedDrupalPhpunitResults\CombinedEnhancedUiHelperTrait
 */
final class UiHelperTraitTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        vfsStream::setup('test');
    }

    public function testBasic(): void
    {
        $session = $this->createMock(Session::class);
        $fileName = 'vfs://test/outputfile.html';
        touch($fileName);
        putenv('BROWSERTEST_OUTPUT_FILE=' . $fileName);
        putenv('BROWSERTEST_OUTPUT_BASE_URL=http://localhost:8080');

        $testClass = new class($session) extends TestCase {
            use CombinedEnhancedUiHelperTrait;

            public function __construct(protected Session $session)
            {
                parent::__construct();
                // @phpstan-ignore-next-line
                $this->siteDirectory = 'vfs://test';
                $this->htmlOutputEnabled = true;
                $this->initBrowserOutputFile();
                $this->htmlOutputDirectory = 'vfs://test';
                $this->htmlOutputClassName = 'Test_Blah_Class';
                // @phpstan-ignore-next-line
                $this->htmlOutputTestId = '';
                $this->htmlOutputCounterStorage = 'vfs://test/counter.counter';
                $this->setName('testMethod with data set "something fake"');
            }

            public function publicHtmlOutput(string $message = null): void
            {
                $this->htmlOutput($message);
            }

            public function getSession(): Session
            {
                return $this->session;
            }
        };

        $session->method('getPage')
            ->willReturn($this->createMock(DocumentElement::class));
        $session->method('getCurrentUrl')
            ->willReturn('http://localhost:8080/mypage');

        $testClass->publicHtmlOutput('foo');

        $json = json_decode(file_get_contents($fileName) ?: throw new \LogicException(), associative: true);
        $json = reset($json);
        $this->assertStringContainsString('@anonymous', $json['class']);
        $this->assertStringContainsString('testMethod with data set "something fake"', $json['name']);
        $this->assertEquals('http://localhost:8080/sites/simpletest/browser_output/Test_Blah_Class-1-.html', $json['uri']);
        $this->assertGreaterThan(0, \count($json['backtrace']));
        $this->assertEquals('http://localhost:8080/mypage', $json['response_url']);
        $this->assertEquals('1', $json['artifact_number']);
    }
}
