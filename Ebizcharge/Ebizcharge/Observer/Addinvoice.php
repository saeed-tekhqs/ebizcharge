<?php
declare(strict_types=1);
/**
 * Observe and upload invoice to EConnect automatically.
 *
 * @author      Century Business Solutions <support@centurybizsolutions.com>
 * @copyright   Copyright (c) 2020 Century Business Solutions  (www.centurybizsolutions.com)
 */

namespace Ebizcharge\Ebizcharge\Observer;

use Ebizcharge\Ebizcharge\Model\Config;
use Ebizcharge\Ebizcharge\Model\Data;
use Ebizcharge\Ebizcharge\Model\EbizLogger;
use Magento\Customer\Model\SessionFactory;
use Magento\Framework\App\Area;
use Magento\Framework\App\State;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Sales\Model\Order;

ini_set("soap.wsdl_cache_enabled", '0');
ini_set('memory_limit', '-1');
ini_set('max_execution_time', '-1');
//ini_set('fastcgi_read_timeout', 600);
//ini_set('proxy_read_timeout', 600);
ini_set('max_input_time', '600');
ini_set('max_input_vars', '3000');
ini_set('post_max_size', '1000M');

/**
 * Observer to upload invoice
 *
 * Class Addinvoice
 * @package Ebizcharge\Ebizcharge\Observer
 */
class Addinvoice implements ObserverInterface
{
    use EbizLogger;

    private $customerSession;

    private $config;

    /**
     * @var State
     */
    private $appState;

    /**
     * @var Order
     */
    private $order;

    /**
     * @var Data
     */
    private $dataClass;

    /**
     * @param Config $config
     * @param Data $dataClass
     * @param Order $order
     * @param SessionFactory $customerSession
     * @param State $appState
     */
    public function __construct(
        Config $config,
        Data $dataClass,
        Order $order,
        SessionFactory $customerSession,
        State $appState
    ) {
        $this->config = $config;
        $this->dataClass = $dataClass;
        $this->order = $order;
        $this->customerSession = $customerSession;
        $this->appState = $appState;
    }

    public function log($message, $level = null)
    {
        $this->ebizLog()->info($message);
    }

    /**
     * Check if area is admin
     */
    public function isAdmin(): bool
    {
        try {
            return $this->appState->getAreaCode() == Area::AREA_ADMINHTML;
        } catch (\Exception $e) {
            return false;
        }
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
            $invoice = $observer->getEvent()->getInvoice();
            if($this->isAdmin()) {
                $currentOrderEntityId = $invoice->getOrderId();

                $order = $this->order->load($currentOrderEntityId);
                $invoiceCollection[] = $invoice;

            } else {
                $orderIds = $observer->getEvent()->getOrderIds();
                $currentOrderEntityId = $orderIds[0];
                $order = $this->order->load($currentOrderEntityId);
                $invoiceCollection = $order->getInvoiceCollection();
            }

            if(!empty($currentOrderEntityId) && !empty($invoiceCollection)) {
                if($this->customerSession->create()->isLoggedIn() || $this->isAdmin()) {
                    $this->dataClass->processInvoices($order, $invoiceCollection);
                    return;
                }
            }
        } catch (\Exception $e) {
            $this->log('Invoice #' . $invoice->getData('increment_id') . ' not added! ' . $e->getMessage());
        }
    }
}
