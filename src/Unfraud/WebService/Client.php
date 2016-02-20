<?php

namespace Unfraud\WebService;

use Unfraud\Exception\HttpException;
use Unfraud\Exception\InvalidRequestException;
use Unfraud\Exception\InvalidInputException;
use Unfraud\Exception\WebServiceException;
use Unfraud\WebService\Http\RequestFactory;

/**
 * This class is not intended to be used directly by an end-user of a
 * Unfraud web service. Please use the appropriate client API for the service
 * that you are using.
 * @package Unfraud\WebService
 * @internal
 */
class Client
{
    const VERSION = '1.0.0';

    private $host = 'api.unfraud.com';
    private $httpRequestFactory;
    private $timeout;
    private $connectTimeout;
    private $version;
    private $debug;
    private $apiKey;
    private $session_id;

    /**
     * @param int $userId Your Unfraud user ID
     * @param string $licenseKey Your Unfraud license key
     * @param array $options An array of options. Possible keys:
     *
     * * `host` - The host to use when connecting to the web service.
     * * `apiKey` - The api_key to use in the request.
     * * `version` - The Unfraud plugin version to use in the request.
     * * `debug` - Debug mode to use when connecting to the web service.
     * * `connectTimeout` - The connect timeout to use for the request.
     * * `timeout` - The timeout to use for the request.
     */
    public function __construct($apiKey,$version,$options = array()
    ) {
        $this->httpRequestFactory = isset($options['httpRequestFactory'])
            ? $options['httpRequestFactory']
            : new RequestFactory();

        $this->apiKey = $apiKey;
        $this->session_id = session_id();
        if (isset($version)) {
            $this->version = $version;
        }
        if (isset($options['host'])) {
            $this->host = $options['host'];
        }
        if (isset($options['debug'])) {
            $this->debug = $options['debug'];
        }
        if (isset($options['connectTimeout'])) {
            $this->connectTimeout = $options['connectTimeout'];
        }
        if (isset($options['timeout'])) {
            $this->timeout = $options['timeout'];
        }
    }

    /**
     * @return mixed
     */
    public function getApiKey(){
        return $this->apiKey;
    }

    /**
     * @return mixed
     */
    public function getSessionId(){
        return $this->session_id;
    }

    /**
     * @param string $service name of the service querying
     * @param string $path the URI path to use
     * @param array $input the data to be posted as JSON
     * @return array The decoded content of a successful response
     * @throws InvalidInputException when the request has missing or invalid
     * data.
     * @throws InvalidRequestException when the request is invalid for some
     * other reason, e.g., invalid JSON in the POST.
     * @throws HttpException when an unexpected HTTP error occurs.
     * @throws WebServiceException when some other error occurs. This also
     * serves as the base class for the above exceptions.
     */
    public function post($service, $path, $input)
    {
        $input["api_id"] = $this->apiKey;
        $input['unfraud_plugin'] = $this->version;
        $body = json_encode($input);
        if ($body === false) {
            throw new InvalidInputException(
                'Error encoding input as JSON: '
                . $this->jsonErrorDescription()
            );
        }

        $request = $this->createRequest(
            $path,
            array('Content-Type: application/json')
        );

        list($statusCode, $contentType, $body) = $request->post($body);
        return $this->handleResponse(
            $statusCode,
            $contentType,
            $body,
            $service,
            $path
        );
    }

    public function get($service, $path)
    {
        $request = $this->createRequest($path);

        list($statusCode, $contentType, $body) = $request->get();

        return $this->handleResponse(
            $statusCode,
            $contentType,
            $body,
            $service,
            $path
        );
    }


    private function createRequest($path, $headers = array())
    {
        array_push(
            $headers,
            'Accept: application/json'
        );

        return $this->httpRequestFactory->request(
            $this->urlFor($path),
            array(
                'headers' => $headers,
                'connectTimeout' => $this->connectTimeout,
                'debug' => $this->debug,
                'timeout' => $this->timeout,
            )
        );

    }

    /**
     * @param integer $statusCode the HTTP status code of the response
     * @param string $contentType the Content-Type of the response
     * @param string $body the response body
     * @param string $service the name of the service
     * @param string $path the path used in the request
     * @return array The decoded content of a successful response
     * @throws InvalidRequestException when the request is invalid for some
     * other reason, e.g., invalid JSON in the POST.
     * @throws HttpException when an unexpected HTTP error occurs.
     * @throws WebServiceException when some other error occurs. This also
     * serves as the base class for the above exceptions
     */
    private function handleResponse(
        $statusCode,
        $contentType,
        $body,
        $service,
        $path
    ) {
        if ($statusCode >= 400 && $statusCode <= 499) {
            $this->handle4xx($statusCode, $contentType, $body, $service, $path);
        } elseif ($statusCode >= 500) {
            $this->handle5xx($statusCode, $service, $path);
        } elseif ($statusCode != 200) {
            $this->handleUnexpectedStatus($statusCode, $service, $path);
        }
        return $this->handleSuccess($body, $service);
    }

