<?php
declare(strict_types=1);
/**
 * Observe and upload customer to EConnect automatically.
 *
 * @author      Century Business Solutions <support@centurybizsolutions.com>
 * @copyright   Copyright (c) 2020 Century Business Solutions  (www.centurybizsolutions.com)
 */

namespace Ebizcharge\Ebizcharge\Observer;

use Ebizcharge\Ebizcharge\Model\Config;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;
use Ebizcharge\Ebizcharge\Model\Data;

/**
 * Config Validation observer
 *
 * Class ConfigObserver
 * @package Ebizcharge\Ebizcharge\Observer
 */
class ConfigObserver implements ObserverInterface
{
    /**
     * @var Config
     */
    private $config;

    /**
     * @var Data
     */
    private $dataClass;

    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @param Config $config
     * @param Data $dataClass
     * @param RequestInterface $request
     */
    public function __construct(
        Config $config,
        Data $dataClass,
        RequestInterface $request
    ) {
        $this->config = $config;
        $this->dataClass = $dataClass;
        $this->request = $request;
    }

    /**
     * @param EventObserver $observer
     */
    public function execute(EventObserver $observer)
    {
        $requestParams = $this->request->getParams();
        $paymentMethods = $requestParams['groups'];
        $ebizChargeArray = $paymentMethods['ebizcharge_ebizcharge'];

        if(empty($ebizChargeArray) || $this->config->isEbizchargeActive() == 0) {
            return;
        }
        $this->dataClass->validateApiKey();
    }

}
