<?php

use Mabolek\Highlevel\Instruction\FieldButton\FieldButtonInstruction;
use Mabolek\Highlevel\Instruction\InstructionProvider;
use TYPO3\CMS\Core\Utility\GeneralUtility;

(static function (): void {
    $instructionProvider = GeneralUtility::makeInstance(InstructionProvider::class);
    foreach ($instructionProvider->getInstructionsFor(FieldButtonInstruction::class) as $instance) {
        $GLOBALS['TCA'][$instance->getTable()]['columns'][$instance->getTargetField()]['config']['fieldControl'][
            $instance->getIdentifier()
        ] = [
            'renderType' => $instance->getIdentifier(),
        ];
    }
})();
