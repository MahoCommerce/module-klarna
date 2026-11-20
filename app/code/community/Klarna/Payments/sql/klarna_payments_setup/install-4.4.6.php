<?php

/**
 * SPDX-FileCopyrightText: 2015-2020 Klarna Bank AB (publ)
 * SPDX-FileCopyrightText: 2026 Maho <https://mahocommerce.com>
 * SPDX-License-Identifier: Apache-2.0
 * @package Klarna_Payments
 */

declare(strict_types=1);

use Maho\Db\Ddl\Table;

/** @var Mage_Core_Model_Resource_Setup $this */
$installer = $this;
$connection = $installer->getConnection();

$installer->startSetup();

// A fresh install needs the current table without running migrations for existing Klarna data.
$table = $connection->newTable($installer->getTable('klarna_payments/quote'))
    ->addColumn(
        'payments_quote_id',
        Table::TYPE_INTEGER,
        null,
        ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
        'Payments Id',
    )
    ->addColumn('session_id', Table::TYPE_TEXT, 255, [], 'Session Id')
    ->addColumn('client_token', Table::TYPE_TEXT, '64k', [], 'Client Token')
    ->addColumn('authorization_token', Table::TYPE_TEXT, 255, [], 'Authorization Token')
    ->addColumn('is_active', Table::TYPE_SMALLINT, null, ['nullable' => false, 'default' => '0'], 'Is Active')
    ->addColumn('quote_id', Table::TYPE_INTEGER, null, ['unsigned' => true, 'nullable' => false], 'Quote Id')
    ->addColumn(
        'payment_method',
        Table::TYPE_TEXT,
        255,
        ['nullable' => false, 'default' => 'klarna_payments'],
        'Payment Method',
    )
    ->addColumn(
        'payment_method_categories',
        Table::TYPE_TEXT,
        4096,
        ['nullable' => false],
        'Payment Method Categories',
    )
    ->addForeignKey(
        $installer->getFkName('klarna_payments/quote', 'quote_id', 'sales/quote', 'entity_id'),
        'quote_id',
        $installer->getTable('sales/quote'),
        'entity_id',
        Table::ACTION_CASCADE,
        Table::ACTION_CASCADE,
    )
    ->setComment('Klarna Payments Quote');

$connection->createTable($table);

$installer->endSetup();
