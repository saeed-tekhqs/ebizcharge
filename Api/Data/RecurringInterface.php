<?php
/**
 * @author Century Business Solutions <support@centurybizsolutions.com>
 * @copyriht Copyright (c) 2021 opyright (c) 2020 Century Business Solutions  (www.centurybizsolutions.com)
 * Created by PhpStorm
 * User: Mobeen
 * Date: 6/7/21
 * Time: 2:12 PM
 */
declare(strict_types=1);

namespace Ebizcharge\Ebizcharge\Api\Data;

/**
 * Class RecurringInterface
 * @api
 */
interface RecurringInterface
{
    const REC_ID = 'rec_id';

    const REC_STATUS = 'rec_status';

    const REC_INDEFINITELY = 'rec_indefinitely';

    const MAGE_CUST_ID = 'mage_cust_id';

    const MAGE_ORDER_ID = 'mage_order_id';

    const MAGE_ITEM_ID = 'mage_item_id';

    const MAGE_ITEM_NAME = 'mage_item_name';

    const QTY_ORDERED = 'qty_ordered';

    const EB_REC_START_DATE = 'eb_rec_start_date';

    const EB_REC_END_DATE = 'eb_rec_end_date';

    const EB_REC_FREQUENCY = 'eb_rec_frequency';

    const EB_REC_METHOD_ID = 'eb_rec_method_id';

    const EB_REC_SCHEDULED_PAYMENT_INTERNAL_ID = 'eb_rec_scheduled_payment_internal_id';

    const EB_REC_TOTAL = 'eb_rec_total';

    const EB_REC_PROCESSED = 'eb_rec_processed';

    const EB_REC_NEXT = 'eb_rec_next';

    const EB_REC_REMAINING = 'eb_rec_remaining';

    const EB_REC_DUE_DATES = 'eb_rec_due_dates';

    const MAGE_PARENT_ITEM_ID = 'mage_parent_item_id';

    const BILLING_ADDRESS_ID = 'billing_address_id';

    const SHIPPING_ADDRESS_ID = 'shipping_address_id';

    const AMOUNT = 'amount';

    const PAYMENT_METHOD_NAME = 'payment_method_name';

    const SHIPPING_METHOD = 'shipping_method';

    const FAILED_ATTEMPTS = 'failed_attempts';

    /**
     * @param int $rec_id
     * @return $this
     */
    public function setRecId(int $rec_id): RecurringInterface;

    /**
     * @return int
     */
    public function getRecId(): int;

    /**
     * @param int $rec_status
     * @return $this
     */
    public function setRecStatus(int $rec_status): RecurringInterface;

    /**
     * @return int
     */
    public function getRecStatus(): int;

    /**
     * @param int $rec_indefinitely
     * @return $this
     */
    public function setRecIndefinitely(int $rec_indefinitely): RecurringInterface;

    /**
     * @return int
     */
    public function getRecIndefinitely(): int;

    /**
     * @param string $mage_cust_id
     * @return $this
     */
    public function setMageCustId(string $mage_cust_id): RecurringInterface;

    /**
     * @return string
     */
    public function getMageCustId(): string;

    /**
     * @param string $mage_order_id
     * @return $this
     */
    public function setMageOrderId(string $mage_order_id): RecurringInterface;

    /**
     * @return string
     */
    public function getMageOrderId(): string;

    /**
     * @param string $mage_item_id
     * @return $this
     */
    public function setMageItemId(string $mage_item_id): RecurringInterface;

    /**
     * @return string
     */
    public function getMageItemId(): string;

    /**
     * @param string $mage_item_name
     * @return $this
     */
    public function setMageItemName(string $mage_item_name): RecurringInterface;

    /**
     * @return string
     */
    public function getMageItemName(): string;

    /**
     * @param string $qty_ordered
     * @return $this
     */
    public function setQtyOrdered(string $qty_ordered): RecurringInterface;

    /**
     * @return string
     */
    public function getQtyOrdered(): string;

