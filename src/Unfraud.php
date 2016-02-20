<?php

namespace Unfraud;

use Unfraud\WebService\Client;

/**
 * Class Unfraud
 * @package Unfraud
 *
 *
 * This class provides a client API for accessing Unfraud Score
 * and Dashboard.
 *
 * ## Usage ##
 *
 * The constructor takes your Unfraud user ID and license key. The object
 * returned is immutable. To build up a request, call the `->with*()` methods.
 * Each of these returns a new object (a clone of the original) with the
 * additional data. These can be chained together:
 *
 * ```
 * $client = new Unfraud('API_KEY');
 *
 * $score = $client->withBilling(['ip_address' => '1.1.1.1'])
 *                 ->with(['user_email' => 'demo@unfraud.com',
 *                          'user_name' => 'Name',
 *                          ``` ])
 *                 ->score();
 * ```
 *
 * If the request fails, an exception is thrown.
 */
class Unfraud
{
    const VERSION = 'unfraud-custom_v1.0.0';
    const LOGIN_URL = 'https://www.unfraud.com/srv/login.php';
    const ANALYTICS_URL = 'http://www.unfraud.com/unfraud_analytics/analytics.php';
    const BEA_URL = '//www.unfraud.com/bea/bea.js';

    private $client;
    private static $host = 'api.unfraud.com';
    private static $path_events = '/events';

    private $content;

    /**
     * @param string $apiKey Your Unfraud license key
     * @param array $options An array of options. Possible keys:
     *
     * * `host` - The host to use when connecting to the web service.
     * * `connectTimeout` - The connect timeout to use for the request.
     * * `timeout` - The timeout to use for the request.
     */
    public function __construct(
        $apiKey,
        $options = []
    ) {
        if (!isset($options['host'])) {
            $options['host'] = self::$host;
        }
        $this->client = new Client($apiKey,self::VERSION,$options);
    }

    /////////// EVENTS

    /**
     * This returns a `Unfraud` object with the array to be sent to the web
     * service set to `$values`. Existing values will be replaced.
     *
     * @param $values
     * @return Unfraud
     */
    public function with($values)
    {
        $new = clone $this;
        $new->content = $values;

        return $new;
    }


    /**
     * This returns a `Unfraud` object with the `billing` array set to
     * `$values`. Existing `billing` data will be replaced.
     *
     * @param $values
     * @return Unfraud
     */
    public function withBilling($values)
    {
        return $this->add('billing_address', $values);
    }

    /**
     * This returns a `Unfraud` object with the `shipping` array set to
     * `$values`. Existing `shipping` data will be replaced.
     *
     * @param $values
     * @return Unfraud
     */
    public function withShipping($values)
    {
        return $this->add('shipping_address', $values);
    }



    /**
     * This returns a `Unfraud` object with `$values` added to the shopping
     * cart array.
     *
     * @param $values
     * @return Unfraud
     */
    public function withShoppingCartItem(array $values)
    {
        $new = clone $this;
        if (!isset($new->content['items'])) {
            $new->content['items'] = [];
        }
        array_push($new->content['items'], $values);

        return $new;
    }

    /**
     * This method performs a minFraud Score lookup using the request data in
     * the current object and returns a model object for minFraud Score.
     *
     * @return Unfraud\Model\Score minFraud Score model object.
     * @throws InvalidInputException when the request has missing or invalid
     * data.
     * @throws InvalidRequestException when the request is invalid for some
     * other reason, e.g., invalid JSON in the POST.
     * @throws HttpException when an unexpected HTTP error occurs.
     * @throws WebServiceException when some other error occurs. This also
     * serves as the base class for the above exceptions.
     */
    public function score()
    {
        return $this->post('Score');
    }



    /**
     * @param $service $service The name of the service to use.
     * @return mixed The model class for the service.
     * @throws InvalidInputException when the request has missing or invalid
     * data.
     * @throws InvalidRequestException when the request is invalid for some
     * other reason, e.g., invalid JSON in the POST.
     * @throws HttpException when an unexpected HTTP error occurs.
     * @throws WebServiceException when some other error occurs. This also
     * serves as the base class for the above exceptions.
     */
    private function post($service)
    {
        $url = self::$path_events;
        $class = "Unfraud\\Model\\" . $service;

        return new $class(
            $this->client->post($service, $url, $this->content)
        );
    }


    /**
     * @param string $className The name of the class (but not the namespace)
     * @param string $key The key in the transaction array to set
     * @param array $values The values to validate
     * @return Unfraud
     * @throws InvalidInputException when $values does not validate
     */
    private function add($key, $values)
    {
        $new = clone $this;
        $new->content[$key] = $values;

        return $new;
    }

    ////////// TRACKING
    /**
     * @return string
     */
    public function getTracking()
    {
        if($this->client->getApiKey())
        {
            return '<script type="text/javascript">'.
            'var bea_api_id = \''.$this->client->getApiKey().'\';'.
            'var bea_session_id= \''.$this->client->getSessionId().'\';'.
            '</script>'.
            '<script type="text/javascript" src="'.self::BEA_URL.'"></script>';
        }
    }

    ///////// DASHBOARD
    /**
     * @param $email
     * @param $password
     * @return string
     */
    public function getDashboardUrl($email,$password)
    {
        if ($this->client->getApiKey()) {
            return self::LOGIN_URL . '?e=' . sha1($email) . '&p=' . sha1(md5($password . "asdz!!3")) . '&t=' . $this->client->getApiKey();
        }
    }


    /**
     * @param $email
     * @param $password
     * @return string
     */
    public function getDashboard($email,$password)
    {
        if ($this->client->getApiKey()) {
            return '<iframe src="' . $this->getDashboardUrl($email, $password) . '" width="100%" height="1000" frameborder="0"></iframe>';
        }
    }
}
