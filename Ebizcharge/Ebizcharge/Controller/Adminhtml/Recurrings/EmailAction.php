<?php
/**
 * Deletes the customer's saved payment method.
 *
 * @author      Century Business Solutions <support@centurybizsolutions.com>
 * @copyright   Copyright (c) 2020 Century Business Solutions  (www.centurybizsolutions.com)
 */

namespace Ebizcharge\Ebizcharge\Controller\Adminhtml\Recurrings;

use Ebizcharge\Ebizcharge\Model\TranApi;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Backend\App\Action;
use Magento\Framework\App\ActionInterface;


/**
 * Email Action class
 *
 * Class EmailAction
 */
class EmailAction extends Action implements HttpGetActionInterface
{
    /**
     * @var TranApi
     */
    private $_tran;

    public function __construct(Context $context, TranApi $_tran)
    {
        parent::__construct($context);
        $this->_tran = $_tran;
    }

    /**
     * @return int
     */
    public function execute()
    {
        //call email list get ref
        // call renderReceipt to get base64 response
        //  decode response and return

        if ($this->_request->getParam('tid') !== null) {
            $receiptRefNum = $_POST['rid'] ?? $this->_tran->getReceiptRefNumber();
            $params = array(
                'securityToken' => $this->_tran->getUeSecurityToken(),
                'transactionRefNum' => $this->_request->getParam('tid'),
                'receiptRefNum' => $receiptRefNum,
                'emailAddress' => $this->_request->getParam('email'),
            );

            $emailReceipt = $this->_tran->getClient()->EmailReceipt($params);

            $emailReceiptResult = $emailReceipt->EmailReceiptResult;

            if ($emailReceiptResult->StatusCode === 1) {
                $this->messageManager->addSuccessMessage(__('Email sent successfully!'));
            } else {
                $this->messageManager->addErrorMessage(__('Email not sent!'));
            }
            return $this->resultFactory->create(ResultFactory::TYPE_JSON)
                ->setData(['html_data' => $emailReceiptResult->StatusCode]);
        }
        return 0;
    }
}
