<?php

/**
 * SPDX-FileCopyrightText: 2015-2020 Klarna Bank AB (publ)
 * SPDX-FileCopyrightText: 2026 Maho <https://mahocommerce.com>
 * SPDX-License-Identifier: Apache-2.0
 * @package Klarna_Payments
 */

class Klarna_Payments_Model_Payment_Attachment_Default extends Klarna_Payments_Model_Payment_Attachment_Abstract
{

    /**
     * @param Klarna_Core_Model_Api_Builder_Abstract $payment
     *
     * @return $this
     */
    public function collect($payment)
    {
        return $this;
    }


    /**
     * @param Klarna_Core_Model_Api_Builder_Abstract $payment
     *
     * @return $this
     */
    public function fetch($payment)
    {
        return $this;
    }

}