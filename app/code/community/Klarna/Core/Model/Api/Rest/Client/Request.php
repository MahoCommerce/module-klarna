<?php

/**
 * SPDX-FileCopyrightText: 2015-2020 Klarna Bank AB (publ)
 * SPDX-FileCopyrightText: 2026 Maho <https://mahocommerce.com>
 * SPDX-License-Identifier: Apache-2.0
 * @package Klarna_Core
 */

/**
 * Used for building a request to send to the API
 *
 * @method Klarna_Core_Model_Api_Rest_Client_Request setResponseType($string)
 * @method string getResponseType()
 * @method Klarna_Core_Model_Api_Rest_Client_Request setMethod($string)
 * @method string getMethod()
 * @method Klarna_Core_Model_Api_Rest_Client_Request setDefaultErrorMessage($string)
 * @method string getDefaultErrorMessage()
 * @method Klarna_Core_Model_Api_Rest_Client_Request setUrl($array)
 * @method array getUrl()
 * @method Klarna_Core_Model_Api_Rest_Client_Request setIdField($string)
 * @method string getIdField()
 * @method string getValidatorMethod()
 * @method array getIds()
 * @method Klarna_Core_Model_Api_Rest_Client_Request setDefaultParamFormat($string)
 * @method Klarna_Core_Model_Api_Rest_Client_Request setPostJson($boolean)
 * @method boolean getPostJson()
 * @method Klarna_Core_Model_Api_Rest_Client_Request setRequestTimeout($int)
 * @method int getRequestTimeout()
 * @method Klarna_Core_Model_Api_Rest_Client_Request setFollowLocationHeader($bool)
 * @method bool getFollowLocationHeader()
 */
class Klarna_Core_Model_Api_Rest_Client_Request extends Varien_Object
{
    /**
     * Single item class name response
     *
     * @var string
     */
    public const RESPONSE_TYPE_SINGLE = 'klarna_core/api_rest_client_response';

    /**
     * Single item class name response
     *
     * @var string
     */
    public const RESPONSE_TYPE_RAW = 'raw';

    /**
     * Request parameter format array
     *
     * @var string
     */
    public const REQUEST_PARAMS_FORMAT_TYPE_ARRAY = 'ARRAY';

    /**
     * Request parameter format json
     *
     * @var string
     */
    public const REQUEST_PARAMS_FORMAT_TYPE_JSON = 'JSON';

    /**
     * Cache group Tag
     */
    public const CACHE_GROUP = 'klarna_api';

    /**
     * Build the default values for the object
     */
    #[\Override]
    protected function _construct()
    {
        $this->setData(
            [
                'response_type'          => self::RESPONSE_TYPE_SINGLE,
                'method'                 => Klarna_Core_Model_Api_Rest_Client::REQUEST_METHOD_GET,
                'default_error_message'  => 'Error: unable to find object in api',
                'url'                    => [],
                'id_field'               => null,
                'ids'                    => [],
                'cache_lifetime'         => null,
                'post_json'              => true,
                'follow_location_header' => false,
            ],
        );
    }

    /**
     * Set the expected IDs in the response.
     *
     * Currently, the API does not return results for IDs that do not exist. This allows error checking to see if a
     * a response for a ID was not returned.
     *
     * @param mixed $id
     *
     * @return $this
     */
    public function setIds($id)
    {
        if (!is_array($id)) {
            $id = [$id];
        }

        $this->setData('ids', $id);

        return $this;
    }

    /**
     * Set data for sending
     *
     * @param array       $params
     * @param bool|string $type
     *
     * @return $this
     */
    public function setParams($params, $type = false)
    {
        if (!is_array($params) || empty($params)) {
            return $this;
        }

        if (!$type || !is_string($type)) {
            $type = $this->getMethod() ?: 'global';
        }

        $this->_data['params'][$type] = $params;

        $this->_hasDataChanges = true;

        return $this;
    }

    /**
     * Get data to be sent
     *
     * @param bool|string|array $type
     * @param string            $format
     *
     * @return array|mixed|string
     */
    public function getParams($type = false, $format = null)
    {
        if (is_array($type)) {
            $data = [];
            foreach ($type as $_type) {
                $_params = $this->getParams($_type, self::REQUEST_PARAMS_FORMAT_TYPE_ARRAY);
                $data    = array_merge($data, $_params);
            }
        } else {
            if (!$type || !is_string($type)) {
                $type = 'global';
            }

            if (isset($this->_data['params'][$type])) {
                $data = $this->_data['params'][$type];
            } else {
                $data = [];
            }
        }

        if (null === $format) {
            $format = $this->getDefaultParamFormat();
        }

        switch ($format) {
            case self::REQUEST_PARAMS_FORMAT_TYPE_JSON:
                return json_encode($data);
            case self::REQUEST_PARAMS_FORMAT_TYPE_ARRAY:
            default:
                if (is_array($data)) {
                    return $data;
                }
                return [$data];
        }
    }

    /**
     * Get the request action name
     *
     * @param string $delimiter
     * @param bool   $allowNumeric
     *
     * @return string
     */
    public function getFullActionName($delimiter = '_', $allowNumeric = false)
    {
        $actionPath = $allowNumeric
            ? $this->getUrl()
            : array_filter(
                $this->getUrl(),
                fn($v) => !is_numeric($v),
            );
        $actionName = implode($delimiter, $actionPath);

        return $actionName;
    }

    /**
     * Get default format to get the data in
     *
     * @return mixed|string
     */
    public function getDefaultParamFormat()
    {
        $format = $this->getData('default_param_format');

        return $format ?? $this->getPostJson()
            ? self::REQUEST_PARAMS_FORMAT_TYPE_JSON : self::REQUEST_PARAMS_FORMAT_TYPE_ARRAY;
    }
}
