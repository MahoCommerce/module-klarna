<?php

/**
 * SPDX-FileCopyrightText: 2015-2020 Klarna Bank AB (publ)
 * SPDX-FileCopyrightText: 2026 Maho <https://mahocommerce.com>
 * SPDX-License-Identifier: Apache-2.0
 * @package Klarna_Payments
 */

/** @var Mage_Core_Model_Resource_Setup $this */
$installer = $this;

$installer->startSetup();


$installer->getConnection()
    ->addColumn(
        $installer->getTable('klarna_payments/quote'),
        'payment_method_categories',
        [
            'TYPE'     => Varien_Db_Ddl_Table::TYPE_TEXT,
            'COMMENT'  => 'Payment Method Categories',
            'LENGTH'   => 4096,
            'NULLABLE' => false,
        ],
    );

$installer->run('
    UPDATE ' . $installer->getTable('sales_flat_order_payment') . " 
    SET method = 'klarna_payments'
    WHERE method LIKE 'klarna_%' AND method != 'klarna_kco'
");

$installer->run('
    UPDATE ' . $installer->getTable('klarna_payments_quote') . " 
    SET payment_method = 'klarna_payments'
    WHERE payment_method LIKE 'klarna_%' 
");

$installer->getConnection()->delete(
    $installer->getTable('core_config_data'),
    ['path IN (?)' => ['payment/klarna_payments/title', 'payment/klarna_payments/force_default']],
);

$installer->getConnection()->update($installer->getTable('klarna_payments/quote'), ['payment_method_categories' => '[]']);

$installer->endSetup();
