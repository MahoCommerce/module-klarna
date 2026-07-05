<?php

declare(strict_types=1);

/**
 * SPDX-FileCopyrightText: 2015-2020 Klarna Bank AB (publ)
 * SPDX-FileCopyrightText: 2026 Maho <https://mahocommerce.com>
 * SPDX-License-Identifier: Apache-2.0
 * @package Klarna_Core
 */
/**
 * Klarna order collection
 */
class Klarna_Core_Model_Resource_Order_Collection extends Mage_Core_Model_Resource_Db_Collection_Abstract
{
    /**
     * Init
     */
    #[\Override]
    protected function _construct()
    {
        $this->_init('klarna_core/order');
    }
}
