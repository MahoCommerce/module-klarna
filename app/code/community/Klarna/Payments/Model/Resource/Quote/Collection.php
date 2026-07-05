<?php

declare(strict_types=1);

/**
 * SPDX-FileCopyrightText: 2015-2020 Klarna Bank AB (publ)
 * SPDX-FileCopyrightText: 2026 Maho <https://mahocommerce.com>
 * SPDX-License-Identifier: Apache-2.0
 * @package Klarna_Payments
 */
/**
 * Klarna quote collection
 */
class Klarna_Payments_Model_Resource_Quote_Collection extends Mage_Core_Model_Resource_Db_Collection_Abstract
{
    /**
     * Init
     */
    #[\Override]
    protected function _construct()
    {
        $this->_init('klarna_payments/quote');
    }
}
