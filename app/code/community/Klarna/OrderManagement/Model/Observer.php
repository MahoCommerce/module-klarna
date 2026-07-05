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
     * @param Varien_Event_Observer $event
     */
    #[\Maho\Config\Observer('klarna_core_client_user_agent_string', id: 'klarna_ordermanagement_ua')]
    public function klarnaCoreClientUserAgentString($event): void
    {
        /** @var Mage_Core_Model_Config $config */
        $config = Mage::getConfig();
        $version = $config->getModuleConfig('Klarna_OrderManagement')->version;
        $versionObj = $event->getVersionStringObject();
        $verString = $versionObj->getVersionString();
        $verString .= ";Klarna_OM_v{$version}";
        $versionObj->setVersionString($verString);
    }
}
