<?php

declare(strict_types=1);

namespace Mabolek\Highlevel\Controller\Ajax;

use Mabolek\Highlevel\Instruction\InstructionProvider;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;
use TYPO3\CMS\Backend\Attribute\AsController;
use TYPO3\CMS\Core\Http\JsonResponse;
use TYPO3\CMS\Core\Utility\ArrayUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;

#[AsController]
class FieldButtonController
{
    public function __construct(
        private readonly LoggerInterface $logger,
    ) {}

    public function __invoke(ServerRequestInterface $request): ResponseInterface
    {
        try {
            $requestData = json_decode((string)$request->getBody(), true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException $e) {
            return new JsonResponse(
                [ 'success' => false, 'message' => $e->getMessage() ],
                400
            );
        }

        [$table, $uid, $field] = explode('][', trim($requestData['inputName'] ?? '', '[]'));

        $instruction = GeneralUtility::makeInstance(InstructionProvider::class)
            ->getByIdentifier($requestData['identifier']);

        $value = ($instruction)($requestData['value']);

        return new JsonResponse(
            [
                'success' => true,//$response->getStatusCode() < 300,
                'value' => $value,
                'error' => implode(' ', ArrayUtility::flatten($data['errors'] ?? [])),
            ],
            200
        );
    }
}