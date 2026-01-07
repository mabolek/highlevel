<?php

declare(strict_types=1);

namespace Mabolek\Highlevel\Instruction\ImplementationFacet;

use TYPO3\CMS\Core\Imaging\Icon;

/**
 * Implementation facet for code to be executed in Configuration/Icons.php.
 */
interface IconsFacet
{
    /**
     * @return array of icon registrations as defined in https://docs.typo3.org/permalink/t3coreapi:icon-registration
     */
    public function iconsFacet(): array;
}
