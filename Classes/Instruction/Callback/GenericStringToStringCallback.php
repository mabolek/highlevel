<?php

declare(strict_types=1);

namespace Mabolek\Highlevel\Instruction\Callback;

/**
 * A generic callback class for instructions that receives a string and returns a string.
 */
class GenericStringToStringCallback extends GenericCallback
{
    public function __invoke(string $string): string
    {
        $this->setInvokedArguments([$string]);

        return $string;
    }

    /**
     * Returns the value passed to the callback during invocation. Will fail if invoked before invocation.
     */
    public function getInvokedValue(): string
    {
        return $this->getInvokedArguments()[0];
    }
}
