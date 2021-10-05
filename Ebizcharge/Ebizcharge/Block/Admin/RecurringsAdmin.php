<?php
/**
 * Accesses data to pass to the 'Manage My Payment Method' pages.
 *
 * @author      Century Business Solutions <support@centurybizsolutions.com>
 * @copyright   Copyright (c) 2020 Century Business Solutions  (www.centurybizsolutions.com)
 */

namespace Ebizcharge\Ebizcharge\Block\Admin;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Customer\Block\Address\Renderer\RendererInterface;
use Magento\Customer\Model\Session;
use Magento\Framework\View\Element\Template;
use Magento\Store\Model\StoreManagerInterface;
use Ebizcharge\Ebizcharge\Model\Token;
use Ebizcharge\Ebizcharge\Model\TranApi;

class RecurringsAdmin extends Template
{
    private $customerTokenManagement;
    protected $_mage_cust_id;
    protected $_ebzc_cust_id;
    protected $_customerSession;
    protected $_tran;
    protected $_paymentConfig;
    protected $_myConfig;
    protected $_resources;

    /**
     * @var \Magento\Sales\Model\Order
     */
    private $orderFactory;

    /**
     * @var \Magento\Customer\Model\CustomerFactory
     */
    private $customerFactory;

    /**
     * @var \Ebizcharge\Ebizcharge\Model\Data
     */
    private $dataClass;

    /**
     * @var \Ebizcharge\Ebizcharge\Api\RecurringRepositoryInterface
     */
    private $recurringRepository;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory
     */
    private $productCollection;

    /**
     * @var \Magento\Sales\Api\OrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * @var \Magento\Customer\Api\AddressRepositoryInterface
     */
    private $addressRepository;

    /**
     * @var \Magento\Customer\Model\Address\Config
     */
    private $addressConfig;

    /**
     * @var \Magento\Customer\Model\Address\Mapper
     */
    private $addressMapper;

    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    private $productRepository;

    public function __construct(
        Template\Context $context,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        Token $customerTokenManagement,
        TranApi $tranApi,
        Session $session,
        \Magento\Payment\Model\Config $paymentConfig,
        \Ebizcharge\Ebizcharge\Model\Config $config,
        \Magento\Customer\Model\AddressFactory $addressFactory,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Shipping\Model\Config $shippingConfig,
        \Magento\Sales\Model\OrderFactory $orderFactory,
        \Ebizcharge\Ebizcharge\Model\Data $dataClass,
        \Ebizcharge\Ebizcharge\Api\RecurringRepositoryInterface $recurringRepository,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollection,
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
        \Magento\Customer\Api\AddressRepositoryInterface $addressRepository,
        \Magento\Customer\Model\Address\Config $addressConfig,
        \Magento\Customer\Model\Address\Mapper $addressMapper,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        array $data = []
    )
    {
        parent::__construct($context, $data);
        $this->customerFactory = $customerFactory;
        $this->customerTokenManagement = $customerTokenManagement;
        $this->_tran = $tranApi;
        $this->_customerSession = $session;
        $this->_paymentConfig = $paymentConfig;
        $this->_myConfig = $config;
        $this->_addressFactory = $addressFactory;
        $this->_resources = $config->getQueryResourceConnection();
        $this->_scopeConfig = $scopeConfig;
        $this->_shippingConfig = $shippingConfig;
        $this->orderFactory = $orderFactory;
        $this->dataClass = $dataClass;
        $this->recurringRepository = $recurringRepository;
        $this->productCollection = $productCollection;
        $this->orderRepository = $orderRepository;
        $this->addressRepository = $addressRepository;
        $this->addressConfig = $addressConfig;
        $this->addressMapper = $addressMapper;
        $this->productRepository = $productRepository;
    }

    public function getCustomerDetail($customerID)
    {
        if (!empty($customerID)) {
           return $this->customerFactory->create()->load($customerID);
        }
        return NULL;
    }

    public function getOrderEntityId($orderIncrementId)
    {
        return $this->orderFactory->create()->loadByIncrementId($orderIncrementId)->getId();
    }

    public function getCustomerAddressList($selectedId = '')
    {
        return $this->dataClass->getCustomerAddressList($this->getMageCustId(), $selectedId);
    }

    public function getCustomerCollection($id)
    {
        return $this->customerFactory->create()->getCollection()
            ->addAttributeToSelect("firstname,lastname")
            ->addAttributeToFilter("entity_id", array("eq" => $id))
            ->load();
    }

    public function getEbzcCustInternalId()
    {
        $customer = $this->getCustomerDetail($this->getMageCustId());

        return $customer['ec_cust_internalid'] ?? '';
    }

    public function getEbzcStart()
    {
        return $this->getRequest()->getParam('sid');
    }

    public function getEbzLimit()
    {
        return $this->getRequest()->getParam('limit');
    }

