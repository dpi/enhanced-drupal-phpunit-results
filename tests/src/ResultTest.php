<?php

declare(strict_types=1);

namespace dpi\EnhancedDrupalPhpunitResults\tests;

use dpi\EnhancedDrupalPhpunitResults\EnhancedResultPrinter;
use org\bovigo\vfs\vfsStream;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\TestResult;

/**
 * @coversDefaultClass \dpi\EnhancedDrupalPhpunitResults\EnhancedResultPrinter
 */
final class ResultTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        putenv('ENHANCED_RESULTS_IDE');
        putenv('ENHANCED_RESULTS_USE_REPEAT_CONTEXT');
        putenv('ENHANCED_RESULTS_USE_SEQUENTIAL_IDS');
        putenv('ENHANCED_RESULTS_DISABLE_TRIM_COLUMNS');
        putenv('ENHANCED_RESULTS_FILE_PREFIX');

        vfsStream::setup('test');
    }

    public function testBasic(): void
    {
        $display = $this->runFixture(__DIR__ . '/../fixtures/sample1-1.json');
        $this->assertStringContainsString('Drupal\Tests\my_module\Functional\MyModuleTest::testTheThing', $display);
        $this->assertStringContainsString(<<<OUT
             #1884 ]8;;http://localhost:8080/sites/simpletest/browser_output/Drupal_Tests_my_module_Functional_MyModuleTest-1884-dtt.html\http://localhost:8080/user/login]8;;\ 🛂
                   -> ]8;;phpstorm://open?file=/home/user/app/modules/custom/my_module/tests/src/Functional/MyModuleTest.php&line=22\/home/user/app/modules/custom/my_module/tests/src/Functional/MyModuleTest.php:22]8;;\
            OUT, $display);
    }

    public function testDataProvider(): void
    {
        $display = $this->runFixture(__DIR__ . '/../fixtures/sample2.json');
        $this->assertStringContainsString('Drupal\Tests\my_module\Functional\MyModuleTest::testTheThing scenario: is disabled', $display);
        $this->assertStringContainsString(<<<OUT
             #200 ]8;;http://localhost:8080/sites/simpletest/browser_output/Drupal_Tests_my_module_Functional_MyModuleTest-200-dtt.html\http://localhost:8080/user/login]8;;\ 🛂
                  -> ]8;;phpstorm://open?file=/home/user/app/modules/custom/my_module/tests/src/Functional/MyModuleTest.php&line=22\/home/user/app/modules/custom/my_module/tests/src/Functional/MyModuleTest.php:22]8;;\
            OUT, $display);
    }

    public function testIconLogin(): void
    {
        $display = $this->runFixture(__DIR__ . '/../fixtures/sample3-1.json');
        $this->assertStringContainsString('Drupal\Tests\my_module\Functional\MyModuleTest::testLoginIcon', $display);
        $this->assertStringContainsString(<<<OUT
             #301 ]8;;http://localhost:8080/sites/simpletest/browser_output/Drupal_Tests_my_module_Functional_MyModuleTest-301-dtt.html\http://localhost:8080/user/login]8;;\ 🛂
                  -> ]8;;phpstorm://open?file=/home/user/app/modules/custom/my_module/tests/src/Functional/MyModuleTest.php&line=22\/home/user/app/modules/custom/my_module/tests/src/Functional/MyModuleTest.php:22]8;;\
            OUT, $display);
    }

    public function testIconLogout(): void
    {
        $display = $this->runFixture(__DIR__ . '/../fixtures/sample3-5.json');
        $this->assertStringContainsString('Drupal\Tests\my_module\Functional\MyModuleTest::testLogoutIcon', $display);
        $this->assertStringContainsString(<<<OUT
             #305 ]8;;http://localhost:8080/sites/simpletest/browser_output/Drupal_Tests_my_module_Functional_MyModuleTest-305-dtt.html\http://localhost:8080/user/login]8;;\ 🥾
                  -> ]8;;phpstorm://open?file=/home/user/app/modules/custom/my_module/tests/src/Functional/MyModuleTest.php&line=22\/home/user/app/modules/custom/my_module/tests/src/Functional/MyModuleTest.php:22]8;;\
            OUT
, $display);
    }

    public function testIconDrupalGet(): void
    {
        $display = $this->runFixture(__DIR__ . '/../fixtures/sample3-2.json');
        $this->assertStringContainsString('Drupal\Tests\my_module\Functional\MyModuleTest::testDrupalGetIcon', $display);
        $this->assertStringContainsString(<<<OUT
             #302 ]8;;http://localhost:8080/sites/simpletest/browser_output/Drupal_Tests_my_module_Functional_MyModuleTest-302-dtt.html\http://localhost:8080/user/login]8;;\ 📄
                  -> ]8;;phpstorm://open?file=/home/user/app/core/tests/Drupal/Tests/UiHelperTrait.php&line=253\/home/user/app/core/tests/Drupal/Tests/UiHelperTrait.php:253]8;;\
            OUT, $display);
    }

    public function testIconDirectOutput(): void
    {
        $display = $this->runFixture(__DIR__ . '/../fixtures/sample3-3.json');
        $this->assertStringContainsString('Drupal\Tests\my_module\Functional\MyModuleTest::testDirectHtmlOutput', $display);
        $this->assertStringContainsString(<<<OUT
             #303 ]8;;http://localhost:8080/sites/simpletest/browser_output/Drupal_Tests_my_module_Functional_MyModuleTest-303-dtt.html\http://localhost:8080/user/login]8;;\ ⚡️
                  -> ]8;;phpstorm://open?file=/home/user/app/core/tests/Drupal/Tests/UiHelperTrait.php&line=253\/home/user/app/core/tests/Drupal/Tests/UiHelperTrait.php:253]8;;\
            OUT, $display);
    }

    public function testIconSubmitForm(): void
    {
        $display = $this->runFixture(__DIR__ . '/../fixtures/sample3-4.json');
        $this->assertStringContainsString('Drupal\Tests\my_module\Functional\MyModuleTest::testSubmitForm', $display);
        $this->assertStringContainsString(<<<OUT
             #304 ]8;;http://localhost:8080/sites/simpletest/browser_output/Drupal_Tests_my_module_Functional_MyModuleTest-304-dtt.html\http://localhost:8080/user/login]8;;\ 🖍
                  -> ]8;;phpstorm://open?file=/home/user/app/core/tests/Drupal/Tests/UiHelperTrait.php&line=253\/home/user/app/core/tests/Drupal/Tests/UiHelperTrait.php:253]8;;\
            OUT, $display);
    }

    public function testMultiLevel(): void
    {
        $display = $this->runFixture(__DIR__ . '/../fixtures/sample4-1.json');
        $this->assertStringContainsString('Drupal\Tests\my_module\Functional\MyModuleTest::testMultiLevel', $display);
        $this->assertStringContainsString(<<<OUT
             #401 ]8;;http://localhost:8080/sites/simpletest/browser_output/Drupal_Tests_my_module_Functional_MyModuleTest-401-dtt.html\http://localhost:8080/user/login]8;;\ 📄
                  -> Drupal\Tests\my_module\Functional\MyModuleTest::getUtility
                     ]8;;phpstorm://open?file=/home/user/app/sites/simpletest/TestCase.php&line=2000\/home/user/app/sites/simpletest/TestCase.php:2000]8;;\
                  -> Drupal\Tests\my_module\Functional\MyModuleTest::moreAbstraction
                     ]8;;phpstorm://open?file=/home/user/app/sites/simpletest/TestCase.php&line=3000\/home/user/app/sites/simpletest/TestCase.php:3000]8;;\
                  -> Drupal\Tests\my_module\Functional\MyTestBase::drupalGet
                     ]8;;phpstorm://open?file=/home/user/app/core/tests/Drupal/Tests/UiHelperTrait.php&line=253\/home/user/app/core/tests/Drupal/Tests/UiHelperTrait.php:253]8;;\
            OUT, $display);
    }

    public function testUseRepeatContextSingleStack(): void
    {
        putenv('ENHANCED_RESULTS_USE_REPEAT_CONTEXT=TRUE');
        $display = $this->runFixture(__DIR__ . '/../fixtures/sample1-1.json');
        $this->assertStringContainsString('Drupal\Tests\my_module\Functional\MyModuleTest::testTheThing', $display);
        $this->assertStringContainsString(<<<OUT
             #1884 ]8;;http://localhost:8080/sites/simpletest/browser_output/Drupal_Tests_my_module_Functional_MyModuleTest-1884-dtt.html\http://localhost:8080/user/login]8;;\ 🛂
                   in Drupal\Tests\my_module\Functional\MyModuleTest::testTheThing
                   -> ]8;;phpstorm://open?file=/home/user/app/modules/custom/my_module/tests/src/Functional/MyModuleTest.php&line=22\/home/user/app/modules/custom/my_module/tests/src/Functional/MyModuleTest.php:22]8;;\
            OUT, $display);
    }

    public function testUseRepeatContextMultiStack(): void
    {
        putenv('ENHANCED_RESULTS_USE_REPEAT_CONTEXT=TRUE');
        $display = $this->runFixture(__DIR__ . '/../fixtures/sample4-1.json');
        $this->assertStringContainsString('Drupal\Tests\my_module\Functional\MyModuleTest::testMultiLevel', $display);
        $this->assertStringContainsString(<<<OUT
             #401 ]8;;http://localhost:8080/sites/simpletest/browser_output/Drupal_Tests_my_module_Functional_MyModuleTest-401-dtt.html\http://localhost:8080/user/login]8;;\ 📄
                  in Drupal\Tests\my_module\Functional\MyModuleTest::testMultiLevel
                  -> Drupal\Tests\my_module\Functional\MyModuleTest::getUtility
                     ]8;;phpstorm://open?file=/home/user/app/sites/simpletest/TestCase.php&line=2000\/home/user/app/sites/simpletest/TestCase.php:2000]8;;\
                  -> Drupal\Tests\my_module\Functional\MyModuleTest::moreAbstraction
                     ]8;;phpstorm://open?file=/home/user/app/sites/simpletest/TestCase.php&line=3000\/home/user/app/sites/simpletest/TestCase.php:3000]8;;\
                  -> Drupal\Tests\my_module\Functional\MyTestBase::drupalGet
                     ]8;;phpstorm://open?file=/home/user/app/core/tests/Drupal/Tests/UiHelperTrait.php&line=253\/home/user/app/core/tests/Drupal/Tests/UiHelperTrait.php:253]8;;\
            OUT, $display);
    }

    /**
     * Rows start with sequence relative to this test run rather than artifacts.
     */
    public function testUseSequential(): void
    {
        putenv('ENHANCED_RESULTS_USE_SEQUENTIAL_IDS=TRUE');
        $display = $this->runFixture(__DIR__ . '/../fixtures/sample1-2.json');
        $this->assertStringContainsString('Drupal\Tests\my_module\Functional\MyModuleTest::testTheThing scenario: is disabled', $display);
        $this->assertStringContainsString(<<<OUT
             #1 ]8;;http://localhost:8080/sites/simpletest/browser_output/Drupal_Tests_my_module_Functional_MyModuleTest-1021-dtt.html\http://localhost:8080/user/login]8;;\ 🛂
                -> ]8;;phpstorm://open?file=/home/user/app/modules/custom/my_module/tests/src/Functional/MyModuleTest.php&line=22\/home/user/app/modules/custom/my_module/tests/src/Functional/MyModuleTest.php:22]8;;\
            OUT, $display);
        $this->assertStringContainsString('Drupal\Tests\my_module\Functional\MyModuleTest::testTheThing scenario: is enabled', $display);
        $this->assertStringContainsString(<<<OUT
             #2 ]8;;http://localhost:8080/sites/simpletest/browser_output/Drupal_Tests_my_module_Functional_MyModuleTest-1022-dtt.html\http://localhost:8080/user/login]8;;\ 🛂
                -> ]8;;phpstorm://open?file=/home/user/app/modules/custom/my_module/tests/src/Functional/MyModuleTest.php&line=22\/home/user/app/modules/custom/my_module/tests/src/Functional/MyModuleTest.php:22]8;;\
            OUT, $display);
    }

    public function testCols(): void
    {
        $display = $this->runFixture(__DIR__ . '/../fixtures/sample2.json', maxColumns: 30);
        $this->assertStringContainsString('phpstorm://open?file=/home/user/app/modules/custom/my_module/tests/src/Functional/MyModuleTest.php&line=22\...yModuleTest.php:22]8;;', $display);

        putenv('ENHANCED_RESULTS_DISABLE_TRIM_COLUMNS=TRUE');
        $display = $this->runFixture(__DIR__ . '/../fixtures/sample2.json', maxColumns: 30);
        $this->assertStringContainsString('phpstorm://open?file=/home/user/app/modules/custom/my_module/tests/src/Functional/MyModuleTest.php&line=22\/home/user/app/modules/custom/my_module/tests/src/Functional/MyModuleTest.php:22]8;;', $display);
    }

    public function testCustomIde(): void
    {
        putenv('ENHANCED_RESULTS_IDE=vscode');
        $display = $this->runFixture(__DIR__ . '/../fixtures/sample2.json');
        $this->assertStringContainsString('vscode://file//home/user/app/modules/custom/my_module/tests/src/Functional/MyModuleTest.php:22', $display);

        putenv('ENHANCED_RESULTS_IDE=foo');
        $this->expectExceptionMessage('Unknown IDE');
        $this->runFixture(__DIR__ . '/../fixtures/sample2.json');
    }

    public function testFilePrefix(): void
    {
        putenv('ENHANCED_RESULTS_FILE_PREFIX=/home/user/www');
        $display = $this->runFixture(__DIR__ . '/../fixtures/sample2.json');
        $this->assertStringContainsString('phpstorm://open?file=/home/user/www/home/user/app/modules/custom/my_module/tests/src/Functional/MyModuleTest.php&line=22', $display);
    }

    private function runFixture(string $fixture, int $maxColumns = 200): string
    {
        $out = fopen('php://memory', 'w', false) ?: throw new \Exception('Failed to create memory stream');
        $filePath = 'vfs://test/browseroutputfile.html';
        putenv('BROWSERTEST_OUTPUT_FILE=' . $filePath);

        $printer = (new EnhancedResultPrinter($out))
            ->setBrowserOutputFile($filePath)
            ->setMaxColumns($maxColumns);

        file_put_contents($filePath, file_get_contents($fixture));

        $testResult = new TestResult();
        $printer->printResult($testResult);

        rewind($out);
        $display = stream_get_contents($out) ?: throw new \Exception('Failed to get stream context.');

        return $display;
    }
}
