<?php
/**
 * @author Century Business Solutions <support@centurybizsolutions.com>
 * @copyriht Copyright (c) 2021 Century Business Solutions  (www.centurybizsolutions.com)
 * Created by PhpStorm
 * User: Mobeen
 * Date: 6/21/21
 * Time: 3:49 PM
 */
declare(strict_types=1);

namespace Ebizcharge\Ebizcharge\Block\Adminhtml\PaymentHistory;

use Magento\Backend\Block\Context;
use Magento\Backend\Block\Widget\Grid\Column\Renderer\Date;
use Magento\Framework\DataObject;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Framework\Stdlib\DateTime\DateTimeFormatterInterface;

/**
 * Fix wrong date issue on block grids
 *
 * Class DateRenderer
 */
class DateRenderer extends Date
{
    /**
     * @var DateTime
     */
    private $dateTime;

    /**
     * @param DateTime $dateTime
     * @param Context $context
     * @param DateTimeFormatterInterface $dateTimeFormatter
     * @param array $data
     */
    public function __construct(
        DateTime $dateTime,
        Context $context,
        DateTimeFormatterInterface $dateTimeFormatter,
        array $data = []
    ) {
        parent::__construct($context, $dateTimeFormatter, $data);
        $this->dateTime = $dateTime;
    }

    /**
     * @param DataObject $row
     * @return string
     */
    public function render(DataObject $row): string
    {
        return $this->dateTime->date('Y-m-d', $row->getData('paymentDate'));
    }

}
