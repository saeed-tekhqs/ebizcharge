<?php

namespace Ebizcharge\Ebizcharge\Cron;

use Ebizcharge\Ebizcharge\Model\EbizLogger;

class SuspendRecurring
{
    use EbizLogger;

    /**
     * @var \Ebizcharge\Ebizcharge\Block\Admin\RecurringsAdmin
     */
    private $dataClass;

    public function __construct(\Ebizcharge\Ebizcharge\Block\Admin\RecurringsAdmin $dataClass)
    {
        $this->dataClass = $dataClass;
    }

    public function execute()
    {
        $this->ebizLog()->info('CreateOrder Cron has been run successfully. The time is ' . date("Y-m-d h:i:sa"));
        $dataclass = $this->dataClass;
        // As per recent(30/05/21) discussion with Frank, we don't need suspend recurring
        //$value = $dataclass->checkTransactionStatus();
    }
}
