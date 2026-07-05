<?php

/**
 * SPDX-FileCopyrightText: 2015-2020 Klarna Bank AB (publ)
 * SPDX-FileCopyrightText: 2026 Maho <https://mahocommerce.com>
 * SPDX-License-Identifier: Apache-2.0
 * @package Klarna_Payments
 */

/**
 * Klarna Payments observer methods
 */
class Klarna_Payments_Model_Observer
{
    /**
     * Changing the klarna payment code to its default
     */
    #[\Maho\Config\Observer('sales_quote_payment_import_data_before', type: 'singleton', id: 'klarna_payments_method')]
    public function adjustPaymentMethodCode(Varien_Event_Observer $observer): void
    {
        /** @var Varien_Object $input */
        $input = $observer->getData()['input'];
        $method = $input->getData('method');

        $klarnaKeyStart = 'klarna_payments_';
        if (!str_starts_with((string) $method, $klarnaKeyStart)) {
            return;
        }

        $paymentKey = substr((string) $method, strlen($klarnaKeyStart));

        /** @var Mage_Checkout_Model_Session $checkoutSession */
        $checkoutSession = Mage::getSingleton('checkout/session');
        $checkoutSession->setData($klarnaKeyStart . 'selected', $paymentKey);

        $method = 'klarna_payments';

        $input->setData('method', $method);
        $observer->setData('input', $input);
    }

    /**
     * Because of Klarna Payments's redirect, Magento does not send the order email.
     *
     * This method will trigger the email sending even though there is a redirect
     */
    #[\Maho\Config\Observer('checkout_submit_all_after', type: 'singleton', id: 'klarna_payments_send_email')]
    public function checkoutSubmitAfterAllSendOrderEmail(Varien_Event_Observer $observer): void
    {
        /** @var Mage_Sales_Model_Order $order */
        $order = $observer->getOrder();
        $payment = $order->getPayment();

        if ($payment &&
            !$order->getEmailSent() &&
            $payment->getMethod() === 'klarna_payments' &&
            $order->getState() === Mage_Sales_Model_Order::STATE_PROCESSING) {

            $order->sendNewOrderEmail();
        }
    }

    /**
     * Clear Klarna Payment session variables when the checkout session is cleared
     */
    #[\Maho\Config\Observer('checkout_quote_destroy', type: 'singleton', id: 'klarna_payments_checkout_session_clear')]
    public function checkoutSessionClear(Varien_Event_Observer $observer): void
    {
        $checkoutSession = Mage::getSingleton('checkout/session');
        $checkoutSession->setKlarnaPaymentsPayloadToken(null);
        $checkoutSession->setKlarnaPaymentsItemCheckToken(null);
    }

    /**
     * Update User-Agent with module version info
     */
    #[\Maho\Config\Observer('klarna_core_client_user_agent_string', id: 'klarna_payments_ua')]
    public function klarnaCoreClientUserAgentString(Varien_Event_Observer $event): void
    {
        /** @var Mage_Core_Model_Config $config */
        $config = Mage::getConfig();
        $version = $config->getModuleConfig('Klarna_Payments')->version;
        $versionObj = $event->getVersionStringObject();
        $verString = $versionObj->getVersionString();
        $verString .= ";Klarna_Payments_v{$version}";
        $versionObj->setVersionString($verString);
    }


    /**
     * validate order total for OSC checkout
     */
    #[\Maho\Config\Observer('klarna_payments_request_create_after', type: 'singleton', id: 'klarna_check_order_total_osc')]
    public function checkOrderTotalForOsc(Varien_Event_Observer $observer): void
    {
        $requestObject = $observer->getRequestObject();
        $request = $requestObject->getRequestBody();

        if (Mage::app()->getRequest()->getRouteName() === 'onestepcheckout') {
            $orderLineTotal = 0;
            foreach ($request['order_lines'] as $orderLine) {
                $orderLineTotal += $orderLine['total_amount'];
            }

            if ($orderLineTotal != $request['order_amount']) {
                $request['order_amount'] = $orderLineTotal;
            }

            $requestObject->setRequestBody($request);
        }
    }

    /**
     * record selected payment type
     */
    #[\Maho\Config\Observer('checkout_submit_all_after', type: 'singleton', id: 'klarna_payments_record_payment_type')]
    public function recordPaymentType(Varien_Event_Observer $observer): void
    {
        $klarnaKeyStart = 'klarna_payments_';
        $checkoutSession = Mage::getSingleton('checkout/session');
        $selectPaymentType = $checkoutSession->getData($klarnaKeyStart . 'selected');

        if ($selectPaymentType) {
            $order = $observer->getOrder();
            $order->getPayment()->setAdditionalInformation(
                'klarna_payment_type',
                $selectPaymentType,
            );
            $order->getPayment()->save();
        }
    }
}
