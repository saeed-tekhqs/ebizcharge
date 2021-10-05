<?php
/**
 * Accesses data to pass to the 'Manage My Payment Method' pages.
 *
 * @author      Century Business Solutions <support@centurybizsolutions.com>
 * @copyright   Copyright (c) 2020 Century Business Solutions  (www.centurybizsolutions.com)
 */

namespace Ebizcharge\Ebizcharge\Block\Customer\Account;

use Magento\Customer\Model\Session;
use Magento\Framework\App\Response\Http;
use Magento\Framework\App\Response\RedirectInterface;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\View\Element\Template;
use Ebizcharge\Ebizcharge\Model\Token;
use Ebizcharge\Ebizcharge\Model\TranApi;

class ACH extends Template
{
    private $customerTokenManagement;
    protected $_mage_cust_id;
    protected $_ebzc_cust_id;
    protected $_customerSession;
    protected $_tran;
    protected $_paymentConfig;
    protected $_myConfig;

    /**
     * @var ManagerInterface
     */
    private $messageManager;

    /**
     * @var Http
     */
    private $response;


    /**
     * @var RedirectInterface
     */
    private $redirect;

    public function __construct(
        ManagerInterface $messageManager,
        Http $response,
        RedirectInterface $redirect,
        Template\Context $context,
        Token $customerTokenManagement,
        TranApi $tranApi,
        Session $session,
        \Magento\Payment\Model\Config $paymentConfig,
        \Ebizcharge\Ebizcharge\Model\Config $config,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->customerTokenManagement = $customerTokenManagement;
        $this->_tran = $tranApi;
        $this->_customerSession = $session;
        $this->_paymentConfig = $paymentConfig;
        $this->_myConfig = $config;

        $customer = $this->_customerSession->getCustomer();
        $this->_mage_cust_id = $customer->getId();
        $this->_ebzc_cust_id = $this->customerTokenManagement->getCollection()
            ->addFieldToFilter('mage_cust_id', $customer->getId())
            ->getFirstItem()
            ->getEbzcCustId();

        $this->_tran->setAchStatus($this->_myConfig->isAchActive());

        $this->messageManager = $messageManager;
        $this->response = $response;
        $this->redirect = $redirect;
        $this->verifyEditAction();

    }

    public function getAchStatus()
    {
        return $this->_tran->getAchStatus();
    }

    public function getEbzcCustId()
    {
        return $this->_ebzc_cust_id;
    }

    public function getMageCustId()
    {
        return $this->_mage_cust_id;
    }

    public function getEbzcMethodId()
    {
        return $this->getRequest()->getParam('mid');
    }

    public function getMethodName()
    {
        $method = $this->getRequest()->getParam('method');
        return urldecode($method);
    }

    public function getBackUrl()
    {
        return $this->getUrl('customer/account/');
    }

    public function getAddCardUrl()
    {
        return $this->getUrl('ebizcharge/ach/addaction/');
    }

    public function getSaveUrl()
    {
        return $this->_urlBuilder->getUrl('ebizcharge/ach/saveaction', ['_secure' => true]);
    }

    public function getConfig($path)
    {
        return $this->_myConfig->getConfig($path);
    }

    public function getCcTypes()
    {
        return $this->_paymentConfig->getCcTypes();
    }

    // for user account payment methods listing
    public function getPaymentMethods()
    {
        return $this->_tran->getSavedAccounts($this->getEbzcCustId());
    }

    public function getCustomerPaymentMethod()
    {
        $mid = $this->getRequest()->getParam('mid');
        $cid = $this->getRequest()->getParam('cid');
        return $this->_tran->getCustomerPaymentMethodProfile($cid, $mid);
    }

    /**
     * @return void
     */
    private function verifyEditAction(): void
    {
        if ($this->getRequest()->getFullActionName() == 'ebizcharge_ach_editaction') {
            $cid = $this->getRequest()->getParam('cid');
            $mid = $this->getRequest()->getParam('mid');
            $method = $this->getRequest()->getParam('method');

            if ($cid && $mid && $method) {
                return;
            }
            $this->messageManager->addErrorMessage(__('Unable to update bank account.'));
            $this->redirect->redirect($this->response, '*/*/listaction');
        }

    }
}
