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

namespace Ebizcharge\Ebizcharge\Model\ResourceModel\Recurring\Grid;

use Ebizcharge\Ebizcharge\Model\ResourceModel\Recurring\Collection as RecurringCollection;
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
class Collection extends RecurringCollection implements SearchResultInterface
{
    /**
     * @var AggregationInterface
     */
    private $aggregations;

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
        $this->addFilterToMap('eb_rec_next_formatted', 'main_table.eb_rec_next');
        $this->addFilterToMap('start_date_formatted', 'main_table.eb_rec_start_date');
        $this->addFilterToMap('end_date_formatted', 'main_table.eb_rec_end_date');
        $this->addFilterToMap('customer_email', 'customer_entity.email');
        $this->addFilterToMap('product_sku', 'catalog_product_entity.sku');
        $this->addFilterToMap('customer_name',
            new \Zend_Db_Expr(
                'CONCAT(customer_entity.firstname, " ", customer_entity.lastname)'
            ));
        parent::_initSelect();
    }

    /**
     * Render Collection
     */
    protected function _renderFiltersBefore()
    {
        $customerEntity = $this->getTable('customer_entity');
        $customerAddressEntity = $this->getTable('customer_address_entity');
        $catalogProductEntity = $this->getTable('catalog_product_entity');
        $this->getSelect()
            ->joinLeft(
                ['customer_entity' => $customerEntity],
                "main_table.mage_cust_id = customer_entity.entity_id",
                [
                    'eb_rec_next_formatted' => "date(main_table.eb_rec_next)",
                    'start_date_formatted' => "date(main_table.eb_rec_start_date)",
                    'end_date_formatted' => "date(main_table.eb_rec_end_date)",
                    'customer_email' => "customer_entity.email",
                    'customer_name' => "CONCAT(customer_entity.firstname, ' ', customer_entity.lastname)"
                ]
            )
            ->joinLeft(
                ['customer_address_entity' => $customerAddressEntity],
                "main_table.shipping_address_id = customer_address_entity.entity_id",
                [
                    'customer_shipping_address' => "CONCAT(customer_address_entity.street, ' ', customer_address_entity.city, ' ', customer_address_entity.region, ' ', customer_address_entity.postcode)"
                ]
            )->joinLeft(
                ['catalog_product_entity' => $catalogProductEntity],
                "main_table.mage_item_id = catalog_product_entity.entity_id",
                [
                    'product_sku' => "catalog_product_entity.sku"
                ]
            );
        parent::_renderFiltersBefore();
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
