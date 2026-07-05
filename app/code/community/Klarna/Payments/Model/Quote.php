<?php

/**
 * SPDX-FileCopyrightText: 2015-2020 Klarna Bank AB (publ)
 * SPDX-FileCopyrightText: 2026 Maho <https://mahocommerce.com>
 * SPDX-License-Identifier: Apache-2.0
 * @package Klarna_Payments
 */

/**
 * Klarna quote to associate a Klarna quote with a Magento quote
 *
 * @method string getSessionId()
 * @method string getClientToken()
 * @method string getAuthorizationToken()
 * @method int getIsActive()
 * @method int getQuoteId()
 * @method Klarna_Payments_Model_Quote setSessionId(string $value)
 * @method Klarna_Payments_Model_Quote setClientToken(string $value)
 * @method Klarna_Payments_Model_Quote setAuthorizationToken(string $value)
 * @method Klarna_Payments_Model_Quote setIsActive(int $value)
 * @method Klarna_Payments_Model_Quote setQuoteId(int $value)
 */
class Klarna_Payments_Model_Quote extends Mage_Core_Model_Abstract
{
    /**
     * Init
     */
    #[\Override]
    protected function _construct()
    {
        $this->_init('klarna_payments/quote');
    }

    /**
     * Load by session id
     *
     * @param string $sessionId
     *
     * @return Klarna_Payments_Model_Quote
     */
    public function loadBySessionId($sessionId)
    {
        return $this->load($sessionId, 'session_id');
    }

    /**
     * Load active Klarna quote object by quote
     *
     * @param string                 $paymentMethod
     * @return Klarna_Payments_Model_Quote
     */
    public function loadActiveByQuote(Mage_Sales_Model_Quote $quote, $paymentMethod = 'klarna_payments')
    {
        /** @var Klarna_Payments_Model_Resource_Quote $resource */
        $resource = $this->_getResource();
        $resource->loadActive($this, (int) $quote->getId(), $paymentMethod);
        $this->_afterLoad();

        return $this;
    }

    /**
     * Load active Klarna quote object by quote id
     *
     * @param int $quoteId
     * @param string $paymentMethod
     * @return $this
     */
    public function loadActiveByQuoteId($quoteId, $paymentMethod = 'klarna_payments')
    {
        /** @var Klarna_Payments_Model_Resource_Quote $resource */
        $resource = $this->_getResource();
        $resource->loadActive($this, $quoteId, $paymentMethod);
        $this->_afterLoad();

        return $this;
    }

    /**
     * @param array|null $values
     * @return Klarna_Payments_Model_Quote
     */
    public function setPaymentMethodCategories($values)
    {
        if (is_null($values)) {
            $values = [];
        }
        $json = json_encode($values);
        $this->setData('payment_method_categories', $json);

        return $this;
    }

    /**
     * @return array
     */
    public function getPaymentMethodCategories()
    {
        $result = $this->getData('payment_method_categories');
        if (empty($result)) {
            return [];
        }

        return json_decode((string) $this->getData('payment_method_categories'), true);
    }
}
