<?php
/**
 * @author Century Business Solutions <support@centurybizsolutions.com>
 * @copyriht Copyright (c) 2021 Century Business Solutions  (www.centurybizsolutions.com)
 * Created by PhpStorm
 * User: Mobeen
 * Date: 6/7/21
 * Time: 9:08 PM
 */
declare(strict_types=1);

namespace Ebizcharge\Ebizcharge\Model\ResourceModel\OrderSubscription\Grid;

use Ebizcharge\Ebizcharge\Model\ResourceModel\OrderSubscription\Collection as OrderSubscriptionCollection;
use Magento\Framework\Api\Search\AggregationInterface;
use Magento\Framework\Api\Search\SearchResultInterface;
use Magento\Framework\Data\Collection\Db\FetchStrategyInterface;
use Magento\Framework\Data\Collection\EntityFactoryInterface;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\View\Element\UiComponent\DataProvider\Document;
use Magento\Framework\Api\SearchCriteriaInterface;
use Psr\Log\LoggerInterface;

/**
 * This is grid collection for ebizcharge_recurring table
 *
 * Class Collection
 */
class Collection extends OrderSubscriptionCollection implements SearchResultInterface
{
    /**
     * @var AggregationInterface
     */
    protected $aggregations;

    /**
     * @param EntityFactoryInterface $entityFactory
     * @param LoggerInterface $logger
     * @param FetchStrategyInterface $fetchStrategy
     * @param ManagerInterface $eventManager
     * @param $mainTable
     * @param $eventPrefix
     * @param $eventObject
     * @param $resourceModel
     * @param string $model
     * @param AdapterInterface|null $connection
     * @param AbstractDb|null $resource
     */
    public function __construct(
        EntityFactoryInterface $entityFactory,
        LoggerInterface $logger,
        FetchStrategyInterface $fetchStrategy,
        ManagerInterface $eventManager,
        $mainTable,
        $resourceModel,
        $model = Document::class,
        AdapterInterface $connection = null,
        AbstractDb $resource = null
    ) {
        parent::__construct($entityFactory, $logger, $fetchStrategy, $eventManager, $connection, $resource);
        $this->_init($model, $resourceModel);
        $this->setMainTable($mainTable);
    }

    protected function _initSelect()
    {
        parent::_initSelect();
        $this->getSelect()->joinLeft(
            ['ebizcharge_recurring' => $this->getTable('ebizcharge_recurring')],
            'main_table.recurring_id = ebizcharge_recurring.rec_id',
            [
                '*'
            ]
        )->joinLeft(
            ['customer_entity'=> $this->getTable('customer_entity')],
            "ebizcharge_recurring.mage_cust_id = customer_entity.entity_id",
            [
                '*',
                'customer_name' => "CONCAT(customer_entity.firstname, ' ', customer_entity.lastname)"
            ]
        )->joinLeft(
            ['catalog_product_entity'=> $this->getTable('catalog_product_entity')],
            "ebizcharge_recurring.mage_item_id = catalog_product_entity.entity_id",
            [
                '*'
            ]
        );
        $this->addFilterToMap('customer_name', 'customer_entity.lastname');
        return $this;
    }

    /**
     * @return AggregationInterface
     */
    public function getAggregations()
    {
        return $this->aggregations;
    }

    /**
     * set aggregation
     * @param AggregationInterface $aggregations
     * @return void
     */
    public function setAggregations($aggregations)
    {
        $this->aggregations = $aggregations;
    }

    /**
     * Get search criteria.
     * @return null
     */
    public function getSearchCriteria()
    {
        return null;
    }

    /**
     * set search cretria
     * @param SearchCriteriaInterface|null $searchCriteria
     * @return $this|Collection|SearchResultInterface
     */
    public function setSearchCriteria(SearchCriteriaInterface $searchCriteria = null)
    {
        return $this;
    }

    /**
     * Get total count.
     *
     * @return int
     */
    public function getTotalCount()
    {
        return $this->getSize();
    }

    /**
     * Set total count.
     * @param int $totalCount
     * @return $this|Collection|SearchResultInterface
     */
    public function setTotalCount($totalCount)
    {
        return $this;
    }

    /**
     * Set items list.
     * @param array|null $items
     * @return $this|Collection|SearchResultInterface
     */
    public function setItems(array $items = null)
    {
        return $this;
    }

}
