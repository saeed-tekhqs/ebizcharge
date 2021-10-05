<?php
/**
 * @author Century Business Solutions <support@centurybizsolutions.com>
 * @copyriht Copyright (c) 2021 Century Business Solutions  (www.centurybizsolutions.com)
 * Created by PhpStorm
 * User: Mobeen
 * Date: 6/23/21
 * Time: 9:57 AM
 */
declare(strict_types=1);

namespace Ebizcharge\Ebizcharge\Api;

use Ebizcharge\Ebizcharge\Api\Data\FutureSubscriptionInterface;
use Magento\Framework\Api\SearchCriteriaInterface;

/**
 * This interface contains operations related to ebizcharge_recurring_dates table
 *
 * Interface FutureSubscriptionRepositoryInterface
 * @api
 * @since 100.0.2
 */
interface FutureSubscriptionRepositoryInterface
{
    /**
     * Save subscription records
     *
     * @param FutureSubscriptionInterface $subscription
     * @return FutureSubscriptionInterface|false
     */
    public function save(FutureSubscriptionInterface $subscription): ?FutureSubscriptionInterface;

    /**
     * Retrieve a specific subscription record
     *
     * @param $entityId
     * @param null $field
     * @return FutureSubscriptionInterface|false
     */
    public function getById($entityId, $field = null): ?FutureSubscriptionInterface;

    /**
     * Retrieve records matching the specified criteria.
     *
     * @param SearchCriteriaInterface $searchCriteria
     * @return mixed
     */
    public function getList(SearchCriteriaInterface $searchCriteria);


}
