<?php

use Mabolek\Highlevel\FormEngine\FieldControl\FieldButtonControl;
use Mabolek\Highlevel\Instruction\FieldButton\FieldButtonInstruction;
use Mabolek\Highlevel\Instruction\InstructionProvider;
use Symfony\Component\Finder\Finder;
use TYPO3\CMS\Core\Package\PackageManager;
use TYPO3\CMS\Core\Utility\GeneralUtility;

(static function ():void {
    $scopedReturnRequire = static function (string $filename) {
        return require $filename;
    };

    $instructionProvider = GeneralUtility::makeInstance(InstructionProvider::class);

    $activePackages = GeneralUtility::makeInstance(PackageManager::class)->getActivePackages();

    foreach ($activePackages as $package) {
        try {
            $finder = Finder::create()->files()->sortByName()->depth(0)->name('*.php')->in($package->getPackagePath() . 'Configuration/HighLevelInstructions');
        } catch (\InvalidArgumentException) {
            // No such directory in this package
            continue;
        }

        foreach ($finder as $fileInfo) {
            $instructionProvider->addInstruction($scopedReturnRequire($fileInfo->getPathname()));
        }
    }

    foreach ($instructionProvider->getInstructionsFor(FieldButtonInstruction::class) as $instruction) {
        $nextRegistrySlot = max(array_keys($GLOBALS['TYPO3_CONF_VARS']['SYS']['formEngine']['nodeRegistry'])) + 1;
        $GLOBALS['TYPO3_CONF_VARS']['SYS']['formEngine']['nodeRegistry'][$nextRegistrySlot] = [
            'nodeName' => $instruction->getIdentifier(),
            'priority' => 30,
            'class' => FieldButtonControl::class,
        ];
    }
})();
