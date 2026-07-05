<?php

/**
 * SPDX-FileCopyrightText: 2015-2020 Klarna Bank AB (publ)
 * SPDX-FileCopyrightText: 2026 Maho <https://mahocommerce.com>
 * SPDX-License-Identifier: Apache-2.0
 * @package Klarna_OrderManagement
 */

class Klarna_OrderManagement_Model_Observer
{
    /**
     * Update User-Agent with module version info
     *
     * @param $event
     */
    public function klarnaCoreClientUserAgentString($event)
    {
        $version = Mage::getConfig()->getModuleConfig('Klarna_OrderManagement')->version;
        $versionObj = $event->getVersionStringObject();
        $verString = $versionObj->getVersionString();
        $verString .= ";Klarna_OM_v{$version}";
        $versionObj->setVersionString($verString);
    }
}