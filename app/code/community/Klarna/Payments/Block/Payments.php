<?php

/**
 * SPDX-FileCopyrightText: 2015-2020 Klarna Bank AB (publ)
 * SPDX-FileCopyrightText: 2026 Maho <https://mahocommerce.com>
 * SPDX-License-Identifier: Apache-2.0
 * @package Klarna_Payments
 */

/**
 * Klarna payments block
 */
class Klarna_Payments_Block_Payments extends Mage_Core_Block_Template
{
    /** @var array Set of payment method categories returned from the API */
    protected $_paymentMethodCategories = [];

    #[\Override]
    protected function _construct()
    {
        $session = Mage::getSingleton('checkout/session');
        $this->_paymentMethodCategories = $session->getData($this->getMethodCode() . '_categories_block_payment');

        $this->setCacheLifetime(null);
        parent::_construct();
    }

    /**
     * @return string|false
     */
    public function getRegionsUS()
    {
        $lookupTable = [];
        $regions = Mage::getResourceModel('directory/region_collection')->addCountryFilter('US');
        foreach ($regions as $region) {
            $lookupTable[$region->getRegionId()] = $region->getCode();
        }
        return json_encode($lookupTable);
    }

    /**
     * @return array
     */
    public function getPaymentMethodCategories()
    {
        return $this->_paymentMethodCategories;
    }

    /**
     * Get Klarna quote details
     *
     * @return Klarna_Payments_Model_Quote|Varien_Object
     */
    public function getKlarnaQuote()
    {
        try {
            return Mage::helper('klarna_payments/checkout')->getKlarnaQuote();
        } catch (Exception $e) {
            Mage::logException($e);
        }

        return new Varien_Object();
    }

    /**
     * Check if an authorization token has been set
     *
     * @return bool
     */
    public function hasAuthorizationToken()
    {
        return (bool) $this->getAuthorizationToken();
    }

    /**
     * Get the authorization token
     *
     * @return string
     */
    public function getAuthorizationToken()
    {
        return $this->getKlarnaQuote()->getAuthorizationToken();
    }

    /**
     * Get client token for checkout session
     *
     * @return string
     */
    public function getClientToken()
    {
        return $this->getKlarnaQuote()->getClientToken();
    }

    /**
     * If the Klarna pre-screen is enabled
     *
     * @return bool
     */
    public function getPreScreenEnabled()
    {
        return Mage::getStoreConfigFlag('payment/klarna_payments/pre_screen');
    }

    public function getFunctionName(): string
    {
        return 'Payments';
    }

    public function getMethodCode(): string
    {
        return 'klarna_payments';
    }

    /**
     * get is one step checkout
     *
     * @return bool
     */
    public function isOneStepCheckout()
    {
        return Mage::helper('klarna_payments/checkout')->isOneStepCheckout();
    }
}
