<?php

/**
 * SPDX-FileCopyrightText: 2015-2020 Klarna Bank AB (publ)
 * SPDX-FileCopyrightText: 2026 Maho <https://mahocommerce.com>
 * SPDX-License-Identifier: Apache-2.0
 * @package Klarna_Core
 */

declare(strict_types=1);

use Symfony\Component\HttpClient\HttpClient;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * Klarna REST api client
 *
 * Uses Symfony HttpClient for transport (Zend_Http_Client was dropped in Maho). The public
 * request() contract and the klarna_core_rest_* events are preserved for the API layer built on
 * top of this class (Klarna_Core_Model_Api_Rest_Client_Abstract and its subclasses).
 *
 * @method Klarna_Core_Model_Api_Rest_Client setRequest(Klarna_Core_Model_Api_Rest_Client_Request $request)
 * @method Klarna_Core_Model_Api_Rest_Client_Request getRequest()
 * @method Klarna_Core_Model_Api_Rest_Client setResponseType($string)
 * @method string getResponseType()
 * @method Klarna_Core_Model_Api_Rest_Client setConfig(Varien_Object $config)
 * @method Varien_Object getConfig()
 * @method Klarna_Core_Model_Api_Rest_Client setLogFileName($string)
 * @method string getLogFileName()
 * @method Klarna_Core_Model_Api_Rest_Client setDebug(bool $flag)
 * @method Klarna_Core_Model_Api_Rest_Client setMethod($string)
 * @method string getMethod()
 * @method Klarna_Core_Model_Api_Rest_Client setAuthUsername($string)
 * @method Klarna_Core_Model_Api_Rest_Client setAuthPassword($string)
 * @method Klarna_Core_Model_Api_Rest_Client setBaseUrl($string)
 */
class Klarna_Core_Model_Api_Rest_Client extends Varien_Object
{
    /**
     * Request method used for get
     */
    public const REQUEST_METHOD_GET = 'GET';

    /**
     * Request method used for post
     */
    public const REQUEST_METHOD_POST = 'POST';

    /**
     * Request method for delete
     */
    public const REQUEST_METHOD_DELETE = 'DELETE';

    /**
     * Request method used for patch
     */
    public const REQUEST_METHOD_PATCH = 'PATCH';

    /**
     * Response type for RAW data
     */
    public const RAW_RESPONSE_TYPE = 'raw';

    /**
     * JSON encoding type string
     */
    public const ENC_JSON = 'application/json';

    /**
     * Current open client connection
     */
    protected ?HttpClientInterface $_client = null;

    /**
     * Default request object type
     *
     * @var string
     */
    protected $_requestObject = 'klarna_core/api_rest_client_request';

    /**
     * Response of last request
     *
     * @var Varien_Object|mixed
     */
    protected $_response = null;

    /**
     * Response object model from type
     *
     * @var Varien_Object
     */
    protected $_responseObject = null;

    /**
     * Default values for the request configuration.
     *
     * @var array
     */
    protected $_requestConfig = [
        'maxredirects' => 5,
        'useragent'    => 'Magento_Rest_Client',
        'timeout'      => 30,
    ];

    /**
     * Init connection client
     *
     * @return $this
     */
    #[\Override]
    protected function _construct()
    {
        /** @var Mage_Core_Model_Config $config */
        $config      = Mage::getConfig();
        $version     = $config->getModuleConfig('Klarna_Core')->version;
        $mageVersion = Mage::getVersion();
        $mageEdition = 'Community';

        $versionStringObject = new Varien_Object(
            [
                'version_string' => "Klarna_Core_v{$version}",
            ],
        );
        Mage::dispatchEvent(
            'klarna_core_client_user_agent_string',
            [
                'version_string_object' => $versionStringObject,
            ],
        );
        $this->setRequestConfig('useragent', $versionStringObject->getVersionString() . " (Magento {$mageEdition} {$mageVersion})");

        return $this;
    }

    /**
     * Get rest client auth username
     *
     * @return string
     */
    public function getAuthUsername()
    {
        if (!$this->hasData('auth_username')) {
            $this->setAuthUsername($this->getConfig()->getAuthUsername());
        }

        return $this->getData('auth_username');
    }

