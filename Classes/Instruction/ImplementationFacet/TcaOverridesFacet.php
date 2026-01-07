<?php

declare(strict_types=1);

namespace Mabolek\Highlevel\Instruction\ImplementationFacet;

/**
 * Implementation facet to be executed in Configuration/TCA/Overrides.
 */
interface TcaOverridesFacet
{
    public function tcaOverridesFacet(): void;
}
