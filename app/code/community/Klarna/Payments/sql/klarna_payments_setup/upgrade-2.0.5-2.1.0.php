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

/**
 * Create table 'klarna_payments/quote'
 */
$table = $installer->getConnection()
                   ->addColumn(
                       $installer->getTable('klarna_payments/quote'),
                       'payment_method',
                       [
                           'TYPE'     => Varien_Db_Ddl_Table::TYPE_TEXT,
                           'COMMENT'  => 'Payment Method',
                           'LENGTH'   => 255,
                           'DEFAULT'  => 'klarna_payments',
                           'NULLABLE' => false,
                       ],
                   );
$installer->getConnection()->update($installer->getTable('klarna_payments/quote'), ['payment_method' => 'klarna_payments']);

$installer->endSetup();
