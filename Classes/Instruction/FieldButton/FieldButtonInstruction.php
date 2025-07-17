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
        protected string|array|\Closure $table,
        protected string|array|\Closure $sourceField,
        protected string|array|null $targetField,
        protected string|self|\Closure $callback,
    ) {}

    public function __invoke(string $value): string
    {
        $callback = $this->callback;

        if (is_string($callback)) {
            $callback = new $callback();
        }

        return ($callback->bindTo($this))($value);
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
        return $this->label instanceof \Closure ? $this->label->bindTo($this) : $this->label;
    }

    public function getSourceField(): \Closure|array|string
    {
        return $this->sourceField instanceof \Closure ? $this->sourceField->bindTo($this) : $this->sourceField;
    }

    public function getTable(): \Closure|array|string
    {
        return $this->table instanceof \Closure ? $this->table->bindTo($this) : $this->table;
    }

    public function getTargetField(): \Closure|array|string
    {
        return $this->targetField instanceof \Closure ? $this->targetField->bindTo($this) : $this->targetField
            ?? $this->getSourceField();
    }
}
