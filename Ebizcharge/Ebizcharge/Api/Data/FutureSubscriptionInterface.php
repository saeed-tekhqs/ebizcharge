<?php
/**
 * @author Century Business Solutions <support@centurybizsolutions.com>
 * @copyriht Copyright (c) 2021 Century Business Solutions  (www.centurybizsolutions.com)
 * Created by PhpStorm
 * User: Mobeen
 * Date: 6/16/21
 * Time: 2:07 PM
 */
declare(strict_types=1);

namespace Ebizcharge\Ebizcharge\Api\Data;

interface FutureSubscriptionInterface
{
    const ID = 'id';

    const RECURRING_ID = 'recurring_id';

    const RECURRING_DATE = 'recurring_date';

    /**
     * @param int $recurring_id
     * @return $this
     */
    public function setRecurringId(int $recurring_id): FutureSubscriptionInterface;

    /**
     * @return int
     */
    public function getRecurringId(): int;

    /**
     * @param string $recurring_date
     * @return $this
     */
    public function setRecurringDate($recurring_date): FutureSubscriptionInterface;

    /**
     * @return string
     */
    public function getRecurringDate();

}
