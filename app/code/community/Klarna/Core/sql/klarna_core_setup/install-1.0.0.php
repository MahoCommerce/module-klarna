<?php

/**
 * SPDX-FileCopyrightText: 2015-2020 Klarna Bank AB (publ)
 * SPDX-FileCopyrightText: 2026 Maho <https://mahocommerce.com>
 * SPDX-License-Identifier: Apache-2.0
 * @package Klarna_Core
 */

/** @var Mage_Sales_Model_Resource_Setup $this */
$installer = $this;

$installer->startSetup();
/**
 * Create table 'klarna_core/order'
 */
$table = $installer->getConnection()
    ->newTable($installer->getTable('klarna_core/order'))
    ->addColumn(
        'klarna_order_id',
        Varien_Db_Ddl_Table::TYPE_INTEGER,
        null,
        [
            'identity' => true,
            'unsigned' => true,
            'nullable' => false,
            'primary'  => true,
        ],
        'Order Id',
    )
    ->addColumn('session_id', Varien_Db_Ddl_Table::TYPE_TEXT, 255, [], 'Session Id')
    ->addColumn('reservation_id', Varien_Db_Ddl_Table::TYPE_TEXT, 255, [], 'Reservation Id')
    ->addColumn(
        'order_id',
        Varien_Db_Ddl_Table::TYPE_INTEGER,
        null,
        [
            'unsigned' => true,
            'nullable' => false,
        ],
        'Order Id',
    )
    ->addColumn(
        'is_acknowledged',
        Varien_Db_Ddl_Table::TYPE_SMALLINT,
        null,
        [
            'nullable' => false,
            'default'  => '0',
        ],
        'Is Acknowledged',
    )
    ->addForeignKey(
        $installer->getFkName('klarna_core/order', 'order_id', 'sales/order', 'entity_id'),
        'order_id',
        $installer->getTable('sales/order'),
        'entity_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE,
        Varien_Db_Ddl_Table::ACTION_CASCADE,
    )
    ->setComment('Klarna Order');
$installer->getConnection()->createTable($table);

$installer->endSetup();
