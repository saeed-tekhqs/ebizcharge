<?php
/**
 * Create the 'token' table, add proper fields to the 'sales_order_payment' table,
 * and add proper fields to the 'quote_payment' table.
 *
 * @author      Century Business Solutions <support@centurybizsolutions.com>
 * @copyright   Copyright (c) 2020 Century Business Solutions  (www.centurybizsolutions.com)
 */

namespace Ebizcharge\Ebizcharge\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\DB\Ddl\Table;

class EbizchargeSchema
{
    public function install(SchemaSetupInterface $installer)
    {
        $connection = $installer->getConnection();
        // Create 'ebizcharge_token' table.
        if (!$connection->isTableExists($installer->getTable('ebizcharge_token'))) {
            $tableName = $installer->getTable('ebizcharge_token');
            $table = $connection
                ->newTable($tableName)
                ->addColumn(
                    'token_id',
                    Table::TYPE_INTEGER,
                    null,
                    [
                        'identity' => true,
                        'unsigned' => true,
                        'nullable' => false,
                        'primary' => true
                    ],
                    'id'
                )
                ->addColumn(
                    'mage_cust_id',
                    Table::TYPE_INTEGER,
                    11,
                    [
                        'nullable' => false
                    ],
                    'Magento Customer ID'
                )
                ->addColumn(
                    'ebzc_cust_id',
                    Table::TYPE_INTEGER,
                    11,
                    [
                        'nullable' => false
                    ],
                    'EBizCharge Customer ID/Token'
                );

            $connection->createTable($table);
        }

        if (!$connection->isTableExists($installer->getTable('ebizcharge_recurring_dates'))) {
            $tableName = $installer->getTable('ebizcharge_recurring_dates');
            $table = $connection
                ->newTable($tableName)
                ->addColumn(
                    'id',
                    Table::TYPE_INTEGER,
                    null,
                    [
                        'identity' => true,
                        'unsigned' => true,
                        'nullable' => false,
                        'primary' => true
                    ],
                    'id'
                )
                ->addColumn(
                    'recurring_id',
                    Table::TYPE_INTEGER,
                    11,
                    [
                        'nullable' => false
                    ],
                    'EBizCharge recurring id'
                )
                ->addColumn(
                    'recurring_date',
                    Table::TYPE_TEXT,
                    50,
                    [
                        'nullable' => false
                    ],
                    'EBizCharge recurring date'
                );

            $connection->createTable($table);
        }

        $recurringOrdertable = $installer->getTable('ebizcharge_recurring_order');
        if (!$connection->isTableExists($recurringOrdertable)) {
            $table = $connection
                ->newTable($recurringOrdertable)
                ->addColumn(
                    'id',
                    Table::TYPE_INTEGER,
                    null,
                    [
                        'identity' => true,
                        'unsigned' => true,
                        'nullable' => false,
                        'primary' => true
                    ],
                    'id'
                )
                ->addColumn(
                    'recurring_id',
                    Table::TYPE_INTEGER,
                    11,
                    [
                        'nullable' => false
                    ],
                    'Ebizcharge recurring reference ID'
                )
                ->addColumn(
                    'rec_order_id',
                    Table::TYPE_TEXT,
                    50,
                    [
                        'nullable' => true
                    ],
                    'Magento Order ID'
                )
                ->addColumn(
                    'created_date',
                    Table::TYPE_TIMESTAMP,
                    50,
                    [
                        'nullable' => false
                    ],
                    'EBizCharge Order created date'
                )
                ->addColumn(
                    'status',
                    Table::TYPE_INTEGER,
                    1,
                    [
                        'nullable' => false
                    ],
                    'EBizCharge Order created status 0|1 '
                )
                ->addColumn(
                    'message',
                    Table::TYPE_TEXT,
                    1000,
                    [
                        'nullable' => false
                    ],
                    'EBizCharge Order created status 0|1 '
                );

            $connection->createTable($table);
        }

        if ($connection->tableColumnExists($recurringOrdertable, 'order_entity_id') === false) {
            $connection->addColumn($recurringOrdertable,
                'order_entity_id',
                [
                    'type' => Table::TYPE_TEXT,
                    'length' => 50,
                    'nullable' => true,
                    'comment' => 'Recurring order entity Id'
                ]
            );
        }

        // Create 'ebizcharge_recurring' table
        $recurringTable = $installer->getTable('ebizcharge_recurring');
        if (!$connection->isTableExists($recurringTable)) {
            $tableName = $installer->getTable('ebizcharge_recurring');
            $table = $connection
                ->newTable($tableName)
                ->addColumn(
                    'rec_id',
                    Table::TYPE_INTEGER,
                    null,
                    [
                        'identity' => true,
                        'unsigned' => true,
                        'nullable' => false,
                        'primary' => true
                    ],
                    'id'
                )
                ->addColumn(
                    'rec_status',
                    Table::TYPE_INTEGER,
                    11,
                    [
                        'unsigned' => true,
                        'nullable' => true
                    ],
                    'Recurring Payment Status'
                )
                ->addColumn(
                    'rec_indefinitely',
                    Table::TYPE_INTEGER,
                    11,
                    [
                        'unsigned' => true,
                        'nullable' => true
                    ],
                    'Recurring Indefinitely'
                )
                ->addColumn(
                    'mage_cust_id',
                    Table::TYPE_TEXT,
                    50,
                    [
                        'unsigned' => true,
                        'nullable' => true
                    ],
                    'Magento Customer ID'
                )
                ->addColumn(
                    'mage_order_id',
                    Table::TYPE_TEXT,
                    50,
                    [
                        'unsigned' => true,
                        'nullable' => true
                    ],
                    'Magento Order ID'
                )
                ->addColumn(
                    'mage_item_id',
                    Table::TYPE_TEXT,
                    50,
                    [
                        'unsigned' => true,
                        'nullable' => true
                    ],
                    'Magento Item ID'
                )
                ->addColumn(
                    'mage_item_name',
                    Table::TYPE_TEXT,
                    200,
                    [
                        'unsigned' => true,
                        'nullable' => true
                    ],
                    'Item Name'
                )
                ->addColumn(
                    'qty_ordered',
                    Table::TYPE_TEXT,
                    50,
                    [
                        'unsigned' => true,
                        'nullable' => true
                    ],
                    'Quantity Ordered'
                )
                ->addColumn(
                    'eb_rec_start_date',
                    Table::TYPE_TIMESTAMP,
                    50,
                    [
                        'unsigned' => true,
                        'nullable' => true
                    ],
                    'EBizCharge Recurring Start Date'
                )
                ->addColumn(
                    'eb_rec_end_date',
                    Table::TYPE_TIMESTAMP,
                    50,
                    [
                        'unsigned' => true,
                        'nullable' => true
                    ],
                    'EBizCharge Recurring End Date'
                )
                ->addColumn(
                    'eb_rec_frequency',
                    Table::TYPE_TEXT,
                    50,
                    [
                        'unsigned' => true,
                        'nullable' => true
                    ],
                    'EBizCharge Recurring Frequency'
                )
                ->addColumn(
                    'eb_rec_method_id',
                    Table::TYPE_TEXT,
                    50,
                    [
                        'unsigned' => true,
                        'nullable' => true
                    ],
                    'EBizCharge Recurring MethodId'
                )
                ->addColumn(
                    'eb_rec_scheduled_payment_internal_id',
                    Table::TYPE_TEXT,
                    100,
                    [
                        'unsigned' => true,
                        'nullable' => true
                    ],
                    'EBizCharge Scheduled Payment InternalId'
                )
                ->addColumn(
                    'eb_rec_total',
                    Table::TYPE_INTEGER,
                    11,
                    [
                        'unsigned' => false,
                        'nullable' => true
                    ],
                    'Total Recurrings'
                )
                ->addColumn(
                    'eb_rec_processed',
                    Table::TYPE_INTEGER,
                    11,
                    [
                        'unsigned' => false,
                        'nullable' => true
                    ],
                    'Processed Recurrings'
                )
                ->addColumn(
                    'eb_rec_next',
                    Table::TYPE_TIMESTAMP,
                    11,
                    [
                        'unsigned' => false,
                        'nullable' => true
                    ],
                    'Next Recurring'
                )
                ->addColumn(
                    'eb_rec_remaining',
                    Table::TYPE_INTEGER,
                    11,
                    [
                        'unsigned' => false,
                        'nullable' => true
                    ],
                    'Remaining Recurrings'
                )
                ->addColumn(
                    'eb_rec_due_dates',
                    Table::TYPE_TEXT,
                    '2M',
                    [
                        'unsigned' => false,
                        'nullable' => true
                    ],
                    'Recurring Due Dates'

                )->addColumn(
                    'mage_parent_item_id',
                    Table::TYPE_TEXT,
                    50,
                    [
                        'unsigned' => true,
                        'nullable' => true,
                    ],
                    'Magento Parent Item ID'
                )
                ->addColumn(
                    'billing_address_id',
                    Table::TYPE_INTEGER,
                    11,
                    [
                        'unsigned' => true,
                        'nullable' => true
                    ],
                    'Billing Address ID'
                )
                ->addColumn(
                    'shipping_address_id',
                    Table::TYPE_INTEGER,
                    11,
                    [
                        'unsigned' => true,
                        'nullable' => true
                    ],
                    'Shipping Address ID'
                );

            $connection->createTable($table);
        }

        if ($connection->tableColumnExists($recurringTable, 'amount') === false) {
            $connection->addColumn($recurringTable,
                'amount',
                [
                    'type' => Table::TYPE_TEXT,
                    'length' => 50,
                    'nullable' => true,
                    'comment' => 'Recurring amount'
                ]
            );
        }

        if ($connection->tableColumnExists($recurringTable, 'payment_method_name') === false) {
            $connection->addColumn($recurringTable,
                'payment_method_name',
                [
                    'type' => Table::TYPE_TEXT,
                    'length' => 100,
                    'nullable' => true,
                    'comment' => 'Recurring payment method name'
                ]
            );
        }

        if ($connection->tableColumnExists($recurringTable, 'shipping_method') === false) {
            $connection->addColumn($recurringTable,
                'shipping_method',
                [
                    'type' => Table::TYPE_TEXT,
                    'length' => 100,
                    'nullable' => true,
                    'comment' => 'Subscription shipping method'
                ]
            );
        }

        if ($connection->tableColumnExists($recurringTable, 'failed_attempts') === false) {
            $connection->addColumn($recurringTable,
                'failed_attempts',
                [
                    'type' => Table::TYPE_INTEGER,
                    'length' => 11,
                    'default' => 0,
                    'nullable' => false,
                    'comment' => 'Recurring order failed attempts'
                ]
            );
        }

        if ($connection->tableColumnExists($recurringOrdertable, 'order_date') === false) {
            $connection->addColumn($recurringOrdertable,
                'order_date',
                [
                    'type' => Table::TYPE_TEXT,
                    'length' => 50,
                    'nullable' => true,
                    'comment' => 'Recurring order due date'
                ]
            );
        }

        // Add columns to 'sales_order_payment' table.
        $salesOrderPayment = $installer->getTable('sales_order_payment');
        if ($connection->tableColumnExists($salesOrderPayment, 'ebzc_option') === false) {
            $connection->addColumn($salesOrderPayment,
                'ebzc_option',
                [
                    'type' => Table::TYPE_TEXT,
                    'length' => 100,
                    'nullable' => false,
                    'comment' => 'EBizCharge Payment Option'
                ]
            );
        }

        if ($connection->tableColumnExists($salesOrderPayment, 'ebzc_cust_id') === false) {
            $connection->addColumn($salesOrderPayment,
                'ebzc_cust_id',
                [
                    'type' => Table::TYPE_INTEGER,
                    'length' => 11,
                    'unsigned' => true,
                    'nullable' => false,
                    'comment' => 'EBizCharge Customer ID'
                ]
            );
        }

        if ($connection->tableColumnExists($salesOrderPayment, 'ebzc_method_id') === false) {
            $connection->addColumn($salesOrderPayment,
                'ebzc_method_id',
                [
                    'type' => Table::TYPE_INTEGER,
                    'length' => 11,
                    'unsigned' => true,
                    'nullable' => false,
                    'comment' => 'EBizCharge Payment Method ID'
                ]
            );
        }

        if ($connection->tableColumnExists($salesOrderPayment, 'ebzc_avs_street') === false) {
            $connection->addColumn($salesOrderPayment,
                'ebzc_avs_street',
                [
                    'type' => Table::TYPE_TEXT,
                    'length' => 255,
                    'nullable' => false,
                    'comment' => 'AVS Street'
                ]
            );
        }

        if ($connection->tableColumnExists($salesOrderPayment, 'ebzc_avs_zip') === false) {
            $connection->addColumn($salesOrderPayment,
                'ebzc_avs_zip',
                [
                    'type' => Table::TYPE_TEXT,
                    'length' => 255,
                    'nullable' => false,
                    'comment' => 'AVS Zip'
                ]
            );
        }
        if ($connection->tableColumnExists($salesOrderPayment, 'ebzc_save_payment') === false) {
            $connection->addColumn($salesOrderPayment,
                'ebzc_save_payment',
                [
                    'type' => Table::TYPE_INTEGER,
                    'length' => 1,
                    'unsigned' => true,
                    'nullable' => false,
                    'comment' => 'EBizCharge - Save Payment Info'
                ]
            );
        }

        // Add columns to 'quote_payment' table.
        $quoteTable = $installer->getTable('quote_payment');
        if ($connection->tableColumnExists($quoteTable, 'ebzc_option') === false) {
            $connection->addColumn($quoteTable,
                'ebzc_option',
                [
                    'type' => Table::TYPE_TEXT,
                    'length' => 100,
                    'nullable' => false,
                    'comment' => 'EBizCharge Payment Option'
                ]);
        }
        if ($connection->tableColumnExists($quoteTable, 'ebzc_cust_id') === false) {
            $connection->addColumn($quoteTable,
                'ebzc_cust_id',
                [
                    'type' => Table::TYPE_INTEGER,
                    'length' => 11,
                    'unsigned' => true,
                    'nullable' => false,
                    'comment' => 'EBizCharge Customer ID'
                ]);
        }
        if ($connection->tableColumnExists($quoteTable, 'ebzc_method_id') === false) {
            $connection->addColumn($quoteTable,
                'ebzc_method_id',
                [
                    'type' => Table::TYPE_INTEGER,
                    'length' => 11,
                    'unsigned' => true,
                    'nullable' => false,
                    'comment' => 'EBizCharge Payment Method ID'
                ]);
        }

        if ($connection->tableColumnExists($quoteTable, 'ebzc_avs_street') === false) {
            $connection->addColumn($quoteTable,
                'ebzc_avs_street',
                [
                    'type' => Table::TYPE_TEXT,
                    'length' => 255,
                    'nullable' => false,
                    'comment' => 'AVS Street'
                ]);
        }
        if ($connection->tableColumnExists($quoteTable, 'ebzc_avs_zip') === false) {
            $connection->addColumn($quoteTable,
                'ebzc_avs_zip',
                [
                    'type' => Table::TYPE_TEXT,
                    'length' => 255,
                    'nullable' => false,
                    'comment' => 'AVS Zip'
                ]);
        }

        if ($connection->tableColumnExists($quoteTable, 'ebzc_save_payment') === false) {
            $connection->addColumn($quoteTable, 'ebzc_save_payment',
                [
                    'type' => Table::TYPE_INTEGER,
                    'length' => 1,
                    'unsigned' => true,
                    'nullable' => false,
                    'comment' => 'EBizCharge - Save Payment Info'
                ]);
        }

        //---------- NEW EConnect Fields start -------------
        // Add columns to customer_entity table.
        $customerTable = $installer->getTable('customer_entity');
        if ($connection->tableColumnExists($customerTable, 'ec_cust_sync_status') === false) {
            $connection->addColumn($customerTable,
                'ec_cust_sync_status',
                [
                    'type' => Table::TYPE_TEXT,
                    'length' => 11,
                    'nullable' => true,
                    'comment' => 'Econnect Customer Sync Status'
                ]);
        }
        if ($connection->tableColumnExists($customerTable, 'ec_cust_internalid') === false) {
            $connection->addColumn($customerTable,
                'ec_cust_internalid',
                [
                    'type' => Table::TYPE_TEXT,
                    'length' => 200,
                    'unsigned' => true,
                    'nullable' => true,
                    'comment' => 'Econnect Customer Internalid'
                ]);
        }

        if ($connection->tableColumnExists($customerTable, 'ec_cust_id') === false) {
            $connection->addColumn($customerTable,
                'ec_cust_id',
                [
                    'type' => Table::TYPE_TEXT,
                    'length' => 200,
                    'unsigned' => true,
                    'nullable' => true,
                    'comment' => 'Econnect CustomerID'
                ]);
        }

        if ($connection->tableColumnExists($customerTable, 'ec_cust_token') === false) {
            $connection->addColumn($customerTable,
                'ec_cust_token',
                [
                    'type' => Table::TYPE_TEXT,
                    'length' => 200,
                    'unsigned' => true,
                    'nullable' => true,
                    'comment' => 'Econnect Customer Token/Number'
                ]);
        }
        if ($connection->tableColumnExists($customerTable, 'ec_cust_lastsyncdate') === false) {
            $connection->addColumn($customerTable,
                'ec_cust_lastsyncdate',
                [
                    'type' => Table::TYPE_TIMESTAMP,
                    'default' => null,
                    'length' => 200,
                    'unsigned' => true,
                    'nullable' => true,
                    'comment' => 'Econnect Customer Last Sync Date'
                ]);
        }

        // Add columns to catalog_product_entity table.
        $productTable = $installer->getTable('catalog_product_entity');
        if ($connection->tableColumnExists($productTable, 'ec_item_sync_status') === false) {
            $connection->addColumn($productTable,
                'ec_item_sync_status',
                [
                    'type' => Table::TYPE_TEXT,
                    'length' => 11,
                    'nullable' => true,
                    'comment' => 'Econnect Item Sync Status'
                ]);
        }
        if ($connection->tableColumnExists($productTable, 'ec_item_internalid') === false) {
            $connection->addColumn($productTable,
                'ec_item_internalid',
                [
                    'type' => Table::TYPE_TEXT,
                    'length' => 200,
                    'unsigned' => true,
                    'nullable' => true,
                    'comment' => 'Econnect Item Internalid'
                ]);
        }

        if ($connection->tableColumnExists($productTable, 'ec_item_id') === false) {
            $connection->addColumn($productTable,
                'ec_item_id',
                [
                    'type' => Table::TYPE_TEXT,
                    'length' => 200,
                    'unsigned' => true,
                    'nullable' => true,
                    'comment' => 'Econnect ItemId'
                ]);
        }

        if ($connection->tableColumnExists($productTable, 'ec_item_lastsyncdate') === false) {
            $connection->addColumn($productTable,
                'ec_item_lastsyncdate',
                [
                    'type' => Table::TYPE_TIMESTAMP,
                    'default' => null,
                    'length' => 200,
                    'unsigned' => true,
                    'nullable' => true,
                    'comment' => 'Econnect Item Last Sync Date'
                ]);
        }

        // Add columns to sales_order table.
        $orderTable = $installer->getTable('sales_order');
        if ($connection->tableColumnExists($orderTable, 'ec_order_sync_status') === false) {
            $connection->addColumn($orderTable,
                'ec_order_sync_status',
                [
                    'type' => Table::TYPE_TEXT,
                    'length' => 11,
                    'nullable' => true,
                    'comment' => 'Econnect Order Sync Status'
                ]);
        }

        if ($connection->tableColumnExists($orderTable, 'ec_order_internalid') === false) {
            $connection->addColumn($orderTable,
                'ec_order_internalid',
                [
                    'type' => Table::TYPE_TEXT,
                    'length' => 200,
                    'unsigned' => true,
                    'nullable' => true,
                    'comment' => 'Econnect Order Internalid'
                ]);
        }
        if ($connection->tableColumnExists($orderTable, 'ec_order_id') === false) {
            $connection->addColumn($orderTable,
                'ec_order_id',
                [
                    'type' => Table::TYPE_TEXT,
                    'length' => 200,
                    'unsigned' => true,
                    'nullable' => true,
                    'comment' => 'Econnect OrderId'
                ]);
        }

        if ($connection->tableColumnExists($orderTable, 'ec_cust_id') === false) {
            $connection->addColumn($orderTable,
                'ec_cust_id',
                [
                    'type' => Table::TYPE_TEXT,
                    'length' => 200,
                    'unsigned' => true,
                    'nullable' => true,
                    'comment' => 'Econnect CustId'
                ]);
        }

        if ($connection->tableColumnExists($orderTable, 'ec_order_lastsyncdate') === false) {
            $connection->addColumn($orderTable,
                'ec_order_lastsyncdate',
                [
                    'type' => Table::TYPE_TIMESTAMP,
                    'default' => null,
                    'length' => 200,
                    'unsigned' => true,
                    'nullable' => true,
                    'comment' => 'Econnect Order Last Sync Date'
                ]);
        }

        // Add columns to sales_invoice' table.
        $invoiceTable = $installer->getTable('sales_invoice');
        if ($connection->tableColumnExists($invoiceTable, 'ec_invoice_sync_status') === false) {
            $connection->addColumn($invoiceTable,
                'ec_invoice_sync_status',
                [
                    'type' => Table::TYPE_TEXT,
                    'length' => 11,
                    'nullable' => true,
                    'comment' => 'Econnect Invoice Sync Status'
                ]);
        }

        if ($connection->tableColumnExists($invoiceTable, 'ec_invoice_internalid') === false) {
            $connection->addColumn($invoiceTable,
                'ec_invoice_internalid',
                [
                    'type' => Table::TYPE_TEXT,
                    'length' => 200,
                    'unsigned' => true,
                    'nullable' => true,
                    'comment' => 'Econnect Invoice Internalid'
                ]);
        }
        if ($connection->tableColumnExists($invoiceTable, 'ec_invoice_id') === false) {
            $connection->addColumn($invoiceTable,
                'ec_invoice_id',
                [
                    'type' => Table::TYPE_TEXT,
                    'length' => 200,
                    'unsigned' => true,
                    'nullable' => true,
                    'comment' => 'Econnect Invoice'
                ]);
        }

        if ($connection->tableColumnExists($invoiceTable, 'ec_cust_id') === false) {
            $connection->addColumn($invoiceTable,
                'ec_cust_id',
                [
                    'type' => Table::TYPE_TEXT,
                    'length' => 200,
                    'unsigned' => true,
                    'nullable' => true,
                    'comment' => 'Econnect CustId'
                ]);
        }

        if ($connection->tableColumnExists($invoiceTable, 'ec_invoice_lastsyncdate') === false) {
            $connection->addColumn($invoiceTable,
                'ec_invoice_lastsyncdate',
                [
                    'type' => Table::TYPE_TIMESTAMP,
                    'default' => null,
                    'length' => 200,
                    'unsigned' => true,
                    'nullable' => true,
                    'comment' => 'Econnect Invoice Last Sync Date'
                ]);
        }
        //---------- NEW EConnect Fields End -------------
    }
}