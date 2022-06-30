<?php

declare(strict_types=1);

namespace dpi\EnhancedDrupalPhpunitResults;

use Drupal\Tests\Listeners\HtmlOutputPrinterTrait;
use PHPUnit\Framework\TestResult;
use PHPUnit\TextUI\DefaultResultPrinter;

/**
 * Enhanced PHPUnit result printer.
 *
 * This class replaces \Drupal\Tests\Listeners\HtmlOutputPrinter.
 */
class EnhancedResultPrinter extends DefaultResultPrinter
{
    use HtmlOutputPrinterTrait {
        __construct as htmlOutputPrinterTraitConstructor;
    }

    private ?int $maxNumberOfColumns = null;
    private bool $useTrimColumns = true;
    private bool $useRepeatContext = false;
    private bool $useSequential = false;
    private string $ideLaunch = self::IDE_LAUNCH['phpstorm'];
    private ?string $filePrefix = null;
    private bool $outputStack = true;

    protected const IDE_LAUNCH = [
        'emacs' => 'emacs://open?url=file://%s&line=%s',
        'macvim' => 'mvim://open?url=file://%s&line=%s',
        'phpstorm' => 'phpstorm://open?file=%s&line=%s',
        'sublime' => 'subl://open?url=file://%s&line=%s',
        'textmate' => 'txmt://open?url=file://%s&line=%s',
        'vscode' => 'vscode://file/%s:%s',
    ];