    /**
     * @param string $eb_rec_start_date
     * @return $this
     */
    public function setEbRecStartDate(string $eb_rec_start_date): RecurringInterface;

    /**
     * @return string
     */
    public function getEbRecStartDate(): string;

    /**
     * @param string $eb_rec_end_date
     * @return $this
     */
    public function setEbRecEndDate(string $eb_rec_end_date): RecurringInterface;

    /**
     * @return string
     */
    public function getEbRecEndDate(): string;

    /**
     * @param string $eb_rec_frequency
     * @return $this
     */
    public function setEbRecFrequency(string $eb_rec_frequency): RecurringInterface;

    /**
     * @return string
     */
    public function getEbRecFrequency(): string;

    /**
     * @param string $eb_rec_method_id
     * @return $this
     */
    public function setEbRecMethodId($eb_rec_method_id): RecurringInterface;

    /**
     * @return string
     */
    public function getEbRecMethodId(): string;

    /**
     * @param string $eb_rec_scheduled_payment_internal_id
     * @return $this
     */
    public function setEbRecScheduledPaymentInternalId(string $eb_rec_scheduled_payment_internal_id): RecurringInterface;

    /**
     * @return string
     */
    public function getEbRecScheduledPaymentInternalId(): string;

    /**
     * @param int $eb_rec_total
     * @return $this
     */
    public function setEbRecTotal(int $eb_rec_total): RecurringInterface;

    /**
     * @return int
     */
    public function getEbRecTotal(): int;

    /**
     * @param int $eb_rec_processed
     * @return $this
     */
    public function setEbRecProcessed(int $eb_rec_processed): RecurringInterface;

    /**
     * @return int
     */
    public function getEbRecProcessed(): int;

    /**
     * @param string $eb_rec_next
     * @return $this
     */
    public function setEbRecNext(string $eb_rec_next): RecurringInterface;

    /**
     * @return string
     */
    public function getEbRecNext(): string;

    /**
     * @param int $eb_rec_remaining
     * @return $this
     */
    public function setEbRecRemaining(int $eb_rec_remaining): RecurringInterface;

    /**
     * @return int
     */
    public function getEbRecRemaining(): int;

    /**
     * @param string $eb_rec_due_dates
     * @return $this
     */
    public function setEbRecDueDates(string $eb_rec_due_dates): RecurringInterface;

    /**
     * @return int
     */
    public function getEbRecDueDates(): int;

    /**
     * @param string $mage_parent_item_id
     * @return $this
     */
    public function setMageParentItemId(string $mage_parent_item_id): RecurringInterface;

    /**
     * @return string
     */
    public function getMageParentItemId(): string;

    /**
     * @param int $billing_address_id
     * @return $this
     */
    public function setBillingAddressId(int $billing_address_id): RecurringInterface;

    /**
     * @return int
     */
    public function getBillingAddressId(): int;

    /**
     * @param int $shipping_address_id
     * @return $this
     */
    public function setShippingAddressId(int $shipping_address_id): RecurringInterface;

    /**
     * @return int
     */
    public function getShippingAddressId(): int;

    /**
     * @param string $amount
     * @return $this
     */
    public function setAmount(string $amount): RecurringInterface;

    /**
     * @return int
     */
    public function getAmount(): int;

    /**
     * @param string $payment_method_name
     * @return $this
     */
    public function setPaymentMethodName(string $payment_method_name): RecurringInterface;

    /**
     * @return string
     */
    public function getPaymentMethodName(): string;

    /**
     * @param string $shipping_method
     * @return $this
     */
    public function setShippingMethod(string $shipping_method): RecurringInterface;

    /**
     * @return string
     */
    public function getShippingMethod();

    /**
     * @param int $failed_attempts
     * @return $this
     */
    public function setFailedAttempts(int $failed_attempts): RecurringInterface;

    /**
     * @return int
     */
    public function getFailedAttempts(): int;
}
