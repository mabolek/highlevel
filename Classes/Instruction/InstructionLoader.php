<?php

declare(strict_types=1);


namespace Mabolek\Highlevel\Instruction;


use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;
use TYPO3\CMS\Core\Package\PackageInterface;
use TYPO3\CMS\Core\Package\PackageManager;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\StringUtility;

class InstructionLoader
{
    protected const INSTRUCTION_PATH = 'Configuration/Instruction';

    protected InstructionRegistry $instructionRegistry;

    public function __construct(
        protected readonly PackageManager $packageManager
    ) {}

    public function load(): void
    {
        $this->instructionRegistry = new InstructionRegistry();

        foreach ($this->packageManager->getActivePackages() as $package) {
            $files = $this->findInPackage($package) ?? [];

            /** @var SplFileInfo $file */
            foreach ($files as $file) {
                $instruction = $this->loadSingle($file);

                if ($instruction === null) {
                    continue;
                }

                $packageKey = strtolower(str_replace('_', '', $package->getPackageKey()));
                $fileName = strtolower($file->getFilenameWithoutExtension());

                if ($packageKey === $fileName) {
                    $instruction->setIdentifier($packageKey);
                } else {
                    $instruction->setIdentifier($package->getPackageKey() . '_' . $file->getFilenameWithoutExtension());
                }

                $instruction->setPackage($package);

                $this->instructionRegistry->register($instruction);
            }
        }
    }

    protected function loadSingle(SplFileInfo $file): ?AbstractInstruction
    {
        $scopedReturnRequire = static function (string $filename) {
            return require $filename;
        };

        return $scopedReturnRequire($file->getPathname());
    }

    protected function findInPackage(PackageInterface $package): ?Finder
    {
        $instructionPath = $package->getPackagePath() . self::INSTRUCTION_PATH;

        if (!file_exists($instructionPath)) {
            return null;
        }

        return (new Finder())->files()->ignoreUnreadableDirs()->depth(0)->name('*.php')->in($instructionPath);
    }
}
