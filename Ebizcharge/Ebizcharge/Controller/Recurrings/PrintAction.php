<?php
/**
 * @author      Century Business Solutions <support@centurybizsolutions.com>
 * @copyright   Copyright (c) 2020 Century Business Solutions  (www.centurybizsolutions.com)
 */

namespace Ebizcharge\Ebizcharge\Controller\Recurrings;

use Ebizcharge\Ebizcharge\Model\TranApi;
use Magento\Customer\Controller\AccountInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Message\ManagerInterface;

/**
 * Print action for recurring
 *
 * Class PrintAction
 */
class PrintAction implements AccountInterface
{
    /**
     * @var JsonFactory
     */
    private $resultJsonFactory;

    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @var TranApi
     */
    private $tranApi;

    /**
     * @var ManagerInterface
     */
    private $messageManager;

    /**
     * @param JsonFactory $resultJsonFactory
     * @param ManagerInterface $messageManager
     * @param RequestInterface $request
     * @param TranApi $tranApi
     */
    public function __construct(
        JsonFactory $resultJsonFactory,
        ManagerInterface $messageManager,
        RequestInterface $request,
        TranApi $tranApi
    ) {
        $this->resultJsonFactory = $resultJsonFactory;
        $this->messageManager = $messageManager;
        $this->request = $request;
        $this->tranApi = $tranApi;
    }

    /**
     * @return int|Json
     */
    public function execute()
    {
        $transactionRefNum = $this->request->getParam('tid');
        $receiptRefNum = $this->request->getParam('rid');

        if ($transactionRefNum && $receiptRefNum) {
            try {
                $params = array(
                    'securityToken' => $this->tranApi->getUeSecurityToken(),
                    'transactionRefNum' => $transactionRefNum,
                    'receiptRefNum' => $receiptRefNum,
                    'contentType' => 'html',
                );

                $getRenderReceipt = $this->tranApi->getClient()->RenderReceipt($params);
                return $this->resultJsonFactory->create()->setData(['html_data' => $getRenderReceipt->RenderReceiptResult]);
            } catch (\Exception $ex) {
                $this->messageManager->addExceptionMessage($ex, __('No Receip Found on Server to Print.'));
            }
        }

        return 0;
    }
}