    /**
     * Get rest client auth password
     *
     * @return string
     */
    public function getAuthPassword()
    {
        if (!$this->hasData('auth_password')) {
            $this->setAuthPassword($this->getConfig()->getAuthPassword());
        }

        return $this->getData('auth_password');
    }

    /**
     * Get base url
     *
     * @return string
     */
    public function getBaseUrl()
    {
        if (!$this->hasData('base_url')) {
            $this->setBaseUrl($this->getConfig()->getBaseUrl());
        }

        return $this->getData('base_url');
    }

    /**
     * Get debug setting
     *
     * @return bool
     */
    public function getDebug()
    {
        if (!$this->hasData('debug')) {
            $this->setDebug((bool) $this->getConfig()->getDebug());
        }

        return (bool) $this->getData('debug');
    }

    /**
     * Load the connection client, configured with the default headers and basic auth.
     */
    public function getClient(): HttpClientInterface
    {
        if ($this->_client === null) {
            $options = [
                'headers' => [
                    'Accept-Encoding' => 'gzip,deflate',
                    'Accept'          => 'application/json',
                    'Content-Type'    => 'application/json',
                ],
                'timeout'       => (float) $this->getRequestConfig('timeout'),
                'max_redirects' => (int) $this->getRequestConfig('maxredirects'),
            ];

            if ($this->getRequestConfig('useragent')) {
                $options['headers']['User-Agent'] = $this->getRequestConfig('useragent');
            }

            if ($this->getAuthUsername()) {
                $options['auth_basic'] = [$this->getAuthUsername(), (string) $this->getAuthPassword()];
            }

            $this->_client = HttpClient::create($options);
        }

        return $this->_client;
    }

    /**
     * Reset client connection
     *
     * @return $this
     */
    public function resetClient()
    {
        $this->_client = null;

        return $this;
    }

    /**
     * Convert response into response object
     *
     * @throws Klarna_Core_Model_Api_Exception
     * @return mixed
     */
    protected function _getResponse()
    {
        if ($this->_response === null) {
            $responseArray = [];

            $response = $this->getLastResponse();

            if (self::RAW_RESPONSE_TYPE === $this->getResponseType()) {
                $this->_response = ($response instanceof Klarna_Core_Model_Api_Rest_Client_Httpresponse)
                    ? $response->getBody()
                    : $response;

                return $this->_response;
            }

            if ($response instanceof Klarna_Core_Model_Api_Rest_Client_Httpresponse) {
                try {
                    $_responseArray = Mage::helper('core')->jsonDecode($response->getBody());
                    if ($_responseArray) {
                        $responseArray = $_responseArray;
                    }
                } catch (Exception) {
                }
            }

            $this->_response = $this->_getResponseObject()
                ->setRequest($this->getData('request'))
                ->setResponseObject($response)
                ->setIsSuccessful($response instanceof Klarna_Core_Model_Api_Rest_Client_Httpresponse && $response->isSuccessful())
                ->setResponse($responseArray);
        }

        return $this->_response;
    }

    /**
     * Get the response type object
     *
     * @throws Klarna_Core_Model_Api_Exception
     * @return Klarna_Core_Model_Api_Rest_Client_Response|Varien_Object
     */
    protected function _getResponseObject()
    {
        if ($this->_responseObject === null) {
            if (self::RAW_RESPONSE_TYPE == $this->getResponseType()) {
                throw new Klarna_Core_Model_Api_Exception('No response object available for raw response type.');
            }

            $responseModel = Mage::getModel($this->getData('response_type'));

            if (!$responseModel) {
                throw new Klarna_Core_Model_Api_Exception('Invalid response type.');
            }

            $this->_responseObject = $responseModel;
        }

        return $this->_responseObject;
    }

    /**
     * Do a request by method
     *
     * @param string $method
     * @param array|string $url
     *
     * @return mixed
     */
    protected function _requestByMethod($method, $url)
    {
        $this->_methodRequest($method, $url);

        $this->_response = null;

        $response = $this->_getResponse();
        $request  = $this->getRequest();

        Mage::dispatchEvent("klarna_core_rest_{$request->getFullActionName()}_{$method}_after", $this->_getEventData());
        Mage::dispatchEvent("klarna_core_rest_request_{$method}_after", $this->_getEventData());
        Mage::dispatchEvent('klarna_core_rest_request_after', $this->_getEventData());

        return $response;
    }

