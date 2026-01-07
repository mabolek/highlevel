<?php

declare(strict_types=1);

namespace Mabolek\Highlevel\Instruction\ImplementationFacet;

/**
 * Implementation facet to be executed in ext_localconf.php.
 */
interface ExtLocalconfFacet
{
    public function extLocalconfFacet(): void;
}
