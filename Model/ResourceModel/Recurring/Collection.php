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

namespace Ebizcharge\Ebizcharge\Model\ResourceModel\Recurring;

use Magento\Framework\Api\Search\AggregationInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Ebizcharge\Ebizcharge\Model\ResourceModel\Recurring as RecurringResourceModel;
use Ebizcharge\Ebizcharge\Model\Recurring as RecurringModel;
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
    protected $_idFieldName = 'rec_id';

    /**
     * Event prefix
     * @var string
     */
    protected $_eventPrefix = 'ebiz_recurring_collection';

    /**
     * Event object
     * @var string
     */
    protected $_eventObject = 'recurring_collection';

    /**
     * @var AggregationInterface
     */
    private $aggregations;

    /**
     * Define resource model.
     */
    public function _construct()
    {
        $this->_init(RecurringModel::class, RecurringResourceModel::class);
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

    /**
     * Update Data for given condition for collection
     *
     * @param $condition
     * @param $columnData
     * @return bool|string
     */
    public function setTableRecords($columnData, $condition)
    {
        $connection = $this->getConnection();
        try {
            $connection->beginTransaction();
            $connection->update(
                $this->getMainTable(),
                $columnData,
                $where = $condition
            );
            $connection->commit();
        } catch (\Exception $e) {
            $connection->rollBack();
            return $e->getMessage();
        }
        return true;
    }

}
