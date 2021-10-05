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
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Request\Http;
use Magento\Framework\App\ResponseFactory;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Data\Form\FormKey\Validator;
use Magento\Framework\Exception\NotFoundException;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\UrlInterface;
use Magento\Ui\Component\MassAction\Filter;
use Ebizcharge\Ebizcharge\Model\ResourceModel\Recurring\CollectionFactory;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Ebizcharge\Ebizcharge\Api\Data\RecurringInterface;

class DeleteAction extends Action implements HttpGetActionInterface, HttpPostActionInterface
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
     * @var Filter
     */
    private $filter;
    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var ResponseFactory
     */
    private $responseFactory;

    /**
     * @var \Ebizcharge\Ebizcharge\Api\RecurringRepositoryInterface
     */
    private $recurringRepository;

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
     * @param CollectionFactory $collectionFactory
     * @param Filter $filter
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
        UrlInterface $url,
        CollectionFactory $collectionFactory,
        Filter $filter,
        \Ebizcharge\Ebizcharge\Api\RecurringRepositoryInterface $recurringRepository
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
        $this->filter = $filter;
        $this->collectionFactory = $collectionFactory;
        $this->recurringRepository = $recurringRepository;
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

        $internalId = $this->getRequest()->getParam('internal_id');
        $atype = $this->getRequest()->getParam('atype');

        if (!empty($internalId)) {
            $deleteItems = explode(',', $internalId);
        }

        $type = $atype == 'del' ? 'Suspended' : 'Unsubscribed';

        try {
            $statusId = $atype == 'del' ? 3 : 1;

            if (!empty($deleteItems)) {
                foreach ($deleteItems as $id) {
                    $params = array(
                        'securityToken' => $this->_tran->getUeSecurityToken(),
                        'scheduledPaymentInternalId' => $id,
                        'statusId' => $statusId,
                    );

                    $res = $this->_tran->getClient()->ModifyScheduledRecurringPaymentStatus($params);
                    $ModifyScheduledRecurringPaymentStatusResult = $res->ModifyScheduledRecurringPaymentStatusResult;
                    if (!empty($ModifyScheduledRecurringPaymentStatusResult) && $ModifyScheduledRecurringPaymentStatusResult->StatusCode == 1) {
                        try {
                            $recurringRecord = $this->recurringRepository->getById(
                                trim($id),
                                RecurringInterface::EB_REC_SCHEDULED_PAYMENT_INTERNAL_ID
                            );
                            $recurringRecord->setRecStatus((int) $statusId);
                            $this->recurringRepository->save($recurringRecord);
                        } catch (\Exception $e) {
                            $this->_tran->ebizLog()->crit($e->getMessage());
                        }
                    }
                }
            }

        } catch (\Exception $e) {

            return $this->createErrorResponse(self::ACTION_EXCEPTION);
        }

        return $this->createSuccessMessage($type);
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

        return $this->_redirect('ebizcharge_ebizcharge/recurrings');
    }

    /**
     * Creates a success message, and passes it to the "Manage
     * My Payment Methods" page.
     *
     * @return ResponseInterface
     */
    private function createSuccessMessage($type)
    {
        $this->messageManager->addSuccessMessage(__('Subscription(s) successfully ' . $type . '.'));
        return $this->_redirect('ebizcharge_ebizcharge/recurrings');
    }
}
