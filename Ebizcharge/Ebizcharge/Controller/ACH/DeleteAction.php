<?php
/**
 * Deletes the customer's saved payment method.
 *
 * @author      Century Business Solutions <support@centurybizsolutions.com>
 * @copyright   Copyright (c) 2020 Century Business Solutions  (www.centurybizsolutions.com)
 */
declare(strict_types=1);

namespace Ebizcharge\Ebizcharge\Controller\ACH;

use Ebizcharge\Ebizcharge\Model\TranApi;
use Magento\Customer\Controller\AccountInterface;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Request\Http;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Data\Form\FormKey\Validator;
use Magento\Framework\Message\ManagerInterface;
use Magento\Store\Model\ScopeInterface;

/**
 * ACH Delete Action
 *
 * Class DeleteAction
 */
class DeleteAction implements AccountInterface, HttpGetActionInterface, HttpPostActionInterface
{
    const WRONG_REQUEST = 1;
    const WRONG_TOKEN = 2;
    const ACTION_EXCEPTION = 3;

    /**
     * @var array
     */
    private $errorsMap = [];

    /**
     * @var Validator
     */
    private $fkValidator;

    /**
     * @var TranApi
     */
    protected $_tran;

    /**
     * @var ScopeConfigInterface
     */
    protected $_scopeConfig;
    /**
     * @var RequestInterface
     */
    private $request;
    /**
     * @var ManagerInterface
     */
    private $messageManager;
    /**
     * @var RedirectFactory
     */
    private $redirectFactory;

    /**
     * @param ManagerInterface $messageManager
     * @param RedirectFactory $redirectFactory
     * @param RequestInterface $request
     * @param ScopeConfigInterface $scopeConfig
     * @param Session $customerSession
     * @param TranApi $tranApi
     * @param Validator $fkValidator
     */
    public function __construct(
        ManagerInterface $messageManager,
        RedirectFactory $redirectFactory,
        RequestInterface $request,
        ScopeConfigInterface $scopeConfig,
        Session $customerSession,
        TranApi $tranApi,
        Validator $fkValidator
    ) {
        $this->_scopeConfig = $scopeConfig;
        $this->_tran = $tranApi;
        $this->fkValidator = $fkValidator;
        $this->messageManager = $messageManager;
        $this->redirectFactory = $redirectFactory;
        $this->request = $request;
        $this->errorsMap = [
            self::WRONG_TOKEN => __('No token found.'),
            self::WRONG_REQUEST => __('Wrong request.'),
            self::ACTION_EXCEPTION => __('Deletion failure. Please try again.')
        ];
    }

    /**
     * Deletes customer's payment method.
     *
     * @return ResultInterface|ResponseInterface
     */
    public function execute()
    {
        if (!$this->request instanceof Http) {
            return $this->createErrorResponse(self::WRONG_REQUEST);
        }

        if (!$this->fkValidator->validate($this->request)) {
            return $this->createErrorResponse(self::WRONG_REQUEST);
        }

        $cid = $this->request->getParam('cid');
        $mid = $this->request->getParam('mid');

        if ($cid === null || $mid === null) {
            return $this->createErrorResponse(self::WRONG_TOKEN);
        }

        try {

            $this->_tran->setData('key', $this->_scopeConfig->getValue('payment/ebizcharge_ebizcharge/sourcekey',
                ScopeInterface::SCOPE_STORE));
            $this->_tran->setData('userid', $this->_scopeConfig->getValue('payment/ebizcharge_ebizcharge/sourceid',
                ScopeInterface::SCOPE_STORE));
            $this->_tran->setData('pin',  $this->_scopeConfig->getValue('payment/ebizcharge_ebizcharge/sourcepin',
                ScopeInterface::SCOPE_STORE));
            $this->_tran->setData('software','Magento2');

            $params = array(
                'securityToken' => $this->_tran->getUeSecurityToken(),
                'customerToken' => $cid,
                'paymentMethodId' => $mid,
            );

            $this->_tran->getClient()->deleteCustomerPaymentMethodProfile($params);

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
     * @return Redirect
     */
    private function createErrorResponse(int $errorCode): Redirect
    {
        $this->messageManager->addErrorMessage(
            $this->errorsMap[$errorCode]);

        return $this->redirectFactory->create()->setPath('ebizcharge/ach/listaction');
    }

    /**
     * Creates a success message, and passes it to the "Manage
     * My Payment Methods" page.
     *
     * @return Redirect
     */
    private function createSuccessMessage(): Redirect
    {
        $this->messageManager->addSuccessMessage(__('Bank Account successfully deleted.'));
        return $this->redirectFactory->create()->setPath('ebizcharge/ach/listaction');
    }
}
