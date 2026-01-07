<?php

declare(strict_types=1);

namespace Mabolek\Highlevel\Instruction\Callback;

/**
 * A generic callback class for callbacks that receives nothing and returns an array.
 */
class GenericToArrayCallback extends GenericCallback
{
    /**
     * @return array
     */
    public function __invoke(): array
    {
        return [];
    }
}
