<?php
/**
 * @author Century Business Solutions <support@centurybizsolutions.com>
 * @copyriht Copyright (c) 2021 Century Business Solutions  (www.centurybizsolutions.com)
 * Created by PhpStorm
 * User: Mobeen
 * Date: 6/30/21
 * Time: 4:51 PM
 */
declare(strict_types=1);

namespace Ebizcharge\Ebizcharge\Model;

use Ebizcharge\Ebizcharge\Api\Data\OrderSubscriptionInterface;
use Ebizcharge\Ebizcharge\Api\OrderSubscriptionRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Ebizcharge\Ebizcharge\Model\ResourceModel\OrderSubscription as Resource;
use Ebizcharge\Ebizcharge\Api\Data\FutureSubscriptionSearchInterfaceFactory as SearchFactory;
use Ebizcharge\Ebizcharge\Model\ResourceModel\OrderSubscription\CollectionFactory as CollectionFactory;
use Ebizcharge\Ebizcharge\Model\OrderSubscriptionFactory as ModelFactory;
use Psr\Log\LoggerInterface;

/**
 * This repository contains operations related to ebizcharge_recurring_order table
 *
 * Class OrderSubscriptionRepository
 */
class OrderSubscriptionRepository extends AbstractRepository implements OrderSubscriptionRepositoryInterface
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
    public function save(OrderSubscriptionInterface $subscription): ?OrderSubscriptionInterface
    {
        return parent::saveRecord($subscription);
    }

    /**
     * @inheritDoc
     */
    public function getById($entityId, $field = null): ?OrderSubscriptionInterface
    {
        return parent::getRecordById($entityId, $field);
    }
}
