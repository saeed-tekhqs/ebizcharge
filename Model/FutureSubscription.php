<?php
/**
 * @author Century Business Solutions <support@centurybizsolutions.com>
 * @copyriht Copyright (c) 2021 Century Business Solutions  (www.centurybizsolutions.com)
 * Created by PhpStorm
 * User: Mobeen
 * Date: 6/7/21
 * Time: 3:55 PM
 */
declare(strict_types=1);

namespace Ebizcharge\Ebizcharge\Model;

use Magento\Framework\Model\AbstractModel;
use Ebizcharge\Ebizcharge\Api\Data\FutureSubscriptionInterface;
use Magento\Framework\DataObject\IdentityInterface;
use Ebizcharge\Ebizcharge\Model\ResourceModel\FutureSubscription as FutureSubscriptionResourceModel;

/**
 * Ebizcharge Future Model
 *
 * Class Recurring
 */
class FutureSubscription extends AbstractModel implements FutureSubscriptionInterface, IdentityInterface
{
    const CACHE_TAG = 'ebizcharge_future';

    protected $_cacheTag = self::CACHE_TAG;

    /**
     * Initialize resource model.
     */
    protected function _construct()
    {
        $this->_init(FutureSubscriptionResourceModel::class);
    }

    /**
     * Return unique ID(s) for each object in system
     *
     * @return string[]
     */
    public function getIdentities(): array
    {
        return [self::CACHE_TAG . '_' . $this->getId(), self::CACHE_TAG . '_' . $this->getId()];
    }

    /**
     * @inheritDoc
     */
    public function getId(): int
    {
        return (int)$this->getData(self::ID);
    }


    public function setRecurringId(int $recurring_id): FutureSubscriptionInterface
    {
        return $this->setData(self::RECURRING_ID, $recurring_id);

    }

    public function getRecurringId(): int
    {
        return (int)$this->getData(self::RECURRING_ID);
    }

    public function setRecurringDate($recurring_date): FutureSubscriptionInterface
    {
        return $this->setData(self::RECURRING_DATE, $recurring_date);
    }

    public function getRecurringDate()
    {
        return $this->getData(self::RECURRING_DATE);
    }
}
