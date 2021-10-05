<?php
/**
 * Connects the EBizCharge method renderer to the system config.
 *
 * @author      Century Business Solutions <support@centurybizsolutions.com>
 * @copyright   Copyright (c) 2020 Century Business Solutions  (www.centurybizsolutions.com)
 */

namespace Ebizcharge\Ebizcharge\Model;

use Magento\Payment\Model\CcGenericConfigProvider;
use Magento\Payment\Model\CcConfig;
use Magento\Framework\Escaper;
use Magento\Payment\Helper\Data as PaymentHelper;

class ConfigProvider extends CcGenericConfigProvider
{
    /**
     * @var string[]
     */
    protected $methodCodes = [
        'ebizcharge_ebizcharge'
    ];

    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $checkoutSession;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * @var \Magento\Payment\Model\Method\AbstractMethod[]
     */
    protected $methods = [];

    /**
     * @var Escaper
     */
    protected $escaper;

    /**
     * @var Config
     */
    protected $config;

    /**
     * @param PaymentHelper $paymentHelper
     * @param Escaper $escaper
     */
    public function __construct(
        PaymentHelper $paymentHelper,
        Escaper $escaper,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Customer\Model\Session $customerSession,
        Config $config
    ) {
        $this->escaper = $escaper;
        $this->checkoutSession = $checkoutSession;
        $this->customerSession = $customerSession;
        $this->config = $config;

        foreach ($this->methodCodes as $code) {
            $this->methods[$code] = $paymentHelper->getMethodInstance($code);
        }
    }

    /**
     * @return array|void
     */
    public function getConfig()
    {
        if (!$this->config->isActive()) {
            return [];
        }

        $config = []; // parent::getConfig();

        $config = array_merge_recursive($config, [
            'payment' => [
                'ebizcharge' => [
                    'sourcekey' => $this->config->getSourceKey(),
                    'getDeleteURL' => $this->config->getDeleteURL(),
                    'useVault' => $this->methods['ebizcharge_ebizcharge']->hasToken(),
                    'getEbzcCustId' => $this->methods['ebizcharge_ebizcharge']->getEbzcCustId(),
                    'storedCards' => $this->methods['ebizcharge_ebizcharge']->getSavedCards(),
                    'storedAccounts' => $this->methods['ebizcharge_ebizcharge']->getSavedAccounts(),
                    'saveCard' => $this->config->getPaymentSavePayment() == 1,
                    'requestCardCode' => $this->config->getRequestCardCode() == 1,
                    'isAchActive' => $this->config->isAchActive(),
                    'getAchAccountTypes' => array('checking', 'savings'),
                    'isRecurringEnabled' => $this->config->isRecurringEnabled(),
                    'hasToken' => $this->methods['ebizcharge_ebizcharge']->hasToken()
                ],
            ],
        ]);

        return $config;
    }
}
