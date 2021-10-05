<?php
declare(strict_types=1);
/**
 * Declares the block for the EBizCharge payment method - utilized in adminhtml.
 *
 * @author      Century Business Solutions <support@centurybizsolutions.com>
 * @copyright   Copyright (c) 2020 Century Business Solutions  (www.centurybizsolutions.com)
 */

namespace Ebizcharge\Ebizcharge\Block\Form;

use Ebizcharge\Ebizcharge\Model\Config;
use Ebizcharge\Ebizcharge\Model\Payment;
use Magento\Framework\View\Element\Template\Context;
use Magento\Payment\Block\Form\Cc;
use Magento\Payment\Model\Config as PaymentConfig;

/**
 * Admin payment checkout processing
 *
 * Class Card
 * @package Ebizcharge\Ebizcharge\Block\Form
 */
class Card extends Cc
{
    /**
     * @var Payment
     */
    private $ebizchargePaymentsCard;

    /**
     * @var Config
     */
    private $config;

    /**
     * @param Context $context
     * @param Config $config
     * @param Payment $payment
     * @param PaymentConfig $paymentConfig
     * @param array $data
     */
    public function __construct(
        Context $context,
        Config $config,
        Payment $payment,
        PaymentConfig $paymentConfig,
        array $data = []
    ) {
        parent::__construct($context, $paymentConfig, $data);
        $this->config = $config;
        $this->ebizchargePaymentsCard = $payment;
    }

    /**
     * @return mixed
     */
    public function getClientKey()
    {
        return $this->config->getSourceKey();
    }

    /**
     * @return bool
     */
    public function getRequestCardCodeAdmin()
    {
        return $this->config->getRequestCardCodeAdmin() == 1;
    }

    /**
     * @return bool
     */
    public function achEnabled()
    {
        return $this->config->isAchActive();
    }

    /**
     * @return bool
     */
    public function saveCardEnabled()
    {
        return $this->config->saveCard();
    }

    /**
     * @return mixed
     */
    public function getPaymentSavePayment()
    {
        return $this->config->getPaymentSavePayment();
    }

    /**
     * @return string
     */
    public function getDeleteUrl()
    {
        return $this->config->getBaseDeleteUrl();
    }

    /**
     * @return array
     */
    public function getSavedAccounts()
    {
        return $this->ebizchargePaymentsCard->getSavedAccounts();
    }

    /**
     * @return array
     */
    public function getSavedCards()
    {
        return $this->ebizchargePaymentsCard->getSavedCards();
    }

    /**
     * @return mixed
     */
    public function getEbzcCustId()
    {
        return $this->ebizchargePaymentsCard->getEbzcCustId();
    }

    /**
     * @return bool
     */
    public function hasToken()
    {
        return $this->ebizchargePaymentsCard->hasToken();
    }

}
