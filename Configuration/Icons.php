<?php

use Mabolek\Highlevel\Instruction\ImplementationFacet\IconsFacet;
use Mabolek\Highlevel\Instruction\InstructionRegistry;

$icons = [];

foreach ((new InstructionRegistry())->getInstructionsByClassOrInterface(IconsFacet::class) as $instance) {
    $icons = array_merge($icons, $instance->iconsFacet());
}

return $icons;
