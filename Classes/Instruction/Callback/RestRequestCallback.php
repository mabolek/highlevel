<?php

declare(strict_types=1);

namespace Mabolek\Highlevel\Instruction\Callback;

use GuzzleHttp\RequestOptions;
use Mabolek\Highlevel\Instruction\InstructionRuntimeException;
use TYPO3\CMS\Core\Http\RequestFactory;
use TYPO3\CMS\Core\Utility\ArrayUtility;
use TYPO3\CMS\Core\Utility\Exception\MissingArrayPathException;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * A callback class that makes a REST request, sending the provided string to a given URL and returning a string from
 * the response.
 *
 * During invocation, $this->getInvokedValue contains the value passed to the callback.
 */
class RestRequestCallback extends GenericStringToStringCallback
{

    /**
     * @param string|array|\Closure|GenericToStringCallback $url The URL to send the request to.
     * @param string|\Closure|GenericToStringCallback $requestStructure The structure of the JSON data to send in the request. A dot-delimited string that is expanded to a multidimensional associative array. The invoked value is inserted as the value of the leaf key.
     * @param string|\Closure|GenericToStringCallback $responseValuePath A dot-delimited path to the value to return from the JSON response.
     * @param string|\Closure|GenericToStringCallback $httpMethod The HTTP method to use.
     * @param array|\Closure():array|GenericToStringCallback $headers The headers to send with the request as an associative array as used with Guzzle.
     * @param int|array|\Closure():int[]|GenericToArrayOfIntegersCallback $successResponse The HTTP status codes that indicate success. Used to determine whether to return $responseValue or trigger an exception with the $responseErrorMessage.
     * @param string|\Closure|GenericToStringCallback $errorMessagePath A dot-delimited path to the error message to return from the response. If the path does not exist or the string is empty, a generic message is used.
     * @param array|\Closure|GenericToArrayCallback $additionalRequestStructure A multidimensional array of additional data to send in the request. Is merged with the array generated from $requestStructure.
     */
    public function __construct(
        protected string|array|\Closure|GenericToStringCallback $url,
        protected string|\Closure|GenericToStringCallback $requestStructure = '',
        protected string|\Closure|GenericToStringCallback $responseValuePath = '',
        protected string|\Closure|GenericToStringCallback $httpMethod = 'GET',
        protected array|\Closure|GenericToStringCallback $headers = [],
        protected int|array|\Closure|GenericToArrayOfIntegersCallback $successResponse = [200, 201, 202],
        protected string|\Closure|GenericToStringCallback $errorMessagePath = '',
        protected array|\Closure|GenericToArrayCallback $additionalRequestStructure = [],
    ) {}

    public function __invoke(string $value): string
    {
        parent::__invoke($value);

        $httpMethod = strtoupper($this->getHttpMethod());

        $requestOptions = [];

        $requestStructure = $this->getRequestStructure();
        $additionalRequestStructure = $this->getAdditionalRequestStructure();

        // Only send JSON data if the request method is GET or the request structure is provided.
        if ($httpMethod !== 'GET' || !($requestStructure === '' || $additionalRequestStructure === [])) {
            $requestDataArray = null;
            foreach (array_reverse(explode('.', $requestStructure)) as $key) {
                $requestDataArray = [$key => $requestDataArray ?? $value];
            }

            ArrayUtility::mergeRecursiveWithOverrule($requestDataArray, $additionalRequestStructure);

            $requestOptions[RequestOptions::JSON] = $requestDataArray;
        }

        $requestOptions[RequestOptions::HEADERS] = array_merge(
            $GLOBALS['TYPO3_CONF_VARS']['HTTP']['headers'] ?? [],
            $this->getHeaders()
        );

        $response = GeneralUtility::makeInstance(RequestFactory::class)->request(
            $this->getUrl(),
            $httpMethod,
            $requestOptions
        );

        if (in_array($response->getStatusCode(), $this->getSuccessResponses(), true)) {
            $responseValuePath = $this->getResponseValuePath();
            $jsonResponse = json_decode($response->getBody()->getContents(), true, 512, JSON_THROW_ON_ERROR);

            if ($responseValuePath === '') {
                return $jsonResponse;
            }

            try {
                return ArrayUtility::getValueByPath($jsonResponse, $this->getResponseValuePath(),'.');
            } catch (\RuntimeException|MissingArrayPathException $e) {
                return '';
            }
        }

        $errorMessagePath = $this->getErrorMessagePath();

        if ($errorMessagePath === '') {
            $responseErrorMessage = 'Request failed. (Status code: ' . $response->getStatusCode() . ')';
        } else {
            try {
                $responseErrorMessage = ArrayUtility::getValueByPath(
                    json_decode((string)$response->getBody(), true),
                    $errorMessagePath,
                    '.'
                );
            } catch (MissingArrayPathException $e) {
                $responseErrorMessage = 'Request failed. No error message found at error message path. (Status code: ' . $response->getStatusCode() . ')';
            }

            if ($responseErrorMessage === '') {
                $responseErrorMessage = 'Request failed with empty error message. (Status code: ' . $response->getStatusCode() . ')';
            }
        }

        throw new InstructionRuntimeException(
            $responseErrorMessage,
            1752746698551
        );
    }

    public function getHeaders(): array
    {
        $headers = $this->headers;

        array_walk_recursive($headers, function (&$value) {
            $value = $this->forgivingBind($value)();
        });

        return $headers;
    }

    public function getUrl(): string
    {
        $urlParts = $this->url;

        if (!is_array($urlParts)) {
            $urlParts = [$urlParts];
        }

        $url = '';

        foreach ($urlParts as $urlPart) {
            $url .= $this->forgivingBind($urlPart)();
        }

        return $url;
    }

    public function getHttpMethod(): string
    {
        return $this->forgivingBind($this->httpMethod)();
    }

    public function getRequestStructure(): string
    {
        return $this->forgivingBind($this->requestStructure)();
    }

    public function getErrorMessagePath(): string
    {
        return $this->forgivingBind($this->errorMessagePath)();
    }

    public function getResponseValuePath(): string
    {
        return $this->forgivingBind($this->responseValuePath)();
    }

    public function getSuccessResponses(): array
    {
        return (array)$this->forgivingBind($this->successResponse)();
    }

    public function getAdditionalRequestStructure(): array
    {
        if (!is_array($this->additionalRequestStructure)) {
            return $this->forgivingBind($this->additionalRequestStructure)();
        }

        $additionalRequestStructure = $this->additionalRequestStructure;

        array_walk_recursive($additionalRequestStructure, function (&$value) {
            $value = $this->forgivingBind($value)();
        });

        return $additionalRequestStructure;
    }
}
