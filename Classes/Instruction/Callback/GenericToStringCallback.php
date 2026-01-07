<?php

declare(strict_types=1);

namespace Mabolek\Highlevel\Instruction\Callback;

use Mabolek\Highlevel\Instruction\Callback\GenericCallback;

/**
 * A generic callback class for instructions that receives nothing and returns a string.
 */
class GenericToStringCallback extends GenericCallback
{
    public function __invoke(): string
    {
        return '';
    }
}
