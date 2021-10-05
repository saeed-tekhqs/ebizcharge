<?php
declare(strict_types=1);

namespace Ebizcharge\Ebizcharge\Cron;

use Ebizcharge\Ebizcharge\Model\Data;
use Ebizcharge\Ebizcharge\Model\EbizLogger;

/**
 * Check if An item is available to stock
 *
 * Class CheckItemStock
 * @package Ebizcharge\Ebizcharge\Cron
 */
class CheckItemStock
{
    use EbizLogger;

    /**
     * @var Data
     */
    private $dataClass;

    public function __construct(Data $dataClass)
    {
        $this->dataClass = $dataClass;
    }

    public function execute()
    {
        $this->ebizLog()->info('CheckItemStock Cron has been run successfully. The time is ' . date("Y-m-d h:i:sa"));
        $this->dataClass->checkItemsStock();
    }
}
