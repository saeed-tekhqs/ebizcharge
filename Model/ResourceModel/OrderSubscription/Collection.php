<?php
/**
 * @author Century Business Solutions <support@centurybizsolutions.com>
 * @copyriht Copyright (c) 2021 Century Business Solutions  (www.centurybizsolutions.com)
 * Created by PhpStorm
 * User: Mobeen
 * Date: 6/7/21
 * Time: 4:24 PM
 */
declare(strict_types=1);

namespace Ebizcharge\Ebizcharge\Model\ResourceModel\OrderSubscription;

use Magento\Framework\Api\Search\AggregationInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Ebizcharge\Ebizcharge\Model\ResourceModel\OrderSubscription as OrderSubscriptionResourceModel;
use Ebizcharge\Ebizcharge\Model\OrderSubscription as OrderSubscriptionModel;
use Magento\Framework\Api\Search\SearchResultInterface;

/**
 * Collection for ebizcharge_recurring table
 * Class Collection
 */
class Collection extends AbstractCollection implements SearchResultInterface
{
    /**
     * @var string
     */
    protected $_idFieldName = 'id';

    /**
     * Event prefix
     * @var string
     */
    protected $_eventPrefix = 'ebiz_Order_collection';

    /**
     * Event object
     * @var string
     */
    protected $_eventObject = 'Order_collection';

    /**
     * @var AggregationInterface
     */
    protected $aggregations;

    public function __construct(
        \Magento\Framework\Data\Collection\EntityFactoryInterface $entityFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Framework\DB\Adapter\AdapterInterface $connection = null,
        \Magento\Framework\Model\ResourceModel\Db\AbstractDb $resource = null
    ) {
        $this->_init(OrderSubscriptionModel::class, OrderSubscriptionResourceModel::class);
        parent::__construct($entityFactory, $logger, $fetchStrategy, $eventManager, $connection, $resource);
    }

    public function setItems(array $items = null)
    {
        return $this;
    }

    /**
     * Get aggregations
     * @return AggregationInterface
     */
    public function getAggregations()
    {
        return $this->aggregations;
    }

    /**
     * get aggregation
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
     * Set search criteria.
     *
     * @param SearchCriteriaInterface|null $searchCriteria
     * @return SearchResultInterface
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
     *
     * @param int $totalCount
     * @return $this|SearchResultInterface
     */
    public function setTotalCount($totalCount)
    {
        return $this;
    }
}