    /**
     * @return string describing the JSON error
     */
    private function jsonErrorDescription()
    {
        $errno = json_last_error();
        switch ($errno) {
            case JSON_ERROR_DEPTH:
                return 'The maximum stack depth has been exceeded.';
            case JSON_ERROR_STATE_MISMATCH:
                return 'Invalid or malformed JSON.';
            case JSON_ERROR_CTRL_CHAR:
                return 'Control character error.';
            case JSON_ERROR_SYNTAX:
                return 'Syntax error.';
            case JSON_ERROR_UTF8:
                return 'Malformed UTF-8 characters.';
            default:
                return "Other JSON error ($errno).";
        }
    }

    /**
     * @param string $path The path to use in the URL
     * @return string The constructed URL
     */
    private function urlFor($path)
    {
        return 'https://' . $this->host . $path;
    }

    /**
     * @param int $statusCode The HTTP status code
     * @param string $contentType The response content-type
     * @param string $body The response body
     * @param string $service The service name
     * @param string $path The path used in the request
     * @throws HttpException
     * @throws InvalidRequestException
     */
    private function handle4xx(
        $statusCode,
        $contentType,
        $body,
        $service,
        $path
    ) {
        if (strlen($body) === 0) {
            throw new HttpException(
                "Received a $statusCode error for $service with no body",
                $statusCode,
                $this->urlFor($path)
            );
        }
        if (!strstr($contentType, 'json')) {
            throw new HttpException(
                "Received a $statusCode error for $service with " .
                "the following body: " . $body,
                $statusCode,
                $this->urlFor($path)
            );
        }

        $message = json_decode($body, true);
        if ($message === null) {
            throw new HttpException(
                "Received a $statusCode error for $service but could " .
                'not decode the response as JSON: '
                . $this->jsonErrorDescription() . ' Body: ' . $body,
                $statusCode,
                $this->urlFor($path)
            );
        }

        if (!isset($message['code']) || !isset($message['error'])) {
            throw new HttpException(
                'Error response contains JSON but it does not ' .
                'specify code or error keys: ' . $body,
                $statusCode,
                $this->urlFor($path)
            );
        }

        $this->handleWebServiceError(
            $message['error'],
            $message['code'],
            $statusCode,
            $path
        );
    }

    /**
     * @param string $message The error message from the web service
     * @param string $code The error code from the web service
     * @param int $statusCode The HTTP status code
     * @param string $path The path used in the request
     * @throws InvalidRequestException
     */
    private function handleWebServiceError(
        $message,
        $code,
        $statusCode,
        $path
    ) {
        switch ($code) {
            default:
                throw new InvalidRequestException(
                    $message,
                    $code,
                    $statusCode,
                    $this->urlFor($path)
                );
        }
    }

    /**
     * @param int $statusCode The HTTP status code
     * @param string $service The service name
     * @param string $path The URI path used in the request
     * @throws HttpException
     */
    private function handle5xx($statusCode, $service, $path)
    {
        throw new HttpException(
            "Received a server error ($statusCode) for $service",
            $statusCode,
            $this->urlFor($path)
        );
    }

    /**
     * @param int $statusCode The HTTP status code
     * @param string $service The service name
     * @param string $path The URI path used in the request
     * @throws HttpException
     */
    private function handleUnexpectedStatus($statusCode, $service, $path)
    {
        throw new HttpException(
            'Received an unexpected HTTP status ' .
            "($statusCode) for $service",
            $statusCode,
            $this->urlFor($path)
        );
    }

    /**
     * @param string $body The successful request body
     * @param string $service The service name
     * @return array The decoded request body
     * @throws WebServiceException if the request body cannot be decoded as
     * JSON
     */
    private function handleSuccess($body, $service)
    {
        if (strlen($body) == 0) {
            throw new WebServiceException(
                "Received a 200 response for $service but did not " .
                "receive a HTTP body."
            );
        }

        $decodedContent = json_decode($body, true);
        if ($decodedContent === null) {
            throw new WebServiceException(
                "Received a 200 response for $service but could " .
                'not decode the response as JSON: '
                . $this->jsonErrorDescription() . ' Body: ' . $body
            );
        }

        if (isset($decodedContent['error_message'])) {
            throw new WebServiceException(
                "Received a 200 response for $service but it " .
                'receive the error: '.$decodedContent['error_message']
            );
        }

        return $decodedContent;
    }

}
