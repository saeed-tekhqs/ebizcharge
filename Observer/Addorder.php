<?php
declare(strict_types=1);
/**
 * Observe and upload order to EConnect automatically.
 *
 * @author      Century Business Solutions <support@centurybizsolutions.com>
 * @copyright   Copyright (c) 2020 Century Business Solutions  (www.centurybizsolutions.com)
 */

namespace Ebizcharge\Ebizcharge\Observer;

use Ebizcharge\Ebizcharge\Model\Config;
use Ebizcharge\Ebizcharge\Model\Data;
use Ebizcharge\Ebizcharge\Model\EbizLogger;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

ini_set("soap.wsdl_cache_enabled", "0");
ini_set('memory_limit', '-1');
ini_set('max_execution_time', '-1');
//ini_set('fastcgi_read_timeout', 9000);
//ini_set('proxy_read_timeout', 9000);
ini_set('max_input_time', '600');
ini_set('max_input_vars', '3000');
ini_set('post_max_size', '1000M');

/**
 * Upload order
 *
 * Class Addorder
 * @package Ebizcharge\Ebizcharge\Observer
 */
class Addorder implements ObserverInterface
{
    use EbizLogger;

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
        Config $config,
        Data $dataClass
    ) {
        $this->dataClass = $dataClass;
        $this->config = $config;
    }

    /**
     * Custom Logger
     *
     * @param string $message
     * @param null $level
     */
    public function log($message, $level = null)
    {
        $this->ebizLog()->info($message);
    }


    /**
     * This is the method that fires when the event runs.
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
        sleep(2);

        try {
            $orderData = $observer->getEvent()->getData('order');
            if(!empty($orderData)) {
                $this->dataClass->syncOrders($orderData);
                return;
            }
        } catch (\Exception $e) {
            $this->log('Order #' . $orderData->getData('increment_id') . ' not added! ' . $e->getMessage());
        }

    }
}
