<?php

declare(strict_types=1);

namespace dpi\EnhancedDrupalPhpunitResults;

use Drupal\Tests\UiHelperTrait;

/**
 * Use this class to include core UiHelperTrait and enhancements.
 *
 * This will allow you to follow further overrides from this library that
 * override functionality in core UiHelperTrait.
 *
 * Alternatively if your project also has overrides for UiHelperTrait,
 * then you can use EnhancedUiHelperTrait instead of this trait.
 */
trait CombinedEnhancedUiHelperTrait
{
    use UiHelperTrait;
    use EnhancedUiHelperTrait {
        EnhancedUiHelperTrait::htmlOutput insteadof UiHelperTrait;
    }
}
