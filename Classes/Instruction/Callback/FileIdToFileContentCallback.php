<?php

declare(strict_types=1);

namespace Mabolek\Highlevel\Instruction\Callback;

use TYPO3\CMS\Core\Resource\FileRepository;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Callback for instructions that receives a file’s UID and returns the file's content.
 */
class FileIdToFileContentCallback extends GenericStringToStringCallback
{
    public function __invoke(string $string): string
    {
        $this->setInvokedArguments([$string]);

        return GeneralUtility::makeInstance(FileRepository::class)
            ->findByUid((int)$string)
            ->getContents();
    }
}
