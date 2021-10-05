<?php
/**
 * Create the 'token' table, add proper fields to the 'sales_order_payment' table,
 * and add proper fields to the 'quote_payment' table.
 *
 * @author      Century Business Solutions <support@centurybizsolutions.com>
 * @copyright   Copyright (c) 2020 Century Business Solutions  (www.centurybizsolutions.com)
 */

namespace Ebizcharge\Ebizcharge\Setup;

use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\DB\Ddl\Table;
use Ebizcharge\Ebizcharge\Setup\EbizchargeSchema;

/**
 * Upgrade the Catalog module DB scheme
 */
class UpgradeSchema implements UpgradeSchemaInterface
{
    public $ebizchargeSchema;

    public function __construct(EbizchargeSchema $schema)
    {
        $this->ebizchargeSchema = $schema;
    }

    public function newVersion()
    {
        return '2.4.2';
    }

    public function upgrade(SchemaSetupInterface $installer, ModuleContextInterface $context)
    {
        $installer->startSetup();

        if (version_compare($context->getVersion(), $this->newVersion(), '<=')) {
            $this->ebizchargeSchema->install($installer);
        }

        $installer->endSetup();
    }
}