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

interface OrderSubscriptionInterface
{
    const ID = 'id';

    const RECURRING_ID = 'recurring_id';

	const REC_ORDER_ID = 'rec_order_id';

	const CREATED_DATE = 'created_date';

	const MESSAGE = 'message';

	const STATUS = 'status';

    const ORDER_DATE = 'order_date';

	const ORDER_ENTITY_ID = 'order_entity_id';

    /**
     * @param int $recurring_id
     * @return $this
     */
    public function setRecurringId(int $recurring_id): OrderSubscriptionInterface;

    /**
     * @return int
     */
    public function getRecurringId(): int;

    /**
     * @param string $recurring_date
     * @return $this
     */
    /**
     * @param int $rec_order_id
     * @return $this
     */
    public function setRecurringOrderId(int $rec_order_id): OrderSubscriptionInterface;

    /**
     * @return int
     */
    public function getRecurringOrderId(): int;

    /**
     * @param $created_date
     * @return $this
     */
	public function setOrderCreatedDate($created_date): OrderSubscriptionInterface;

    /**
     * @return string
     */
    public function getOrderCreatedDate();

    /**
     * @param $message
     * @return $this
     */
	public function setOrderMessage($message): OrderSubscriptionInterface;

    /**
     * @return string
     */
    public function getOrderMessage(): string;

    /**
     * @param int $status
     * @return $this
     */
	public function setOrderStatus(int $status): OrderSubscriptionInterface;

    /**
     * @return int
     */
    public function getOrderStatus(): int;

    /**
     * @param $recurring_date
     * @return $this
     */
    public function setRecurringOrderDate($recurring_date): OrderSubscriptionInterface;

    /**
     * @return string
     */
    public function getRecurringOrderDate();

    /**
     * @param int $order_entity_id
     * @return OrderSubscriptionInterface
     */
	public function setOrderEntityId(int $order_entity_id): OrderSubscriptionInterface;

    /**
     * @return int
     */
    public function getOrderEntityId(): int;

}
