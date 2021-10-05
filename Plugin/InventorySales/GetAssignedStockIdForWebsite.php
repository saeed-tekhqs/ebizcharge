<?php
/**
 * @author Century Business Solutions <support@centurybizsolutions.com>
 * @copyriht Copyright (c) 2021 Century Business Solutions  (www.centurybizsolutions.com)
 * Created by PhpStorm
 * User: Mobeen
 * Date: 8/2/21
 * Time: 4:32 PM
 */
declare(strict_types=1);

namespace Ebizcharge\Ebizcharge\Plugin\InventorySales;

use Magento\Framework\App\ResourceConnection;
use Magento\InventorySales\Model\ResourceModel\GetAssignedStockIdForWebsite as GetAssignedStockIdForWebsiteCore;
use Magento\InventorySalesApi\Api\Data\SalesChannelInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 * This class fixes an issue from magento core MSI (Multi Source Inventory)
 * https://github.com/magento/inventory/issues/2269
 * https://cutt.ly/KQzbbpr
 *
 * Class GetAssignedStockIdForWebsite
 * @package Ebizcharge\Ebizcharge\Plugin
 */
class GetAssignedStockIdForWebsite
{
    const DEFAULT_WEBSITE_CODE =  'base';

    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @param ResourceConnection $resourceConnection
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        ResourceConnection $resourceConnection,
        StoreManagerInterface $storeManager
    ) {
        $this->resourceConnection = $resourceConnection;
        $this->storeManager = $storeManager;
    }

    /**
     * @param GetAssignedStockIdForWebsiteCore $subject
     * @param ?int $result
     * @param string $websiteCode
     * @return ?int
     */
    public function afterExecute(
        GetAssignedStockIdForWebsiteCore $subject,
        ?int $result,
        string $websiteCode
    ) {

        if($result == null) {
            try {
                $websiteCode = $this->storeManager->getWebsite()->getCode();
            } catch (\Exception $e) {
                $websiteCode = self::DEFAULT_WEBSITE_CODE;
            }
            $result = $this->getStockIdForWebsite($websiteCode)??1;

        }
        return $result;
    }

    /**
     * Get stock id by website code
     *
     * @param string $websiteCode
     * @return int|null
     */
    private function getStockIdForWebsite(string $websiteCode): ?int
    {
        $connection = $this->resourceConnection->getConnection();
        $tableName = $this->resourceConnection->getTableName('inventory_stock_sales_channel');

        $select = $connection->select()
            ->from($tableName, ['stock_id'])
            ->where('code = ?', $websiteCode)
            ->where('type = ?', SalesChannelInterface::TYPE_WEBSITE);

        $result = $connection->fetchCol($select);

        if (count($result) === 0) {
            return null;
        }
        return (int)reset($result);
    }
}
