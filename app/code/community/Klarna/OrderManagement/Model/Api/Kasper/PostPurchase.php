<?php

/**
 * SPDX-FileCopyrightText: 2015-2020 Klarna Bank AB (publ)
 * SPDX-FileCopyrightText: 2026 Maho <https://mahocommerce.com>
 * SPDX-License-Identifier: Apache-2.0
 * @package Klarna_OrderManagement
 */

/**
 * Klarna Order management integration
 */
class Klarna_OrderManagement_Model_Api_Kasper_PostPurchase extends Klarna_Core_Model_Api_PostPurchaseAbstract
{
    /**
     * If a request is being made recursively, to prevent loops
     *
     * @var bool
     */
    protected $_isRecursiveCall = false;

    /**
     * API allowed shipping method code
     */
    public const KLARNA_API_SHIPPING_METHOD_HOME = 'Home';
    public const KLARNA_API_SHIPPING_METHOD_PICKUPSTORE = 'PickUpStore';
    public const KLARNA_API_SHIPPING_METHOD_BOXREG = 'BoxReg';
    public const KLARNA_API_SHIPPING_METHOD_BOXUNREG = 'BoxUnreg';
    public const KLARNA_API_SHIPPING_METHOD_PICKUPPOINT = 'PickUpPoint';
    public const KLARNA_API_SHIPPING_METHOD_OWN = 'Own';

    /**
     * Acknowledge an order in order management
     *
     * @param string $orderId
     *
     * @return Klarna_Core_Model_Api_Response
     */
    #[\Override]
    public function acknowledgeOrder($orderId)
    {
        return $this->_getOrderManagementApi()->acknowledgeOrder($orderId);
    }

    /**
     * Update merchant references for a Klarna order
     *
     * @param string $orderId
     * @param string $reference1
     * @param string $reference2
     *
     * @return Klarna_Core_Model_Api_Response
     */
    #[\Override]
    public function updateMerchantReferences($orderId, $reference1, $reference2 = null)
    {
        return $this->_getOrderManagementApi()->updateMerchantReferences($orderId, $reference1, $reference2);
    }

    /**
     * Capture an amount on an order
     *
     * @param string                         $orderId
     * @param float                          $amount
     * @param Mage_Sales_Model_Order_Invoice $invoice
     *
     * @return Klarna_Core_Model_Api_Response
     */
    #[\Override]
    public function capture($orderId, $amount, $invoice = null)
    {
        $data['captured_amount'] = Mage::helper('klarna_core')->toApiFloat($amount);
        $requestData = Mage::app()->getRequest()->getPost();
        /**
         * Get items for capture
         */
        if ($invoice instanceof Mage_Sales_Model_Order_Invoice) {
            /** @var Mage_Sales_Model_Order_Payment $payment */
            $payment = $invoice->getOrder()->getPayment();
            $paymentMethod = $payment->getMethod();
            $orderItems    = $this->getHelper()
                ->getPurchaseApiInstance($paymentMethod, $invoice->getStore())
                ->getGenerator()
                ->setObject($invoice)
                ->collectOrderLines()
                ->getOrderLines();

            if ($orderItems) {
                $data['order_lines'] = $orderItems;
            }
        }

        /**
         * Set shipping delay for capture
         *
         * Change this setting when items will not be shipped for x amount of days after capture.
         *
         * For instance, you capture on Friday but won't ship until Monday. A 3 day shipping delay would be set.
         */
        $shippingDelayObject = new Varien_Object(
            [
                'shipping_delay' => 0,
            ],
        );

        Mage::dispatchEvent(
            'klarna_capture_shipping_delay',
            [
                'shipping_delay_object' => $shippingDelayObject,
            ],
        );

        if ($shippingDelayObject->getShippingDelay()) {
            $data['shipping_delay'] = $shippingDelayObject->getShippingDelay();
        }

        $response = $this->_getOrderManagementApi()->captureOrder($orderId, $data);

        /**
         * If a capture fails, attempt to extend the auth and attempt capture again.
         * This work in certain cases that cannot be detected via api calls
         */
        if (!$response->getIsSuccessful() && !$this->_isRecursiveCall) {
            $extendResponse = $this->_getOrderManagementApi()->extendAuthorization($orderId);

            if ($extendResponse->getIsSuccessful()) {
                $this->_isRecursiveCall = true;
                $response               = $this->capture($orderId, $amount);
                $this->_isRecursiveCall = false;

                return $response;
            }
        }

        if ($response->getIsSuccessful()) {
            $captureId = $response->getResponseObject()->getHeader('Capture-id')
                ?: $this->_getOrderManagementApi()->getLocationResourceId(
                    (string) $response->getResponseObject()->getHeader('Location'),
                );

            if ($captureId) {
                $captureDetails = $this->_getOrderManagementApi()->getCapture($orderId, $captureId);

                if ($captureDetails->getKlarnaReference()) {
                    $captureDetails->setTransactionId($captureDetails->getKlarnaReference());

                    if (isset($requestData['invoice']['do_shipment'])
                        && $response->getCaptureId()
                        && $requestData['invoice']['do_shipment'] == true
                        && isset($requestData['tracking'])) {
                        $this->addShippingInfo(
                            $response->getCaptureId(),
                            $orderId,
                            $requestData['tracking'],
                            $invoice,
                        );
                    }

                    return $captureDetails;
                }
            }
        }

        return $response;
    }

