<?php

declare(strict_types=1);

namespace Mabolek\Highlevel\Instruction\FieldButton;

use Mabolek\Highlevel\Instruction\InstructionInterface;
use TYPO3\CMS\Core\Imaging\Icon;

final class FieldButtonInstruction implements InstructionInterface
{
    public function __construct(
        protected string $identifier,
        protected string|\Closure $label,
        protected string|Icon $icon,
        protected string|\Closure $table,
        protected string|\Closure $sourceField,
        protected string|null $targetField,
        protected string|\Closure $callback,
    ) {}

    public function __invoke(string $value): string
    {
        $callback = $this->callback;

        if (is_string($callback)) {
            $callback = new $callback();
        }

        return ($callback)($value, $this);
    }

    public function getIcon(): Icon|string
    {
        return $this->icon;
    }

    public function getIdentifier(): string
    {
        return $this->identifier;
    }

    public function getLabel(): \Closure|string
    {
        return $this->label;
    }

    public function getSourceField(): \Closure|string
    {
        return $this->sourceField;
    }

    public function getTable(): \Closure|string
    {
        return $this->table;
    }

    public function getTargetField(): ?string
    {
        return $this->targetField;
    }
}