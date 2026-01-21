<?php

declare(strict_types=1);

namespace Mabolek\Highlevel\Instruction\Callback;

/**
 * A callback that encodes a string to base64.
 */
class Base64EncodeCallback extends GenericStringToStringCallback
{
    public function __invoke(string $string): string
    {
        $this->setInvokedArguments([$string]);

        return base64_encode($string);
    }
}
