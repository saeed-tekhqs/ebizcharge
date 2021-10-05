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

use Ebizcharge\Ebizcharge\Api\Data\RecurringInterface;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\DataObject\IdentityInterface;
use Ebizcharge\Ebizcharge\Model\ResourceModel\Recurring as RecurringResourceModel;

/**
 * Ebizcharge Recurring Model
 *
 * Class Recurring
 */
class Recurring extends AbstractModel implements RecurringInterface, IdentityInterface
{
    const CACHE_TAG = 'ebizcharge_recurring';

    const CUSTOMER_EMAIL = 'customer_email';

    protected $_cacheTag = self::CACHE_TAG;

    /**
     * Initialize resource model.
     */
    protected function _construct()
    {
        $this->_init(RecurringResourceModel::class);
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
    public function setRecId(int $rec_id): RecurringInterface
    {
        return $this->setData(self::REC_ID, $rec_id);
    }

    /**
     * @inheritDoc
     */
    public function getRecId(): int
    {
        return (int)$this->getData(self::REC_ID);
    }

    /**
     * @inheritDoc
     */
    public function setRecStatus(int $rec_status): RecurringInterface
    {
        return $this->setData(self::REC_STATUS, $rec_status);
    }

    /**
     * @inheritDoc
     */
    public function getRecStatus(): int
    {
        return (int)$this->getData(self::REC_STATUS);
    }

    /**
     * @inheritDoc
     */
    public function setRecIndefinitely(int $rec_indefinitely): RecurringInterface
    {
        return $this->setData(self::REC_INDEFINITELY, $rec_indefinitely);
    }

    /**
     * @inheritDoc
     */
    public function getRecIndefinitely(): int
    {
        return $this->getData(self::REC_INDEFINITELY);
    }

    /**
     * @inheritDoc
     */
    public function setMageCustId(string $mage_cust_id): RecurringInterface
    {
        return $this->setData(self::MAGE_CUST_ID, $mage_cust_id);
    }

    /**
     * @inheritDoc
     */
    public function getMageCustId(): string
    {
        return $this->getData(self::MAGE_CUST_ID);
    }

    /**
     * @inheritDoc
     */
    public function setMageOrderId(string $mage_order_id): RecurringInterface
    {
        return $this->setData(self::MAGE_ORDER_ID, $mage_order_id);
    }

    /**
     * @inheritDoc
     */
    public function getMageOrderId(): string
    {
        return $this->getData(self::MAGE_ORDER_ID);
    }

    /**
     * @inheritDoc
     */
    public function setMageItemId(string $mage_item_id): RecurringInterface
    {
        return $this->setData(self::MAGE_ITEM_ID, $mage_item_id);
    }

    /**
     * @inheritDoc
     */
    public function getMageItemId(): string
    {
        return $this->getData(self::MAGE_ITEM_ID);
    }

    /**
     * @inheritDoc
     */
    public function setMageItemName(string $mage_item_name): RecurringInterface
    {
        return $this->setData(self::MAGE_ITEM_NAME, $mage_item_name);
    }

    /**
     * @inheritDoc
     */
    public function getMageItemName(): string
    {
        return $this->getData(self::MAGE_ITEM_NAME);
    }

    /**
     * @inheritDoc
     */
    public function setQtyOrdered(string $qty_ordered): RecurringInterface
    {
        return $this->setData(self::QTY_ORDERED, $qty_ordered);
    }

    /**
     * @inheritDoc
     */
    public function getQtyOrdered(): string
    {
        return $this->getData(self::QTY_ORDERED);
    }

    /**
     * @inheritDoc
     */
    public function setEbRecStartDate(string $eb_rec_start_date): RecurringInterface
    {
        return $this->setData(self::EB_REC_START_DATE, $eb_rec_start_date);
    }

    /**
     * @inheritDoc
     */
    public function getEbRecStartDate(): string
    {
        return $this->getData(self::EB_REC_START_DATE);
    }

    /**
     * @inheritDoc
     */
    public function setEbRecEndDate(string $eb_rec_end_date): RecurringInterface
    {
        return $this->setData(self::EB_REC_END_DATE, $eb_rec_end_date);
    }

    /**
     * @inheritDoc
     */
    public function getEbRecEndDate(): string
    {
        return $this->getData(self::EB_REC_END_DATE);
    }

    /**
     * @inheritDoc
     */
    public function setEbRecFrequency(string $eb_rec_frequency): RecurringInterface
    {
        return $this->setData(self::EB_REC_FREQUENCY, $eb_rec_frequency);
    }

    /**
     * @inheritDoc
     */
    public function getEbRecFrequency(): string
    {
        return $this->getData(self::EB_REC_FREQUENCY);
    }

    /**
     * @inheritDoc
     */
    public function setEbRecMethodId($eb_rec_method_id): RecurringInterface
    {
        return $this->setData(self::EB_REC_METHOD_ID, $eb_rec_method_id);
    }

    /**
     * @inheritDoc
     */
    public function getEbRecMethodId(): string
    {
        return $this->getData(self::EB_REC_METHOD_ID);
    }

    /**
     * @inheritDoc
     */
    public function setEbRecScheduledPaymentInternalId(string $eb_rec_scheduled_payment_internal_id): RecurringInterface
    {
        return $this->setData(self::EB_REC_SCHEDULED_PAYMENT_INTERNAL_ID, $eb_rec_scheduled_payment_internal_id);
    }

    /**
     * @inheritDoc
     */
    public function getEbRecScheduledPaymentInternalId(): string
    {
        return $this->getData(self::EB_REC_SCHEDULED_PAYMENT_INTERNAL_ID);
    }

    /**
     * @inheritDoc
     */
    public function setEbRecTotal(int $eb_rec_total): RecurringInterface
    {
        return $this->setData(self::EB_REC_TOTAL, $eb_rec_total);
    }

    /**
     * @inheritDoc
     */
    public function getEbRecTotal(): int
    {
        return $this->getData(self::EB_REC_TOTAL);
    }

    /**
     * @inheritDoc
     */
    public function setEbRecProcessed(int $eb_rec_processed): RecurringInterface
    {
        return $this->setData(self::EB_REC_PROCESSED, $eb_rec_processed);
    }

    /**
     * @inheritDoc
     */
    public function getEbRecProcessed(): int
    {
        return $this->getData(self::EB_REC_PROCESSED);
    }

    /**
     * @inheritDoc
     */
    public function setEbRecNext(string $eb_rec_next): RecurringInterface
    {
        return $this->setData(self::EB_REC_NEXT, $eb_rec_next);
    }

    /**
     * @inheritDoc
     */
    public function getEbRecNext(): string
    {
        return $this->getData(self::EB_REC_NEXT);
    }

    /**
     * @inheritDoc
     */
    public function setEbRecRemaining(int $eb_rec_remaining): RecurringInterface
    {
        return $this->setData(self::EB_REC_REMAINING, $eb_rec_remaining);
    }

    /**
     * @inheritDoc
     */
    public function getEbRecRemaining(): int
    {
        return $this->getData(self::EB_REC_REMAINING);
    }

    /**
     * @inheritDoc
     */
    public function setEbRecDueDates(string $eb_rec_due_dates): RecurringInterface
    {
        return $this->setData(self::EB_REC_DUE_DATES, $eb_rec_due_dates);
    }

    /**
     * @inheritDoc
     */
    public function getEbRecDueDates(): int
    {
        return $this->getData(self::EB_REC_DUE_DATES);
    }

    /**
     * @inheritDoc
     */
    public function setMageParentItemId(string $mage_parent_item_id): RecurringInterface
    {
        return $this->setData(self::MAGE_PARENT_ITEM_ID, $mage_parent_item_id);
    }

    /**
     * @inheritDoc
     */
    public function getMageParentItemId(): string
    {
        return $this->getData(self::MAGE_PARENT_ITEM_ID);
    }

    /**
     * @inheritDoc
     */
    public function setBillingAddressId(int $billing_address_id): RecurringInterface
    {
        return $this->setData(self::BILLING_ADDRESS_ID, $billing_address_id);
    }

    /**
     * @inheritDoc
     */
    public function getBillingAddressId(): int
    {
        return $this->getData(self::BILLING_ADDRESS_ID);
    }

    /**
     * @inheritDoc
     */
    public function setShippingAddressId(int $shipping_address_id): RecurringInterface
    {
        return $this->setData(self::SHIPPING_ADDRESS_ID, $shipping_address_id);
    }

    /**
     * @inheritDoc
     */
    public function getShippingAddressId(): int
    {
        return $this->getData(self::SHIPPING_ADDRESS_ID);
    }

    /**
     * @inheritDoc
     */
    public function setAmount(string $amount): RecurringInterface
    {
        return $this->setData(self::AMOUNT, $amount);
    }

    /**
     * @inheritDoc
     */
    public function getAmount(): int
    {
        return $this->getData(self::AMOUNT);
    }

    /**
     * @inheritDoc
     */
    public function setPaymentMethodName(string $payment_method_name): RecurringInterface
    {
        return $this->setData(self::PAYMENT_METHOD_NAME, $payment_method_name);
    }

    /**
     * @inheritDoc
     */
    public function getPaymentMethodName(): string
    {
        return $this->getData(self::PAYMENT_METHOD_NAME);
    }

    /**
     * @inheritDoc
     */
    public function setShippingMethod(string $shipping_method): RecurringInterface
    {
        return $this->setData(self::SHIPPING_METHOD, $shipping_method);
    }

    /**
     * @inheritDoc
     */
    public function getShippingMethod()
    {
        return $this->getData(self::SHIPPING_METHOD);
    }

    /**
     * @inheritDoc
     */
    public function setFailedAttempts(int $failed_attempts): RecurringInterface
    {
        return $this->setData(self::FAILED_ATTEMPTS, $failed_attempts);
    }

    /**
     * @inheritDoc
     */
    public function getFailedAttempts(): int
    {
        return $this->getData(self::FAILED_ATTEMPTS);
    }
}
