<?php

/**
 * SPDX-FileCopyrightText: 2015-2020 Klarna Bank AB (publ)
 * SPDX-FileCopyrightText: 2026 Maho <https://mahocommerce.com>
 * SPDX-License-Identifier: Apache-2.0
 * @package Klarna_Core
 */

/**
 * Klarna versions config source
 */
class Klarna_Core_Model_System_Config_Source_Version
{
    /**
     * Get version details
     *
     * @return array
     */
    public function toOptionArray()
    {
        $helper  = Mage::helper('klarna_core');
        $options = [];

        /** @var array $versions */
        $versions = $helper->getApiVersion();
        if ($versions) {
            foreach ($versions as $version) {
                $options[] = [
                    'label' => Mage::helper('klarna_core')->__($version->getLabel()),
                    'value' => $version->getCode(),
                ];
            }
        } else {
            $options[] = [
                'label' => Mage::helper('klarna_core')->__('No API Versions Available'),
                'value' => null,
            ];
        }

        return $options;
    }
}
