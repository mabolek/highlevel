<?php

declare(strict_types=1);

namespace Mabolek\Highlevel\Instruction;

use Mabolek\Highlevel\Instruction\Callback\GenericCallback;
use TYPO3\CMS\Core\Package\Package;

abstract class AbstractInstruction extends GenericCallback
{
    /**
     * A lowercase alphanumeric identifier of the instruction consisting of the extension key with underscores removed
     * and the instruction file name separated by an underscore. If both parts are the same, the identifier is only the
     * first part.
     */
    readonly private string $identifier;

    /**
     * The extension/package the instruction belongs to.
     */
    readonly private Package $package;

    public function setIdentifier(string $identifier): void
    {
        $this->identifier = preg_replace('/[^a-z0-9_]/', '', strtolower($identifier));
    }

    public function getIdentifier(): string
    {
        return $this->identifier;
    }

    public function setInstruction(AbstractInstruction $instruction): void
    {
        throw new InstructionRuntimeException(
            'Cannot set instruction on instruction',
            1767267757001
        );
    }

    public function getInstruction(): AbstractInstruction
    {
        return $this;
    }

    public function setPackage(Package $package): void
    {
        $this->package = $package;
    }

    public function getPackage(): Package
    {
        return $this->package;
    }
}
