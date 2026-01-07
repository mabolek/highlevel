<?php

use Mabolek\Highlevel\Instruction\ImplementationFacet\TcaOverridesFacet;
use Mabolek\Highlevel\Instruction\InstructionRegistry;

(static function (): void {
    foreach ((new InstructionRegistry())->getInstructionsByClassOrInterface(TcaOverridesFacet::class) as $instance) {
        $instance->tcaOverridesFacet();
    }
})();
