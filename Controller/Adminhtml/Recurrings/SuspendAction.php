<?php
/**
 * Deletes the customer's saved payment method.
 *
 * @author      Century Business Solutions <support@centurybizsolutions.com>
 * @copyright   Copyright (c) 2020 Century Business Solutions  (www.centurybizsolutions.com)
 */

namespace Ebizcharge\Ebizcharge\Controller\Adminhtml\Recurrings;

use Ebizcharge\Ebizcharge\Model\Token;
use Ebizcharge\Ebizcharge\Model\TranApi;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\ResponseFactory;
use Magento\Framework\Controller\ResultFactory;

use Magento\Customer\Model\Session;
use Magento\Framework\App\Request\Http;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Data\Form\FormKey\Validator;
use Magento\Framework\Exception\NotFoundException;
use Magento\Framework\App\RequestInterface;
use Magento\Backend\App\Action\Context;
use Magento\Backend\App\Action;
use Magento\Framework\UrlInterface;

class SuspendAction extends Action
{
    const WRONG_REQUEST = 1;
    const WRONG_TOKEN = 2;
    const ACTION_EXCEPTION = 3;

    private $errorsMap = [];
    private $jsonFactory;
    private $fkValidator;
    private $token;
    protected $_tran;
    protected $_scopeConfig;

    /**
     * @param Context $context
     * @param Session $customerSession
     * @param JsonFactory $jsonFactory
     * @param Validator $fkValidator
     * @param ScopeConfigInterface $scopeConfig
     * @param Token $token
     * @param TranApi $tranApi
     * @param ResponseFactory $responseFactory
     * @param UrlInterface $url
     */
    public function __construct(
        Context $context,
        Session $customerSession,
        JsonFactory $jsonFactory,
        Validator $fkValidator,
        ScopeConfigInterface $scopeConfig,
        Token $token,
        TranApi $tranApi,
        ResponseFactory $responseFactory,
        UrlInterface $url
    )
    {
        parent::__construct($context);
        $this->jsonFactory = $jsonFactory;
        $this->fkValidator = $fkValidator;
        $this->token = $token;
        $this->_tran = $tranApi;
        $this->responseFactory = $responseFactory;
        $this->url = $url;
        $this->_scopeConfig = $scopeConfig;

        $this->errorsMap = [
            self::WRONG_TOKEN => __('No token found.'),
            self::WRONG_REQUEST => __('Wrong request.'),
            self::ACTION_EXCEPTION => __('Deletion failure. Please try again.')];
    }

    /**
     * Deletes customer's payment method.
     *
     * @return ResultInterface|ResponseInterface
     */
    public function execute()
    {
        $ueSecurityToken = $this->_tran->getUeSecurityToken();
        $client = $this->_tran->getClient();

        $response = $client->SearchRecurringPayments(
            array(
                'securityToken' => $ueSecurityToken,
                'fromDateTime' => '2019-10-01',
                'toDateTime' => '2028-12-30',
                'start' => 0,
                'limit' => 1000,
            ));

        $recPayment = $response->SearchRecurringPaymentsResult->Payment;
        $decline = array();
        $i = 0;
        if (!empty($recPayment)) {
            foreach ($recPayment as $payment) {

                $responseGetTransactionDetails = $client->GetTransactionDetails(
                    array(
                        'securityToken' => $ueSecurityToken,
                        'transactionRefNum' => $payment->RefNum,
                    )
                );

                $GetGetTransactionDetailsResult = $responseGetTransactionDetails->GetTransactionDetailsResult;
                $paymentRes = $GetGetTransactionDetailsResult->Response;

                if ($paymentRes->ResultCode == 'D') {
                    $decline[] = $payment->PaymentMethod . '-' . $payment->Last4 . '-' . 'Declined' . '+++' . $payment->ScheduledPaymentInternalId;
                }
            }
        }

        if (!empty($recPayment)) {
            foreach (array_count_values($decline) as $key => $val) {
                if ($val > 2) {
                    $explodeArray = explode("+++", $key);
                    $inernalSheduleId = $explodeArray[1];
                    $params = array(
                        'securityToken' => $ueSecurityToken,
                        'scheduledPaymentInternalId' => $inernalSheduleId,
                        'statusId' => 1,
                    );

                    $client->ModifyScheduledRecurringPaymentStatus($params);
                }
            }
        }
    }

    /**
     * Creates an error message, and passes it to the "Manage
     * My Payment Methods" page.
     *
     * @param int $errorCode
     * @return ResponseInterface
     */
    private function createErrorResponse($errorCode)
    {
        $this->messageManager->addErrorMessage(
            $this->errorsMap[$errorCode]);

        return $this->_redirect('ebizcharge/recurrings/listaction');
    }

    /**
     * Creates a success message, and passes it to the "Manage
     * My Payment Methods" page.
     *
     * @return ResponseInterface
     */
    private function createSuccessMessage()
    {
        $this->messageManager->addSuccessMessage(__('Subscription(s) successfully Deleted.'));
        return $this->_redirect('ebizcharge/recurrings/listaction');
    }
}
