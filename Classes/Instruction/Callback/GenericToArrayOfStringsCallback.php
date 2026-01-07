<?php

declare(strict_types=1);

namespace Mabolek\Highlevel\Instruction\Callback;

/**
 * A generic callback class for callbacks that receives nothing and returns an array of strings.
 */
class GenericToArrayOfStringsCallback extends GenericCallback
{
    /**
     * @return string[]
     */
    public function __invoke(): array
    {
        return [];
    }
}
