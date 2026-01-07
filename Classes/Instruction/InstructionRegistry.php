<?php

declare(strict_types=1);

namespace Mabolek\Highlevel\Instruction;

final class InstructionRegistry
{
    private const BY_ID_KEY = '_byId';

    private static array $instructions = [];

    public function register(AbstractInstruction $instruction): void
    {
        if (key_exists($instruction->getIdentifier(), self::$instructions[self::BY_ID_KEY] ?? [])) {
            return;
        }

        self::$instructions[self::BY_ID_KEY][$instruction->getIdentifier()] = $instruction;

        foreach ([get_class($instruction), ...class_parents($instruction), ...class_implements($instruction)] as $fqcn) {
            self::$instructions[$fqcn][] = $instruction;
        }
    }

    public function getInstructionsByClassOrInterface(string $fqcn): array
    {
        return self::$instructions[$fqcn] ?? [];
    }

    public function hasInstruction(string $identifier): bool
    {
        return self::getInstruction($identifier) !== null;
    }

    public function getInstruction(string $identifier): ?AbstractInstruction
    {
        return self::$instructions[self::BY_ID_KEY][$identifier] ?? null;
    }

    public function getAllInstructions(): array
    {
        return self::$instructions[self::BY_ID_KEY];
    }
}
