<?php

/**
 * SPDX-FileCopyrightText: 2015-2020 Klarna Bank AB (publ)
 * SPDX-FileCopyrightText: 2026 Maho <https://mahocommerce.com>
 * SPDX-License-Identifier: Apache-2.0
 * @package Klarna_Core
 */

/**
 * Generate tax order line details
 */
class Klarna_Core_Model_Api_Builder_Orderline_Tax extends Klarna_Core_Model_Api_Builder_Orderline_Abstract
{
    public const ITEM_TYPE_TAX = 'sales_tax';

    /**
     * Collect totals process.
     *
     * @param Klarna_Core_Model_Api_Builder_Abstract $checkout
     *
     * @return $this
     */
    #[\Override]
    public function collect($checkout)
    {
        $object = $checkout->getObject();
        $helper = Mage::helper('klarna_core');

        if (!$helper->getSeparateTaxLine($object->getStore())) {
            return $this;
        }

        if ($checkout->getObject() instanceof Mage_Sales_Model_Quote) {
            $totalTax = $object->isVirtual() ? $object->getBillingAddress()->getBaseTaxAmount()
                : $object->getShippingAddress()->getBaseTaxAmount();
        } else {
            $totalTax = $object->getBaseTaxAmount();
        }

        $checkout->addData(
            [
                'tax_unit_price'   => $helper->toApiFloat($totalTax),
                'tax_total_amount' => $helper->toApiFloat($totalTax),

            ],
        );

        return $this;
    }

    /**
     * Add order details to checkout request
     *
     * @param Klarna_Core_Model_Api_Builder_Abstract $checkout
     *
     * @return $this
     */
    #[\Override]
    public function fetch($checkout)
    {
        $helper = Mage::helper('klarna_core');

        if ($checkout->getTaxUnitPrice()) {
            $checkout->addOrderLine(
                [
                    'type'             => self::ITEM_TYPE_TAX,
                    'reference'        => $helper->__('Sales Tax'),
                    'name'             => $helper->__('Sales Tax'),
                    'quantity'         => 1,
                    'unit_price'       => $checkout->getTaxUnitPrice(),
                    'tax_rate'         => 0,
                    'total_amount'     => $checkout->getTaxTotalAmount(),
                    'total_tax_amount' => 0,
                ],
            );
        }

        return $this;
    }
}
