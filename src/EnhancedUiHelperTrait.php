<?php

declare(strict_types=1);

namespace dpi\EnhancedDrupalPhpunitResults;

trait EnhancedUiHelperTrait
{
    /**
     * Replaces BrowserHtmlDebugTrait::htmlOutput.
     *
     * @see \Drupal\Tests\BrowserHtmlDebugTrait::htmlOutput
     */
    // @phpstan-ignore-next-line
    protected function htmlOutput($message = null): void
    {
        if (!$this->htmlOutputEnabled) {
            return;
        }
        $message = $message ?: $this->getSession()->getPage()->getContent();
        $message = '<hr />ID #' . $this->htmlOutputCounter . ' (<a href="' . $this->htmlOutputClassName . '-' . ($this->htmlOutputCounter - 1) . '-' . $this->htmlOutputTestId . '.html">Previous</a> | <a href="' . $this->htmlOutputClassName . '-' . ($this->htmlOutputCounter + 1) . '-' . $this->htmlOutputTestId . '.html">Next</a>)<hr />' . $message;
        $html_output_filename = $this->htmlOutputClassName . '-' . $this->htmlOutputCounter . '-' . $this->htmlOutputTestId . '.html';
        file_put_contents($this->htmlOutputDirectory . '/' . $html_output_filename, $message);
        file_put_contents($this->htmlOutputCounterStorage, $this->htmlOutputCounter++);
        // Do not use the file_url_generator service as the module_handler service
        // might not be available.

        $uri = $this->htmlOutputBaseUrl . '/sites/simpletest/browser_output/' . $html_output_filename;

        $target = file_get_contents($this->htmlOutputFile);
        if (false === $target) {
            throw new \LogicException('Missing output file');
        }
        $decoded = json_decode($target) ?? [];
        $decoded[] = [
            'class' => $this::class,
            'name' => $this->getName(true),
            'uri' => $uri,
            'backtrace' => debug_backtrace(\DEBUG_BACKTRACE_IGNORE_ARGS),
            'response_url' => $this->getSession()->getCurrentUrl(),
            'artifact_number' => $this->htmlOutputCounter - 1,
        ];

        file_put_contents(
             $this->htmlOutputFile,
             data: json_encode($decoded),
        );
    }
}
