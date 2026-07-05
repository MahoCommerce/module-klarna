<?php

/**
 * SPDX-FileCopyrightText: 2015-2020 Klarna Bank AB (publ)
 * SPDX-FileCopyrightText: 2026 Maho <https://mahocommerce.com>
 * SPDX-License-Identifier: Apache-2.0
 * @package Klarna_Core
 */

/**
 * Klarna multi-select backend model to remove empty values
 */
class Klarna_Core_Model_System_Config_Backend_Multiselect extends Mage_Core_Model_Config_Data
{
    /**
     * Before saving of multi select to remove empty values
     *
     * @return mixed
     */
    #[\Override]
    protected function _beforeSave()
    {
        if ($this->getValue() == -1) {
            $this->setValue(null);
        }

        return parent::_beforeSave();
    }
}
