<?php

/**
 * SPDX-FileCopyrightText: 2015-2020 Klarna Bank AB (publ)
 * SPDX-FileCopyrightText: 2026 Maho <https://mahocommerce.com>
 * SPDX-License-Identifier: Apache-2.0
 * @package Klarna_Core
 */

/**
 * Klarna core observer methods
 */
class Klarna_Core_Model_Observer
{
    /**
     * Generate item list for payment capture
     */
    #[\Maho\Config\Observer('sales_order_payment_capture', type: 'singleton', id: 'klarna_prepare_capture')]
    public function prepareCapture(Varien_Event_Observer $observer)
    {
        /** @var Mage_Sales_Model_Order_Payment $payment */
        $payment = $observer->getPayment();

        if ($payment->getMethodInstance() instanceof Klarna_Core_Model_Payment_Method_Abstract) {
            $payment->setInvoice($observer->getInvoice());
        }
    }
}
