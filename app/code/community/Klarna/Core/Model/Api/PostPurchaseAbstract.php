<?php

declare(strict_types=1);

/**
 * SPDX-FileCopyrightText: 2015-2020 Klarna Bank AB (publ)
 * SPDX-FileCopyrightText: 2026 Maho <https://mahocommerce.com>
 * SPDX-License-Identifier: Apache-2.0
 * @package Klarna_Core
 */
/**
 * Klarna api integration abstract
 *
 * @method Klarna_Core_Model_Api_PostPurchaseAbstract setStore(?Mage_Core_Model_Store $store)
 * @method Mage_Core_Model_Store getStore()
 * @method Klarna_Core_Model_Api_PostPurchaseAbstract setConfig(Varien_Object $config)
 * @method Varien_Object getConfig()
 */
class Klarna_Core_Model_Api_PostPurchaseAbstract extends Klarna_Core_Model_Api_ApiTypeAbstract implements Klarna_Core_Model_Api_PostPurchaseApiInterface
{
    /**
     * Acknowledge an order in order management
     *
     * @param string $orderId
     *
     * @return Klarna_Core_Model_Api_Response
     */
    #[\Override]
    public function acknowledgeOrder($orderId)
    {
        return new Klarna_Core_Model_Api_Response();
    }

    /**
     * Update merchant references for a Klarna order
     *
     * @param string $orderId
     * @param string $reference1
     * @param string $reference2
     *
     * @return Klarna_Core_Model_Api_Response
     */
    #[\Override]
    public function updateMerchantReferences($orderId, $reference1, $reference2 = null)
    {
        return new Klarna_Core_Model_Api_Response();
    }

    /**
     * Capture an amount on an order
     *
     * @param string                         $orderId
     * @param float                          $amount
     * @param Mage_Sales_Model_Order_Invoice $invoice
     *
     * @return Klarna_Core_Model_Api_Response
     */
    #[\Override]
    public function capture($orderId, $amount, $invoice = null)
    {
        return new Klarna_Core_Model_Api_Response();
    }

    /**
     * Refund for an order
     *
     * @param string                            $orderId
     * @param float                             $amount
     * @param Mage_Sales_Model_Order_Creditmemo $creditMemo
     *
     * @return Klarna_Core_Model_Api_Response
     */
    #[\Override]
    public function refund($orderId, $amount, $creditMemo = null)
    {
        return new Klarna_Core_Model_Api_Response();
    }

    /**
     * Cancel an order
     *
     * @param string $orderId
     *
     * @return Klarna_Core_Model_Api_Response
     */
    #[\Override]
    public function cancel($orderId)
    {
        return new Klarna_Core_Model_Api_Response();
    }

    /**
     * Release the authorization for an order
     *
     * @param string $orderId
     *
     * @return Klarna_Core_Model_Api_Response
     */
    #[\Override]
    public function release($orderId)
    {
        return new Klarna_Core_Model_Api_Response();
    }

    /**
     * Get order details from the api
     *
     * @param string $orderId
     *
     * @return Klarna_Core_Model_Api_Response
     */
    #[\Override]
    public function getOrder($orderId)
    {
        return new Klarna_Core_Model_Api_Response();
    }
}