    /**
     * Perform the request
     *
     * @param string $method
     * @param array|string $url
     *
     * @return Klarna_Core_Model_Api_Rest_Client_Httpresponse
     */
    protected function _methodRequest($method, $url)
    {
        /** @var Klarna_Core_Model_Api_Rest_Client_Request $request */
        $request  = $this->getRequest();
        $fullUrl  = $this->_resolveUrl($url);
        $options  = $this->_buildRequestOptions($request, $method);

        $this->setData('last_raw_request', "{$method} {$fullUrl}\n" . ($options['body'] ?? ($options['json'] ?? '')));

        try {
            Mage::dispatchEvent("klarna_core_rest_{$request->getFullActionName()}_{$method}_before", $this->_getEventData());
            Mage::dispatchEvent("klarna_core_rest_request_{$method}_before", $this->_getEventData());
            Mage::dispatchEvent('klarna_core_rest_request_before', $this->_getEventData());

            $response = $this->_execute($method, $fullUrl, $options);

            if ($request->getFollowLocationHeader()
                && $response->isSuccessful()
                && ($location = $response->getHeader('Location'))
            ) {
                $this->_debug('Following Location header', Mage::LOG_DEBUG);
                $response = $this->_execute(self::REQUEST_METHOD_GET, $this->_resolveUrl($location), []);
            }
        } catch (Exception $e) {
            $this->_debug($e, Mage::LOG_CRITICAL);
            $code = $e->getCode();

            if (5 !== (int) floor((int) $code / 100)) {
                $code = 500;
            }

            $response = new Klarna_Core_Model_Api_Rest_Client_Httpresponse((int) $code, [], $e->getMessage());
        }

        $this->_debug($response, Mage::LOG_DEBUG);

        $this->setData('last_response', $response);

        return $response;
    }

    /**
     * Issue a single HTTP request and normalise the result into a response value object.
     *
     * @param array<string, mixed> $options
     */
    protected function _execute(string $method, string $url, array $options): Klarna_Core_Model_Api_Rest_Client_Httpresponse
    {
        $sfResponse = $this->getClient()->request($method, $url, $options);

        // Passing false to getContent()/getHeaders() prevents Symfony from throwing on 4xx/5xx —
        // the Klarna API layer inspects status codes and error payloads itself.
        return new Klarna_Core_Model_Api_Rest_Client_Httpresponse(
            $sfResponse->getStatusCode(),
            $sfResponse->getHeaders(false),
            $sfResponse->getContent(false),
        );
    }

    /**
     * Build the Symfony HttpClient per-request options from the request object.
     *
     * @return array<string, mixed>
     */
    protected function _buildRequestOptions(Klarna_Core_Model_Api_Rest_Client_Request $request, string $method): array
    {
        $options = [];

        $getParams    = $request->getParams(self::REQUEST_METHOD_GET, Klarna_Core_Model_Api_Rest_Client_Request::REQUEST_PARAMS_FORMAT_TYPE_ARRAY);
        $bodyParams   = $request->getParams([self::REQUEST_METHOD_POST, self::REQUEST_METHOD_PATCH], Klarna_Core_Model_Api_Rest_Client_Request::REQUEST_PARAMS_FORMAT_TYPE_ARRAY);
        $globalParams = $request->getParams(false, Klarna_Core_Model_Api_Rest_Client_Request::REQUEST_PARAMS_FORMAT_TYPE_ARRAY);

        if ($method === self::REQUEST_METHOD_GET) {
            $query = array_merge($getParams, $globalParams);
            if (!empty($query)) {
                $options['query'] = $query;
            }
        } else {
            if (!empty($getParams)) {
                $options['query'] = $getParams;
            }

            $payload = array_merge($bodyParams, $globalParams);
            if (!empty($payload)) {
                if ($request->getPostJson()) {
                    $options['json'] = $payload;
                } else {
                    $options['body'] = $payload;
                }
            }
        }

        $timeout = $request->getRequestTimeout();
        if ($timeout !== null) {
            $options['timeout'] = (float) $timeout;
        }

        return $options;
    }

