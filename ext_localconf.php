<?php

use Mabolek\Highlevel\Instruction\ImplementationFacet\ExtLocalconfFacet;
use Mabolek\Highlevel\Instruction\InstructionLoader;
use Mabolek\Highlevel\Instruction\InstructionRegistry;
use TYPO3\CMS\Core\Package\PackageManager;
use TYPO3\CMS\Core\Utility\GeneralUtility;

(static function ():void {
    (new InstructionLoader(GeneralUtility::makeInstance(PackageManager::class)))->load();

    foreach (
        (new InstructionRegistry())->getInstructionsByClassOrInterface(ExtLocalconfFacet::class) as $instruction
    ) {
        $instruction->extLocalconfFacet();
    }
})();
