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
use Ebizcharge\Ebizcharge\Api\Data\OrderSubscriptionInterface;
use Magento\Framework\DataObject\IdentityInterface;
use Ebizcharge\Ebizcharge\Model\ResourceModel\OrderSubscription as OrderSubscriptionResourceModel;

/**
 * Ebizcharge Order Model
 *
 * Class Recurring
 */
class OrderSubscription extends AbstractModel implements OrderSubscriptionInterface, IdentityInterface
{
    const CACHE_TAG = 'ebizcharge_Order';

    protected $_cacheTag = self::CACHE_TAG;

    /**
     * Initialize resource model.
     */
    protected function _construct()
    {
        $this->_init(OrderSubscriptionResourceModel::class);
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
    public function getId(): ?int
    {
        return $this->getData(self::ID) == null ? null : (int)$this->getData(self::ID);
    }


    public function setRecurringId(int $recurring_id): OrderSubscriptionInterface
    {
        return $this->setData(self::RECURRING_ID, $recurring_id);

    }

    public function getRecurringId(): int
    {
        return (int)$this->getData(self::RECURRING_ID);
    }

    public function setRecurringOrderId(int $rec_order_id): OrderSubscriptionInterface
    {
        return $this->setData(self::REC_ORDER_ID, $rec_order_id);

    }

    public function getRecurringOrderId(): int
    {
        return (int)$this->getData(self::REC_ORDER_ID);
    }

    public function setOrderCreatedDate($created_date): OrderSubscriptionInterface
    {
        return $this->setData(self::CREATED_DATE, $created_date);
    }

    public function getOrderCreatedDate()
    {
        return $this->getData(self::CREATED_DATE);
    }

    public function setOrderMessage($message): OrderSubscriptionInterface
    {
        return $this->setData(self::MESSAGE, $message);
    }

    public function getOrderMessage(): string
    {
        return $this->getData(self::MESSAGE);
    }

    public function setOrderStatus(int $status): OrderSubscriptionInterface
    {
        return $this->setData(self::STATUS, $status);
    }

    public function getOrderStatus(): int
    {
        return $this->getData(self::STATUS);
    }

    public function setRecurringOrderDate($recurring_date): OrderSubscriptionInterface
    {
        return $this->setData(self::ORDER_DATE, $recurring_date);
    }

    public function getRecurringOrderDate()
    {
        return $this->getData(self::ORDER_DATE);
    }

    public function setOrderEntityId(int $order_entity_id): OrderSubscriptionInterface
    {
        return $this->setData(self::ORDER_ENTITY_ID, $order_entity_id);
    }

    public function getOrderEntityId(): int
    {
        return (int)$this->getData(self::ORDER_ENTITY_ID);
    }


}