    /**
     * Resolve a request URL. Accepts an absolute URL string or an array of path segments to be
     * appended to the configured base URL.
     *
     * @param array|string $url
     */
    protected function _resolveUrl($url): string
    {
        if (is_string($url) && parse_url($url, PHP_URL_SCHEME) !== null) {
            return (string) preg_replace('/\s+/', '', $url);
        }

        if (!is_array($url)) {
            $url = [$url];
        }

        array_unshift($url, rtrim((string) $this->getBaseUrl(), '/'));

        return (string) preg_replace('/\s+/', '', implode('/', $url));
    }

    /**
     * Perform a request
     *
     * @throws Klarna_Core_Model_Api_Exception
     * @return Klarna_Core_Model_Api_Rest_Client_Response|string
     */
    public function request(Klarna_Core_Model_Api_Rest_Client_Request $request)
    {
        $this->setData('request', $request);
        $this->setData('response_type', $request->getData('response_type'));

        $method = strtoupper(trim((string) $request->getMethod()));

        $this->setMethod($method);

        return $this->_requestByMethod($this->getMethod(), $request->getUrl());
    }

    /**
     * Get a new request object for building a request
     *
     * @return Klarna_Core_Model_Api_Rest_Client_Request
     */
    public function getNewRequestObject()
    {
        $request = Mage::getModel($this->_requestObject);

        if (!$request instanceof Klarna_Core_Model_Api_Rest_Client_Request) {
            throw new Klarna_Core_Model_Api_Exception('Cannot instantiate request object.');
        }

        return $request;
    }

    /**
     * The request configuration used for the request.
     *
     * @param string $name
     *
     * @return array|mixed|null
     */
    public function getRequestConfig($name = null)
    {
        if ($name === null) {
            return $this->_getRequestConfig();
        }

        return $this->_requestConfig[$name] ?? null;
    }

    /**
     * Prepares the request configuration array to be used in the HTTP request.
     *
     * @return array
     */
    protected function _getRequestConfig()
    {
        $_requestConfigNew = [];
        foreach ($this->_requestConfig as $name => $value) {
            if (!empty($value)) {
                if (is_array($value)) {
                    $value = implode(',', $value);
                }

                $_requestConfigNew[$name] = $value;
            }
        }

        return $_requestConfigNew;
    }

    /**
     * Set the configuration for sending a request.
     *
     * @param array|string $name
     * @param mixed        $value
     *
     * @return $this
     */
    public function setRequestConfig($name, $value = null)
    {
        if (is_array($name)) {
            foreach ($name as $k => $v) {
                $this->_requestConfig[$k] = $v;
            }
        } else {
            $this->_requestConfig[$name] = $value;
        }

        return $this;
    }

    /**
     * Get the last response from the API
     *
     * @return Klarna_Core_Model_Api_Rest_Client_Httpresponse|false
     */
    public function getLastResponse()
    {
        $lastResponse = $this->getData('last_response');

        if ($lastResponse instanceof Klarna_Core_Model_Api_Rest_Client_Httpresponse) {
            return $lastResponse;
        }

        return false;
    }

    /**
     * Log debug messages
     *
     * @param mixed $message
     * @param mixed $level
     */
    protected function _debug($message, $level): void
    {
        if (Mage::LOG_DEBUG != $level || $this->getDebug()) {
            Mage::log($this->_rawDebugMessage($message), $level, $this->getLogFileName(), true);
        }
    }

    /**
     * Raw debug message for logging
     *
     * @param mixed $message
     *
     * @return string
     */
    protected function _rawDebugMessage($message)
    {
        if ($message instanceof Klarna_Core_Model_Api_Rest_Client_Httpresponse) {
            $message = $message->asString();
        } elseif ($message instanceof Exception) {
            $message = $message->__toString();
        }

        return (string) $message;
    }

    /**
     * Get array of objects transferred to default events processing
     *
     * @return array
     */
    protected function _getEventData()
    {
        $eventData = [
            'request'     => $this->getRequest(),
            'raw_request' => $this->getData('last_raw_request'),
        ];

        $lastResponse = $this->getLastResponse();
        if ($lastResponse instanceof Klarna_Core_Model_Api_Rest_Client_Httpresponse) {
            $eventData = array_merge(
                $eventData,
                [
                    'response'     => $this->_getResponse(),
                    'raw_response' => $lastResponse->asString(),
                ],
            );
        }

        return $eventData;
    }
}