    public function __construct($out = null, bool $verbose = false, string $colors = self::COLOR_DEFAULT, bool $debug = false, string|int $numberOfColumns = 80, bool $reverse = false)
    {
        $this->htmlOutputPrinterTraitConstructor($out, $verbose, $colors, $debug, $numberOfColumns, $reverse);

        $ide = getenv('ENHANCED_RESULTS_IDE');
        if (\is_string($ide) && \strlen($ide) > 0) {
            $this->ideLaunch = static::IDE_LAUNCH[$ide] ?? throw new \Exception('Unknown IDE');
        }
        if (getenv('ENHANCED_RESULTS_USE_REPEAT_CONTEXT')) {
            $this->useRepeatContext = true;
        }
        if (getenv('ENHANCED_RESULTS_USE_SEQUENTIAL_IDS')) {
            $this->useSequential = true;
        }
        if (getenv('ENHANCED_RESULTS_DISABLE_TRIM_COLUMNS')) {
            $this->useTrimColumns = false;
        }
        if (getenv('ENHANCED_RESULTS_DISABLE_OUTPUT_STACK')) {
            $this->outputStack = false;
        }
        $filePrefix = getenv('ENHANCED_RESULTS_FILE_PREFIX');
        if (\is_string($filePrefix) && \strlen($filePrefix) > 0) {
            $this->filePrefix = $filePrefix;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function printResult(TestResult $result): void
    {
        parent::printResult($result);
        $this->printHtmlOutput($result);
    }

    /**
     * Prints the list of HTML output generated during the test.
     */
    protected function printHtmlOutput(TestResult $result): void
    {
        if (!$this->browserOutputFile) {
            return;
        }

        $contents = file_get_contents($this->browserOutputFile);
        if (!$contents) {
            return;
        }

        $this->writeNewLine();
        $this->writeWithColor('bg-yellow, fg-black', 'HTML output was generated');

        // As output by \dpi\EnhancedDrupalPhpunitResults\EnhancedUiHelperTrait::htmlOutput
        /** @var array<array{class?:string,name:string,uri:string,backtrace:array<backtraceItem>,response_url:string,artifact_number:int}> $decoded */
        $decoded = json_decode($contents, associative: true);

        if ($this->useSequential) {
            $maxIdLength = \strlen((string) \count($decoded));
        } else {
            $artifactNumbers = array_map(function (array $response): int {
                return $response['artifact_number'];
            }, $decoded);
            $maxIdLength = \strlen((string) max($artifactNumbers));
        }

        $classAndNames = [];
        foreach ($decoded as $responseNumber => ['class' => $class, 'name' => $nameAndSet, 'uri' => $uri, 'backtrace' => $backtrace, 'response_url' => $responseUrl, 'artifact_number' => $artifactNumber]) {
            $classAndName = $class . '::' . $nameAndSet;
            $matches = [];
            preg_match_all('/^(?<method>.*) with data set (?<set>.*)$/m', $nameAndSet, $matches);
            $name = $matches['method'][0] ?? $nameAndSet;
            $set = $matches['set'][0] ?? null;

            if (!\in_array($classAndName, $classAndNames, true)) {
                $classAndNames[] = $classAndName;
                $this->writeNewLine();
                $this->writeWithColor(
                    sprintf('bg-black, %s', \array_key_exists($classAndName, $result->passed()) ? 'fg-green' : 'fg-red'),
                    $class . '::' . $name,
                    false
                );
                if ($set) {
                    $this->write(' scenario: ');
                    $this->writeWithColor('fg-cyan', trim($set, '"'), false);
                }
                $this->writeNewLine();
                $this->writeNewLine();
            }

            $id = $this->useSequential ? $responseNumber + 1 : $artifactNumber;
            $this->singleResult($id, $maxIdLength + 3, $uri, $responseUrl, $backtrace, $class, $name);
        }

        // Delete the result file.
        unlink($this->browserOutputFile);
    }

    /**
     * @param array<backtraceItem> $backtrace
     */
    protected function singleResult(int $id, int $linePad, string $uri, string $responseUrl, array $backtrace, string $class, string $name): void
    {
        $reference = function (array $item) use ($linePad): string {
            return $this->trimmer(sprintf('%s::%s', $item['class'], $item['function']), $linePad + 3);
        };
        $fileAndLine = function (array $item, $pad, $linePad): string {
            $dir = getcwd() ?: throw new \Exception('Unable to determine CWD');
            $fileName = str_starts_with($item['file'], $dir) ? substr($item['file'], \strlen($dir)) : $item['file'];

            $nativeFilePath = $item['file'];
            if (null !== $this->filePrefix) {
                $nativeFilePath = $this->filePrefix . $fileName;
            }

            return $this->leftPad(
                $this->href(
                    $this->ideLink($nativeFilePath, $item['line']),
                    $this->trimmer(sprintf('%s:%s', $fileName, $item['line']), $linePad)
                ),
                $pad,
            );
        };

        $stack = $this->getStack(array_reverse($backtrace), $class, $name);

        $this->write(sprintf(' %s %s %s',
            str_pad('#' . $id, $linePad - 2, pad_type: \STR_PAD_LEFT),
            $this->href($uri, $responseUrl),
            $this->icon($stack),
        ));
        $this->writeNewLine();

        $first = array_shift($stack);
        if (null === $first) {
            throw new \Exception('Nothing in the stack.');
        }

        if ($this->useRepeatContext) {
            $this->write(
                    $this->leftPad(
                        sprintf('in %s',
                            $this->colorizeTextBox('bg-black, fg-yellow', $reference($first)),
                        ),
                        $linePad,
                    ),
                );
            $this->writeNewLine();
        }

        if ($this->outputStack) {
            $stackStringed = [];
            foreach ($stack as $item) {
                if (1 === \count($stack)) {
                    $stackStringed[] = sprintf("%s\n",
                        $this->colorizeTextBox('bg-black, fg-green', $fileAndLine($item, 0, $linePad + 3)),
                    );
                    break;
                } else {
                    $stackStringed[] = sprintf("%s\n%s\n",
                        $this->colorizeTextBox('bg-black, fg-yellow', $reference($item)),
                        $this->colorizeTextBox('bg-black, fg-green', $fileAndLine($item, 3, $linePad + 3)),
                    );
                }
            }

            $this->write(
                $this->leftPad(
                    sprintf(
                        '-> %s',
                        trim(implode('-> ', $stackStringed))
                    ),
                    $linePad,
                )
            );
            $this->writeNewLine();
        }
    }

    private function leftPad(string $str, int $padding): string
    {
        foreach (explode("\n", $str) as $line) {
            $result[] = str_repeat(' ', $padding) . $line;
        }

        return implode(\PHP_EOL, $result);
    }

    private function trimmer(string $str, int $padding = 0): string
    {
        if (!$this->useTrimColumns) {
            return $str;
        }

        $etc = '...';
        $maxlength = ($this->maxNumberOfColumns ?? $this->maxColumn) - $padding - \strlen($etc);
        $strAndEtcLength = \strlen($str) + \strlen($etc);
        if ($strAndEtcLength > $maxlength) {
            return $etc . substr($str, max(\strlen($str) - $maxlength, 0), $maxlength);
        } else {
            return $str;
        }
    }

    private function href(string $uri, string $innerText): string
    {
        return "\e]8;;{$uri}\e\\{$innerText}\e]8;;\e\\";
    }

    private function ideLink(string $path, int $line): string
    {
        return sprintf($this->ideLaunch, $path, (string) $line);
    }

    /**
     * Determine the stack from test to beginning of artifact creation.
     *
     * Login or direct invocations of output are considered the start of
     * artifact creation.
     *
     * @param array<backtraceItem> $backtrace
     *
     * @return array<backtraceItem>
     */
    protected function getStack(array $backtrace, mixed $class, mixed $name): array
    {
        $stack = [];
        $inStack = false;
        foreach ($backtrace as $item) {
            if ($name === ($item['function'] ?? null) && $class === ($item['class'] ?? null)) {
                $inStack = true;
            }

            if ($inStack) {
                $stack[] = $item;
            }

            if (\in_array($item['function'] ?? null, [
                'drupalGet',
                'drupalLogin',
                'htmlOutput',
                'submitForm',
            ], true)) {
                return $stack;
            }
        }

        return $stack;
    }

    /**
     * @param array<array{function?: string, class?: class-string, type: string, file: string, line: int}> $stack
     */
    private function icon(array $stack): string
    {
        if (!\count($stack)) {
            return '';
        }

        $k = array_key_last($stack);

        return match ($stack[$k]['function'] ?? null) {
            'drupalGet' => '📄',
            'drupalLogin' => '🛂',
            'submitForm' => '🖍',
            'htmlOutput' => '⚡️',
            default => '❓',
        };
    }

    /**
     * Invoked for testing.
     *
     * @return $this
     */
    public function setBrowserOutputFile(string $path)
    {
        $this->browserOutputFile = $path;

        return $this;
    }

    /**
     * Invoked for testing.
     *
     * @return $this
     */
    public function setMaxColumns(int $columnCount)
    {
        $this->maxNumberOfColumns = $columnCount;

        return $this;
    }
}
