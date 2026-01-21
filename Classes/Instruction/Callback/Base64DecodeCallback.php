<?php

declare(strict_types=1);

namespace Mabolek\Highlevel\Instruction\Callback;

/**
 * A callback that decodes a base64 encoded string.
 */
class Base64DecodeCallback extends GenericStringToStringCallback
{
    public function __invoke(string $string): string
    {
        $this->setInvokedArguments([$string]);

        return base64_decode($string);
    }
}
