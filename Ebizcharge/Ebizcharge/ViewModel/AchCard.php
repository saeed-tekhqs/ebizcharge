<?php
/**
 * @author Century Business Solutions <support@centurybizsolutions.com>
 * @copyriht Copyright (c) 2021 Century Business Solutions  (www.centurybizsolutions.com)
 * Created by PhpStorm
 * User: Mobeen
 * Date: 9/30/21
 * Time: 2:58 PM
 */
declare(strict_types=1);

namespace Ebizcharge\Ebizcharge\ViewModel;

use Magento\Customer\Model\Address;
use Magento\Customer\Model\SessionFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Payment\Model\Config as PaymentConfig;
use Magento\Store\Model\ScopeInterface;

/**
 * AddAch customer account details
 *
 * Class AddAch
 * @package Ebizcharge\Ebizcharge\ViewModel
 */
class AchCard implements ArgumentInterface
{
    const DISPLAY_ALL_REGION_CONFIG_PATH = 'general/region/display_all';

    const EBIZCHARGE_CC_TYPES_CONFIG_PATH = 'payment/ebizcharge_ebizcharge/cctypes';

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var SessionFactory
     */
    private $customerSession;

    /**
     * @var UrlInterface
     */
    private $urlBuilder;

    /**
     * @var PaymentConfig
     */
    private $paymentConfig;

    /**
     * @param PaymentConfig $paymentConfig
     * @param ScopeConfigInterface $scopeConfig
     * @param SessionFactory $customerSession
     * @param UrlInterface $urlBuilder
     */
    public function __construct(
        PaymentConfig $paymentConfig,
        ScopeConfigInterface $scopeConfig,
        SessionFactory $customerSession,
        UrlInterface $urlBuilder
    ) {
        $this->paymentConfig = $paymentConfig;
        $this->scopeConfig = $scopeConfig;
        $this->customerSession = $customerSession;
        $this->urlBuilder = $urlBuilder;
    }

    /**
     * Get save url for save action
     *
     * @return string
     */
    public function getSaveUrl()
    {
        return $this->urlBuilder->getUrl(
            'ebizcharge/*/saveaction',
            ['_secure' => true]
        );
    }

    /**
     * Whether to display all regions or not
     *
     * @return mixed
     */
    public function displayAllRegion()
    {
        return $this->scopeConfig->getValue(
            static::DISPLAY_ALL_REGION_CONFIG_PATH,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Get Ebizcharge payment CC types
     *
     * @return mixed
     */
    public function getEbizCcTypes()
    {
        return $this->scopeConfig->getValue(
            static::EBIZCHARGE_CC_TYPES_CONFIG_PATH,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Get customer billing address
     *
     * @return false|Address
     */
    public function getCustomerBillingAddress()
    {
        return $this->customerSession->create()->getCustomer()->getDefaultBillingAddress();
    }

    /**
     * Get CC types for payment
     *
     * @return array
     */
    public function getCcTypes(): array
    {
        return $this->paymentConfig->getCcTypes();
    }

}
