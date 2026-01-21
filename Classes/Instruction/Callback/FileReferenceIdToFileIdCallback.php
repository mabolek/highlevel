<?php

declare(strict_types=1);

namespace Mabolek\Highlevel\Instruction\Callback;

use TYPO3\CMS\Core\Resource\ResourceFactory;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * A callback that returns the File UID (sys_file) for a given File Reference ID (sys_file_reference).
 */
class FileReferenceIdToFileIdCallback extends GenericStringToStringCallback
{
    public function __invoke(string $string): string
    {
        $this->setInvokedArguments([$string]);

        return (string)GeneralUtility::makeInstance(ResourceFactory::class)
            ->getFileReferenceObject((int)$string)
            ->getOriginalFile()
            ->getUid();
    }
}
