<?php

/**
 * SPDX-FileCopyrightText: 2015-2020 Klarna Bank AB (publ)
 * SPDX-FileCopyrightText: 2026 Maho <https://mahocommerce.com>
 * SPDX-License-Identifier: Apache-2.0
 * @package Klarna_Core
 */

/**
 * Generate order line details for reward
 */
class Klarna_Core_Model_Api_Builder_Orderline_Reward extends Klarna_Core_Model_Api_Builder_Orderline_Abstract
{
    /**
     * Collect totals process.
     *
     * @param Klarna_Kco_Model_Api_Builder_Abstract $checkout
     *
     * @return $this
     */
    #[\Override]
    public function collect($checkout)
    {
        /** @var Mage_Sales_Model_Quote $quote */
        $quote  = $checkout->getObject();
        $totals = $quote->getTotals();

        if (is_array($totals) && isset($totals['reward'])) {
            $total  = $totals['reward'];
            $helper = Mage::helper('klarna_core');
            $value  = $helper->toApiFloat($total->getValue());

            $checkout->addData(
                [
                    'reward_unit_price'   => $value,
                    'reward_tax_rate'     => 0,
                    'reward_total_amount' => $value,
                    'reward_tax_amount'   => 0,
                    'reward_title'        => $total->getTitle(),
                    'reward_reference'    => $total->getCode(),

                ],
            );
        }

        return $this;
    }

    /**
     * Add grand total information to address
     *
     * @param Klarna_Kco_Model_Api_Builder_Abstract $checkout
     *
     * @return $this
     */
    #[\Override]
    public function fetch($checkout)
    {
        if ($checkout->getRewardTotalAmount()) {
            $checkout->addOrderLine(
                [
                    'type'             => Klarna_Core_Model_Api_Builder_Orderline_Discount::ITEM_TYPE_DISCOUNT,
                    'reference'        => $checkout->getRewardReference(),
                    'name'             => $checkout->getRewardTitle(),
                    'quantity'         => 1,
                    'unit_price'       => $checkout->getRewardUnitPrice(),
                    'tax_rate'         => $checkout->getRewardTaxRate(),
                    'total_amount'     => $checkout->getRewardTotalAmount(),
                    'total_tax_amount' => $checkout->getRewardTaxAmount(),
                ],
            );
        }

        return $this;
    }
}
