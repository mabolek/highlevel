<?php

declare(strict_types=1);

namespace Mabolek\Highlevel\Controller\Ajax;

use Mabolek\Highlevel\Instruction\AbstractInstruction;
use Mabolek\Highlevel\Instruction\ImplementationFacet\AjaxControllerFacet;
use Mabolek\Highlevel\Instruction\InstructionRegistry;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;
use TYPO3\CMS\Backend\Attribute\AsController;
use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\Http\JsonResponse;

#[AsController]
class AjaxController
{
    public function __construct(
        private readonly LoggerInterface $logger,
    ) {}

    public function __invoke(ServerRequestInterface $request): JsonResponse
    {
        if (
            preg_match('#\/([a-z0-9_]+)$#', $request->getUri()->getPath(), $matches) === false
            || !isset($matches[1])
        ) {
            return $this->generateAndLogErrorResponse(
                'Invalid request URI format "' . $request->getUri()->getPath() . '".',
                $request,
                null,
                null,
                400
            );
        }

        $assumedIdentifier = $matches[1];

        $instruction = (new InstructionRegistry())->getInstruction($assumedIdentifier);

        if ($instruction === null) {
             return $this->generateAndLogErrorResponse(
                'No instruction with identifier "' . $assumedIdentifier . '" found.',
                $request,
                null,
                null,
                404
             );
        }

        if (!($instruction instanceof AjaxControllerFacet)) {
            return $this->generateAndLogErrorResponse(
                'Instruction with identifier "' . $assumedIdentifier . '" does not implement AjaxControllerFacet.',
                $request,
                $instruction,
                null,
                400
            );
        }

        try {
            $jsonData = json_decode($request->getBody()->getContents(), true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException $e) {
            return $this->generateAndLogErrorResponse(
                'Invalid JSON received.',
                $request,
                $instruction,
                $e,
                400
            );
        }

        try {
            $jsonResponseData = $instruction->ajaxControllerFacet($jsonData);
        } catch (\Exception|\Error $e) {
            if (Environment::getContext()->isDevelopment()) {
                throw $e;
            }

            return $this->generateAndLogErrorResponse(
                $e->getMessage() . ' (' . get_class($e) . ')',
                $request,
                $instruction,
                $e,
                500
            );
        }

        $this->logger->debug(
            'Response from instruction',
            [
                'request' => $request,
                'instruction' => $instruction,
                'response' => $jsonResponseData,
            ]
        );

        return new JsonResponse([
            'success' => true,
            'data' => $jsonResponseData,
        ]);
    }

    /**
     * @param string $message
     * @param ServerRequestInterface $request
     * @param int $httpStatusCode
     * @param AbstractInstruction|null $instruction
     * @param \Exception|\Error|null $exception
     * @return JsonResponse
     */
    protected function generateAndLogErrorResponse(
        string $message,
        ServerRequestInterface $request,
        ?AbstractInstruction $instruction,
        \Exception|\Error|null $exception,
        int $httpStatusCode,
    ): JsonResponse {
        $this->logger->error(
            $message,
            [
                'request' => $request,
                'instruction' => $instruction,
                'exception' => $exception,
            ]
        );

        return new JsonResponse(
            [
                'success' => false,
                'message' => $message,
            ],
            $httpStatusCode
        );
    }
}
