<?php
/**
 * Sync EBizCharge Connect customers to Magento.
 *
 * @author      Century Business Solutions <support@centurybizsolutions.com>
 * @copyright   Copyright (c) 2020 Century Business Solutions (www.centurybizsolutions.com)
 */

namespace Ebizcharge\Ebizcharge\Controller\Adminhtml\System\Config;

use Ebizcharge\Ebizcharge\Model\Data;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;

class DownloadCustomers extends Action
{
    /**
     * @var JsonFactory
     */
    private $resultJsonFactory;

    /**
     * @var Data
     */
    private $dataClass;

    /**
     * @param Data $dataClass
     * @param Context $context
     * @param JsonFactory $resultJsonFactory
     */
    public function __construct(
        Data $dataClass,
        Context $context,
        JsonFactory $resultJsonFactory
    )
    {
        $this->resultJsonFactory = $resultJsonFactory;
        parent::__construct($context);
        $this->dataClass = $dataClass;
    }

    /**
     * Execute
     * Ebizcharge\Ebizcharge\Controller\Adminhtml\System\Config\SyncCustomers::execute
     * @return \Magento\Framework\Controller\Result\Json
     */
    public function execute()
    {
        $result = $this->resultJsonFactory->create();
        $dataclass = $this->dataClass;
        $value = $dataclass->downloadCustomer();
        //return $result->setData(['success' => true, 'time' => $value]);
    }
}
