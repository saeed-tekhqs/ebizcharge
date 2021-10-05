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
use Magento\Customer\Model\Session;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Request\Http;
use Magento\Framework\App\ResponseFactory;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Data\Form\FormKey\Validator;
use Magento\Framework\Exception\NotFoundException;
use Magento\Framework\App\RequestInterface;
use Magento\Backend\App\Action;
use Magento\Framework\UrlInterface;

class EmailBulkAction extends Action implements HttpGetActionInterface
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
     * @throws NotFoundException
     */
    public function execute()
    {
        $request = $this->_request;

        if (!$request instanceof Http) {
            return $this->createErrorResponse(self::WRONG_REQUEST);
        }

        if (!$this->fkValidator->validate($request)) {
            return $this->createErrorResponse(self::WRONG_REQUEST);
        }

        $internalId = $this->getRequest()->getParam('massActionField');

        if (!empty($internalId)) {
            $selected = explode(',', $internalId);
        }

        try {
            $ueSecurityToken = $this->_tran->getUeSecurityToken();
            $receiptRefNum = $_POST['rid'] ?? $this->_tran->getReceiptRefNumber();
            $client = $this->_tran->getClient();

            if (!empty($selected)) {

                foreach ($selected as $id) {

                    $selectedStrArray = explode('#', $id);

                    $tid = $selectedStrArray[0];
                    $email = $selectedStrArray[1];

                    $params = array(
                        'securityToken' => $ueSecurityToken,
                        'transactionRefNum' => $tid,
                        'receiptRefNum' => $receiptRefNum,
                        'emailAddress' => $email,
                    );

                    $GetEmailReceipt = $client->EmailReceipt($params);

                    $GetEmailReceiptResult = $GetEmailReceipt->EmailReceiptResult;
                    echo $GetEmailReceiptResult->StatusCode;
                }
            }

        } catch (\Exception $e) {

            return $this->createErrorResponse(self::ACTION_EXCEPTION);
        }

        return $this->createSuccessMessage();
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

        return $this->_redirect('ebizcharge_ebizcharge/recurrings/history');
    }

    /**
     * Creates a success message, and passes it to the "Manage
     * My Payment Methods" page.
     *
     * @return ResponseInterface
     */
    private function createSuccessMessage()
    {
        $this->messageManager->addSuccessMessage(__('Email(s) sent successfully.'));
        return $this->_redirect('ebizcharge_ebizcharge/recurrings/history');
    }
}
