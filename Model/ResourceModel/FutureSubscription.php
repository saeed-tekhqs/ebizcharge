<?php
/**
 * @author Century Business Solutions <support@centurybizsolutions.com>
 * @copyriht Copyright (c) 2021 Century Business Solutions  (www.centurybizsolutions.com)
 * Created by PhpStorm
 * User: Mobeen
 * Date: 6/7/21
 * Time: 3:42 PM
 */
declare(strict_types=1);

namespace Ebizcharge\Ebizcharge\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

/**
 * Resource Model for ebizcharge_recurring_dates table
 *
 * Class Recurring
 */
class FutureSubscription extends AbstractDb
{

    /**
     * @inheritDoc
     */
    protected function _construct()
    {
        $this->_init('ebizcharge_recurring_dates', 'id');
    }
}
