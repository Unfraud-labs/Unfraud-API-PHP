<?php

namespace Unfraud\WebService\Http;

use Unfraud\Exception\HttpException;

/**
 * This class is for internal use only. Semantic versioning does not not apply.
 * @package Unfraud\WebService\Http
 * @internal
 */
class CurlRequest implements Request
{
    private $url;
    private $options;

    /**
     * @param $url
     * @param $options
     */
    public function __construct($url, $options)
    {
        $this->url = $url;
        $this->options = $options;
    }

    /**
     * @param $body
     * @return array
     */
    public function post($body)
    {
        $curl = $this->createCurl();

        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $body);

        return $this->execute($curl);
    }

    public function get()
    {
        $curl = $this->createCurl();

        curl_setopt($curl, CURLOPT_HTTPGET, true);

        return $this->execute($curl);
    }

    /**
     * @return resource
     */
    private function createCurl()
    {
        $curl = curl_init($this->url);

        $opts[CURLOPT_FOLLOWLOCATION] = false;
        $opts[CURLOPT_SSL_VERIFYPEER] = false;
        $opts[CURLOPT_RETURNTRANSFER] = true;
        $opts[CURLOPT_VERBOSE] = $this->options['debug'];


        $opts[CURLOPT_HTTPHEADER] = $this->options['headers'];

        $connectTimeout = $this->options['connectTimeout'];
        if (defined('CURLOPT_CONNECTTIMEOUT_MS')) {
            $opts[CURLOPT_CONNECTTIMEOUT_MS] = ceil($connectTimeout * 1000);
        } else {
            $opts[CURLOPT_CONNECTTIMEOUT] = ceil($connectTimeout);
        }

        $timeout = $this->options['timeout'];
        if (defined('CURLOPT_TIMEOUT_MS')) {
            $opts[CURLOPT_TIMEOUT_MS] = ceil($timeout * 1000);
        } else {
            $opts[CURLOPT_TIMEOUT] = ceil($timeout);
        }

        curl_setopt_array($curl, $opts);
        return $curl;
    }

    private function execute($curl)
    {
        $body = curl_exec($curl);
        if ($errno = curl_errno($curl)) {
            $error_message = curl_error($curl);

            throw new HttpException(
                "cURL error ({$errno}): {$error_message}",
                0,
                $this->url
            );
        }

        $statusCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $contentType = curl_getinfo($curl, CURLINFO_CONTENT_TYPE);
        curl_close($curl);

        return array($statusCode, $contentType, $body);
    }
}
