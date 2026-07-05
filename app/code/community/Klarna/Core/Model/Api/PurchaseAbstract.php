<?php

/**
 * SPDX-FileCopyrightText: 2015-2020 Klarna Bank AB (publ)
 * SPDX-FileCopyrightText: 2026 Maho <https://mahocommerce.com>
 * SPDX-License-Identifier: Apache-2.0
 * @package Klarna_Core
 */

/**
 * Klarna api integration abstract
 *
 * @method Klarna_Core_Model_Api_PurchaseAbstract setStore(Mage_Core_Model_Store $store)
 * @method Mage_Core_Model_Store getStore()
 * @method Klarna_Core_Model_Api_PurchaseAbstract setConfig(Varien_Object $config)
 * @method Varien_Object getConfig()
 */
class Klarna_Core_Model_Api_PurchaseAbstract extends Klarna_Core_Model_Api_ApiTypeAbstract
    implements Klarna_Core_Model_Api_PurchaseApiInterface
{
    /**
     * Create or update a session
     *
     * @param string $sessionId
     * @param bool   $createIfNotExists
     * @param bool   $updateAllowed
     *
     * @return Varien_Object
     */
    public function initKlarnaSession($sessionId = null, $createIfNotExists = false, $updateAllowed = false)
    {
        return new Klarna_Core_Model_Api_Response();
    }

    /**
     * Create new session
     *
     * @throws Klarna_Core_Model_Api_Exception
     *
     * @return Varien_Object
     */
    public function createSession()
    {
        return $this->initKlarnaSession();
    }

    /**
     * Update existing session
     *
     * @param string $sessionId
     *
     * @throws Klarna_Core_Model_Api_Exception
     *
     * @return Varien_Object
     */
    public function updateSession($sessionId)
    {
        return $this->initKlarnaSession($sessionId, false, true);
    }
}
