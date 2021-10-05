<?php
/**
 * @author      Century Business Solutions <support@centurybizsolutions.com>
 * @copyright   Copyright (c) 2020 Century Business Solutions  (www.centurybizsolutions.com)
 */
declare(strict_types=1);

namespace Ebizcharge\Ebizcharge\Controller\Recurrings;

use Ebizcharge\Ebizcharge\Model\TranApi;
use Magento\Customer\Controller\AccountInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\Result\JsonFactory;

/**
 * Deletes the customer's saved payment method.
 *
 * Class EmailAction
 */
class EmailAction implements AccountInterface
{
    /**
     * @var TranApi
     */
    private $tranApi;

    /**
     * @var RequestInterface
     */
    private $request;
    /**
     * @var JsonFactory
     */
    private $resultJsonFactory;

    /**
     * @param JsonFactory $resultJsonFactory
     * @param RequestInterface $request
     * @param TranApi $tranApi
     */
    public function __construct(
        JsonFactory $resultJsonFactory,
        RequestInterface $request,
        TranApi $tranApi
    ) {
        $this->resultJsonFactory = $resultJsonFactory;
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
        $emailAddress = $this->request->getParam('email');

        if ($transactionRefNum && $receiptRefNum && $emailAddress) {
            $params = array(
                'securityToken' => $this->tranApi->getUeSecurityToken(),
                'transactionRefNum' => $transactionRefNum,
                'receiptRefNum' => $receiptRefNum,
                'emailAddress' => $emailAddress,
            );

            $emailReceipt = $this->tranApi->getClient()->EmailReceipt($params);

            $getEmailReceiptResult = $emailReceipt->EmailReceiptResult;
            return $this->resultJsonFactory->create()->setData(['html_data' => $getEmailReceiptResult->StatusCode]);
        }

        return 0;
    }
}
