<?php

/**
 * SPDX-FileCopyrightText: 2015-2020 Klarna Bank AB (publ)
 * SPDX-FileCopyrightText: 2026 Maho <https://mahocommerce.com>
 * SPDX-License-Identifier: Apache-2.0
 * @package Klarna_Payments
 */

/**
 * Klarna quote resource
 */
class Klarna_Payments_Model_Resource_Quote extends Mage_Core_Model_Resource_Db_Abstract
{
    /**
     * Init
     */
    public function _construct()
    {
        $this->_init('klarna_payments/quote', 'payments_quote_id');
    }

    /**
     * Load only active quote
     *
     * @param Klarna_Payments_Model_Quote $klarnaQuote
     * @param int                         $quoteId
     * @param string                      $paymentMethod
     *
     * @return Mage_Sales_Model_Resource_Quote
     */
    public function loadActive($klarnaQuote, $quoteId, $paymentMethod = 'klarna_payments')
    {
        $adapter = $this->_getReadAdapter();
        $select = $this->_getLoadSelect('quote_id', $quoteId, $klarnaQuote)
                       ->where('is_active = ?', 1)
                       ->where('payment_method = ?', $paymentMethod);

        $data = $adapter->fetchRow($select);
        if ($data) {
            $klarnaQuote->setData($data);
        }

        $this->_afterLoad($klarnaQuote);

        return $this;
    }
}
