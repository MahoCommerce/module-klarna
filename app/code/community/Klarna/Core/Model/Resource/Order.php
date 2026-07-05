<?php

/**
 * SPDX-FileCopyrightText: 2015-2020 Klarna Bank AB (publ)
 * SPDX-FileCopyrightText: 2026 Maho <https://mahocommerce.com>
 * SPDX-License-Identifier: Apache-2.0
 * @package Klarna_Core
 */

/**
 * Klarna order resource
 */
class Klarna_Core_Model_Resource_Order extends Mage_Core_Model_Resource_Db_Abstract
{
    /**
     * Init
     */
    public function _construct()
    {
        $this->_init('klarna_core/order', 'klarna_order_id');
    }
}
