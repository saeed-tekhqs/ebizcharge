<?php
/**
 * @author Century Business Solutions <support@centurybizsolutions.com>
 * @copyriht Copyright (c) 2021 Century Business Solutions  (www.centurybizsolutions.com)
 * Created by PhpStorm
 * User: Mobeen
 * Date: 6/22/21
 * Time: 12:26 PM
 */
declare(strict_types=1);

namespace Ebizcharge\Ebizcharge\Api;

use Ebizcharge\Ebizcharge\Api\Data\RecurringInterface;
use Magento\Framework\Api\SearchCriteriaInterface;

/**
 * This interface contains operations related to ebizcharge_recurring table
 *
 * Interface RecurringRepositoryInterface
 * @api
 * @since 100.0.2
 */
interface RecurringRepositoryInterface
{
    /**
     * Save recurring records
     *
     * @param RecurringInterface $recurringRecord
     * @return RecurringInterface|false
     */
    public function save(RecurringInterface $recurringRecord): ?RecurringInterface;

    /**
     * Retrieve a specific record
     *
     * @param $entityId
     * @param null $field
     * @return RecurringInterface|false
     */
    public function getById($entityId, $field = null): ?RecurringInterface;

    /**
     * Retrieve records matching the specified criteria.
     *
     * @param SearchCriteriaInterface $searchCriteria
     * @return mixed
     */
    public function getList(SearchCriteriaInterface $searchCriteria);


}
