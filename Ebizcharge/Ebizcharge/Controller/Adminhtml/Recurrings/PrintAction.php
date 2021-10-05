<?php
/**
 * Deletes the customer's saved payment method.
 *
 * @author      Century Business Solutions <support@centurybizsolutions.com>
 * @copyright   Copyright (c) 2020 Century Business Solutions  (www.centurybizsolutions.com)
 */

namespace Ebizcharge\Ebizcharge\Controller\Adminhtml\Recurrings;

use Ebizcharge\Ebizcharge\Model\TranApi;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Customer\Model\Session;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Backend\App\Action\Context;
use Magento\Backend\App\Action;

class PrintAction extends Action implements HttpGetActionInterface
{
    protected $_tran;
    protected $_scopeConfig;

    /**
     * @param Context $context
     * @param Session $customerSession
     * @param JsonFactory $jsonFactory
     * @param TranApi $tranApi
     */
    public function __construct(
        Context $context,
        Session $customerSession,
        JsonFactory $jsonFactory,
        TranApi $tranApi
    )
    {
        parent::__construct($context);
        $this->_tran = $tranApi;
    }

    /**
     * @return int
     */
    public function execute()
    {
        if ($this->_request->getParam('tid') !== null) {
            $receiptRefNum = $this->_tran->getReceiptRefNumber();
            $params = array(
                'securityToken' => $this->_tran->getUeSecurityToken(),
                'transactionRefNum' => $this->_request->getParam('tid'),
                'receiptRefNum' => $receiptRefNum,
                'contentType' => 'html',
            );

            $renderReceipt = $this->_tran->getClient()->RenderReceipt($params);

            $GetRenderReceiptResult = $renderReceipt->RenderReceiptResult;

            return  $this->resultFactory->create(ResultFactory::TYPE_JSON)->setData(['html_data' => $GetRenderReceiptResult]);
        }

        return 0;
    }
}
