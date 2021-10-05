<?php
/**
 * @author Century Business Solutions <support@centurybizsolutions.com>
 * @copyriht Copyright (c) 2021 Century Business Solutions  (www.centurybizsolutions.com)
 * Created by PhpStorm
 * User: Mobeen
 * Date: 7/17/21
 * Time: 12:17 PM
 */
declare(strict_types=1);

namespace Ebizcharge\Ebizcharge\Ui;

use Magento\Checkout\Model\ConfigProviderInterface;
use Ebizcharge\Ebizcharge\Model\Config;
use Magento\Payment\Helper\Data as PaymentHelper;
use Magento\Payment\Model\MethodInterface;
use Ebizcharge\Ebizcharge\Model\Payment;

/**
 * Checkout config provider for Ebizcharge
 *
 * Class ConfigProvider
 */
class ConfigProvider implements ConfigProviderInterface
{
    const CODE = 'ebizcharge_ebizcharge';

    /**
     * @var Config
     */
    private $config;

    /**
     * @var PaymentHelper
     */
    private $paymentHelper;
    /**
     * @var Payment
     */
    private $ebizPayment;

    /**
     * @param Config $config
     * @param PaymentHelper $paymentHelper
     */
    public function __construct(
        Payment $ebizPayment,
        Config $config,
        PaymentHelper $paymentHelper
    ) {
        $this->config = $config;
        $this->paymentHelper = $paymentHelper;
        $this->ebizPayment = $ebizPayment;
    }

    /**
     * @inheritDoc
     */
    public function getConfig(): array
    {
        $config = [];

        if (!$this->config->isActive()) {
            return $config;
        }

        $ebizMethodDetails = $this->getEbizMethodDetails();

        return array_merge_recursive($config, [
            'payment' => [
                'ebizcharge' => [
                    'sourcekey' => $this->config->getSourceKey(),
                    'getDeleteURL' => $this->config->getDeleteURL(),
                    'useVault' => $this->ebizPayment->hasToken(),
                    'getEbzcCustId' => $this->ebizPayment->getEbzcCustId(),
                    'storedCards' => $this->ebizPayment->getSavedCards(),
                    'storedAccounts' => $this->ebizPayment->getSavedAccounts(),
                    'saveCard' => $this->config->getPaymentSavePayment() == 1,
                    'requestCardCode' => $this->config->getRequestCardCode() == 1,
                    'isAchActive' => $this->config->isAchActive(),
                    'getAchAccountTypes' => array('checking', 'savings'),
                    'isRecurringEnabled' => $this->config->isRecurringEnabled(),
                    'hasToken' => $this->ebizPayment->hasToken()
                ],
            ],
        ]);
    }

    /**
     * @return MethodInterface
     */
    private function getEbizMethodDetails(): ?MethodInterface
    {
        try {
            return $this->paymentHelper->getMethodInstance(static::CODE);
        } catch (\Exception $e) {
            return null;
        }
    }
}
