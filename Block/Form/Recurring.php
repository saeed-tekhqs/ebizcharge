<?php
declare(strict_types=1);
/**
 * Declares the block for the EBizCharge payment method - utilized in adminhtml.
 *
 * @author      Century Business Solutions <support@centurybizsolutions.com>
 * @copyright   Copyright (c) 2020 Century Business Solutions  (www.centurybizsolutions.com)
 */

namespace Ebizcharge\Ebizcharge\Block\Form;

use Ebizcharge\Ebizcharge\Model\Config as EbizConfig;
use Magento\Customer\Model\SessionFactory;
use Magento\Framework\View\Element\Template\Context;
use Magento\Payment\Block\Form as PaymentForm;

/**
 * This block renders recurring form on PDP (Product detail page)
 *
 * Class Recurring
 * @package Ebizcharge\Ebizcharge\Block\Form
 */
class Recurring extends PaymentForm
{
    /**
     * @var EbizConfig
     */
    private $config;

    /**
     * @var SessionFactory
     */
    private $customerSession;

    /**
     * @param SessionFactory $customerSession
     * @param Context $context
     * @param EbizConfig $config
     * @param array $data
     */
    public function __construct(
        SessionFactory $customerSession,
        Context $context,
        EbizConfig $config,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->config = $config;
        $this->customerSession = $customerSession;
    }

    public function isEbizChargeActive(): bool
    {
        return $this->config->isActive();
    }

    public function isRecurringEnabled(): bool
    {
        return $this->config->isRecurringEnabled() == 1;
    }

    public function showRecurringForm(): bool
    {
        return $this->isEbizchargeActive() && $this->isRecurringEnabled() && $this->getLoggedInCustomerId();
    }

    public function getLoggedInCustomerId()
    {
        return $this->customerSession->create()->getCustomerId();
    }

    public function getConfiguredFrequencies($selectedFrequency = null)
    {
        $this->config->getRecurringFrequencyOptions($selectedFrequency);
    }
}
