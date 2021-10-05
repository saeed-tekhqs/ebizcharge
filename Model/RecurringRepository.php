<?php
/**
 * @author Century Business Solutions <support@centurybizsolutions.com>
 * @copyriht Copyright (c) 2021 Century Business Solutions  (www.centurybizsolutions.com)
 * Created by PhpStorm
 * User: Mobeen
 * Date: 6/22/21
 * Time: 12:42 PM
 */
declare(strict_types=1);

namespace Ebizcharge\Ebizcharge\Model;

use Ebizcharge\Ebizcharge\Api\Data\RecurringInterface;
use Ebizcharge\Ebizcharge\Api\RecurringRepositoryInterface;
use Ebizcharge\Ebizcharge\Model\RecurringFactory as ModelFactory;
use Ebizcharge\Ebizcharge\Model\ResourceModel\Recurring as Resource;
use Ebizcharge\Ebizcharge\Api\Data\RecurringSearchResultInterfaceFactory as SearchFactory;
use Ebizcharge\Ebizcharge\Model\ResourceModel\Recurring\CollectionFactory as CollectionFactory;
use Psr\Log\LoggerInterface;

/**
 * Contains operations related to ebizcharge_recurring table
 *
 * Class RecurringRepository
 */
class RecurringRepository extends AbstractRepository implements RecurringRepositoryInterface
{
    /**
     * @param ModelFactory $modelFactory
     * @param SearchFactory $searchFactory
     * @param CollectionFactory $collectionFactory
     * @param Resource $resource
     * @param LoggerInterface $logger
     */
    public function __construct(
        ModelFactory $modelFactory,
        SearchFactory $searchFactory,
        CollectionFactory $collectionFactory,
        Resource $resource,
        LoggerInterface $logger
    ) {
        $this->modelFactory = $modelFactory;
        $this->searchFactory = $searchFactory;
        $this->collectionFactory = $collectionFactory;
        $this->resource = $resource;
        $this->logger = $logger;
    }

    /**
     * @inheritDoc
     */
    public function save(RecurringInterface $recurringRecord):? RecurringInterface
    {
        return parent::saveRecord($recurringRecord);
    }

    /**
     * @inheritDoc
     */
    public function getById($entityId, $field = null):? RecurringInterface
    {
        return parent::getRecordById($entityId, $field);
    }
}