    public function getEbzcCustId()
    {
        return $this->getRequest()->getParam('cid');
    }

    public function getMageCustId()
    {
        return $this->getRequest()->getParam('magcid');
    }

    public function getPageNo()
    {
        return $this->getRequest()->getParam('page');
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
        return $this->getUrl('ebizcharge/cards/addaction/');
    }

    public function getSaveUrl()
    {
        return $this->_urlBuilder->getUrl('ebizcharge/recurrings/saveaction', ['_secure' => true]);
    }

    public function getPaymentCards()
    {
        return $this->customerTokenManagement->getCollection()
            ->addFieldToFilter('mage_cust_id', $this->_customerSession->getCustomerId());
    }

    public function getConfig($path)
    {
        return $this->_myConfig->getConfig($path);
    }

    public function getCcTypes()
    {
        return $this->_paymentConfig->getCcTypes();
    }

    public function getPaymentMethods()
    {
        $ebizCustomer = $this->_tran->getCustomer($this->getMageCustId());

        if ($ebizCustomer !== null) {
            $profiles = isset($ebizCustomer->PaymentMethodProfiles)
                ? $ebizCustomer->PaymentMethodProfiles->PaymentMethodProfile ?? []
                : [];

            if (is_object($profiles)) {
                $paymentMethods[] = $profiles;
            } else {
                $paymentMethods = $profiles;
            }
            return $paymentMethods;
        }

        return [];
    }

    // used for suspend recurring cronjob
    // Todo: Fix logic
    public function checkTransactionStatus()
    {
        $ueSecurityToken = $this->_tran->getUeSecurityToken();
        $client = $this->_tran->getClient();

        $response = $client->SearchRecurringPayments(
            array(
                'securityToken' => $ueSecurityToken,
                'fromDateTime' => '2020-12-01',
                'toDateTime' => date('Y-m-d'),
                'start' => 0,
                'limit' => 1000,
            ));

        $recurringPayments = $response->SearchRecurringPaymentsResult->Payment;

        $declineTransactions = array();

        if (!empty($recurringPayments)) {

            foreach ($recurringPayments as $payment) {

                $responseGetTransactionDetails = $client->GetTransactionDetails(
                    array(
                        'securityToken' => $ueSecurityToken,
                        'transactionRefNum' => $payment->RefNum,
                    ));

                $transactionResult = $responseGetTransactionDetails->GetTransactionDetailsResult;
                $paymentRes = $transactionResult->Response;

                if ($paymentRes->ResultCode == 'D') {
                    $declineTransactions[$payment->ScheduledPaymentInternalId][] = 1;
                }
            }
        }

        if (!empty($declineTransactions)) {

            foreach ($declineTransactions as $scheduledPaymentInternalId => $val) {
                if (count($val) > 2) {
                    // suspend recurring
                    $params = array(
                        'securityToken' => $ueSecurityToken,
                        'scheduledPaymentInternalId' => $scheduledPaymentInternalId,
                        'statusId' => 1,
                    );

                    $client->ModifyScheduledRecurringPaymentStatus($params);
                }
            }
        }
    }

    public function getSearchScheduledRecurringPayments()
    {
        return $this->_tran->getSearchScheduledRecurringPayments(
            $this->getMageCustId(),
            $this->getEbzcCustInternalId(),
            $this->getRequest()->getParam('mid')
        );
    }

    public function getAllSearchRecurringPayments($start = 0, $limit = 10)
    {
        return $this->_tran->getSearchTransactions(null, $start, $limit);
    }

    public function getReceiptRefNumber()
    {
        return $this->_tran->getReceiptRefNumber();
    }

    /* Add new payment functions */
    public function getRequestCardCodeAdmin()
    {
        return $this->_myConfig->getRequestCardCodeAdmin() == 1;
    }

    public function saveCardEnabled()
    {
        return $this->_myConfig->saveCard();
    }

    public function getPaymentCctypes()
    {
        return explode(',', $this->_myConfig->getPaymentCctypes());
    }

    public function getCcAvailableTypes()
    {
        $applicableTypes = $this->getPaymentCctypes();
        $types = $this->_paymentConfig->getCcTypes();

        foreach (array_keys($types) as $code) {
            if (!in_array($code, $applicableTypes)) {
                unset($types[$code]);
            }
        }

        return $types;
    }

    public function getCcMonths()
    {
        $months = $this->getData('cc_months');

        if ($months === null) {
            $months[0] = __('Month');
            $months = array_merge($months, $this->_paymentConfig->getMonths());
            $this->setData('cc_months', $months);
        }

        return $months;
    }

    public function getCcYears()
    {
        $years = $this->getData('cc_years');

        if ($years === null) {
            $years = $this->_paymentConfig->getYears();
            $years = [0 => __('Year')] + $years;
            $this->setData('cc_years', $years);
        }

        return $years;
    }

    public function getPaymentSavePayment()
    {
        return $this->_myConfig->getPaymentSavePayment();
    }

