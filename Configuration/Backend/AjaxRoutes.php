<?php

use Mabolek\Highlevel\Controller\Ajax\AjaxController;
use Mabolek\Highlevel\Instruction\ImplementationFacet\AjaxControllerFacet;
use Mabolek\Highlevel\Instruction\InstructionRegistry;

$routes = [];

foreach ((new InstructionRegistry())->getInstructionsByClassOrInterface(AjaxControllerFacet::class) as $instance) {
    $routes['highlevel_' . $instance->getIdentifier()] = [
        'path' => '/highlevel/' . strtolower($instance->getIdentifier()),
        'target' => AjaxController::class
    ];
}

return $routes;
