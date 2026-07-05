<?php

/**
 * SPDX-FileCopyrightText: 2015-2020 Klarna Bank AB (publ)
 * SPDX-FileCopyrightText: 2026 Maho <https://mahocommerce.com>
 * SPDX-License-Identifier: Apache-2.0
 * @package Klarna_Core
 */

/**
 * Post purchase API source for admin configuration
 */
class Klarna_Core_Model_System_Config_Source_Postpurchase extends Mage_Core_Model_Config_Data
{
    /**
     * Get version details
     *
     * @return array
     */
    public function toOptionArray()
    {
        $helper  = Mage::helper('klarna_core');
        $options = array();

        if ($types = $helper->getPostPurchaseApiType()) {
            foreach ($types as $type) {
                $options[] = array(
                    'label' => Mage::helper('klarna_core')->__($type->getLabel()),
                    'value' => $type->getCode()
                );
            }
        }

        array_unshift(
            $options, array(
            'label' => $helper->__('Disabled'),
            'value' => null
            )
        );

        return $options;
    }
}
