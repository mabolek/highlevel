<?php

declare(strict_types=1);

namespace Mabolek\Highlevel\Instruction\Callback;

use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;

/**
 * Fetches a configuration value from an environment variable or the extension configuration.
 *
 * Environment variable names are the instruction prefixed with "HIGHLEVEL_EXTENSIONKEY_" where "EXTENSIONKEY" is the
 * key of the extension the instruction belongs to. All uppercased.
 */
class ConfigurationValueCallback extends GenericToStringCallback
{
    public function __construct(
        private string $key,
        private string $defaultValue = ''
    ) {}

    public function __invoke(): string
    {
         return getenv()['HIGHLEVEL_' . strtoupper($this->getInstruction()->getPackage()->getPackageKey() . '_' . $this->key)]
            ?? (new ExtensionConfiguration())->get($this->getInstruction()->getPackage()->getPackageKey())[$this->key]
            ?? $this->defaultValue;
    }

}
