<?php

namespace Unfraud\WebService\Http;

/**
 * Interface Request
 * @package Unfraud\WebService\Http
 * @internal
 */
interface Request
{
    /**
     * @param $url
     * @param $options
     */
    public function __construct($url, $options);

    /**
     * @param $body
     * @return mixed
     */
    public function post($body);

    /**
     * @return mixed
     */
    public function get();
}
