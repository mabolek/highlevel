<?php

declare(strict_types=1);

namespace Mabolek\Highlevel\Instruction\Callback;

class RestRequestCallback
{
    public function __construct(
        protected string|\Closure $url,
        protected string|\Closure $httpMethod,
        protected array|\Closure $headers,
        protected string|\Closure $requestData,
        protected int|\Closure $successResponse,
        protected string|\Closure $responseValue,
        protected string|\Closure $responseError
    ) {}

    public function __invoke(string $value): string
    {
        // TODO: Implement __invoke() method.
    }
}