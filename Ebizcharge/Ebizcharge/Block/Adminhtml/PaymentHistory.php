<?php
/**
 * @author Century Business Solutions <support@centurybizsolutions.com>
 * @copyriht Copyright (c) 2021 Century Business Solutions  (www.centurybizsolutions.com)
 * Created by PhpStorm
 * User: Mobeen
 * Date: 6/11/21
 * Time: 11:59 AM
 */
declare(strict_types=1);

namespace Ebizcharge\Ebizcharge\Block\Adminhtml;

use Magento\Backend\Block\Widget\Container;
use Magento\Backend\Block\Widget\Context;
use Ebizcharge\Ebizcharge\Block\Adminhtml\PaymentHistory\Grid as PaymentHistoryGrid;

/**
 * Subscriptions Payment History
 *
 * Class RecurringHistory
 */
class PaymentHistory extends Container
{
    /**
     * @var string
     */
    protected $_template = 'paymenthistory/view.phtml';

    /**
     * @param Context $context
     * @param array data
     */
    public function __construct(
        Context $context,
        array $data = []
    ) {
        parent::__construct($context, $data);
    }
    /**
     * {@inheritdoc}
     */
    protected function _prepareLayout()
    {
        $this->setChild('grid', $this->getLayout()->createBlock(PaymentHistoryGrid::class, 'grid.view.grid'));
        return parent::_prepareLayout();
    }
    /**
     * @return string
     */
    public function getGridHtml()
    {
        return $this->getChildHtml('grid');
    }

}
