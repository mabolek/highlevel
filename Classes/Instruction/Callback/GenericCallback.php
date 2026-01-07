<?php

declare(strict_types=1);

namespace Mabolek\Highlevel\Instruction\Callback;

use Mabolek\Highlevel\Instruction\AbstractInstruction;
use Mabolek\Highlevel\Instruction\InstructionCompositeTypeException;

/**
 * The most basic callable class for instructions. All callbacks should extend this class.
 */
class GenericCallback
{
    /**
     * The originating instruction of this callback.
     */
    private ?AbstractInstruction $instruction;

    private array $invokedArguments;

    /**
     * Returns the originating instruction of this callback.
     */
    public function getInstruction(): AbstractInstruction
    {
        return $this->instruction;
    }

    /**
     * Sets the originating instruction of this callback.
     */
    protected function setInstruction(AbstractInstruction $instruction): void
    {
        $this->instruction = $instruction;
    }

    public function getInvokedArguments(): array
    {
        return $this->invokedArguments;
    }

    public function setInvokedArguments(array $invokedArguments): void
    {
        $this->invokedArguments = $invokedArguments;
    }

    /**
     * Returns the given closure bound to this callback and with $this as the context.
     *
     * @param string|callable|\Closure|GenericCallback $closure The user-defined closure to bind and set the context for.
     * @return callable
     */
    protected function strictBind(string|callable|\Closure|GenericCallback $closure): callable
    {
        if ($closure instanceof GenericCallback) {
            $closure->setInstruction($this->getInstruction());

            return $closure;
        }

        if (
            is_string($closure)
            && is_callable($closure)
            && !(new \ReflectionFunction($closure))->isUserDefined()
        ) {
            // Wrap strings that could be non-user-defined function names in an anonymous function, so they won't be
            // executed as function names. Only user-defined function names are allowed.
            $closure = fn(...$arguments) => $closure;
        }

        return \Closure::fromCallable($closure)->bindTo($this, $this);
    }

    /**
     * Like self::strictBind(), but also allows scalars and arrays by wrapping them in closures that return the scalar value.
     *
     * @param mixed $candidate A user-defined closure to bind and set the context for.
     * @return callable
     */
    protected function forgivingBind(mixed $candidate): callable
    {
        $candidate = $this->forgivingInvokableObjectFromClassName($candidate) ?? $candidate;

        if (
            (
                is_scalar($candidate)
                && !is_string($candidate)
            )
            || (
                (
                    is_string($candidate)
                    || is_array($candidate)
                )
                && !is_callable($candidate)
            )
            || (
                is_string($candidate)
                && is_callable($candidate)
                && !(new \ReflectionFunction($candidate))->isUserDefined()
            )
        ) {
            // Non-callable values and non-user-defined function names are considered scalars.
            $candidate = fn(...$arguments) => $candidate;
        }

        return $this->strictBind($candidate);
    }

    /**
     * Takes a fully qualified class name and returns it as an invokable object if possible, or null otherwise.
     */
    protected function invokableObjectFromClassName(string $fqcn): ?callable
    {
        if (!class_exists($fqcn)) {
            return null;
        }

        $closure = new $fqcn();

        if (!is_callable($closure)) {
            return null;
        }

        return $closure;
    }

    /**
     * Like self::invokableObjectFromClassName(), but also allows other input than string, in which case null is
     * returned.
     */
    protected function forgivingInvokableObjectFromClassName(mixed $fqcn): ?callable
    {
        if (!is_string($fqcn)) {
            return null;
        }

        return $this->invokableObjectFromClassName($fqcn);
    }

    protected function initializeCallable(string|callable $callableOrInvokableFqcn): ?callable
    {
        $callable = $this->forgivingInvokableObjectFromClassName($callableOrInvokableFqcn) ?? $callableOrInvokableFqcn;

        if (
            is_string($callable)
            && is_callable($callable)
            && !(new \ReflectionFunction($callable))->isUserDefined()
        ) {
            // Non-callable values and non-user-defined function names are considered scalars.
            $callable = fn(...$arguments) => $callable;
        }

        if ($callable instanceof GenericCallback) {
            $callable->setInstruction($this->getInstruction());
        }

        return $callable;
    }
}
