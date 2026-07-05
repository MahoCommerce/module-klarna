<?php

/**
 * SPDX-FileCopyrightText: 2015-2020 Klarna Bank AB (publ)
 * SPDX-FileCopyrightText: 2026 Maho <https://mahocommerce.com>
 * SPDX-License-Identifier: Apache-2.0
 * @package Klarna_Core
 */

class Klarna_Core_Block_Adminhtml_Sales_Order_View_Info extends Mage_Adminhtml_Block_Sales_Order_View_Info
{
    /**
     * Get link to edit order address page
     *
     * @param Mage_Sales_Model_Order_Address $address
     * @param string $label
     * @return string
     */
    public function getAddressEditLink($address, $label='')
    {
        if ($address->getOrder()->getPayment()->getMethod() == 'klarna_payments') {
            return '';
        }

        return parent::getAddressEditLink($address, $label);
    }
}