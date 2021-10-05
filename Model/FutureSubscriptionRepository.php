<?php
/**
 * @author Century Business Solutions <support@centurybizsolutions.com>
 * @copyriht Copyright (c) 2021 Century Business Solutions  (www.centurybizsolutions.com)
 * Created by PhpStorm
 * User: Mobeen
 * Date: 6/23/21
 * Time: 1:09 PM
 */
declare(strict_types=1);

namespace Ebizcharge\Ebizcharge\Model;

use Ebizcharge\Ebizcharge\Api\FutureSubscriptionRepositoryInterface;
use Ebizcharge\Ebizcharge\Model\ResourceModel\FutureSubscription as Resource;
use Ebizcharge\Ebizcharge\Api\Data\FutureSubscriptionSearchInterfaceFactory as SearchFactory;
use Ebizcharge\Ebizcharge\Model\ResourceModel\FutureSubscription\CollectionFactory as CollectionFactory;
use Ebizcharge\Ebizcharge\Model\FutureSubscriptionFactory as ModelFactory;
use Ebizcharge\Ebizcharge\Api\Data\FutureSubscriptionInterface;
use Psr\Log\LoggerInterface;

/**
 * This repository contains operations related to ebizcharge_recurring_dates table
 *
 * Class FutureSubscriptionRepository
 */
class FutureSubscriptionRepository extends AbstractRepository implements FutureSubscriptionRepositoryInterface
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
    public function save(FutureSubscriptionInterface $subscription): ?FutureSubscriptionInterface
    {
        return parent::saveRecord($subscription);
    }

    /**
     * @inheritDoc
     */
    public function getById($entityId, $field = null): ?FutureSubscriptionInterface
    {
       return parent::getRecordById($entityId, $field = null);
    }
}
