<?php
/**
 * @author Century Business Solutions <support@centurybizsolutions.com>
 * @copyriht Copyright (c) 2021 Century Business Solutions  (www.centurybizsolutions.com)
 * Created by PhpStorm
 * User: Mobeen
 * Date: 6/30/21
 * Time: 5:46 PM
 */
declare(strict_types=1);

namespace Ebizcharge\Ebizcharge\Api;

use Ebizcharge\Ebizcharge\Api\Data\OrderSubscriptionInterface;
use Magento\Framework\Api\SearchCriteriaInterface;

interface OrderSubscriptionRepositoryInterface
{
    /**
     * Save order subscription records
     *
     * @param OrderSubscriptionInterface $subscription
     * @return OrderSubscriptionInterface|false
     */
    public function save(OrderSubscriptionInterface $subscription): ?OrderSubscriptionInterface;

    /**
     * Retrieve a specific order subscription record
     *
     * @param $entityId
     * @param null $field
     * @return OrderSubscriptionInterface|false
     */
    public function getById($entityId, $field = null): ?OrderSubscriptionInterface;

    /**
     * Retrieve records matching the specified criteria.
     *
     * @param SearchCriteriaInterface $searchCriteria
     * @return mixed
     */
    public function getList(SearchCriteriaInterface $searchCriteria);

}
