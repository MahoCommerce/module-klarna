<?php

/**
 * SPDX-FileCopyrightText: 2015-2020 Klarna Bank AB (publ)
 * SPDX-FileCopyrightText: 2026 Maho <https://mahocommerce.com>
 * SPDX-License-Identifier: Apache-2.0
 * @package Klarna_Core
 */

/**
 * Generate order lines for discounts
 */
class Klarna_Core_Model_Api_Builder_Orderline_Discount extends Klarna_Core_Model_Api_Builder_Orderline_Abstract
{
    public const ITEM_TYPE_DISCOUNT = 'discount';

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
        /** @var Mage_Core_Model_Store $store */
        $store = $object->getStore();
        $totals = $object->getTotals();
        $helper = Mage::helper('klarna_core');

        if (is_array($totals) && isset($totals['discount'])) {
            /** @var Mage_Sales_Model_Quote_Address_Total $total */
            $total = $totals['discount'];
            $subtotal = $totals['subtotal'];

            $taxAmount = $this->getTaxAmount($subtotal, $total);
            $amount = -$total->getValue();

            if ($helper->getSeparateTaxLine($store) || !$helper->getTaxBeforeDiscount($store)) {
                $unitPrice   = $amount;
                $totalAmount = $amount;
                $taxRate     = 0;
                $taxAmount   = 0;
            } else {
                $taxRate = $this->getDiscountTaxRate($checkout);
                $unitPrice = $amount;
                $totalAmount = $amount;
                if ($helper->getPriceExcludesVat($store)) {
                    $unitPrice += $taxAmount;
                    $totalAmount += $taxAmount;
                }
            }

            $checkout->addData(
                [
                    'discount_unit_price'   => -$helper->toApiFloat($unitPrice),
                    'discount_tax_rate'     => $taxRate,
                    'discount_total_amount' => -$helper->toApiFloat($totalAmount),
                    'discount_tax_amount'   => -$helper->toApiFloat($taxAmount),
                    'discount_title'        => $total->getTitle(),
                    'discount_reference'    => $total->getCode(),

                ],
            );
        } elseif (((float) $object->getDiscountAmount()) != 0) {
            if ($object->getDiscountDescription()) {
                $discountLabel = Mage::helper('sales')->__('Discount (%s)', $object->getDiscountDescription());
            } else {
                $discountLabel = Mage::helper('sales')->__('Discount');
            }

            $taxAmount = $object->getBaseHiddenTaxAmount();
            $amount    = -$object->getBaseDiscountAmount() - $taxAmount;

            if ($helper->getSeparateTaxLine($store)) {
                $unitPrice   = $amount;
                $totalAmount = $amount;
                $taxRate     = 0;
                $taxAmount   = 0;
            } else {
                $taxRate     = $this->getDiscountTaxRate($checkout);
                $unitPrice   = $amount + $taxAmount;
                $totalAmount = $amount + $taxAmount;
            }

            $checkout->addData(
                [
                    'discount_unit_price'   => -$helper->toApiFloat($unitPrice),
                    'discount_tax_rate'     => $taxRate,
                    'discount_total_amount' => -$helper->toApiFloat($totalAmount),
                    'discount_tax_amount'   => -$helper->toApiFloat($taxAmount),
                    'discount_title'        => $discountLabel,
                    'discount_reference'    => self::ITEM_TYPE_DISCOUNT,

                ],
            );
        }

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
        if ($checkout->getDiscountReference()) {
            $checkout->addOrderLine(
                [
                    'type'             => self::ITEM_TYPE_DISCOUNT,
                    'reference'        => $checkout->getDiscountReference(),
                    'name'             => $checkout->getDiscountTitle(),
                    'quantity'         => 1,
                    'unit_price'       => $checkout->getDiscountUnitPrice(),
                    'tax_rate'         => $checkout->getDiscountTaxRate(),
                    'total_amount'     => $checkout->getDiscountTotalAmount(),
                    'total_tax_amount' => $checkout->getDiscountTaxAmount(),
                ],
            );
        }

        return $this;
    }
}
