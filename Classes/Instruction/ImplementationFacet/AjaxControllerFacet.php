<?php

declare(strict_types=1);

namespace Mabolek\Highlevel\Instruction\ImplementationFacet;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Core\Http\JsonResponse;

/**
 * Implementation facet to be executed in AjaxController.
 */
interface AjaxControllerFacet
{
    /**
     * Receives data from JSON and returns an array of JSON responses.
     */
    public function ajaxControllerFacet(array $data): array;
}
