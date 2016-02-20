<?php

namespace Unfraud\WebService\Http;

/**
 * Class RequestFactory
 * @package Unfraud\WebService\Http
 * @internal
 */
class RequestFactory
{
    public function __construct()
    {
    }

    /**
     * @param $url
     * @param $options
     * @return CurlRequest
     */
    public function request($url, $options)
    {
        return new CurlRequest($url, $options);
    }
}
