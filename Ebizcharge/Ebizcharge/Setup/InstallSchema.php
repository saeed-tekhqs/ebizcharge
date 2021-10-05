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

class InstallSchema implements InstallSchemaInterface
{
    public $ebizchargeSchema;

    public function __construct(EbizchargeSchema $schema)
    {
        $this->ebizchargeSchema = $schema;
    }

    public function install(SchemaSetupInterface $installer, ModuleContextInterface $context)
    {
        $installer->startSetup();

        $this->ebizchargeSchema->install($installer);

        $installer->endSetup();

    }
}