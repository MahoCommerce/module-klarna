<?php

/**
 * SPDX-FileCopyrightText: 2015-2020 Klarna Bank AB (publ)
 * SPDX-FileCopyrightText: 2026 Maho <https://mahocommerce.com>
 * SPDX-License-Identifier: Apache-2.0
 * @package Klarna_Core
 */

/**
 * Klarna api integration interface for purchases
 */
interface Klarna_Core_Model_Api_PurchaseApiInterface
{
    /**
     * Create or update a session
     *
     * @param string     $sessionId
     * @param bool|false $createIfNotExists
     * @param bool|false $updateAllowed
     *
     * @return Klarna_Core_Model_Api_Response
     */
    public function initKlarnaSession($sessionId = null, $createIfNotExists = false, $updateAllowed = false);

    /**
     * @return Klarna_Core_Model_Api_Response
     */
    public function createSession();

    /**
     * @param string $sessionId
     *
     * @return Klarna_Core_Model_Api_Response
     */
    public function updateSession($sessionId);

    /**
     * Get Klarna Reservation Id
     *
     * @return string
     */
    public function getReservationId();
}