    //*********** for add new subscription **************//

    public function getAllItemsList()
    {
        $collection = $this->productCollection->create()
            ->addAttributeToSelect('*')
            ->addAttributeToSort('name')
            ->load();

        foreach ($collection as $product) {
			$itemPrice = (!empty($product->getSpecialPrice())) ? $product->getSpecialPrice() : $product->getPrice();
            if (($product->getTypeId() != 'configurable') && ($product->getTypeId() != 'grouped') && ($itemPrice > 0)) {
                ?>
                <option value="<?php echo $product->getId(); ?>"><?php echo $product->getName(); ?>
                    (<?php echo "Price: " . number_format((float)$itemPrice, 2, '.', ''); ?>)
                </option>
                <?php
            }
        }
    }

    public function getAllActiveCustomersList()
    {
        //Get customer collection
        $customerCollection = $this->customerFactory->create()->getCollection()
            ->addAttributeToSelect("*")
            ->addAttributeToSort('firstname', 'ASC')
            ->load();

        if ($customerCollection && count($customerCollection) > 0) {
            foreach ($customerCollection as $customer) {
                ?>
                <option value="<?php echo $customer->getId(); ?>"><?php echo $customer->getFirstname() . ' ' . $customer->getLastname(); ?>
                    (<?php echo $customer->getEmail(); ?>)
                </option>
                <?php
            }
        }
    }

    // for subscription shipping address
    public function defaultShippingAddress($customerId)
    {
        $customer = $this->getCustomerDetail($customerId);

        if (!empty($customerId)) {

            $shippingAddressId = $customer->getDefaultShipping();
            $shippingAddress = $this->_addressFactory->create()->load($shippingAddressId);
            return array(
                'firstname' => $shippingAddress->getData('firstname'),
                'lastname' => $shippingAddress->getData('lastname'),
                'company' => $shippingAddress->getData('company'),
                'street' => $shippingAddress->getData('street'),
                'street2' => '',
                'city' => $shippingAddress->getData('city'),
                'region' => $shippingAddress->getData('region'),
                'postcode' => $shippingAddress->getData('postcode'),
                'country_id' => $shippingAddress->getData('country_id'),
                'telephone' => $shippingAddress->getData('telephone')
            );
        }

        return null;
    }

    /**
     * Get formatted address html
     *
     * @param $orderId
     * @param null $shippingAddressId
     * @return string|null
     */
    public function getCustomerShippingAddress($orderId, $shippingAddressId = null): ?string
    {
        try {
            if (empty($orderId) || !empty($shippingAddressId)) {
                $address = $this->addressRepository->getById($shippingAddressId);
            } else {
                $order = $this->orderRepository->get($orderId);
                $address = $order->getShippingAddress();
            }

            /** @var RendererInterface $renderer */
            $renderer = $this->addressConfig->getFormatByCode('html')->getRenderer();
            return $renderer->renderArray($this->addressMapper->toFlatArray($address));

        } catch (\Exception $e) {
            $this->dataClass->ebizLog()->err($e->getMessage());
            return null;
        }
    }

    public function getRecurring()
    {
        try {
            return $this->recurringRepository->getById($this->getEbzcMethodId(),
                'eb_rec_scheduled_payment_internal_id')->getData();

        } catch (\Exception $e) {
            $this->_tran->log(__METHOD__ . $e->getMessage());
            return [];
        }
    }

    /**
     * @param null $selectedMethod
     */
    public function getShippingMethods($selectedMethod = null)
    {
        $carriers = $this->_shippingConfig->getAllCarriers();
        foreach ($carriers as $carrierCode => $carrierModel) {
            if ($carrierModel->isActive()) {
                $carrierMethods = $carrierModel->getAllowedMethods();
                if ($carrierMethods) {
                    //$carrierTitle = $this->_scopeConfig->getValue('carriers/' . $carrierCode . '/title',\Magento\Store\Model\ScopeInterface::SCOPE_STORE);
                    foreach ($carrierMethods as $methodCode => $methodTitle) {
                        $value = $carrierCode . '_' . $methodCode;
                        $title = $methodTitle . ' [' . $carrierCode . ']';
                        ?>
                        <option value="<?php echo $value ?>"<?php if ($value == $selectedMethod) {
                            echo 'selected';
                        } ?>>
                            <?php echo $title ?>
                        </option>

                        <?php
                    }
                }
            }
        }
    }

    public function getConfiguredFrequencies($selectedFrequency = null)
    {
        $this->_myConfig->getRecurringFrequencyOptions($selectedFrequency);
    }
    /**
     * Get recurring product
     *
     * @param int $productId
     * @return ProductInterface|null
     */
    public function getProduct(int $productId): ?ProductInterface
    {
        try {
            return $this->productRepository->getById($productId);
        } catch (\Exception $e) {
            return null;
        }
    }

}
