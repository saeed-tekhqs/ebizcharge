<?php
/**
 * Accesses data to pass to the 'Manage My Payment Method' pages.
 *
 * @author      Century Business Solutions <support@centurybizsolutions.com>
 * @copyright   Copyright (c) 2020 Century Business Solutions  (www.centurybizsolutions.com)
 */

namespace Ebizcharge\Ebizcharge\Block\Customer\Account;

use Ebizcharge\Ebizcharge\Api\Data\RecurringInterface;
use Ebizcharge\Ebizcharge\Model\Config as EbizConfig;
use Ebizcharge\Ebizcharge\Model\Token;
use Ebizcharge\Ebizcharge\Model\TranApi;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Response\Http;
use Magento\Framework\App\Response\RedirectInterface;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\View\Element\Template;
use Magento\Payment\Model\Config;
use Magento\Framework\Api\SearchCriteriaBuilder;

class Cards extends Template
{
    /**
     * @var Token
     */
    private $customerTokenManagement;

    /**
     * @var mixed
     */
    private $mage_cust_id;

    /**
     * @var mixed
     */
    private $ebzc_cust_id;

    /**
     * @var Session
     */
    private $customerSession;

    /**
     * @var TranApi
     */
    private $tranApi;

    /**
     * @var Config
     */
    private $_paymentConfig;

    /**
     * @var EbizConfig
     */
    private $_myConfig;

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

    /**
     * @var \Ebizcharge\Ebizcharge\Api\RecurringRepositoryInterface
     */
    private $recurringRepository;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @param ManagerInterface $messageManager
     * @param Http $response
     * @param RedirectInterface $redirect
     * @param Template\Context $context
     * @param Token $customerTokenManagement
     * @param TranApi $tranApi
     * @param Session $session
     * @param Config $paymentConfig
     * @param EbizConfig $config
     * @param array $data
     */
    public function __construct(
        ManagerInterface $messageManager,
        Http $response,
        RedirectInterface $redirect,
        Template\Context $context,
        Token $customerTokenManagement,
        TranApi $tranApi,
        Session $session,
        Config $paymentConfig,
        EbizConfig $config,
        \Ebizcharge\Ebizcharge\Api\RecurringRepositoryInterface $recurringRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        array $data = [])
    {
        parent::__construct($context, $data);
        $this->customerTokenManagement = $customerTokenManagement;
        $this->tranApi = $tranApi;
        $this->customerSession = $session;
        $this->_paymentConfig = $paymentConfig;
        $this->_myConfig = $config;

        $customer = $this->customerSession->getCustomer();
        $this->mage_cust_id = $customer->getId();
        $this->ebzc_cust_id = $this->customerTokenManagement->getCollection()
            ->addFieldToFilter('mage_cust_id', $customer->getId())
            ->getFirstItem()
            ->getEbzcCustId();

        $this->tranApi->setAchStatus($this->_myConfig->isAchActive());

        $this->verifyEditAction();

        $this->messageManager = $messageManager;
        $this->response = $response;
        $this->redirect = $redirect;
        $this->recurringRepository = $recurringRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
    }

    /**
     * Get Ach status from current payment object
     *
     * @return bool
     */
    public function getAchStatus(): bool
    {
        return $this->tranApi->getAchStatus();
    }

    public function getEbzcCustId()
    {
        return $this->ebzc_cust_id;
    }

    public function getMageCustId()
    {
        return $this->mage_cust_id;
    }

    public function getEbzcMethodId()
    {
        $mid = $this->getRequest()->getParam('mid');
        return $mid;
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
        return $this->getUrl('ebizcharge/cards/addaction/');
    }

    public function getSaveUrl()
    {
        return $this->_urlBuilder->getUrl('ebizcharge/cards/saveaction', ['_secure' => true]);
    }

    public function getPaymentCards()
    {
        $collection = $this->customerTokenManagement->getCollection()
            ->addFieldToFilter('mage_cust_id', $this->customerSession->getCustomerId());

        return $collection;
    }

    public function getConfig($path)
    {
        return $this->_myConfig->getConfig($path);
    }

    public function getCcTypes()
    {
        return $this->_paymentConfig->getCcTypes();
    }

    public function getCCTypeImage($cardType)
    {
        $image = '';
        switch (strtolower($cardType)) {
            case 'v':
            case 'vi':
                $image = 'visa.png';
                break;
            case 'ae':
            case 'a':
                $image = 'american_express.png';
                break;
            case 'mc':
            case 'm':
                $image = 'mastercard.png';
                break;
            case 'ds':
                $image = 'discover.png';
                break;
            case 'jcb':
                $image = 'visa.png';
                break;
        }

        return $image;
    }
    // for user account payment methods listing
    public function getPaymentMethods()
    {
        return $this->tranApi->getCustomerPaymentMethods($this->ebzc_cust_id);
    }

    public function getCustomerPaymentMethod()
    {
        $mid = $this->getRequest()->getParam('mid');
        $cid = $this->getRequest()->getParam('cid');

        return $this->tranApi->getCustomerPaymentMethodProfile($cid, $mid);
    }

    /**
     * @return void
     */
    private function verifyEditAction(): void
    {
        if ($this->getRequest()->getFullActionName() == 'ebizcharge_cards_editaction') {
            $cid = $this->getRequest()->getParam('cid');
            $mid = $this->getRequest()->getParam('mid');
            $method = $this->getRequest()->getParam('method');
            if ($cid && $mid && $method) {
                return;
            }
            $this->messageManager->addErrorMessage(__('Unable to update card.'));
            $this->redirect->redirect($this->response, '*/*/listaction');
        }

    }

    /**
     * This method verify either payment method is associated to some recurring that is under way
     *
     * @param int $customerId
     * @param string $methodId
     * @return bool
     */
    public function checkPaymentMethodStatus(int $customerId, $methodId): bool
    {
        try {
            $searchCriteria = $this->searchCriteriaBuilder
                ->addFilter(RecurringInterface::MAGE_CUST_ID, $customerId)
                ->addFilter(RecurringInterface::EB_REC_METHOD_ID, $methodId)
                ->addFilter(RecurringInterface::REC_STATUS, 0);
            $paymentMethodRecurrings = $this->recurringRepository->getList($searchCriteria->create());

            return count($paymentMethodRecurrings->getItems()) > 0;
        } catch (\Exception $e) {
            return false;
        }
    }

}