    /**
     * Add shipping info to capture
     *
     * @param string                         $captureId
     * @param string                         $klarnaOrderId
     * @param array                          $trackingData
     * @param Mage_Sales_Model_Order_Invoice $invoice
     */
    private function addShippingInfo($captureId, $klarnaOrderId, $trackingData, ?Mage_Sales_Model_Order_Invoice $invoice): void
    {
        $data = $this->prepareShippingInfo($trackingData);
        $response =  $this->_getOrderManagementApi()->addShippingDetailsToCapture($klarnaOrderId, $captureId, $data);

        if (!$response->getIsSuccessful()) {
            foreach ($response->getErrorMessages() as $message) {
                $invoice?->addComment($message, false, false);
            }
        } else {
            $invoice?->addComment('Shipping info sent to Klarna API', false, false);
        }
    }


    /**
     * Prepare shipping info request,For merchant who implement this feature
     * overwrite this function to add additional information
     *
     * @return array
     */
    public function prepareShippingInfo(array $shippingInfo)
    {
        $data = [];
        foreach ($shippingInfo as $shipping) {
            $data['shipping_info'][] = [
                'tracking_number' => $shipping['number'],
                'shipping_method' => $this->getKlarnaShippingMethod($shipping),
                'shipping_company' => $shipping['title'],
            ];
        }
        return $data;
    }

    /**
     * Get Api Accepted shipping method,For merchant who implement this feature
     * overwrite this function to return correct method code
     * Allowed values matches (PickUpStore|Home|BoxReg|BoxUnreg|PickUpPoint|Own)
     *
     * @return string
     */
    public function getKlarnaShippingMethod(array $shipping)
    {
        return self::KLARNA_API_SHIPPING_METHOD_HOME;
    }

    /**
     * Refund for an order
     *
     * @param string                            $orderId
     * @param float                             $amount
     * @param Mage_Sales_Model_Order_Creditmemo $creditMemo
     *
     * @return Klarna_Core_Model_Api_Response
     */
    #[\Override]
    public function refund($orderId, $amount, $creditMemo = null)
    {
        $data['refunded_amount'] = Mage::helper('klarna_core')->toApiFloat($amount);

        /**
         * Get items for refund
         */
        if ($creditMemo instanceof Mage_Sales_Model_Order_Creditmemo) {
            /** @var Mage_Sales_Model_Order_Payment $payment */
            $payment = $creditMemo->getOrder()->getPayment();
            $paymentMethod = $payment->getMethod();
            $orderItems    = $this->getHelper()
                ->getPurchaseApiInstance($paymentMethod, $creditMemo->getStore())
                ->getGenerator()
                ->setObject($creditMemo)
                ->collectOrderLines()
                ->getOrderLines();

            if ($orderItems) {
                $data['order_lines'] = $orderItems;
            }
        }

        $response = $this->_getOrderManagementApi()->refund($orderId, $data);

        $response->setTransactionId($this->_getOrderManagementApi()->getLocationResourceId($response));

        return $response;
    }

    /**
     * Cancel an order
     *
     * @param string $orderId
     *
     * @return Klarna_Core_Model_Api_Response
     */
    #[\Override]
    public function cancel($orderId)
    {
        return $this->_getOrderManagementApi()->cancelOrder($orderId);
    }

    /**
     * Release the authorization for an order
     *
     * @param string $orderId
     *
     * @return Klarna_Core_Model_Api_Response
     */
    #[\Override]
    public function release($orderId)
    {
        return $this->_getOrderManagementApi()->releaseAuthorization($orderId);
    }

    /**
     * Get the fraud status of an order to determine if it should be accepted or denied within Magento
     *
     * Return value of 1 means accept
     * Return value of 0 means still pending
     * Return value of -1 means deny
     *
     * @param string $orderId
     *
     * @return int
     */
    #[\Override]
    public function getFraudStatus($orderId)
    {
        return match ($this->getOrder($orderId)->getFraudStatus()) {
            self::ORDER_FRAUD_STATUS_ACCEPTED => 1,
            self::ORDER_FRAUD_STATUS_REJECTED => -1,
            default => 0,
        };
    }

    /**
     * Get order details from the api
     *
     * @param string $orderId
     *
     * @return Klarna_Core_Model_Api_Response
     */
    #[\Override]
    public function getOrder($orderId)
    {
        return $this->_getOrderManagementApi()->getOrder($orderId);
    }

    /**
     * Get rest order management api
     *
     * @return Klarna_OrderManagement_Model_Api_Rest_Ordermanagement
     */
    protected function _getOrderManagementApi()
    {
        return Mage::getSingleton('klarna_ordermanagement/api_rest_ordermanagement')
            ->setConfig($this->getConfig())
            ->setStore($this->getStore());
    }
}
