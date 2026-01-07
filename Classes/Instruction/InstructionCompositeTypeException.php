<?php

declare(strict_types=1);

namespace Mabolek\Highlevel\Instruction;

/**
 * An exception thrown when an argument or return value of a callback is not a valid type in the context of the
 * instruction.
 */
class InstructionCompositeTypeException extends \InvalidArgumentException
{}
