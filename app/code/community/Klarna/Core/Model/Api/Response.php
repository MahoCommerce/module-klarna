<?php

declare(strict_types=1);

/**
 * SPDX-FileCopyrightText: 2015-2020 Klarna Bank AB (publ)
 * SPDX-FileCopyrightText: 2026 Maho <https://mahocommerce.com>
 * SPDX-License-Identifier: Apache-2.0
 * @package Klarna_Core
 */
/**
 * Response object from a remote request
 *
 * @method bool getIsSuccessful()
 * @method Klarna_Core_Model_Api_Response setIsSuccessful($boolean)
 * @method string getTransactionId()
 * @method Klarna_Core_Model_Api_Response setTransactionId($string)
 */
class Klarna_Core_Model_Api_Response extends Varien_Object
{
    /**
     * Build the default values for the object
     */
    #[\Override]
    protected function _construct()
    {
        $this->setData(
            [
                'is_successful' => false,
            ],
        );
    }
}
