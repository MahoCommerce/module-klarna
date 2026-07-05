<?php

/**
 * SPDX-FileCopyrightText: 2015-2020 Klarna Bank AB (publ)
 * SPDX-FileCopyrightText: 2026 Maho <https://mahocommerce.com>
 * SPDX-License-Identifier: Apache-2.0
 * @package Klarna_Core
 */

/**
 * Customer group config source
 */
class Klarna_Core_Model_System_Config_Source_Customergroup
{
    /**
     * Customer groups options array
     *
     * @var array
     */
    protected $_options;

    /**
     * Retrieve customer groups as array
     *
     * @return array
     */
    public function toOptionArray()
    {
        if (null === $this->_options) {
            $this->_options = Mage::getResourceModel('customer/group_collection')->toOptionArray();
            array_unshift($this->_options, ['value' => -1, 'label' => '']);
        }

        return $this->_options;
    }
}
