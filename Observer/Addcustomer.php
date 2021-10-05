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
use Ebizcharge\Ebizcharge\Model\Data;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

ini_set("soap.wsdl_cache_enabled", "0");
ini_set('memory_limit', '-1');
ini_set('max_execution_time', '-1');
//ini_set('fastcgi_read_timeout',600);
//ini_set('proxy_read_timeout', 600);
ini_set('max_input_time', '600');
ini_set('max_input_vars', '3000');
ini_set('post_max_size', '1000M');

/**
 * Upload customer
 *
 * Class Addcustomer
 * @package Ebizcharge\Ebizcharge\Observer
 */
class Addcustomer implements ObserverInterface
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
     * @param Data $dataClass
     * @param Config $config
     */
    public function __construct(
        Data $dataClass,
        Config $config
    ) {
        $this->config = $config;
        $this->dataClass = $dataClass;
    }

    /**
     * Sync customer
     *
     * @param Observer $observer
     */
    public function execute(Observer $observer)
    {
        if($this->config->isEbizchargeActive() == 0) {
            return;
        }
        if($this->config->isEconnectUploadEnabled() == 0) {
            return;
        }

        $customer = $observer->getEvent()->getCustomer();
        $customerId = $customer->getId();
        $this->dataClass->syncCustomer($customerId);
    }
}
