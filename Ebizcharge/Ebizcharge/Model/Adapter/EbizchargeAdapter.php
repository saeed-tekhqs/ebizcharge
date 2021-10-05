<?php
/**
 * @author Century Business Solutions <support@centurybizsolutions.com>
 * @copyriht Copyright (c) 2021 Century Business Solutions  (www.centurybizsolutions.com)
 * Created by PhpStorm
 * User: Mobeen
 * Date: 7/19/21
 * Time: 12:03 PM
 */
declare(strict_types=1);

namespace Ebizcharge\Ebizcharge\Model\Adapter;

use Ebizcharge\Ebizcharge\Model\Payment;
use Magento\Framework\Exception\LocalizedException;

/**
 * PLEASE ENTER ONE LINE SHORT DESCRIPTION OF CLASS
 *
 * Class EbizchargeAdapter
 */
class EbizchargeAdapter
{
    /**
     * @var Payment
     */
    private $ebizchargePayment;

    /**
     * @param Payment $ebizchargePayment
     */
    public function __construct(Payment $ebizchargePayment)
    {
        $this->ebizchargePayment = $ebizchargePayment;
    }

    /**
     * Capture payment
     * @param $attributes
     * @return false|Payment
     * @throws \Magento\Framework\Validator\Exception
     */
    public function capture($attributes)
    {
        $amount = $attributes['amount'];
        return $this->ebizchargePayment->capture($attributes['payment'], $amount);
    }

    /**
     * sale command
     *
     * @param array $attributes
     * @return Payment
     * @throws LocalizedException
     */
    public function sale(array $attributes)
    {
        $amount = $attributes['amount'];
        return $this->ebizchargePayment->quickSale($attributes['payment'], $amount);
    }

    /**
     * authorize command
     *
     * @param array $attributes
     * @return Payment
     * @throws LocalizedException
     */
    public function authorize(array $attributes)
    {
        $amount = $attributes['amount'];
        return $this->ebizchargePayment->authorize($attributes['payment'], $amount);
    }

    /**
     * Refund command
     *
     * @param array $attributes
     * @return Payment
     * @throws LocalizedException
     */
    public function refund(array $attributes)
    {
        $amount = $attributes['amount'];
        return $this->ebizchargePayment->refund($attributes['payment'], $amount);
    }

    /**
     * Void command
     *
     * @param array $attributes
     * @return Payment
     * @throws LocalizedException
     */
    public function void(array $attributes)
    {
        return $this->ebizchargePayment->void($attributes['payment']);
    }

    /**
     * Cancel command
     *
     * @param array $attributes
     * @return Payment
     * @throws LocalizedException
     */
    public function cancel(array $attributes)
    {
        return $this->ebizchargePayment->cancel($attributes['payment']);
    }

}
