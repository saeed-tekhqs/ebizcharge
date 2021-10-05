<?php
/**
 * @author Century Business Solutions <support@centurybizsolutions.com>
 * @copyriht Copyright (c) 2021 Century Business Solutions  (www.centurybizsolutions.com)
 * Created by PhpStorm
 * User: Mobeen
 * Date: 6/23/21
 * Time: 12:54 PM
 */
declare(strict_types=1);

namespace Ebizcharge\Ebizcharge\Model;

use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Api\SearchResultsInterface;
use Magento\Framework\Data\Collection;
use Magento\Search\Model\Search;

/**
 * This class provides basic repository functionality to other classes
 *
 * Class AbstractRepository
 */
abstract class AbstractRepository
{
    /**
     * @var 'model factory class to get single record'
     */
    protected $modelFactory;

    /**
     * @var 'Search factory'
     */
    protected $searchFactory;

    /**
     * @var 'Collection factory for get list method'
     */
    protected $collectionFactory;

    /**
     * @var 'Resource model class to save records'
     */
    protected $resource;

    /**
     * @var 'Logger to log error and exceptions'
     */
    protected $logger;


    /**
     * save a record
     * @param $record
     * @return mixed|null
     */
    protected function saveRecord($record)
    {
        try {
            $this->resource->save($record);
            return $record;
        } catch (\Exception $exception) {
            $this->logger->debug($exception->getMessage());
        }
        return null;
    }

    /**
     * @param $entityId
     * @param null $field
     * @return mixed|null
     */
    protected function getRecordById($entityId, $field = null)
    {
        try {
            $record = $this->modelFactory->create();
            $this->resource->load($record, $entityId, $field);
            return $record;
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
        }
        return null;
    }

    /**
     * @param SearchCriteriaInterface $searchCriteria
     * @return mixed
     */
    public function getList(SearchCriteriaInterface $searchCriteria)
    {

        /**
        * @var SearchResultsInterface $searchResults
         */
        $searchResults = $this->modelFactory->create();
        $searchResults->setSearchCriteria($searchCriteria);

        /**
         * @var Collection $collection
         */
        $collection = self::searchList($searchCriteria, $this->collectionFactory->create());
        $searchResults->setTotalCount($collection->getSize());
        $searchResults->setItems($collection->getItems());
        return $searchResults;
    }

    /**
     * @param SearchCriteriaInterface $searchCriteria
     * @param $collection
     * @return mixed
     */
    public static function searchList(SearchCriteriaInterface $searchCriteria, $collection){
        foreach ($searchCriteria->getFilterGroups() as $filterGroup) {
            foreach ($filterGroup->getFilters() as $filter) {
                if (is_array($filter->getValue())) {
                    $collection->addFieldToFilter($filter->getField(), $filter->getValue());
                } else {
                    $condition = $filter->getConditionType() ?: 'eq';
                    $collection->addFieldToFilter($filter->getField(), [$condition => $filter->getValue()]);
                }
            }
        }
        return $collection;
    }

}
