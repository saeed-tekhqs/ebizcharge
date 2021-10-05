<?php

namespace Ebizcharge\Ebizcharge\Cron;

use Psr\Log\LoggerInterface;
use Ebizcharge\Ebizcharge\Model\CreateOrder as CreateOrderModel;

/**
 * This cron creates orders
 *
 * Class CreateOrder
 * @package Ebizcharge\Ebizcharge\Cron
 */
class CreateOrder
{
    /**
     * @var LoggerInterface
     */
    private $logger;
    /**
     * @var CreateOrderModel
     */
    private $createOrderModel;

    public function __construct(
        CreateOrderModel $createOrderModel,
        LoggerInterface $logger
    ) {
        $this->logger = $logger;
        $this->createOrderModel = $createOrderModel;
    }

    public function execute()
    {
        $startDate = date_create("@" . strtotime('-5 days'));
        $this->createOrderModel->checkRecurringOrders($startDate, true);
        return true;

    }
}
