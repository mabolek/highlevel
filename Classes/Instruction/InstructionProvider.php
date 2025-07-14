<?php

declare(strict_types=1);


namespace Mabolek\Highlevel\Instruction;


use TYPO3\CMS\Core\SingletonInterface;

final class InstructionProvider implements SingletonInterface
{
    private const BY_ID_KEY = '_byId';

    private array $instructions = [];

    public function addInstruction(InstructionInterface $instruction): void
    {
        if (key_exists($instruction->getIdentifier(), $this->instructions['_byId'] ?? [])) {
            return;
        }

        $this->instructions[get_class($instruction)][] = $instruction;

        $this->instructions[self::BY_ID_KEY][$instruction->getIdentifier()] = $instruction;
    }

    public function getInstructionsFor(string $fqcn): array
    {
        return $this->instructions[$fqcn] ?? [];
    }

    public function getByIdentifier(string $identifier): ?InstructionInterface
    {
        return $this->instructions[self::BY_ID_KEY][$identifier] ?? null;
    }
}