<?php

declare(strict_types=1);

namespace Mabolek\Highlevel\Instruction\Callback;

/**
 * A generic callback class for callbacks that receives nothing and returns an array of integers.
 */
class GenericToArrayOfIntegersCallback extends GenericToArrayCallback
{
    /**
     * @return int[]
     */
    public function __invoke(): array
    {
        return [];
    }
}
