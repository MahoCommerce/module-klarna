<?php

/**
 * SPDX-FileCopyrightText: 2015-2020 Klarna Bank AB (publ)
 * SPDX-FileCopyrightText: 2026 Maho <https://mahocommerce.com>
 * SPDX-License-Identifier: Apache-2.0
 * @package Klarna_Core
 */

/**
 * Klarna order used to associate a Klarna order with a Magento order
 *
 * @method Klarna_Core_Model_Order setKlarnaOrderId()
 * @method int getKlarnaOrderId()
 * @method Klarna_Core_Model_Order setSessionId()
 * @method string getSessionId()
 * @method Klarna_Core_Model_Order setReservationId()
 * @method string getReservationId()
 * @method Klarna_Core_Model_Order setOrderId()
 * @method int getOrderId()
 * @method Klarna_Core_Model_Order setIsAcknowledged(int $value)
 * @method int getIsAcknowledged()
 */
class Klarna_Core_Model_Order extends Mage_Core_Model_Abstract
{
    /**
     * Init
     */
    #[\Override]
    protected function _construct()
    {
        $this->_init('klarna_core/order');
    }

    /**
     * Load by session id
     *
     * @param string $sessionId
     *
     * @return $this
     */
    public function loadBySessionId($sessionId)
    {
        return $this->load($sessionId, 'session_id');
    }

    /**
     * Load by an order
     *
     * @return $this
     */
    public function loadByOrder(Mage_Sales_Model_Order $order)
    {
        return $this->load($order->getId(), 'order_id');
    }
}
