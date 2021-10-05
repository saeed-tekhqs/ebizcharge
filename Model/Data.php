<?php
declare(strict_types=1);
/**
 * EBizCharge Connect helper functions.
 *
 * @author      Century Business Solutions <support@centurybizsolutions.com>
 * @copyright   Copyright (c) 2020 Century Business Solutions (www.centurybizsolutions.com)
 */

namespace Ebizcharge\Ebizcharge\Model;

use Ebizcharge\Ebizcharge\Api\Data\TokenInterfaceFactory as TokenInterface;
use Ebizcharge\Ebizcharge\Api\TokenRepositoryInterface;
use Magento\Catalog\Model\ProductFactory;
use Magento\Config\Model\Config\Factory as CoreConfigData;
use \Magento\Framework\App\Area;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\CatalogInventory\Api\StockRegistryInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Model\AddressFactory;
use Magento\Customer\Model\CustomerFactory;
use Magento\Customer\Model\ResourceModel\Customer\CollectionFactory as CustomerCollectionFactory;
use Magento\Directory\Model\RegionFactory;
use Magento\Framework\App\Cache\Frontend\Pool;
use Magento\Framework\App\Cache\TypeListInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\App\State;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Message\ManagerInterface;
use Magento\Indexer\Model\IndexerFactory;
use Magento\Payment\Model\Config as PaymentConfig;
use Magento\Quote\Model\QuoteFactory;
use Magento\Quote\Model\QuoteManagement;
use Magento\Sales\Model\OrderFactory;
use Magento\Sales\Model\OrderRepository;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory as OrderCollectionFactory;
use Magento\Shipping\Model\Config as ShippingConfig;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;

// For invoices

// For Emails`

ini_set("soap.wsdl_cache_enabled", "0");
ini_set('memory_limit', '4000M');
ini_set('max_execution_time', '-1');
ini_set('fastcgi_read_timeout', '9000');
ini_set('proxy_read_timeout', '9000');

/**
 * Payment Data helper class
 *
 * Class Data
 * @package Ebizcharge\Ebizcharge\Model
 */
class Data extends AbstractHelper
{
    use EbizLogger;

    private $messageTemplate = '<li class="%s-msg"><ul><li>%s</li></ul></li>';

    /**
     * @var ManagerInterface
     */
    private $messageManager;

    /**
     * @var CustomerCollectionFactory
     */
    private $customerCollectionFactory;

    /**
     * @var OrderCollectionFactory
     */
    private $orderCollectionFactory;

    // For TranAPI Config import

    /**
     * @var TranApi
     */
    private $tranApi;

    /**
     * @var Config
     */
    private $config;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var RegionFactory
     */
    private $regionFactory;

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var ShippingConfig
     */
    private $shipConfig;

    /**
     * @var \SoapClient
     */
    private $soapClient;

    /**
     * @var TypeListInterface
     */
    private $cacheTypeList;

    /**
     * @var Pool
     */
    private $cacheFrontendPool;

    /**
     * @var QuoteFactory
     */
    private $quoteFactory;

    /**
     * @var QuoteManagement
     */
    private $quoteManagement;

    /**
     * @var CustomerRepositoryInterface
     */
    private $customerRepository;

    /**
     * @var CustomerFactory
     */
    private $customerFactory;

    /**
     * @var State
     */
    private $appState;

    /**
     * @var AddressFactory
     */
    private $addressFactory;

    /**
     * @var IndexerFactory
     */
    private $indexerFactory;

    /**
     * @var PaymentConfig
     */
    private $paymentConfig;

    /**
     * @var StockRegistryInterface
     */
    private $stockRegistry;

    /**
     * @var CollectionFactory
     */
    private $productCollectionFactory;

    /**
     * @var ProductFactory
     */
    private $productFactory;

    /**
     * @var OrderFactory
     */
    private $orderFactory;

    /**
     * @var OrderRepository
     */
    private $orderRepository;

    /**
     * @var TokenRepositoryInterface
     */
    private $tokenRepository;

    /**
     * @var CoreConfigData
     */
    private $coreConfigData;

    /**
     * @var TokenInterface
     */
    private $tokenInterfaceFactory;

    /**
     * @param AddressFactory $addressFactory
     * @param CollectionFactory $productCollectionFactory
     * @param Config $config
     * @param Context $context
     * @param CustomerCollectionFactory $customerCollectionFactory
     * @param CustomerFactory $customerFactory
     * @param CustomerRepositoryInterface $customerRepository
     * @param IndexerFactory $indexerFactory
     * @param ManagerInterface $messageManager
     * @param OrderCollectionFactory $orderCollectionFactory
     * @param OrderFactory $orderFactory
     * @param PaymentConfig $paymentConfig
     * @param Pool $cacheFrontendPool
     * @param ProductFactory $productFactory
     * @param QuoteFactory $quoteFactory
     * @param QuoteManagement $quoteManagement
     * @param RegionFactory $regionFactory
     * @param ShippingConfig $shipConfig
     * @param State $appState
     * @param StockRegistryInterface $stockRegistry
     * @param StoreManagerInterface $storeManager
     * @param TranApi $tranApi
     * @param TypeListInterface $cacheTypeList
     * @param OrderRepository $orderRepository
     * @param TokenFactory $tokenFactory
     * @param TokenInterface $tokenInterfaceFactory
     * @param TokenRepositoryInterface $tokenRepository
     * @param CoreConfigData $coreConfigData
     */
    public function __construct(
        AddressFactory $addressFactory,
        CollectionFactory $productCollectionFactory,
        Config $config,
        Context $context,
        CustomerCollectionFactory $customerCollectionFactory,
        CustomerFactory $customerFactory,
        CustomerRepositoryInterface $customerRepository,
        IndexerFactory $indexerFactory,
        ManagerInterface $messageManager,
        OrderCollectionFactory $orderCollectionFactory,
        OrderFactory $orderFactory,
        PaymentConfig $paymentConfig,
        Pool $cacheFrontendPool,
        ProductFactory $productFactory,
        QuoteFactory $quoteFactory,
        QuoteManagement $quoteManagement,
        RegionFactory $regionFactory,
        ShippingConfig $shipConfig,
        State $appState,
        StockRegistryInterface $stockRegistry,
        StoreManagerInterface $storeManager,
        TranApi $tranApi,
        TypeListInterface $cacheTypeList,
        OrderRepository $orderRepository,
        TokenInterface $tokenInterfaceFactory,
        TokenRepositoryInterface $tokenRepository,
        CoreConfigData $coreConfigData
    ) {
        parent::__construct($context);
        $this->addressFactory = $addressFactory;
        $this->productCollectionFactory = $productCollectionFactory;
        $this->config = $config;
        $this->customerCollectionFactory = $customerCollectionFactory;
        $this->customerFactory = $customerFactory;
        $this->customerRepository = $customerRepository;
        $this->indexerFactory = $indexerFactory;
        $this->messageManager = $messageManager;
        $this->orderCollectionFactory = $orderCollectionFactory;
        $this->orderFactory = $orderFactory;
        $this->paymentConfig = $paymentConfig;
        $this->cacheFrontendPool = $cacheFrontendPool;
        $this->productFactory = $productFactory;
        $this->quoteFactory = $quoteFactory;
        $this->quoteManagement = $quoteManagement;
        $this->regionFactory = $regionFactory;
        $this->scopeConfig = $context->getScopeConfig();
        $this->shipConfig = $shipConfig;
        $this->appState = $appState;
        $this->stockRegistry = $stockRegistry;
        $this->storeManager = $storeManager;
        $this->tranApi = $tranApi;
        $this->soapClient = $this->tranApi->getClient();
        $this->cacheTypeList = $cacheTypeList;
        $this->orderRepository = $orderRepository;
        $this->tokenInterfaceFactory = $tokenInterfaceFactory;
        $this->tokenRepository = $tokenRepository;
        $this->coreConfigData = $coreConfigData;
    }

    public function softwareId()
    {
        return "Magento2";
    }

    public function getStoreValues($repository, $ident, $value)
    {
        $dataString = $repository . '/' . $ident . '/' . $value;
        return $this->scopeConfig->getValue($dataString, ScopeInterface::SCOPE_STORE);
    }

    public function getStoreAdminEmails()
    {
        return [
            'generalName' => $this->getStoreValues('trans_email', 'ident_general', 'name'),
            'generalEmail' => $this->getStoreValues('trans_email', 'ident_general', 'email'),
            'salesName' => $this->getStoreValues('trans_email', 'ident_sales', 'name'),
            'salesEmail' => $this->getStoreValues('trans_email', 'ident_sales', 'email'),
            'supportName' => $this->getStoreValues('trans_email', 'ident_support', 'name'),
            'supportEmail' => $this->getStoreValues('trans_email', 'ident_support', 'email'),
        ];
    }

    /**
     * Check if admin side or front
     *
     * @return bool
     * @throws LocalizedException
     */
    private function isBackend(): bool
    {
        return $this->appState->getAreaCode() != Area::AREA_FRONTEND;
    }

    /**
     * Add Message to Magento Response
     *
     * @return void
     */
    private function _prepareMessages($messages)
    {
        foreach ($messages as $message) {
            switch ($message['type']) {
                case 'error':
                    $this->messageManager->addErrorMessage($message['message']);
                    break;
                case 'notice':
                    $this->messageManager->addNoticeMessage($message['message']);
                    break;
                case 'success':
                    $this->messageManager->addSuccessMessage($message['message']);
                    break;
            }
        }
    }

    /**
     * Get Response message HTML string
     *
     * @return string
     */
    private function _prepareResponse($messages)
    {
        $result = '<ul class="messages">';
        foreach ($messages as $message) {
            $result = $result . sprintf($this->messageTemplate, $message['type'], $message['message']);
        }
        return $result . "</ul>";
    }

    //---------- New EBizCharge Connect Methods Start ----------

    /**
     * New Function Search Ebiz customer
     */
    function searchCustomers($magCustomerId)
    {
        $ebzcCustomer = '';
        try {
            // find CustomerInternalId using SearchCustomers ebiz method
            $searchCustomer = $this->soapClient->SearchCustomers(
                array(
                    'securityToken' => $this->tranApi->getUeSecurityToken(),
                    'customerId' => $magCustomerId,
                    'start' => 0,
                    'limit' => 1,
                    'sort' => 'CustomerId'
                ));

            if(!isset($searchCustomer->SearchCustomersResult->Customer)) {
                $ebzcCustomer = 'Not Found';
            } else {
                $ebzcCustomer = $searchCustomer->SearchCustomersResult->Customer;
            }

        } catch (\SoapFault $ex) {
            throw new LocalizedException(__('SoapFault: SearchCustomers' . $ex->getMessage()));
        }
        // Calling = $this->searchCustomers('Mag custID','EC internalID', limit);
        return $ebzcCustomer;
    }

    /**
     * New Function getCustomerCollection
     */
    public function getCustomerCollection()
    {
        //Get customer collection
        return $this->customerCollectionFactory->create();
    }

    /**
     * get the customer default billing address
     * @param $billingAddressId
     * @return array
     */
    private function getBillingAddress($billingAddressId)
    {
        $billingAddress = $this->addressFactory->create()->load($billingAddressId);
        $street2 = "";
        if(!empty($billingAddress)) {
            return array(
                'FirstName' => $billingAddress->getData('firstname'),
                'LastName' => $billingAddress->getData('lastname'),
                'CompanyName' => $billingAddress->getData('company'),
                'Address1' => $billingAddress->getData(['street'][0]),
                'Address2' => $street2,
                'City' => $billingAddress->getData('city'),
                'State' => $billingAddress->getData('region'),
                'ZipCode' => $billingAddress->getData('postcode'),
                'Country' => $billingAddress->getData('country_id')
            );
        } else {
            return array();
        }
    }

    /**
     * get the customer default shipping address
     * @param $shippingAddressId
     * @return array
     */
    private function getShippingAddress($shippingAddressId)
    {
        $shippingAddress = $this->addressFactory->create()->load($shippingAddressId);
        $street2 = "";
        if(!empty($shippingAddress)) {
            return array(
                'FirstName' => $shippingAddress->getData('firstname'),
                'LastName' => $shippingAddress->getData('lastname'),
                'CompanyName' => $shippingAddress->getData('company'),
                'Address1' => $shippingAddress->getData('street'),
                'Address2' => $street2,
                'City' => $shippingAddress->getData('city'),
                'State' => $shippingAddress->getData('region'),
                'ZipCode' => $shippingAddress->getData('postcode'),
                'Country' => $shippingAddress->getData('country_id')
            );
        } else {
            return array();
        }
    }

    /**
     * Select from any table with one where condition
     *
     * @param string $tableName
     * @param string $whereKey
     * @param string|int $whereValue
     * @return array
     */
    private function runSelectQueryItem(string $tableName, string $whereKey, $whereValue): array
    {
        $connection = $this->config->getQueryResourceConnection()->getConnection();
        $tableName = $this->config->getQueryResourceConnection()->getTableName($tableName);

        $select = $connection->select()
            ->from($tableName)
            ->where($whereKey . ' = ?', $whereValue);

        return $connection->fetchAll($select);
    }

    /**
     * Update customer_entity table
     *
     * @param $tableName
     * @param $column
     * @param $ecSyncStatus
     * @param $ecInternalId
     * @param $ecCustomerId
     * @param $ebizCustomerToken
     * @param $entityId
     * @return bool
     */
    public function runUpdateQueryCustomer(
        $tableName,
        $column,
        $ecSyncStatus,
        $ecInternalId,
        $ecCustomerId,
        $ebizCustomerToken,
        $entityId
    ): bool {
        $timeNow = date('Y-m-d h:i:s');
        $connection = $this->config->getQueryResourceConnection()->getConnection();
        $tableName = $this->config->getQueryResourceConnection()->getTableName($tableName);

        $dataToUpdate = [
            'ec_' . $column . '_sync_status' => $ecSyncStatus,
            'ec_' . $column . '_internalid' => $ecInternalId,
            'ec_' . $column . '_id' => $ecCustomerId,
            'ec_' . $column . '_token' => $ebizCustomerToken,
            'ec_' . $column . '_lastsyncdate' => $timeNow,
        ];
        $where = ['entity_id = ?' => $entityId];

        try {
            $connection->beginTransaction();
            $connection->update($tableName, $dataToUpdate, $where);
            $connection->commit();
            return true;
        } catch (\Exception $e) {
            $this->ebizLog()->info($e->getMessage());
            $connection->rollBack();
            return false;
        }
    }

    /**
     * update catalog_product_entity table
     *
     * @param $tableName
     * @param $column
     * @param $ecSyncStatus
     * @param $ecInternalId
     * @param $ecItemId
     * @param $entityId
     * @return bool
     */
    public function runUpdateQueryItem($tableName, $column, $ecSyncStatus, $ecInternalId, $ecItemId, $entityId): bool
    {
        $timeNow = date("Y-m-d h:i:s");
        $connection = $this->config->getQueryResourceConnection()->getConnection();
        $tableName = $this->config->getQueryResourceConnection()->getTableName($tableName);

        $dataToUpdate = [
            'ec_' . $column . '_sync_status' => $ecSyncStatus,
            'ec_' . $column . '_internalid' => $ecInternalId,
            'ec_' . $column . '_id' => $ecItemId,
            'ec_' . $column . '_lastsyncdate' => $timeNow,
        ];
        $where = ['entity_id = ?' => $entityId];

        try {
            $connection->beginTransaction();
            $connection->update($tableName, $dataToUpdate, $where);
            $connection->commit();
            return true;
        } catch (\Exception $e) {
            $this->ebizLog()->info($e->getMessage());
            $connection->rollBack();
            return false;
        }
    }

    /**
     * update catalog_product_entity table
     * @param $tableName
     * @param $column
     * @param $ecSyncStatus
     * @param $ecInternalId
     * @param $ecOrderNumber
     * @param $ecCustomerId
     * @param $entityId
     * @return bool
     */
    public function runUpdateQueryOrders(
        $tableName,
        $column,
        $ecSyncStatus,
        $ecInternalId,
        $ecOrderNumber,
        $ecCustomerId,
        $entityId
    ): bool {
        $timeNow = date("Y-m-d h:i:s");
        $connection = $this->config->getQueryResourceConnection()->getConnection();
        $tableName = $this->config->getQueryResourceConnection()->getTableName($tableName);

        $dataToUpdate = [
            'ec_' . $column . '_sync_status' => $ecSyncStatus,
            'ec_' . $column . '_internalid' => $ecInternalId,
            'ec_' . $column . '_id' => $ecOrderNumber,
            'ec_cust_id' => $ecCustomerId,
            'ec_' . $column . '_lastsyncdate' => $timeNow,
        ];
        $where = ['entity_id = ?' => $entityId];

        try {
            $connection->beginTransaction();
            $connection->update($tableName, $dataToUpdate, $where);
            $connection->commit();
            return true;
        } catch (\Exception $e) {
            $this->ebizLog()->info($e->getMessage());
            $connection->rollBack();
            return false;
        }
    }

    /**
     * Update sales_invoice table
     *
     * @param $tableName
     * @param $column
     * @param $ecSyncStatus
     * @param $ecInternalId
     * @param $ecInvoiceNumber
     * @param $ecCustomerId
     * @param $entityId
     * @return bool
     */
    public function runUpdateQueryInvoice(
        $tableName,
        $column,
        $ecSyncStatus,
        $ecInternalId,
        $ecInvoiceNumber,
        $ecCustomerId,
        $entityId
    ): bool {
        $timeNow = date("Y-m-d h:i:s");
        $connection = $this->config->getQueryResourceConnection()->getConnection();
        $tableName = $this->config->getQueryResourceConnection()->getTableName($tableName);

        $dataToUpdate = [
            'ec_' . $column . '_sync_status' => $ecSyncStatus,
            'ec_' . $column . '_internalid' => $ecInternalId,
            'ec_' . $column . '_id' => $ecInvoiceNumber,
            'ec_cust_id' => $ecCustomerId,
            'ec_' . $column . '_lastsyncdate' => $timeNow,
        ];
        $where = ['entity_id = ?' => $entityId];

        try {
            $connection->beginTransaction();
            $connection->update($tableName, $dataToUpdate, $where);
            $connection->commit();
            return true;
        } catch (\Exception $e) {
            $this->ebizLog()->info($e->getMessage());
            $connection->rollBack();
            return false;
        }
    }

    /** ============= Upload Magento Customers to Econnect =============== **/
    /**
     * @param null $magentoCustomerId
     */
    public function syncCustomer($magentoCustomerId = null)
    {
        $counter = 0;
        $addCount = 0;
        $updateCount = 0;
        $_processedCount = 0;
        $_errorCountOne = 0;
        $_errorCountTwo = 0;
        $_messages = array();

        // Redirect if module or upload is disabled
        if($this->config->isEbizchargeActive() == 0) {
            $_messages[] = array(
                "type" => 'error',
                "message" => "EBizCharge module is inactive. Please activate before upload."
            );
            if($this->isBackend()) {
                $this->_prepareMessages($_messages);
                exit;
            }
        } elseif($this->config->isEconnectUploadEnabled() == 0) {
            $_messages[] = array(
                "type" => 'error',
                "message" => "EBizCharge Upload functionality is inactive. Please activate before upload."
            );
            if($this->isBackend()) {
                $this->_prepareMessages($_messages);
                exit;
            }
        } else {
            $securityToken = $this->tranApi->getUeSecurityToken();

            if(!empty($magentoCustomerId)) {
                $customerFactory = $this->customerFactory->create();
                $collection[] = $customerFactory->load($magentoCustomerId);
                $this->ebizLog()->info('1- Single customer ' . $magentoCustomerId . ' adding/updating to Econnect.');
            } else {
                $this->ebizLog()->info('2- Multiple customer(s) adding/updating to Econnect.');
                //Get customer collection
                $collection = $this->getCustomerCollection();
            }

            foreach ($collection as $item) {
                ini_set('memory_limit', '1000M');
                ini_set('max_execution_time', '-1');
                ini_set('max_input_time', '-1');
                // Getting Customer Data from customer_address_entity table
                $fullMagentoCustomerAddress = $this->customerFactory->create()->getAddressCollection()->addFieldToFilter('parent_id',
                    $item->getData('entity_id'))->getFirstItem();
                $company = (!empty($fullMagentoCustomerAddress->getData('company')) ? $fullMagentoCustomerAddress->getData('company') : '');
                $telephone = (!empty($fullMagentoCustomerAddress->getData('telephone')) ? $fullMagentoCustomerAddress->getData('telephone') : '');
                $fax = (!empty($fullMagentoCustomerAddress->getData('fax')) ? $fullMagentoCustomerAddress->getData('fax') : '');

                if(!empty($item->getData('ec_cust_id')) || ($item->getData('ec_cust_id') != '') || ($item->getData('ec_cust_id') != null)) {
                    $customerId = $item->getData('ec_cust_id');
                } else {
                    $customerId = $item->getData('entity_id');
                }

                $customer = array(
                    'CustomerId' => $customerId,
                    'FirstName' => $item->getData('firstname'),
                    'LastName' => $item->getData('lastname'),
                    'CompanyName' => $company,
                    'Phone' => $telephone,
                    'Fax' => $fax,
                    'Email' => $item->getData('email'),
                    'WebSite' => $item->getData('website_id'),
                    'BillingAddress' => $this->getBillingAddress($item->getData('entity_id')),
                    'ShippingAddress' => $this->getShippingAddress($item->getData('entity_id')),
                    'SoftwareId' => $this->softwareId()
                );

                try {
                    $addCustomerEbiz = $this->soapClient->AddCustomer(
                        array(
                            'securityToken' => $securityToken,
                            'customer' => $customer
                        )
                    );

                    $obj = $addCustomerEbiz->AddCustomerResult;

                    if($obj->Status == 'Success') {
                        $customerId = $obj->CustomerId;
                        $customerInternalId = $obj->CustomerInternalId;

                        $ebizCustomerNumber = $this->tranApi->GetCustomerToken($customerId);
                        if(!empty($ebizCustomerNumber)) {

                            //insert values in ebizcharge_token table
                            $this->saveEbizToken((int)$item->getData('entity_id'), (int)$ebizCustomerNumber);

                            $this->runUpdateQueryCustomer('customer_entity', 'cust', 1, $customerInternalId,
                                $customerId, $ebizCustomerNumber, $item->getData('entity_id'));
                            $this->ebizLog()->info('3- Customer ' . $customerId . '-' . $customerInternalId . ' added to Econnect successfully.');
                        }
                        // Call Update Query fuction to update values in customer_entity table
                        //$this->ebizLog()->info('4- Customer '.$CustomerId.'-'.$CustomerInternalId.' added to Econnect successfully.');
                        $addCount++;
                    }

                    if($obj->Error) {
                        if($obj->ErrorCode == 2) {
                            $status = $obj->Status;
                            $Error = $obj->Error;
                            //--------------------------------
                            if(!empty($item->getData('ec_cust_internalid')) && (!empty($item->getData('ec_cust_id')))) {

                                $fullMagentoCustomerAddress = $this->customerFactory->create()->getAddressCollection()->addFieldToFilter('parent_id',
                                    $customerId)->getFirstItem();
                                $company = (!empty($fullMagentoCustomerAddress->getData('company')) ? $fullMagentoCustomerAddress->getData('company') : '');
                                $telephone = (!empty($fullMagentoCustomerAddress->getData('telephone')) ? $fullMagentoCustomerAddress->getData('telephone') : '');
                                $fax = (!empty($fullMagentoCustomerAddress->getData('fax')) ? $fullMagentoCustomerAddress->getData('fax') : '');
                                $fullMagentoCustomer = $this->customerFactory->create()->getCollection()->addFilter('ec_cust_id',
                                    $customerId)->getFirstItem();
                                $customerUpdate = array(
                                    'CustomerId' => $customerId,
                                    'FirstName' => $fullMagentoCustomer->getData('firstname'),
                                    'LastName' => $fullMagentoCustomer->getData('lastname'),
                                    'CompanyName' => $company,
                                    'Phone' => $telephone,
                                    'Fax' => $fax,
                                    'Email' => $fullMagentoCustomer->getData('email'),
                                    'WebSite' => $fullMagentoCustomer->getData('website_id'),
                                    'BillingAddress' => $this->getBillingAddress($fullMagentoCustomer->getData('default_billing')),
                                    'ShippingAddress' => $this->getShippingAddress($fullMagentoCustomer->getData('default_shipping')),
                                    'SoftwareId' => $this->softwareId()
                                );

                                //$customer['CustomerId'] = $item->getData('ec_cust_id');
                                $parameters = array(
                                    'securityToken' => $securityToken,
                                    'customer' => $customerUpdate,
                                    'customerId' => $item->getData('ec_cust_id'),
                                    'customerInternalId' => $item->getData('ec_cust_internalid')
                                );

                                $updateCustResponse = $this->soapClient->UpdateCustomer($parameters);
                                $obj = $updateCustResponse->UpdateCustomerResult;

                                if($obj->Status == "Success") {
                                    $customerId = $item->getData('ec_cust_id');
                                    $customerInternalId = $item->getData('ec_cust_internalid');

                                    $ebizCustomerNumber = $this->tranApi->GetCustomerToken($customerId);

                                    if(!empty($ebizCustomerNumber)) {
                                        // Call Update Query fuction to update values in DB
                                        $this->runUpdateQueryCustomer('customer_entity', 'cust', 1, $customerInternalId,
                                            $customerId, $ebizCustomerNumber, $item->getData('entity_id'));


                                        $updateCount++;
                                        $this->ebizLog()->info('5- Customer Updated. custID: ' . $customerId . ' = custInternalId ' . $customerInternalId);
                                    } else {
                                        $this->ebizLog()->info('55- Customer not found for custID: ' . $customerId . ' = custInternalId ' . $customerInternalId);
                                        $_errorCountTwo++;
                                    }

                                } else {
                                    $this->ebizLog()->info('6- Customer not Updated. custID: ' . $customerId . ' = custInternalId ' . $customerInternalId);
                                }
                            } else {
                                $this->ebizLog()->info('Unable to add/update customer ' . $customerId . ', already exist or internalId is empty. ');
                                $_errorCountTwo++;
                            }
                            //----------------------------------
                        } else {
                            $status = $obj->Status;
                            $Error = $obj->Error;
                            $this->ebizLog()->info('7- Customer(' . $item->getData('entity_id') . ') ' . $Error);
                            $_errorCountTwo++;
                        }
                    }

                } catch (\Exception $ex) {
                    $this->ebizLog()->info('8- Customer not Updated. custID: ' . $item->getData('ec_cust_id') . ' = custInternalId ' . $item->getData('ec_cust_internalid') . '-' . $ex->getMessage());

                    $_messages[] = array(
                        "type" => 'error',
                        "message" => "Errors have occurred during the process : " . $ex->getMessage()
                    );
                    $this->ebizLog()->info('9- Customer (' . $item->getData('entity_id') . ') Errors have occurred during the process : ' . $ex->getMessage());

                    //$_errorCount++;
                } finally {
                    $_processedCount++;
                }
                //}
                $counter++;
            }

            array_unshift($_messages, array(
                "type" => 'notice',
                "message" => sprintf("Customers sync process has been completed. %s record(s) processed.", $counter)
            ));

            if($addCount > 0) {
                $_messages[] = array(
                    "type" => 'success',
                    "message" => $addCount . " Customer(s) are added successfully."
                );
            }

            if($updateCount > 0) {
                $_messages[] = array(
                    "type" => 'success',
                    "message" => $updateCount . " Customer(s) are updated successfully."
                );
            }

            if($_errorCountOne > 0) {
                $_messages[] = array("type" => 'error', "message" => $_errorCountOne . ' Customer(s) already exist.');
            }

            if($_errorCountTwo > 0) {
                $_messages[] = array("type" => 'error', "message" => $_errorCountTwo . ' Customer(s) not added.');
            }

            if($this->isBackend()) {
                $this->_prepareMessages($_messages);
            }
        }
    }

    /**
     * Upload Magento Items to Econnect
     * @param null $productId
     * @return void
     */
    public function syncItem($productId = null)
    {
        $counter = 0;
        $_errorCount = 0;
        $_processedCount = 0;
        $addCount = 0;
        $updateCount = 0;
        $_messages = array();

        // Redirect if module or upload is disabled
        if($this->config->isEbizchargeActive() == 0) {
            $_messages[] = array(
                "type" => 'error',
                "message" => "EBizCharge module is inactive. Please activate before upload."
            );
            if($this->isBackend()) {
                $this->_prepareMessages($_messages);
                exit;
            }
        } elseif($this->config->isEconnectUploadEnabled() == 0) {
            $_messages[] = array(
                "type" => 'error',
                "message" => "EBizCharge Upload functionality is inactive. Please activate before upload."
            );
            if($this->isBackend()) {
                $this->_prepareMessages($_messages);
                exit;
            }
        } else {
            if(!empty($productId)) {
                $collection[] = $this->productFactory->create()->load($productId);

            } else {
                $productCollection = $this->productCollectionFactory->create();
                $collection = $productCollection->addAttributeToSelect('entity_id')->load();
            }

            foreach ($collection as $product) {
                // Add Item start
                try {
                    ini_set('memory_limit', '1000M');
                    ini_set('max_execution_time', '-1');
                    ini_set('max_input_time', '-1');
                    $item = $this->productFactory->create()->load($product->getId());

                    // Get each Item remaining quantity in Stock
                    $productStockObj = $this->stockRegistry->getStockItem($item->getData('entity_id'));

                    if(($productStockObj != null) || !empty($productStockObj)) {
                        $productStockObjFinal = $productStockObj->getData('qty');
                    } else {
                        $productStockObjFinal = '';
                    }

                    if(empty($item->getData('ec_item_id')) ||
                        ($item->getData('ec_item_id') == '') ||
                        ($item->getData('ec_item_id') == null)) {
                        $itemId = $item->getData('entity_id');
                    } else {
                        $itemId = $item->getData('ec_item_id');
                    }


                    if(($productStockObjFinal > 0) && ($item->getData('type_id') != 'configurable')) {
                        $itemDetails = array(
                            'ItemId' => $itemId,
                            'Name' => $item->getData('name'),
                            'SKU' => $item->getData('sku'),
                            'Description' => substr((strip_tags($item->getData('short_description') ?? '')), 0, 100),
                            'UnitPrice' => $item->getData('price'),
                            'UnitCost' => '0',
                            'UnitOfMeasure' => '',
                            'Active' => $item->getData('status'),
                            'ItemType' => $item->getData('type_id'),
                            'QtyOnHand' => $productStockObjFinal,
                            'UPC' => '',
                            'Taxable' => '0',
                            'TaxRate' => '0',
                            'ItemCategoryId' => '',
                            'TaxCategoryID' => '',
                            'SoftwareId' => $this->softwareId(),
                            'ImageUrl' => '',
                            'ItemNotes' => '',
                            'GrossPrice' => 0,
                            'WarrantyDiscount' => 0,
                            'SalesDiscount' => 0
                        );

                        $securityToken = $this->tranApi->getUeSecurityToken();

                        $addItemResponse = $this->soapClient->AddItem(array(
                            'securityToken' => $securityToken,
                            'itemDetails' => $itemDetails
                        ));
                        $obj = $addItemResponse->AddItemResult;

                        if($obj->Status == "Success") {
                            $EcItemInternalId = $obj->ItemInternalId;
                            // Call Update Query fuction to update values in DB
                            $this->runUpdateQueryItem('catalog_product_entity', 'item', 1, $EcItemInternalId, $itemId,
                                $item->getData('entity_id'));
                            $addCount++;
                        }

                        if($obj->Status == "Error") {
                            //$this->ebizLog()->info('Item '.$ItemId.' already exist, Now updating item...');
                            $parameters = array(
                                'securityToken' => $securityToken,
                                'itemDetails' => $itemDetails,
                                'itemId' => $itemId,
                                'itemInternalId' => $item->getData('ec_item_internalid')
                            );

                            $updateItemResponse = $this->soapClient->UpdateItem($parameters);
                            $obj = $updateItemResponse->UpdateItemResult;

                            if($obj->Status == "Success") {
                                $EcItemInternalId = $obj->ItemInternalId;
                                // Call Update Query fuction to update values in DB
                                $this->runUpdateQueryItem('catalog_product_entity', 'item', 1, $EcItemInternalId,
                                    $itemId, $item->getData('entity_id'));
                                $updateCount++;

                            } else {
                                $this->ebizLog()->info('Item ' . $itemId . ' not added/updated to Econnect. ');
                            }

                        }
                    } else {
                        $this->ebizLog()->info('Unable to sync Item ' . $itemId . ', type is configurable or saleable quantity is 0.');
                    }

                } catch (\Exception $ex) {
                    $this->ebizLog()->info("Errors have occurred during the item process Error: " . $ex->getMessage());
                    $_messages[] = array(
                        "type" => 'error',
                        "message" => "Errors have occurred during the item process, please try again. " . $ex->getMessage()
                    );
                    $_errorCount++;

                } finally {
                    $_processedCount++;
                }
                // Add item end
                //}

                //}
                $counter++;
            }
            // show message when sync item button is used
            if(empty($productId)) {
                array_unshift($_messages, array(
                    "type" => 'notice',
                    "message" => sprintf("Items sync process has been completed. %s record(s) processed.",
                        $_processedCount)
                ));

                if($addCount > 0) {
                    $_messages[] = array(
                        "type" => 'success',
                        "message" => " (T1) " . $addCount . " Item(s) are added successfully."
                    );
                }
                if($updateCount > 0) {
                    $_messages[] = array(
                        "type" => 'success',
                        "message" => $updateCount . " Item(s) are updated successfully."
                    );
                }

                if($_errorCount > 0) {
                    $_messages[] = array("type" => 'error', "message" => $_errorCount . 'Item(s) not added!');
                }
                if($this->isBackend()) {
                    $this->_prepareMessages($_messages);
                }
            }
        }
    }

    /**
     * @param $order
     * @return array|string
     */
    private function getOrderBillingAddress($order)
    {
        if($order->getBillingAddress() == null) {
            $billingAddress = '';

        } else {
            // Billing address data
            $billingAddressData = $order->getBillingAddress()->getData();
            $billingAddress = array(
                'FirstName' => $billingAddressData['firstname'],
                'LastName' => $billingAddressData['lastname'],
                'CompanyName' => $billingAddressData['company'],
                'Address1' => $billingAddressData['street'],
                'Address2' => '',
                'City' => $billingAddressData['city'],
                'State' => $billingAddressData['region'],
                'ZipCode' => $billingAddressData['postcode'],
                'Country' => $billingAddressData['country_id'],
                'IsDefault' => 0,
                'AddressId' => isset($billingAddressData['entity_id']) ? $billingAddressData['entity_id'] : 0
            );
        }

        return $billingAddress;
    }

    /**
     * @param $order
     * @return array|string
     */
    private function getOrderShippingAddress($order)
    {
        if(($order->getShippingAddress()) == null) {
            $shippingAddress = '';

        } else {
            // Shipping address data
            $shippingAddressData = $order->getShippingAddress()->getData();
            $shippingAddress = array(
                'FirstName' => $shippingAddressData['firstname'],
                'LastName' => $shippingAddressData['lastname'],
                'CompanyName' => $shippingAddressData['company'],
                'Address1' => $shippingAddressData['street'],
                'Address2' => '',
                'City' => $shippingAddressData['city'],
                'State' => $shippingAddressData['region'],
                'ZipCode' => $shippingAddressData['postcode'],
                'Country' => $shippingAddressData['country_id'],
                'IsDefault' => 0,
                'AddressId' => isset($shippingAddressData['entity_id']) ? $shippingAddressData['entity_id'] : 0
            );
        }

        return $shippingAddress;
    }

    /**
     * @param $order
     * @param $mageMapCustomerId
     * @param $orderId
     * @return array
     */
    private function getUploadOrderDetails($order, $mageMapCustomerId, $orderId)
    {
        if($order->getData('send_email') != 1) {
            $IsToBeEmailed = 0;
        } else {
            $IsToBeEmailed = $order->getData('send_email');
        }

        $orderDetails = array(
            'CustomerId' => $mageMapCustomerId,
            'SalesOrderNumber' => $orderId,
            'Date' => $order->getData('created_at'),
            'Currency' => $order->getData('order_currency_code'),
            'Amount' => $order->getData('grand_total'),
            'DueDate' => $order->getData('created_at'),
            'AmountDue' => $order->getData('total_due'),
            'PoNum' => $orderId,
            'DateUploaded' => $order->getData('created_at'),
            'DateUpdated' => $order->getData('updated_at'),
            'Software' => $this->softwareId(),
            'NotifyCustomer' => $order->getData('customer_note_notify'),
            'TotalTaxAmount' => $order->getData('tax_amount'),
            'UniqueId' => $order->getData('entity_id'),
            'Description' => $order->getData('shipping_description'),
            'CustomerMessage' => $order->getData('customer_note'),
            'Memo' => '',
            'ShipDate' => $order->getData('updated_at'),
            'ShipVia' => '',
            'IsToBeEmailed' => $IsToBeEmailed,
            'BillingAddress' => $this->getOrderBillingAddress($order),
            'ShippingAddress' => $this->getOrderShippingAddress($order)
        );

        $items = $this->getUploadOrderItems($order, 'order');

        if(sizeof($items) > 0) {
            $orderDetails["Items"] = $items;
        }

        return $orderDetails;
    }

    /**
     * @param $order
     * @param $orderInvoice
     * @return array
     */
    private function getUploadOrderItems($order, $orderInvoice)
    {
        $items = [];
        $lineNumber = 0;

        $allItems = $order->getAllItems();

        foreach ($allItems as $line) {
            if($orderInvoice == 'order') {
                if($line->getParentItemId()) {
                    continue;
                }
            }

            if($line->getData('base_row_total') != null) {
                $lineNumber++;

                if(($line->getData('tax_amount') == null) || ($line->getData('tax_amount') == '')) {
                    $TotalLineTax = 0;
                } else {
                    $TotalLineTax = $line->getData('tax_amount');
                }

                if(($line->getData('tax_percent') == null) || ($line->getData('tax_percent') == '')) {
                    $TotalLinePercent = 0;
                } else {
                    $TotalLinePercent = $line->getData('tax_percent');
                }

                $TotalLineAmountProduct = $line->getData('row_total_incl_tax');
                if(empty ($TotalLineAmountProduct) || $TotalLineAmountProduct == null) {
                    $TotalLineAmount = $line->getData('price');
                    //$TotalLineAmount = 0.0000;
                } else {
                    $TotalLineAmount = $TotalLineAmountProduct;
                }

                if(!empty ($line->getQtyOrdered())) {
                    $qty = $line->getQtyOrdered();
                } elseif(!empty ($line->getData('qty'))) {
                    $qty = $line->getData('qty');
                } else {
                    $qty = 1;
                }

                $Item = array(
                    'ItemId' => $line->getData('product_id'),
                    'Name' => $line->getData('name'),
                    'Description' => $line->getData('name') . " - " . $line->getData('sku'),
                    'UnitPrice' => $line->getData('price'),
                    'Qty' => $qty,
                    'Taxable' => 'false',
                    'TaxRate' => $TotalLinePercent,
                    'TotalLineAmount' => $TotalLineAmount,
                    'TotalLineTax' => $TotalLineTax,
                    'ItemLineNumber' => $lineNumber,
                    'GrossPrice' => 0,
                    'WarrantyDiscount' => 0,
                    'SalesDiscount' => 0
                );

                // Ordered multiple items data
                $items[] = $Item;
                // Add Item method goes here for each product
                $this->syncItem($line->getData('product_id'));
            }
        }

        return $items;
    }

    /**
     * @param $mageCustomerId
     * @param $mageCustomer
     * @return mixed
     */
    public function addCustomerToEbiz($mageCustomerId, $mageCustomer)
    {
        $securityToken = $this->tranApi->getUeSecurityToken();

        try {
            $customer = array(
                'CustomerId' => $mageCustomerId,
                'FirstName' => $mageCustomer['firstname'],
                'LastName' => $mageCustomer['lastname'],
                'CompanyName' => $mageCustomer['firstname'],
                'Phone' => $mageCustomer['ec_cust_id'],
                'CellPhone' => $mageCustomer['ec_cust_id'],
                'Fax' => $mageCustomer['ec_cust_id'],
                'Email' => $mageCustomer['email'],
                'WebSite' => $mageCustomer['website_id'],
                'BillingAddress' => $this->getBillingAddress($mageCustomer['default_billing']),
                'ShippingAddress' => $this->getShippingAddress($mageCustomer['default_shipping']),
                'SoftwareId' => $this->softwareId()
            );

            $addCustomerEbiz = $this->soapClient->AddCustomer(
                array(
                    'securityToken' => $securityToken,
                    'customer' => $customer
                ));

            $addCustomerResult = $addCustomerEbiz->AddCustomerResult;

            if($addCustomerResult->Status == 'Success') {
                $customerId = $addCustomerResult->CustomerId;
                $customerInternalId = $addCustomerResult->CustomerInternalId;

                $ebizCustomerNumber = $this->tranApi->getCustomerToken($customerId);

                $this->saveEbizToken((int)$mageCustomer['entity_id'], (int)$ebizCustomerNumber);


                $this->runUpdateQueryCustomer('customer_entity', 'cust', 1, $customerInternalId, $customerId,
                    $ebizCustomerNumber, $mageCustomer['entity_id']);

                return $customerInternalId;
            }

            if($addCustomerResult->Error) {
                if($addCustomerResult->ErrorCode == 2) {
                    $status = $addCustomerResult->Status;
                    $Error = $addCustomerResult->Error;
                    $this->ebizLog()->info('Customer(' . $mageCustomerId . ') not saved ' . $Error);
                } else {
                    $status = $addCustomerResult->Status;
                    $Error = $addCustomerResult->Error;
                    $this->ebizLog()->info('Customer(' . $mageCustomerId . ') not saved ' . $Error);
                }
            }

        } catch (\Exception $e) {
            throw new LocalizedException(__('SoapFault: Customer, not added.' . $e->getMessage()));
        }
    }

    /**
     * @param $magCustomerId
     * @param bool $returnInternalId
     * @return mixed
     */
    public function getMappedAndAddCustomer($magCustomerId, $returnInternalId = false)
    {
        $mageMapCustomerId = $magCustomerId;
        $customerInterId = null;
        $mageCustomerEntity = $this->runSelectQuery('customer_entity', '*', 'entity_id', $magCustomerId);

        if($mageCustomerEntity && isset($mageCustomerEntity[0])) {
            $mageCustomer = $mageCustomerEntity[0];

            if(isset($mageCustomer['ec_cust_id']) && !empty($mageCustomer['ec_cust_id'])) {
                $mageMapCustomerId = $mageCustomer['ec_cust_id'];
            }
            if(isset($mageCustomer['ec_cust_internalid']) && !empty($mageCustomer['ec_cust_internalid'])) {
                $customerInterId = $mageCustomer['ec_cust_internalid'];
            }

            // add customer if internal id not exists
            if(empty($mageCustomer['ec_cust_internalid'])) {
                $searchCustomerResultEbiz = $this->searchCustomers($mageMapCustomerId);
                if($searchCustomerResultEbiz == 'Not Found') {
                    $customerInterId = $this->addCustomerToEbiz($mageMapCustomerId, $mageCustomer);
                }
            }
        }

        return $returnInternalId ? $customerInterId : $mageMapCustomerId;
    }

    /**
     * Upload Magento Orders to Econnect
     * @param null $orderData
     * @throws LocalizedException
     */
    public function syncOrders($orderData = null)
    {
        $counter = 0;
        $_processedCount = 0;
        $_errorCount = 0;
        $addCount = 0;
        $updateCount = 0;
        $_messages = array();
        $OrderInternalId = null;
        // Soap credentials
        $securityToken = $this->tranApi->getUeSecurityToken();

        // Redirect if module or upload is disabled
        if($this->config->isEbizchargeActive() == 0) {
            $_messages[] = array(
                "type" => 'error',
                "message" => "EBizCharge module is inactive. Please activate before upload."
            );
            if($this->isBackend()) {
                $this->_prepareMessages($_messages);
                exit;
            }
        } elseif($this->config->isEconnectUploadEnabled() == 0) {
            $_messages[] = array(
                "type" => 'error',
                "message" => "EBizCharge Upload functionality is inactive. Please activate before upload."
            );
            if($this->isBackend()) {
                $this->_prepareMessages($_messages);
                exit;
            }
        } else {
            if(!empty($orderData)) {
                $collection[] = $orderData;
                //$this->orderFactory->create()->loadByIncrementId($orderId);
            } else {
                //Get customer collection
                $collection = $this->orderCollectionFactory->create()->addAttributeToSelect('entity_id');
            }

            foreach ($collection as $order) {
                ini_set('memory_limit', '1000M');
                ini_set('max_execution_time', '-1');
                ini_set('max_input_time', '-1');
                // Get Full order by Oreder ID
                try {

                    if(empty($orderData)) {
                        $order = $this->orderFactory->create()->load($order->getData('entity_id'));
                    }

                    if(!empty($customerId = $order->getData('customer_id'))) {
                        $mageMapCustomerId = $this->getMappedAndAddCustomer($customerId);
                    } else {
                        $mageMapCustomerId = 'Guest';
                        //$searchCustomerResultEbiz = $this->tranApi->searchCustomers($mageMapCustomerId);
                        $searchCustomerResultEbiz = $this->searchCustomers($mageMapCustomerId);
                        if($searchCustomerResultEbiz == 'Not Found') {
                            $this->ebizLog()->info('Guest customer not found. Adding Guest customer.');

                            $customer = array(
                                'CustomerId' => $mageMapCustomerId,
                                'FirstName' => 'Guest',
                                'LastName' => 'Guest',
                                'CompanyName' => 'Guest',
                                'Phone' => +10000000,
                                'CellPhone' => +10000000,
                                'Fax' => +10000000,
                                'Email' => 'Guest@guest.com',
                                'WebSite' => '',
                                'BillingAddress' => '',
                                'ShippingAddress' => '',
                                'SoftwareId' => $this->softwareId()
                            );
                            $addCustomerEbiz = $this->soapClient->AddCustomer(
                                array(
                                    'securityToken' => $securityToken,
                                    'customer' => $customer
                                ));
                            //$obj = $addCustomerEbiz->AddCustomerResult;
                        }
                    }

                    if(empty($order->getData('ec_order_id'))) {
                        $orderNewId = $order->getData('increment_id');
                    } else {
                        $orderNewId = $order->getData('ec_order_id');
                    }

                    // get Order data with items
                    $orderDetails = $this->getUploadOrderDetails($order, $mageMapCustomerId, $orderNewId);

                    $addSalesOrderResponse = $this->soapClient->AddSalesOrder(array(
                        'securityToken' => $securityToken,
                        'salesOrder' => $orderDetails
                    ));
                    $addOrderResult = $addSalesOrderResponse->AddSalesOrderResult;
                    // For new order sync
                    if($addOrderResult->Status == 'Success') {
                        $addSalesOrderInternalId = $addOrderResult->SalesOrderInternalId;
                        // Call Update Query fuction to update values in DB
                        $this->runUpdateQueryOrders('sales_order', 'order', 1, $addSalesOrderInternalId, $orderNewId,
                            $mageMapCustomerId, $order->getData('entity_id'));
                        $addCount++;

                        // AddApplicationTransaction start
                        $getCustomerInternalId = $this->runSelectQuery('customer_entity', 'ec_cust_internalid',
                            'ec_cust_id', $mageMapCustomerId);
                        $customerInternalId = !empty($getCustomerInternalId[0]['ec_cust_internalid']) ? $getCustomerInternalId[0]['ec_cust_internalid'] : false;

                        $getTranNo = $this->runSelectQuery('sales_payment_transaction', '*', 'order_id',
                            $order->getData('entity_id'));
                        $tranNo = !empty($getTranNo[0]['txn_id']) ? $getTranNo[0]['txn_id'] : false;

                        $paymentStatus = !empty($getTranNo[0]['txn_type']) ? $getTranNo[0]['txn_type'] : false;

                        if(!empty($tranNo) && !empty($customerInternalId)) {
                            // sync transaction
                            $addTranResultRequest = array(
                                'securityToken' => $securityToken,
                                'applicationTransactionRequest' => [
                                    'CustomerInternalId' => $customerInternalId,
                                    'TransactionId' => $tranNo,
                                    'TransactionTypeId' => $paymentStatus,
                                    'LinkedToInternalId' => $addSalesOrderInternalId,
                                    'SoftwareId' => $this->softwareId(),
                                    'TransactionDate' => date('Y-m-d H:i:s'),
                                    'TransactionNotes' => 'Order Id: ' . $orderNewId,
                                    // new fields added
                                    'LinkedToTypeId' => 'SalesOrder',
                                    'LinkedToExternalUniqueId' => $order->getData('entity_id')
                                ]
                            );

                            $addTranResult = $this->soapClient->AddApplicationTransaction($addTranResultRequest);
                            $addApplicationTransactionResultStatus = $addTranResult->AddApplicationTransactionResult->Status;
                            $addApplicationTransactionResultInternalId = $addTranResult->AddApplicationTransactionResult->ApplicationTransactionInternalId;

                        } else {
                            $this->ebizLog()->info('Failure: application transaction not added for Magento Order #' . $orderNewId);
                        }
                        // AddApplicationTransaction end

                    } else {
                        $this->ebizLog()->info("Order #" . $orderNewId . " not created! (" . $addOrderResult->ErrorCode . ") " . $addOrderResult->Error . ". trying to update.");
                        // For existing order sync and update
                        $parametersUpdateOrder = array(
                            'securityToken' => $securityToken,
                            'salesOrder' => $orderDetails,
                            'customerId' => $mageMapCustomerId,
                            'salesOrderNumber' => $orderNewId
                        );

                        $updateSalesOrderResponse = $this->soapClient->UpdateSalesOrder($parametersUpdateOrder);
                        $orderUpdateResult = $updateSalesOrderResponse->UpdateSalesOrderResult;

                        if($orderUpdateResult->Status == "Success") {
                            $updateSalesOrderInternalId = $orderUpdateResult->SalesOrderInternalId;
                            // Call Update Query fuction to update values in DB
                            $timeNow = date("Y-m-d h:i:s");
                            $entity_order_id = $order->getData('entity_id');
                            $orderData = $this->orderRepository->get($entity_order_id);
                            $orderData->setData('ec_order_sync_status', 1);
                            $orderData->setData('ec_order_internalid', $updateSalesOrderInternalId);
                            $orderData->setData('ec_order_id', $orderNewId);
                            $orderData->setData('ec_cust_id', $mageMapCustomerId);
                            $orderData->setData('ec_lastsyncdate', $timeNow);
                            $this->orderRepository->save($orderData);
                            $updateCount++;
                        } else {
                            $this->ebizLog()->info("Order # " . $orderNewId . " not added/updated to Econnect! ErrorCode:" . $orderUpdateResult->ErrorCode);
                            $_errorCount++;
                        }

                    }

                } catch (\Exception $ex) {
                    $this->ebizLog()->info("Order # " . $order->getData('increment_id') . " not synced. " . $ex->getMessage());
                    $_errorCount++;
                } finally {
                    $_processedCount++;
                }

                $counter++;
            }

            array_unshift($_messages, array(
                "type" => 'notice',
                "message" => sprintf("Orders sync process has been completed. %s record(s) processed out of %s",
                    $_processedCount, $counter)
            ));

            if($addCount > 0) {
                $_messages[] = array(
                    "type" => 'success',
                    "message" => $addCount . " Orders(s) are uploaded successfully."
                );
            }

            if($updateCount > 0) {
                $_messages[] = array(
                    "type" => 'success',
                    "message" => $updateCount . " Orders(s) are updated successfully."
                );
            }

            if($_errorCount > 0) {
                $_messages[] = array(
                    "type" => 'error',
                    "message" => $_errorCount . ' Order(s) not uploaded! Please check logs for details.'
                );
            }

            if($this->isBackend()) {
                $this->_prepareMessages($_messages);
            }
        }
    }

    /**
     * Upload Magento Invoices to Econnect
     *
     * @return void
     */
    public function syncInvoices()
    {
        $processedCount = 0;
        $errorCount = 0;
        $addCount = 0;
        $updateCount = 0;
        $_messages = array();
        $InvoiceInternalId = null;

        // Redirect if module or upload is disabled
        if($this->config->isEbizchargeActive() == 0) {
            $_messages[] = array(
                "type" => 'error',
                "message" => "EBizCharge module is inactive. Please activate before upload."
            );
            if($this->isBackend()) {
                $this->_prepareMessages($_messages);
                exit;
            }
        } elseif($this->config->isEconnectUploadEnabled() == 0) {
            $_messages[] = array(
                "type" => 'error',
                "message" => "EBizCharge Upload functionality is inactive. Please activate before upload."
            );
            if($this->isBackend()) {
                $this->_prepareMessages($_messages);
                exit;
            }
        } else {
            $collection = $this->orderCollectionFactory->create()->addAttributeToSelect('entity_id');

            foreach ($collection as $order) {
                ini_set('memory_limit', '1000M');
                ini_set('max_execution_time', '-1');
                ini_set('max_input_time', '-1');
                // Get Full order by Oreder ID
                $orderEntityId = $order->getData('entity_id');
                $order = $this->orderFactory->create()->load($orderEntityId);
                // Get Invoice collection against each order
                $invoiceCollection = $order->getInvoiceCollection();
                $this->processInvoices($order, $invoiceCollection, $addCount, $updateCount, $errorCount,
                    $processedCount);
            }

            array_unshift($_messages, array(
                "type" => 'notice',
                "message" => sprintf("Invoice sync process has been completed. %s record(s) processed.",
                    $processedCount)
            ));

            if($addCount > 0) {
                $_messages[] = array(
                    "type" => 'success',
                    "message" => $addCount . " Invoice(s) are added successfully."
                );
            }
            if($updateCount > 0) {
                $_messages[] = array(
                    "type" => 'success',
                    "message" => $updateCount . " Invoice(s) are updated successfully."
                );
            }
            if($errorCount > 0) {
                $_messages[] = array("type" => 'error', "message" => $errorCount . ' Invoice(s) not added!');
            }

            if($this->isBackend()) {
                $this->_prepareMessages($_messages);
            }
        }
    }

    /**
     * @param $invoice
     * @param $orderId
     * @param $customerId
     * @param $invoiceNewId
     * @return array
     */
    private function getInvoiceDetails($invoice, $orderId, $customerId, $invoiceNewId)
    {
        $state = $invoice->getData('state');
        if($state == 2) {
            $amountDue = 0;
        } elseif($state == 1) {
            $amountDue = $invoice->getData('grand_total');
        } else {
            $amountDue = $invoice->getData('grand_total');
        }

        $invoiceDetails = [
            'CustomerId' => $customerId,
            'InvoiceNumber' => $invoiceNewId,
            'InvoiceDate' => $invoice->getData('created_at'),
            'Currency' => $invoice->getData('order_currency_code'),
            'InvoiceAmount' => $invoice->getData('grand_total'),
            'InvoiceDueDate' => $invoice->getData('created_at'),
            'AmountDue' => $amountDue,
            'PoNum' => $orderId,
            'SoNum' => $orderId,
            'TotalTaxAmount' => $invoice->getData('tax_amount'),
            'InvoiceUniqueId' => $invoice->getData('entity_id'),
            'InvoiceDescription' => $invoiceNewId,
            'NotifyCustomer' => 'false',
            'Software' => $this->softwareId(),
        ];

        $items = $this->getUploadOrderItems($invoice, 'invoice');

        if(sizeof($items) > 0) {
            $invoiceDetails["Items"] = $items;
        }
        return $invoiceDetails;
    }

    /**
     * @param $order
     * @param $invoiceCollection
     * @param int $addCount
     * @param int $updateCount
     * @param int $errorCount
     * @param int $processedCount
     */
    public function processInvoices(
        $order,
        $invoiceCollection,
        &$addCount = 0,
        &$updateCount = 0,
        &$errorCount = 0,
        &$processedCount = 0
    ) {
        if(!empty($order->getData('customer_id'))) {
            if(!empty($invoiceCollection)) {

                foreach ($invoiceCollection as $invoice) {

                    try {
                        if(empty($invoice->getData('ec_invoice_id'))) {
                            $invoiceNewId = $invoice->getData('increment_id');
                        } else {
                            $invoiceNewId = $invoice->getData('ec_invoice_id');
                        }

                        if(!empty($customerId = $order->getData('customer_id'))) {
                            // get mapped customer id and add customer if not exists
                            $mageMappedCustomerId = $this->getMappedAndAddCustomer($customerId);

                        } else {
                            $mageMappedCustomerId = 'Guest';
                        }
                        //$this->ebizLog()->info('try after getMappedAndAddCustomer function');

                        if(empty($order->getData('ec_order_id'))) {
                            $orderNewId = $order->getData('increment_id');
                        } else {
                            $orderNewId = $order->getData('ec_order_id');
                        }

                        $invoiceDetails = $this->getInvoiceDetails($invoice, $orderNewId, $mageMappedCustomerId,
                            $invoiceNewId);

                        $securityToken = $this->tranApi->getUeSecurityToken();

                        $addInvoiceResponse = $this->soapClient->AddInvoice(
                            [
                                'securityToken' => $securityToken,
                                'invoice' => $invoiceDetails
                            ]
                        );

                        $obj = $addInvoiceResponse->AddInvoiceResult;

                        if($obj->Status == "Success") {
                            $invoiceInternalId = $obj->InvoiceInternalId;
                            // Call Update Query fuction to update values in DB

                            $this->runUpdateQueryInvoice('sales_invoice', 'invoice', 1, $invoiceInternalId,
                                $invoiceNewId, $mageMappedCustomerId, $invoice->getData('entity_id'));
                            $addCount++;
                            // add invoice payment start
                            $tranNoData = !empty($invoice->getData('transaction_id')) ? $invoice->getData('transaction_id') : false;
                            $tranNo = $tranNoData ? strtok($tranNoData, '-') : false;

                            $getCustomerNumber = $this->customerFactory->create()->addAttributeToFilter('entity_id',
                                array('eq' => $mageMappedCustomerId))->getFirstItem()->getData('ec_cust_token');
                            $custNo = !empty($getCustomerNumber) ? $getCustomerNumber : false;

                            $pyamentOrder = $this->orderRepository->get($order->getData('entity_id'));
                            $PaymentMethodType = !empty($pyamentOrder->getPayment()->getData('ebzc_option')) ? $pyamentOrder->getPayment()->getData('ebzc_option') : '0';
                            $PaymentMethodId = !empty($pyamentOrder->getPayment()->getData('ebzc_method_id')) ? $pyamentOrder->getPayment()->getData('ebzc_method_id') : '0';

                            $totalPaidAmount = !empty($order->getData('total_paid')) ? $order->getData('total_paid') : $invoice->getData('grand_total');

                            if($totalPaidAmount == 0) {
                                $this->ebizLog()->info('5: Invoice upload is skipped due to TotalPaidAmount is 0.');
                                continue;
                            }

                            if(!empty($tranNo) && !empty($custNo) && !empty($PaymentMethodId)) {
                                $paymentDetails[0] = [
                                    'InvoiceInternalId' => $invoiceInternalId,
                                    'PaidAmount' => $totalPaidAmount,
                                    'Currency' => $invoice->getData('order_currency_code'),
                                ];
                                $paymentParms = [
                                    'securityToken' => $securityToken,
                                    'payment' => [
                                        'InvoicePaymentDetails' => $paymentDetails,
                                        'CustomerId' => $mageMappedCustomerId,
                                        'RefNum' => $tranNo,
                                        'Currency' => $invoice->getData('order_currency_code'),
                                        'TotalPaidAmount' => $totalPaidAmount,
                                        'CustNum' => $custNo,
                                        'PaymentMethodId' => $PaymentMethodId,
                                        'PaymentMethodType' => $PaymentMethodType,
                                        //'PaymentMethodId' => '?',
                                        //'PaymentMethodType' => '?',
                                        'Software' => $this->softwareId(),
                                    ],
                                ];

                                $invoicePaymentResult = $this->soapClient->AddInvoicePayment($paymentParms);

                                $AddInvoicePaymentResultStatus = $invoicePaymentResult->AddInvoicePaymentResult->Status;
                                $AddInvoicePaymentResultInternalId = $invoicePaymentResult->AddInvoicePaymentResult->PaymentInternalId;

                            } else {
                                $this->ebizLog()->info('Failure: Invoice Payment not added for Magento Invoice #' . $invoiceNewId);
                            }
                            // add invoice payment end

                        }

                        if($obj->Status == "Error") {
                            $parameters = array(
                                'securityToken' => $securityToken,
                                'invoice' => $invoiceDetails,
                                'invoiceNumber' => $invoiceNewId,
                                //'customerId' => $order->getData('customer_id')
                                'customerId' => $mageMappedCustomerId
                            );

                            $updateInvoiceResponse = $this->soapClient->UpdateInvoice($parameters);
                            $obj = $updateInvoiceResponse->UpdateInvoiceResult;

                            if($obj->Status == "Success") {
                                $invoiceInternalId = $obj->InvoiceInternalId;
                                // Call Update Query fuction to update values in DB
                                $this->runUpdateQueryInvoice('sales_invoice', 'invoice', 1, $invoiceInternalId,
                                    $invoiceNewId, $mageMappedCustomerId, $invoice->getData('entity_id'));
                                $updateCount++;
                            }
                        }

                    } catch (\Exception $ex) {
                        $this->ebizLog()->info('Invoice #' . $invoiceNewId . ' not added! ' . $ex->getMessage());
                        $errorCount++;
                    } finally {
                        $processedCount++;
                    }
                }
            }
        }

    }

    //* =========== Download Options from EConnect Item ============ *//

    /**
     * @param $tableName
     * @param $tableFields
     * @param $whereKey
     * @param $whereValue
     * @return mixed
     */
    public function runDownloadSelectQuery($tableName, $tableFields, $whereKey, $whereValue)
    {
        $connection = $this->config->getQueryResourceConnection()->getConnection();
        $tableName = $this->config->getQueryResourceConnection()->getTableName($tableName);

        $select = $connection->select()
            ->from($tableName)
            ->where($whereKey . ' = ?', $whereValue);

        return $connection->fetchAll($select);
    }

    /* Full data fetch list from EBiz */
    /**
     * @param $method
     * @param $parameters
     * @param $parameter
     * @param $eq_or_neq
     * @param $limit
     * @param $itemId
     * @param $itemInternalId
     * @return array
     */
    public function downloadMergedListItems(
        $method,
        $parameters,
        $parameter,
        $eq_or_neq,
        $limit,
        $itemId,
        $itemInternalId
    ) {
        //---- Fetch all Records in an array list Start ----//
        // don't refactor it - used in api
        $MethodParameters = $method . $parameters;
        $MethodParametersResult = $method . $parameters . "Result";
        $ParameterDetails = $parameter . "Details";
        // Condition start
        $maxSize = 0;
        $position = 0;
        $itemsObj = array();
        do {
            // Search Criteria
            $searchFilter = array(
                'FieldName' => 'SoftwareId',
                'ComparisonOperator' => $eq_or_neq,
                'FieldValue' => $this->softwareId()
            );
            $filters = array(
                'SearchFilter' => $searchFilter
            );
            $searchItems = array(
                'securityToken' => $this->tranApi->getUeSecurityToken(),
                'itemInternalId' => $itemInternalId,
                'itemId' => $itemId,
                'start' => $position,
                'limit' => $limit,
                'sort' => 'ItemId',
                'filters' => $filters
            );
            $SearchItemsResponse = $this->soapClient->$MethodParameters($searchItems);

            if(!isset ($SearchItemsResponse->$MethodParametersResult->$ParameterDetails)) {
                $itemsObj = array();
                $new_counter = 0;
            } elseif((is_array($SearchItemsResponse->$MethodParametersResult->$ParameterDetails)) &&
                (count($SearchItemsResponse->$MethodParametersResult->$ParameterDetails)) > 1) {
                $dummy_Items_obj = $SearchItemsResponse->$MethodParametersResult->$ParameterDetails;
                $new_counter = count($SearchItemsResponse->$MethodParametersResult->$ParameterDetails);
                $itemsObj = array_merge($itemsObj, $dummy_Items_obj);
            } else {
                $itemsObj = $SearchItemsResponse->$MethodParametersResult;
                $new_counter = 1;
            }

            if($new_counter < 1000) {
                $maxSize = 1;
            }
            $position = $position + 1000;

        } while ($maxSize == 0);
        //---- Fetch all Records in an array list End ----//
        return ($itemsObj);
    }

    /**
     * Delete URL from url_rewrite table in Magento
     *
     * @param $tableName
     * @param $entityId
     * @return int
     */
    public function runDeleteUrl($tableName, $entityId): int
    {
        $connection = $this->config->getQueryResourceConnection()->getConnection();
        $tableName = $this->config->getQueryResourceConnection()->getTableName($tableName);
        return $connection->delete($tableName, ['entity_id = ?' => $entityId,]);
    }

    /**
     * Delete URL from url_rewrite table in Magento
     *
     * @param $tableName
     * @param $skuKey
     * @return int
     */
    public function runDeleteUrlPath($tableName, $skuKey): int
    {
        $skuKeyLower = strtolower($skuKey);
        $skuKeyFinal = $skuKeyLower . '.html';
        $connection = $this->config->getQueryResourceConnection()->getConnection();
        $tableName = $this->config->getQueryResourceConnection()->getTableName($tableName);
        return $connection->delete($tableName, ['request_path = ?' => $skuKeyFinal,]);
    }

    /**
     * Truncate table in Magento
     * @param $tableName
     */
    public function runTruncateTable($tableName)
    {
        $resource = $this->config->getQueryResourceConnection();
        $connection = $resource->getConnection();
        $tableNamef = $resource->getTableName($tableName); // add table name along with prefix
        // Getting Db resource connection for query Run start
        $sql = "Truncate table " . $tableNamef;
        $connection->query($sql);
    }

    /**
     * get Next Autoincrement ID of product in Magento
     * @param $tableName
     * @return mixed
     */
    public function getNextAutoincrement($tableName)
    {
        $resource = $this->config->getQueryResourceConnection();
        $connection = $resource->getConnection();
        $tableNamef = $resource->getTableName($tableName); // add table name along with prefix
        // Getting Db resource connection for query Run start
        $entityStatus = $connection->showTableStatus($tableNamef);
        if(empty($entityStatus['Auto_increment'])) {
            throw new LocalizedException(__('Cannot get autoincrement value'));
        }
        return $entityStatus['Auto_increment'];
        // SHOW TABLE STATUS LIKE 'd233_catalog_product_entity'
        // $this->getNextAutoincrement(catalog_product_entity);
    }

    /**
     * Econnect Update Single Item Sofware ID
     * @param $oldLiveItemId
     * @param $oldLiveItemInternalId
     */
    public function ECupdateSingleItemSofwareID($oldLiveItemId, $oldLiveItemInternalId)
    {
        $_messages = array();
        $ecUpdateCount = 0;
        try {
            ini_set('memory_limit', '1000M');

            $itemDetails = array(
                'ItemId' => $oldLiveItemId,
                'SoftwareId' => $this->softwareId()
            );

            $securityToken = $this->tranApi->getUeSecurityToken();

            $parameters = array(
                'securityToken' => $securityToken,
                'itemDetails' => $itemDetails,
                'itemId' => $oldLiveItemId,
                'itemInternalId' => $oldLiveItemInternalId
            );

            $updateItemResponse = $this->soapClient->UpdateItem($parameters);
            $obj = $updateItemResponse->UpdateItemResult;

            if($obj->Status == "Success") {
                // Call Update Query fuction to update values in DB
                //$this->runUpdateQueryItem('catalog_product_entity', 'item', 2, $Ec_ItemInternalId, $oldliveitemID, $item->getData('entity_id'));
                $ecUpdateCount++;
            }

        } catch (\Exception $ex) {
            //$_messages[] = array("type" => 'error', "message" => "Errors have occurred during the process, please try again. " . $ex->getMessage());
            //$_errorCount++;
        }

        $this->_prepareMessages($_messages);
    }

    /**
     * @param $regionCode
     * @param $countryCode
     * @return mixed
     */

    public function getRegionIdFinal($regionCode, $countryCode)
    {
        if(empty($regionCode)) {
            $regionCode = 'CA';
        } else {
            $regionCode = substr($regionCode, 0, 2);
            $regionCode = strtoupper($regionCode);
        }
        if(empty($countryCode)) {
            $countryCode = 'US';
        } else {
            $countryCode = substr($countryCode, 0, 2);
            $countryCode = strtoupper($countryCode);
        }

        $region = $this->regionFactory->create()->loadByCode($regionCode, $countryCode);
        return $region->getData();
    }

    private function getItemTypeMap($itemType)
    {
        switch ($itemType) {
            case "simple":
                $itemTypeFinal = "simple";
                break;
            case "virtual":
                $itemTypeFinal = "virtual";
                break;
            case "downloadable":
                $itemTypeFinal = "downloadable";
                break;
            case "configurable":
                $itemTypeFinal = "configurable";
                break;
            case "grouped":
                $itemTypeFinal = "grouped";
                break;
            case "bundle":
                $itemTypeFinal = "bundle";
                break;
            default:
                $itemTypeFinal = "simple";
        }

        return $itemTypeFinal;
    }

    /**
     * Download Econnect Items to Magento DB
     * @return void
     */
    public function downloadItem()
    {
        $counter = 0;
        $addCount = 0;
        $updateCount = 0;
        $ecUpdateCount = 0;
        $_errorCount = 0;
        $_processedCount = 0;
        $_messages = array();
        $local_itemID = '';
        $local_itemInternalID = '';

        // Redirect if module is disabled
        if($this->config->isEbizchargeActive() == 0) {
            $_messages[] = array(
                "type" => 'error',
                "message" => "EBizCharge module is inactive. Please activate before download."
            );
            $this->_prepareMessages($_messages);
            exit;
        }

        // Redirect if Download is disabled
        if($this->config->isEconnectDownlaodEnabled() == 0) {
            $_messages[] = array(
                "type" => 'error',
                "message" => "EBizCharge Download functionality is inactive. Please activate before download."
            );
            $this->_prepareMessages($_messages);
            exit;
        }

        // Get Full List
        //$Items_obj = $this->downloadMergedListItems("Search","Items","Item","eq", 1000, $itemId, $itemInternalId);
        $itemsObj = $this->downloadMergedListItems("Search", "Items", "Item", "notequal", 1000, '', '');

        if((!isset($itemsObj)) ||
            (empty($itemsObj)) ||
            ($itemsObj == null) ||
            ($itemsObj == '')) {
            $_messages[] = array("type" => 'error', "message" => "There are no Items to be download.");
            $this->_prepareMessages($_messages);
            exit;
        }

        // instance of object manager
        try {
            foreach ($itemsObj as $ecItem) {
                ini_set('memory_limit', '1000M');
                ini_set('max_execution_time', '-1');
                ini_set('max_input_time', '-1');

                if((empty($ecItem->ItemId)) || (empty($ecItem->Name))) {
                    $this->ebizLog()->info('Invalid item ' . $ecItem->ItemId . ', unable to download. Item name or ID is empty.',
                        '');
                    continue;
                }

                // For Item Add Process
                $product = $this->productFactory->create();

                // Live ID's
                $liveItemId = $ecItem->ItemId;
                $liveItemInternalId = $ecItem->ItemInternalId;
                $NextAutoincrement = $this->getNextAutoincrement('catalog_product_entity');
                //$local_itemID_bysku = $product->getIdBySku($ec_item->SKU);

                //get root category id
                $getRootCategoryId = $this->storeManager->getStore()->getRootCategoryId();

                // Get full Item For Item Update Process
                //$get_item = $this->productFactory->create()->load($product->getIdBySku($ec_item->SKU));
                $localEntityId = $this->runDownloadSelectQuery('catalog_product_entity', '*', 'ec_item_id',
                    $liveItemId);

                $qtyOnHand = intval($ecItem->QtyOnHand);
                $itemType = $ecItem->ItemType;
                $itemTypeFinal = $this->getItemTypeMap($itemType);

                $active = $ecItem->Active;
                switch ($active) {
                    case "true":
                        $activeFinal = 1;
                        break;
                    case "false":
                        $activeFinal = 2;
                        break;
                    default:
                        $activeFinal = 1;
                }

                if(empty($ecItem->SKU)) {
                    $ecItemSku = strtolower($ecItem->Name);
                    $ecItem->SKU = str_replace(" ", "-", $ecItemSku);
                }

                if(isset($localEntityId[0]['entity_id'])) {
                    try {
                        $local_itemID = $localEntityId[0]['entity_id'];
                        $local_itemInternalID = $localEntityId[0]['ec_item_internalid'];
                        // Econnect Update Start
                        if($local_itemInternalID == $liveItemInternalId) {
                            // Delete URL if exist
                            $this->runDeleteUrl('url_rewrite', $local_itemID);
                            $this->runDeleteUrlPath('url_rewrite', $ecItem->SKU);
                            // Mage Add Start
                            $product->setEntityId($local_itemID); // ID of the product
                            // Get the default attribute set id
                            //$attributeSetId = $product->getDefaultAttributeSetId();
                            $attributeSetId = $localEntityId[0]['attribute_set_id'];
                            $sku = $urlKey = $ecItem->SKU;
                            $product->setSku($sku); // sku of the product
                            $product->setName($ecItem->Name); // name of the product
                            //$product->setUrlKey($urlKey.'-'.$local_itemID); // url key of the product
                            $product->setUrlKey($urlKey); // url key of the product
                            $product->setAttributeSetId($attributeSetId); // attribute set id
                            $product->setStatus($activeFinal); // enabled = 1, disabled = 2
                            // visibilty of product, 1 = Not Visible Individually, 2 = Catalog, 3 = Search, 4 = Catalog, Search
                            $product->setVisibility(4);
                            $product->setTaxClassId(0); // Tax class id, 0 = None, 2 = Taxable Goods, etc.
                            $product->setTypeId($itemTypeFinal); // type of product (simple/virtual/downloadable/configurable)
                            $product->setProductHasWeight(1); // 1 = simple product, 0 = virtual product
                            $product->setWeight(1.000); // weight of product
                            $product->setPrice($ecItem->UnitPrice); // price of the product
                            $product->setWebsiteIds(array(1)); // Assign product to Websites
                            $product->setCategoryIds(array($getRootCategoryId)); // Assign product to categories
                            $product->setCreatedAt($ecItem->DateTimeCreated);
                            $product->setUpdatedAt($ecItem->DateTimeModified);
                            $product->setEcItemSyncStatus(2); // EC Status
                            $product->setEcItemInternalid($ecItem->ItemInternalId); // EC ItemInternalid
                            $product->setEcItemId($ecItem->ItemId); // EC ItemId
                            $product->setEcItemLastsyncdate($ecItem->DateTimeModified); // EC ItemLastsyncdate
                            $product->setStockData(
                                array(
                                    'use_config_manage_stock' => 0,
                                    'manage_stock' => 1,
                                    'is_in_stock' => 1,
                                    'qty' => $qtyOnHand
                                )
                            );
                            $product->save();

                            // EC update
                            $this->ECupdateSingleItemSofwareID($liveItemId, $liveItemInternalId);
                            // Update counter
                            $updateCount++;
                        }

                    } catch (\Exception $ex) {
                        //throw new \Magento\Framework\Exception\LocalizedException(__('SoapFault: Unable to add Product' . $ex->getMessage()));
                        $_errorCount++;
                    }
                } else {
                    try {
                        // Delete URL if exist
                        $this->ebizLog()->info('Product adding ' . $ecItem->Name . ': LocalID ' . $NextAutoincrement . ' = LiveID ' . $ecItem->ItemId);
                        $this->runDeleteUrl('url_rewrite', $NextAutoincrement);
                        $this->runDeleteUrlPath('url_rewrite', $ecItem->SKU);
                        // Mage Add Start
                        $product->setEntityId($NextAutoincrement); // ID of the product
                        // Get the default attribute set id
                        $attributeSetId = $product->getDefaultAttributeSetId();
                        $sku = $urlKey = $ecItem->SKU;
                        $product->setSku($sku); // sku of the product
                        $product->setName($ecItem->Name); // name of the product
                        //$product->setUrlKey($urlKey.'-'.$NextAutoincrement); // url key of the product
                        $product->setUrlKey($urlKey); // url key of the product
                        $product->setAttributeSetId($attributeSetId); // attribute set id
                        $product->setStatus($activeFinal); // enabled = 1, disabled = 0
                        // visibilty of product, 1 = Not Visible Individually, 2 = Catalog, 3 = Search, 4 = Catalog, Search
                        $product->setVisibility(4);
                        $product->setTaxClassId(0); // Tax class id, 0 = None, 2 = Taxable Goods, etc.
                        $product->setTypeId($itemTypeFinal); // type of product (simple/virtual/downloadable/configurable)
                        $product->setProductHasWeight(1); // 1 = simple product, 0 = virtual product
                        $product->setWeight(1.000); // weight of product
                        $product->setPrice($ecItem->UnitPrice); // price of the product
                        $product->setWebsiteIds(array(1)); // Assign product to Websites
                        $product->setCategoryIds(array()); // Assign product to categories // $getRootCategoryId
                        $product->setCreatedAt($ecItem->DateTimeCreated);
                        $product->setUpdatedAt($ecItem->DateTimeModified);
                        $product->setEcItemSyncStatus(2); // EC Status
                        $product->setEcItemInternalid($ecItem->ItemInternalId); // EC ItemInternalid
                        $product->setEcItemId($ecItem->ItemId); // EC ItemId
                        $product->setEcItemLastsyncdate($ecItem->DateTimeModified); // EC ItemLastsyncdate
                        $product->setStockData(
                            array(
                                'use_config_manage_stock' => 0,
                                'manage_stock' => 1,
                                'is_in_stock' => 1,
                                'qty' => $qtyOnHand
                            )
                        );
                        $product->save();

                        $this->ebizLog()->info('Magento Product Add : LocalID ' . $product->getId() . '-' . $NextAutoincrement . ' = LiveID ' . $ecItem->ItemId);
                        // EC Update
                        $this->ECupdateSingleItemSofwareID($liveItemId, $liveItemInternalId);
                        // Add Counter
                        $addCount++;
                    } catch (\Exception $ex) {
                        //throw new \Magento\Framework\Exception\LocalizedException(__('SoapFault: Unable to add Product' . $ex->getMessage()));
                        $_errorCount++;
                    }
                }
                $_processedCount++;
                //}
                $counter++;
            }
        } catch (\Exception $ex) {
            $_messages[] = array(
                "type" => 'error',
                "message" => " There is an Error during the process, please try again. " . $ex->getMessage()
            );
            $_errorCount++;
        }
        // Info Messages
        array_unshift($_messages, array(
            "type" => 'notice',
            "message" => sprintf("Items sync process has been completed. %s record(s) processed.", $_processedCount)
        ));

        if($addCount > 0) {
            $_messages[] = array(
                "type" => 'success',
                "message" => " (T2) " . $addCount . " Item(s) are added successfully."
            );
        }
        if($updateCount > 0) {
            $_messages[] = array("type" => 'success', "message" => $updateCount . " Item(s) are updated successfully.");
        }
        if($_errorCount > 0) {
            $_messages[] = array("type" => 'error', "message" => $_errorCount . 'Item(s) not added!');
        }
        $this->_prepareMessages($_messages);
    }

    //* =========== Download Options for EConnect Customers ============ *//

    /**
     * New Function Search Ebiz customer list merged
     */
    /**
     * @param $magCustomerId
     * @param $customerInternalId
     * @param $limit
     * @return array
     */
    function searchCustomersDownloadMerged($magCustomerId, $customerInternalId, $limit)
    {
        $securityToken = $this->tranApi->getUeSecurityToken();
        $ebzcCustomer = '';
        $maxSize = 0;
        $position = 0;
        $customersObj = array();
        do {
            $searchFilter = array(
                'FieldName' => 'SoftwareId',
                'ComparisonOperator' => 'eq',
                'FieldValue' => $this->softwareId()
            );
            $filters = array(
                'SearchFilter' => $searchFilter
            );

            $searchCustomerList = array(
                'securityToken' => $securityToken,
                'customerId' => $magCustomerId,
                'customerInternalId' => $customerInternalId,
                'start' => $position,
                'limit' => $limit,
                'sort' => 'SalesOrderNumber',
                'includeCustomerToken' => 1,
                'includePaymentMethodProfiles' => 0,
                'countOnly' => 0,
                'filters' => $filters
            );

            $searchCustomer = $this->soapClient->SearchCustomerList($searchCustomerList);

            if(!isset ($searchCustomer->SearchCustomerListResult->CustomerList->Customer)) {
                $customersObj = array();
                $resultCount = 0;
            } elseif((is_array($searchCustomer->SearchCustomerListResult->CustomerList->Customer)) && (count($searchCustomer->SearchCustomerListResult->CustomerList->Customer)) > 1) {
                $ebzcCustomer = $searchCustomer->SearchCustomerListResult->CustomerList->Customer;
                $resultCount = count($searchCustomer->SearchCustomerListResult->CustomerList->Customer);
                $customersObj = array_merge($customersObj, $ebzcCustomer);
            } else {
                $customersObj = $searchCustomer->SearchCustomerListResult->CustomerList;
                $resultCount = 1;
            }

            if($resultCount < 1000) {
                $maxSize = 1;
            }
            $position = $position + 1000;
        } while ($maxSize == 0);
        //---- Fetch all Records in an array list End ----//
        return ($customersObj);
    }

    /**
     * Econnect Update Single Customer Sofware ID
     * @param $EbizFullCustomer
     */
    public function ECupdateSingleCustSofwareID($EbizFullCustomer)
    {
        $EbizFullCustomer->SoftwareId = $this->softwareId();
        //$_messages = array();
        //$ecUpdateCount = 0;
        try {

            $parameters = array(
                'securityToken' => $this->tranApi->getUeSecurityToken(),
                'customer' => $EbizFullCustomer,
                'customerId' => $EbizFullCustomer->CustomerId,
                'customerInternalId' => $EbizFullCustomer->CustomerInternalId
            );

            $updateCustResponse = $this->soapClient->UpdateCustomer($parameters);
            $obj = $updateCustResponse->UpdateCustomerResult;

            if($obj->Status == "Success") {
                // Call Update Query fuction to update values in DB
                $this->ebizLog()->info('4- Customer SoftwareID Updated. ItemID: ' . $EbizFullCustomer->CustomerId . ' = itemInternalId ' . $EbizFullCustomer->CustomerInternalId);
                //$ecUpdateCount ++;
            } else {
                $this->ebizLog()->info('5- Customer ' . $EbizFullCustomer->CustomerId . ' SoftwareID not Updated.');
            }
        } catch (\Exception $ex) {
            $this->ebizLog()->info('6- unable to update Customer (' . $EbizFullCustomer->CustomerId . ') SoftwareID.');
        }
    }

    /**
     * Download Econnect Customers to Magento DB
     * @return void
     */
    public function downloadCustomer()
    {
        $counter = 0;
        $addCount = 0;
        $updateCount = 0;
        $_errorCount = 0;
        $_processedCount = 0;
        $_messages = array();
        $timenow = date("Y-m-d h:i:s");
        $regionID['region_id'] = '';

        // Redirect if module is disabled
        if($this->config->isEbizchargeActive() == 0) {
            $_messages[] = array(
                "type" => 'error',
                "message" => "EBizCharge module is inactive. Please activate before download."
            );
            $this->_prepareMessages($_messages);
            exit;
        }

        // Redirect if Download is disabled
        if($this->config->isEconnectDownlaodEnabled() == 0) {
            $_messages[] = array(
                "type" => 'error',
                "message" => "EBizCharge Download functionality is inactive. Please activate before download."
            );
            $this->_prepareMessages($_messages);
            exit;
        }

        $websiteId = $this->storeManager->getStore()->getWebsiteId();
        // Customer Factory to Create Customer
        $customerFactory = $this->customerFactory->create();

        $searchAllCustomers = $this->searchCustomersDownloadMerged('', '', 1000);

        try {
            $this->processCustomerDownload($counter, $websiteId, $customerFactory, $searchAllCustomers, $addCount,
                $updateCount, $_errorCount, $_processedCount);
        } catch (\Exception $ex) {
            $_messages[] = array(
                "type" => 'error',
                "message" => " There is an Error during the process, please try again. " . $ex->getMessage()
            );
            $_errorCount++;
        }

        array_unshift($_messages, array(
            "type" => 'notice',
            "message" => sprintf("Customers sync process has been completed. %s record(s) processed.", $counter)
        ));

        if($addCount > 0) {
            $_messages[] = array("type" => 'success', "message" => $addCount . " Customer(s) are added successfully.");
        }

        if($updateCount > 0) {
            $_messages[] = array("type" => 'error', "message" => $updateCount . ' Customer(s) already exist.');
        }

        if($_errorCount > 0) {
            $_messages[] = array("type" => 'error', "message" => $_errorCount . ' Customer(s) not added.');
        }
        $this->_prepareMessages($_messages);
    }

    /**
     * @param $counter
     * @param $websiteId
     * @param $customerFactory
     * @param $searchAllCustomers
     * @param int $addCount
     * @param int $updateCount
     * @param int $_errorCount
     * @param int $_processedCount
     */
    public function processCustomerDownload(
        $counter,
        $websiteId,
        $customerFactory,
        $searchAllCustomers,
        &$addCount = 0,
        &$updateCount = 0,
        &$_errorCount = 0,
        &$_processedCount = 0
    ) {
        if(count($searchAllCustomers) > 0) {
            foreach ($searchAllCustomers as $ebizFullCustomer) {
                ini_set('memory_limit', '1000M');
                ini_set('max_execution_time', '-1');
                ini_set('max_input_time', '-1');
                // Live ID's
                $liveCustId = $ebizFullCustomer->CustomerId;
                $liveCustInternalId = $ebizFullCustomer->CustomerInternalId;
                $getEbizCustomerNumber = $ebizFullCustomer->CustomerToken;

                if((!isset($getEbizCustomerNumber)) ||
                    (empty($getEbizCustomerNumber)) ||
                    (empty($ebizFullCustomer->FirstName)) ||
                    (empty($ebizFullCustomer->LastName))) {
                    $this->ebizLog()->info('Customer (' . $liveCustId . ') Econnect Customer Token/Firstname/Lastname is empty.');
                    continue;
                }
                if(empty($ebizFullCustomer->ShippingAddress->Country)) {
                    $country_id = 'US';
                } else {
                    $country_id = $ebizFullCustomer->ShippingAddress->Country;
                }

                if(empty($ebizFullCustomer->Phone)) {
                    $Phone = '0123456789';
                } else {
                    $Phone = $ebizFullCustomer->Phone;
                }

                $regionID = $this->getRegionIdFinal($ebizFullCustomer->BillingAddress->State, $country_id);

                if(empty($regionID['region_id'])) {
                    $regionID['region_id'] = '12';
                }

                $address = array(
                    'customer_address_id' => '',
                    'prefix' => '',
                    'firstname' => $ebizFullCustomer->FirstName,
                    'middlename' => '',
                    'lastname' => $ebizFullCustomer->LastName,
                    'suffix' => '',
                    'company' => $ebizFullCustomer->CompanyName,
                    'street' => array(
                        '0' => $ebizFullCustomer->BillingAddress->Address1, // this is mandatory
                        '1' => $ebizFullCustomer->BillingAddress->Address1 // this is optional // Address2
                    ),
                    'city' => $ebizFullCustomer->BillingAddress->City,
                    'country_id' => $country_id, // two letters country code
                    'region' => $ebizFullCustomer->BillingAddress->State, // can be empty '' if no region
                    'region_id' => $regionID['region_id'], // can be empty '' if no region_id
                    //'region_id' => '', // can be empty '' if no region_id
                    'postcode' => $ebizFullCustomer->BillingAddress->ZipCode,
                    'telephone' => $Phone,
                    'fax' => '',
                    'save_in_address_book' => 1
                );


                /**
                 * check whether the email address is already registered or not
                 */
                $customer = $customerFactory->setWebsiteId($websiteId)->loadByEmail($ebizFullCustomer->Email);
                /**
                 * if email address already registered, return the error message
                 * else, create new customer account
                 */
                if($customer->getId()) {
                    //echo 'Customer with email '.$email.' is already registered.';
                    $this->ebizLog()->info('5- Customer (' . $customer->getId() . ') with email ' . $ebizFullCustomer->Email . ' is already registered.');
                    $updateCount++;
                } else {
                    try {
                        $this->updateDownloadCustomer($address, $websiteId, $ebizFullCustomer);
                        // Update SoftwareID on Econnect
                        $this->ECupdateSingleCustSofwareID($ebizFullCustomer);
                        $addCount++;
                    } catch (\Exception $ex) {
                        //throw new \Magento\Framework\Exception\LocalizedException(__('SoapFault: Unable to add Product' . $ex->getMessage()));
                        $_errorCount++;
                    }
                }
                $counter++;
            }

        } else {

            $_messages[] = array("type" => 'error', "message" => "There are no Customers to be download.");
            $this->_prepareMessages($_messages);
            exit;
        }
    }

    /**
     * @param $address
     * @param $websiteId
     * @param $ebizFullCustomer
     */
    public function updateDownloadCustomer($address, $websiteId, $ebizFullCustomer)
    {
        $customer = $this->customerFactory->create();
        //$customer->setEntityId($live_custID); // ID of the product

        $customer->setWebsiteId($websiteId);
        $customer->setEmail($ebizFullCustomer->Email);
        $customer->setFirstname($ebizFullCustomer->FirstName);
        $customer->setLastname($ebizFullCustomer->LastName);
        $customer->setPassword($ebizFullCustomer->Email);
        // save customer
        $customer->save();
        $customer->setConfirmation(null);
        $customer->save();

        $setCountryId = !empty($address['country_id']) ? $address['country_id'] : 'US';
        $setPostcode = !empty($address['postcode']) ? $address['postcode'] : '92618';
        $setCity = !empty($address['city']) ? $address['city'] : 'irvine';
        $setStreet = !empty($address['street'][0]) ? $address['street'][0] : array(0 => '20, pacifica');

        $customAddress = $this->addressFactory->create();
        $customAddress->setData($address)
            ->setCustomerId($customer->getId())
            ->setFirstname($address['firstname'])
            ->setLastname($address['lastname'])
            ->setCountryId($setCountryId)
            ->setPostcode($setPostcode)
            ->setCity($setCity)
            ->setTelephone($address['telephone'])
            ->setCompany('')
            ->setStreet($setStreet)
            ->setIsDefaultBilling('1')
            ->setIsDefaultShipping('1')
            ->setSaveInAddressBook($address['save_in_address_book']);

        // save customer address
        $customAddress->save();
        //reindex customer grid index
        $indexerId = 'customer_grid';
        $indexer = $this->indexerFactory->create();
        $indexer->load($indexerId);
        $indexer->reindexAll();

        $this->ebizLog()->info('6- Customer ' . $customer->getId() . ' with email ' . $ebizFullCustomer->Email . ' is successfully created.');

        // Add vlues in Token table
        $this->saveEbizToken((int)$customer->getId(), (int)$ebizFullCustomer->CustomerToken);


        // Add Ec values in cutomer table
        $this->runUpdateQueryCustomer('customer_entity', 'cust', 2, $ebizFullCustomer->CustomerInternalId,
            $ebizFullCustomer->CustomerId, $ebizFullCustomer->CustomerToken, $customer->getId());
    }

    //* =========== Download Options for EConnect Orders ============ *//

    /**
     * @param $tableName
     * @param $tableFields
     * @param $whereKey
     * @param $whereValue
     * @return mixed
     */
    public function runSelectQuery($tableName, $tableFields, $whereKey, $whereValue)
    {
        $connection = $this->config->getQueryResourceConnection()->getConnection();
        $tableName = $this->config->getQueryResourceConnection()->getTableName($tableName);

        $select = $connection->select()
            ->from($tableName)
            ->where($whereKey . ' = ?', $whereValue);

        return $connection->fetchAll($select);
    }

    /**
     * @param $magentoCustId
     * @return array
     */
    public function runSelectQueryCustomerAddress($magentoCustId)
    {
        $connection = $this->config->getQueryResourceConnection()->getConnection();
        $tableName = $this->config->getQueryResourceConnection()->getTableName('customer_address_entity');

        $select = $connection->select()
            ->from($tableName)
            ->where('parent_id = ?', $magentoCustId);

        return $connection->fetchAll($select);
    }


    /* Full Orders list data fetch from EBiz */
    /**
     * @param $customerId
     * @param $salesOrderNumber
     * @param $salesOrderInternalId
     * @param $eqOrNot
     * @param $limit
     * @return array
     */
    public function downloadMergedListOrders($customerId, $salesOrderNumber, $salesOrderInternalId, $eqOrNot, $limit)
    {
        // Condition start
        $maxSize = 0;
        $position = 0;
        $ordersObj = array();
        $securityToken = $this->tranApi->getUeSecurityToken();

        do {
            // Search Criteria
            $searchFilter = array(
                'FieldName' => 'Software',
                'ComparisonOperator' => $eqOrNot,
                'FieldValue' => $this->softwareId()
            );

            $searchOrders = array(
                'securityToken' => $securityToken,
                'customerId' => $customerId,
                'salesOrderNumber' => $salesOrderNumber,
                'salesOrderInternalId' => $salesOrderInternalId,
                'start' => $position,
                'limit' => $limit,
                'sort' => 'CustomerId',
                'includeItems' => 0,
                'filters' => ['SearchFilter' => $searchFilter]
            );

            $SearchSalesOrders = $this->soapClient->SearchSalesOrders($searchOrders);

            if(!isset ($SearchSalesOrders->SearchSalesOrdersResult->SalesOrder)) {
                $ordersObj = array();
                $resultCount = 0;
            } elseif((is_array($SearchSalesOrders->SearchSalesOrdersResult->SalesOrder)) &&
                (count($SearchSalesOrders->SearchSalesOrdersResult->SalesOrder)) > 1) {
                $ebzcOrders = $SearchSalesOrders->SearchSalesOrdersResult->SalesOrder;
                $resultCount = count($SearchSalesOrders->SearchSalesOrdersResult->SalesOrder);
                $ordersObj = array_merge($ordersObj, $ebzcOrders);
            } else {
                $ordersObj = $SearchSalesOrders->SearchSalesOrdersResult;
                $resultCount = 1;
            }

            if($resultCount < 1000) {
                $maxSize = 1;
            }
            $position = $position + 1000;

        } while ($maxSize == 0);

        return ($ordersObj);
    }

    /**
     * Econnect Update Single Order Sofware ID
     * @param $ebizFullOrder
     */
    public function ecUpdateSingleOrderSofwareID($ebizFullOrder)
    {
        $ebizFullOrder->Software = $this->softwareId();

        $ecUpdateCount = 0;
        $_errorCount = 0;
        try {

            $parameters = array(
                'securityToken' => $this->tranApi->getUeSecurityToken(),
                'salesOrder' => $ebizFullOrder,
                'customerId' => $ebizFullOrder->CustomerId,
                'salesOrderNumber' => $ebizFullOrder->SalesOrderNumber,
                'salesOrderInternalId' => $ebizFullOrder->SalesOrderInternalId
            );

            $updateSalesOrderResponse = $this->soapClient->UpdateSalesOrder($parameters);
            $obj = $updateSalesOrderResponse->UpdateSalesOrderResult;

            if($obj->Status == "Success") {
                // Call Update Query fuction to update values in DB
                $this->ebizLog()->info('Order SoftwareID Updated. OrderID: ' . $ebizFullOrder->SalesOrderNumber . ' = itemInternalId ' . $ebizFullOrder->SalesOrderInternalId);
                $ecUpdateCount++;
            } else {
                $this->ebizLog()->info('Order #' . $ebizFullOrder->SalesOrderNumber . ' SoftwareID not updated Updated.');
            }
        } catch (\Exception $ex) {
            $this->ebizLog()->info('unable to update Order #' . $ebizFullOrder->SalesOrderNumber . ' SoftwareID.');
        }
    }

    // Econnect get Order payments

    /**
     * @param $customerId
     * @param $orderId
     * @return mixed
     */
    public function ecGetOrderPayments($customerId, $orderId)
    {
        try {
            $securityToken = $this->tranApi->getUeSecurityToken();

            $searchFilter = array(
                'FieldName' => 'CustomerId',
                'ComparisonOperator' => 'eq',
                'FieldValue' => $customerId
            );
            $searchFilter2 = array(
                'FieldName' => 'OrderID',
                'ComparisonOperator' => 'eq',
                'FieldValue' => $orderId
            );

            $searchFilter3['SearchFilter'][0] = $searchFilter;
            $searchFilter3['SearchFilter'][1] = $searchFilter2;

            $searchTrans = array(
                'securityToken' => $securityToken,
                'matchAll' => 1,
                'countOnly' => 0,
                'start' => 0,
                'limit' => 1000,
                'sort' => 'DateTime',
                'filters' => $searchFilter3
            );

            $searchTransactions = $this->soapClient->SearchTransactions($searchTrans);
            if(isset($searchTransactions->SearchTransactionsResult)) {
                return $searchTransactions->SearchTransactionsResult;
            }

        } catch (\Exception $ex) {
            $this->ebizLog()->info(__METHOD__ . $ex->getMessage());
        }

        return [];
    }

    /**
     * @param $customerId
     * @param $orderId
     * @param $getPaymentMethod
     * @return array|string
     */
    public function ecGetOrderPaymentsSaved($customerId, $orderId, $getPaymentMethod)
    {
        $_messages = array();
        $transactions = $this->ecGetOrderPayments($customerId, $orderId);
        $transactionsCount = $transactions->TransactionsReturned;
        $transactionsCountFinal = $transactionsCount - 1;
        $transactionsResultObject = '';

        try {
            if((!isset($transactions->Transactions->TransactionObject)) ||
                (empty($transactions->Transactions->TransactionObject)) ||
                ($transactions->Transactions->TransactionObject == null) ||
                ($transactions->Transactions->TransactionObject == '')) {

                $this->ebizLog()->info("There are no Transactions for Order #" . $orderId);
                $transactionsResultObject = 'Not Found';
                return $transactionsResultObject;

            } elseif((is_array($transactions->Transactions->TransactionObject)) &&
                (count($transactions->Transactions->TransactionObject)) > 1) {
                $transactionObject = $transactions->Transactions->TransactionObject;
                $transactionObjectFinal = $transactionObject[$transactionsCountFinal];

            } else {
                $transactionObject = $transactions->Transactions->TransactionObject;
                //$TransactionObject = $transactions->Transactions;
                $transactionObjectFinal = $transactionObject;
            }

            if($transactionObjectFinal->CreditCardData->CardType = 'V') {
                $cardType = "VI";
            }

            if($transactionObjectFinal->CreditCardData->CardType = 'M') {
                $cardType = "MA";
            }

            if($transactionObjectFinal->CreditCardData->CardType = 'A') {
                $cardType = "AE";
            }

            if($transactionObjectFinal->CreditCardData->CardType = 'DS') {
                $cardType = "DS";
            }

            $transactionType = $transactionObjectFinal->TransactionType;

            switch ($transactionType) {
                case "Sale":
                    $TransactionTypeFinal = "sale";
                    break;
                case "Credit":
                    $TransactionTypeFinal = "credit";
                    break;
                case "Auth Only":
                    $TransactionTypeFinal = "authorization";
                    break;
                case "Voided Sale":
                    $TransactionTypeFinal = "void";
                    break;
                case "Refunded":
                    $TransactionTypeFinal = "refund";
                    break;
                default:
                    $TransactionTypeFinal = "sale";
            }

            $authCode = isset($transactionObjectFinal->Response->AuthCode) ? $transactionObjectFinal->Response->AuthCode : '';
            $avsResultCode = isset($transactionObjectFinal->Response->AvsResultCode) ? $transactionObjectFinal->Response->AvsResultCode : '';
            $cardCodeResultCode = isset($transactionObjectFinal->Response->CardCodeResultCode) ? $transactionObjectFinal->Response->CardCodeResultCode : '';

            $responseError = isset($transactionObjectFinal->Response->Error) ? $transactionObjectFinal->Response->Error : '';
            $responseErrorCode = isset($transactionObjectFinal->Response->ErrorCode) ? $transactionObjectFinal->Response->ErrorCode : '';

            $transactionsResultObject = [
                'additional_data' => [
                    'cc_cid' => $transactionObjectFinal->CreditCardData->CardCode,
                    'cc_type' => $cardType,
                    'cc_exp_year' => $transactionObjectFinal->CreditCardData->CardExpiration,
                    'cc_exp_month' => 'XX',
                    'cc_number' => $transactionObjectFinal->CreditCardData->CardNumber,
                    'cc_owner' => $transactionObjectFinal->AccountHolder,
                    'ebzc_option' => 'downloadorder',
                    'ebzc_method_id' => '',
                    'ebzc_cust_id' => $customerId,
                    'ebzc_save_payment' => 1,
                    'paymentToken' => 1,
                    // New TransactionData
                    'method' => $getPaymentMethod,
                    'TransactionsMatched' => $transactions->TransactionsMatched,
                    'TransactionsReturned' => $transactions->TransactionsReturned,
                    'CustNum' => $transactionObjectFinal->Response->CustNum,
                    'CustId' => $transactionObjectFinal->CustomerID,
                    'RefNum' => $transactionObjectFinal->Response->RefNum,
                    'AuthCode' => $authCode,
                    'Status' => $transactionObjectFinal->Response->Status,
                    'Result' => $transactionObjectFinal->Response->Result,
                    'ResultCode' => $transactionObjectFinal->Response->ResultCode,
                    'BatchNum' => $transactionObjectFinal->Response->BatchNum,
                    'Shipping' => $transactionObjectFinal->Details->Shipping,
                    'ShipFromZip' => (isset($transactionObjectFinal->Details->ShipFromZip) ? $transactionObjectFinal->Details->ShipFromZip : null),
                    'Tax' => $transactionObjectFinal->Details->Tax,
                    'Discount' => $transactionObjectFinal->Details->Discount,
                    'PONum' => $transactionObjectFinal->Details->PONum,
                    'OrderID' => $transactionObjectFinal->Details->OrderID,
                    'Invoice' => $transactionObjectFinal->Details->Invoice,
                    'Amount' => $transactionObjectFinal->Details->Amount,
                    'CardType' => $cardType,
                    'CardNumber' => $transactionObjectFinal->CreditCardData->CardNumber,
                    'CardExpiration' => $transactionObjectFinal->CreditCardData->CardExpiration,
                    'CardCode' => $transactionObjectFinal->CreditCardData->CardCode,
                    'AvsZip' => $transactionObjectFinal->CreditCardData->AvsZip,
                    'AvsStreet' => $transactionObjectFinal->CreditCardData->AvsStreet,
                    'AvsResult' => $transactionObjectFinal->Response->AvsResult,
                    'AvsResultCode' => $avsResultCode,
                    'AccountHolder' => $transactionObjectFinal->AccountHolder,
                    'Status' => $transactionObjectFinal->Status,
                    'TransactionType' => $TransactionTypeFinal,
                    'CardCodeResult' => $transactionObjectFinal->Response->CardCodeResult,
                    'CardCodeResultCode' => $cardCodeResultCode,
                    'ConvertedAmount' => $transactionObjectFinal->Response->ConvertedAmount,
                    'ConversionRate' => $transactionObjectFinal->Response->ConversionRate,
                    'Error' => $responseError,
                    'ErrorCode' => $responseErrorCode
                ]
            ];
            return $transactionsResultObject;

        } catch (\Exception $ex) {
            $transactionsResultObject = 'Not Found';
            return $transactionsResultObject;
        }
    }

    public function getShippingMethods()
    {
        $activeCarriers = $this->shipConfig->getActiveCarriers();
        $storeScope = ScopeInterface::SCOPE_STORE;
        foreach ($activeCarriers as $carrierCode => $carrierModel) {
            $options = array();
            if($carrierMethods = $carrierModel->getAllowedMethods()) {
                foreach ($carrierMethods as $methodCode => $method) {
                    $code = $carrierCode . '_' . $methodCode;
                    $options[] = array('value' => $code, 'label' => $method);
                }
                $carrierTitle = $this->scopeConfig->getValue('carriers/' . $carrierCode . '/title');
            }
            $methods[] = array('smethod' => $carrierTitle, 'scode' => $code);
        }

        $defaultScode = $this->scopeConfig->getValue('payment/ebizcharge_ebizcharge/shippingmethod', $storeScope);

        if((isset($defaultScode)) || ($defaultScode != null)) {
            $scode = explode(',', $defaultScode);
            $scode = reset($scode);
        } else {
            $scode_final = reset($methods);
            $scode = $scode_final['scode'];
        }
        return $scode;
    }

    public function getPaymentMethods()
    {
        $activePaymentMethods = $this->paymentConfig->getActiveMethods();

        $orderPaymentCollection = array_keys($activePaymentMethods);

        $foundKey = array_search('ebizcharge_ebizcharge', $orderPaymentCollection);

        if($foundKey) {
            $paymentMethods = $orderPaymentCollection[$foundKey];
        } else {
            $paymentMethods = reset($orderPaymentCollection);
        }

        return $paymentMethods;
    }

    /**
     * @param $ecCustomerId
     * @return string
     */
    public function addSingleCustomerToMagento($ecCustomerId)
    {
        $result = '';
        $liveCustomerId = '';
        $liveCustomerInternalId = '';
        $ShippingAddress = array();

        $websiteId = $this->storeManager->getStore()->getWebsiteId();
        // Customer Factory to Create Customer
        $customerFactory = $this->customerFactory->create();

        // Get full Customer
        $getCustomer = $this->soapClient->GetCustomer(
            array(
                'securityToken' => $this->tranApi->getUeSecurityToken(),
                'customerId' => $ecCustomerId
            ));

        $ebizCustomer = $getCustomer->GetCustomerResult;

        try {
            // Live ID's
            $liveCustomerId = $ebizCustomer->CustomerId;
            $liveCustomerInternalId = $ebizCustomer->CustomerInternalId;
            $getEbizCustomerNumber = $ebizCustomer->CustomerToken;

            $firstName = $ebizCustomer->FirstName;
            $lastName = $ebizCustomer->LastName;
            $email = $ebizCustomer->Email;
            $password = $ebizCustomer->Email;

            if((!isset($getEbizCustomerNumber)) || (empty($getEbizCustomerNumber)) || (empty($firstName)) || (empty($lastName))) {
                $this->ebizLog()->info('Customer (' . $liveCustomerId . ') Econnect Customer Token/Firstname/Lastname is empty.');
                $result = "Not added";
                return $result;
            }

            if(empty($ebizCustomer->ShippingAddress->Country)) {
                $countryId = 'US';
            } else {
                $countryId = $ebizCustomer->ShippingAddress->Country;
            }

            if(empty($ebizCustomer->BillingAddress->City)) {
                $city = 'Irvine';
            } else {
                $city = $ebizCustomer->BillingAddress->City;
            }

            if(empty($ebizCustomer->BillingAddress->ZipCode)) {
                $zipCode = '92618';
            } else {
                $zipCode = $ebizCustomer->BillingAddress->ZipCode;
            }

            if(empty($ebizCustomer->BillingAddress->Address1)) {
                $Address1 = '20, pacifica';
            } else {
                $Address1 = $ebizCustomer->BillingAddress->Address1;
            }

            if(empty($ebizCustomer->Phone)) {
                $phone = '0123456789';
            } else {
                $phone = $ebizCustomer->Phone;
            }

            $regionID = $this->getRegionIdFinal($ebizCustomer->BillingAddress->State, $countryId);

            if(empty($regionID['region_id'])) {
                $regionID['region_id'] = '12';
            }

            $address = array(
                'customer_address_id' => '',
                'prefix' => '',
                'firstname' => $firstName,
                'middlename' => '',
                'lastname' => $lastName,
                'suffix' => '',
                'company' => $ebizCustomer->CompanyName,
                'street' => array(
                    '0' => $Address1, // this is mandatory
                    '1' => $Address1 // this is optional // Address2
                ),
                'city' => $city,
                'country_id' => $countryId, // two letters country code
                'region' => $ebizCustomer->BillingAddress->State, // can be empty '' if no region
                'region_id' => $regionID['region_id'], // can be empty '' if no region_id
                'postcode' => $zipCode,
                'telephone' => $phone,
                'fax' => '',
                'save_in_address_book' => 1
            );

            /**
             * check whether the email address is already registered or not
             */
            $customer = $customerFactory->setWebsiteId($websiteId)->loadByEmail($email);
            /**
             * if email address already registered, return the error message
             * else, create new customer account
             */
            if($customer->getId()) {
                $this->ebizLog()->info('7- Customer (' . $customer->getId() . ') with email ' . $email . ' is already registered.');
                $result = "Existing";
            } else {
                try {
                    $customer = $this->customerFactory->create();
                    //$customer->setEntityId($live_custID); // ID of the product
                    $customer->setWebsiteId($websiteId);
                    $customer->setEmail($email);
                    $customer->setFirstname($ebizCustomer->FirstName);
                    $customer->setLastname($ebizCustomer->LastName);
                    $customer->setPassword($password);
                    // save customer
                    $customer->save();
                    $customer->setConfirmation(null);
                    $customer->save();
                    $customAddress = $this->addressFactory->create();
                    $customAddress->setData($address)
                        ->setCustomerId($customer->getId())
                        ->setFirstname($address['firstname'])
                        ->setLastname($address['lastname'])
                        ->setCountryId($address['country_id'])
                        ->setPostcode($address['postcode'])
                        ->setCity($address['city'])
                        ->setTelephone($address['telephone'])
                        ->setCompany('')
                        ->setStreet($address['street'][0])
                        ->setIsDefaultBilling('1')
                        ->setIsDefaultShipping('1')
                        ->setSaveInAddressBook($address['save_in_address_book']);
                    //->setSaveInAddressBook($address['save_in_address_book']);

                    // save customer address
                    $customAddress->save();
                    //reindex customer grid index
                    $indexerId = 'customer_grid';
                    $indexer = $this->indexerFactory->create();
                    $indexer->load($indexerId);
                    $indexer->reindexAll();

                    $this->ebizLog()->info('8- Customer ' . $customer->getId() . ' with email ' . $email . ' is successfully created.');

                    // Add vlues in Token table
                    $this->saveEbizToken((int)$customer->getId(), (int)$getEbizCustomerNumber);

                    // Add Ec values in customer table
                    $this->runUpdateQueryCustomer('customer_entity', 'cust', 2, $liveCustomerInternalId,
                        $liveCustomerId, $getEbizCustomerNumber, $customer->getId());
                    // Update SoftwareID on Econnect
                    $this->ECupdateSingleCustSofwareID($ebizCustomer);
                    $result = "Added";
                } catch (\Exception $ex) {
                    $result = "Not added";
                }
            }

        } catch (\Exception $ex) {
            $result = "Not added";
        }

        return $result;
    }

    public function downloadOrders()
    {
        $counter = 0;
        $_processedCount = 0;
        $_errorCount = 0;
        $addCount = 0;
        $updateCount = 0;
        $_messages = array();
        $itemsObj = array();
        $getCustomerEbiz = '';
        $localItemTypeId = '';

        // Redirect if module is disabled
        if($this->config->isEbizchargeActive() == 0) {
            $_messages[] = array(
                "type" => 'error',
                "message" => "EBizCharge module is inactive. Please activate before download."
            );
            $this->_prepareMessages($_messages);
            exit;
        }

        // Redirect if Download is disabled
        if($this->config->isEconnectDownlaodEnabled() == 0) {
            $_messages[] = array(
                "type" => 'error',
                "message" => "EBizCharge Download functionality is inactive. Please activate before download."
            );
            $this->_prepareMessages($_messages);
            exit;
        }

        // Redirect if shipping method not selected
        if((empty($this->config->isShippingSelected())) ||
            ($this->config->isShippingSelected() == null)) {
            $_messages[] = array(
                "type" => 'error',
                "message" => "Please select default shipping method (Choose Shipping Method) in download options."
            );
            $this->_prepareMessages($_messages);
            exit;
        }

        try {

            $this->processOrderDownload($counter, $addCount, $updateCount, $_processedCount);

        } catch (\Exception $ex) {
            //throw new \Magento\Framework\Exception\LocalizedException(__('downloadOrders' . $ex->getMessage()));
            $_messages[] = array(
                "type" => 'error',
                "message" => " There is an Error during the process, please try again. " . $ex->getMessage()
            );
            $_errorCount++;
        }

        array_unshift($_messages, array(
            "type" => 'notice',
            "message" => sprintf("Orders sync process has been completed. %s record(s) processed.", $counter)
        ));

        if($addCount > 0) {
            $_messages[] = array("type" => 'success', "message" => $addCount . " Order(s) are added successfully.");
        }
        if($updateCount > 0) {
            $_messages[] = array("type" => 'error', "message" => $updateCount . " Order(s) are already exist.");
        }
        if($_errorCount > 0) {
            $_messages[] = array("type" => 'error', "message" => $_errorCount . ' Order(s) not added!');
        }

        $this->_prepareMessages($_messages);
    }

    public function processOrderDownload($counter, &$addCount = 0, &$updateCount = 0, &$_processedCount = 0)
    {
        $regionID = '';
        $orderDataItems = '';
        $regionIDF = '';
        $ecOrdersList = $this->downloadMergedListOrders('', '', '', 'notequal', 1000);
        $timeNow = date("Y-m-d h:i:s");

        if(!empty($ecOrdersList)) {

            foreach ($ecOrdersList as $order) {
                $this->ebizLog()->info("---------------------Econnect Sales Order #" . trim($order->SalesOrderNumber) . "---------------------");

                $ecCustomerIdList = trim($order->CustomerId);
                $ecSalesOrderNumberList = trim($order->SalesOrderNumber);
                $ecSalesOrderInternalIdList = trim($order->SalesOrderInternalId);

                if(empty($ecCustomerIdList) || empty($ecSalesOrderNumberList)) {
                    $this->ebizLog()->info("Invalid Sales Order #" . $order->SalesOrderNumber . " or CustomerId is empty.");
                    continue;
                }

                $getOrders = array(
                    'securityToken' => $this->tranApi->getUeSecurityToken(),
                    'customerId' => $ecCustomerIdList,
                    'salesOrderNumber' => $ecSalesOrderNumberList,
                    'salesOrderInternalId' => $ecSalesOrderInternalIdList
                );

                $getSalesOrder = $this->soapClient->GetSalesOrder($getOrders);
                $order = $getSalesOrder->GetSalesOrderResult;

                $ecCustomerId = trim($order->CustomerId);
                $ecSalesOrderNumber = trim($order->SalesOrderNumber);
                $ecSalesOrderInternalId = trim($order->SalesOrderInternalId);
                $ecPoNum = trim($order->PoNum);

                // Check 1 - if Order has items
                if(empty ($order->Items->Item)) {
                    $itemsObj = array();
                } elseif((is_array($order->Items->Item)) && (count($order->Items->Item)) > 1) {
                    $itemsObj = $order->Items->Item;
                } else {
                    $itemsObj = $order->Items;
                }

                if(!empty($itemsObj)) {

                    if(!empty($order->BillingAddress->FirstName)) {
                        $state = !empty($order->BillingAddress->State) ? $order->BillingAddress->State : 'CA';
                        $country = !empty($order->BillingAddress->Country) ? $order->BillingAddress->Country : 'US';
                        $address1 = !empty($order->BillingAddress->Address1) ? $order->BillingAddress->Address1 : '20, pacifica';
                        $city = !empty($order->BillingAddress->City) ? $order->BillingAddress->City : 'irvine';
                        $zipCode = !empty($order->BillingAddress->ZipCode) ? $order->BillingAddress->ZipCode : '92618';
                        $regionID = $this->getRegionIdFinal($state, $country);

                        if(empty($regionID['region_id'])) {
                            $regionIDF = '12';
                        } else {
                            $regionIDF = $regionID['region_id'];
                        }

                        $billingAddress = array(
                            'firstname' => $order->BillingAddress->FirstName, //address Details
                            'lastname' => $order->BillingAddress->LastName,
                            'street' => $address1,
                            'city' => $city,
                            'country_id' => $country,
                            'region' => $state,
                            'postcode' => $zipCode,
                            'telephone' => '0123456789',
                            'region_id' => $regionIDF,
                            'save_in_address_book' => 0
                        );

                    } else {
                        $billingAddress = array();
                    }

                    $shippingAddress = $this->shippingAddressDetail($order, $billingAddress, $regionIDF);

                    // Ordered items data
                    $orderDataItems = $this->itemDetailDownload($itemsObj, $ecSalesOrderNumber);

                    //----------  Order Object For Customer End ----------------

                    // Check 2 - if Customer is Guest
                    if($ecCustomerId == "Guest") {
                        $mageCustomerId = null;
                        $currency = $order->Currency;
                        //echo $counter." (1) Customer is Guest. New order will be added # ".$EcSalesOrderNumber;
                        $this->ebizLog()->info($counter . ' (1) Customer is Guest for # ' . $ecSalesOrderNumber);

                        $createMageOrderGuest = $this->createGuestOrder($ecSalesOrderInternalId, $ecSalesOrderNumber,
                            $ecPoNum, $orderDataItems, $currency);
                        if($createMageOrderGuest == 'Not Found') {
                            $this->ebizLog()->info("Order #" . $ecSalesOrderNumber . " not saved.");
                            continue;
                        } else {
                            $this->ecUpdateSingleOrderSofwareID($order);
                            $_processedCount++;
                            $addCount++;
                        }

                    } else {
                        // Check 3 - if Mage customer exists
                        $mageCustomer = $this->runSelectQuery('customer_entity', '*', 'ec_cust_id', $ecCustomerId);

                        if($mageCustomer) {
                            $mageCustomerId = $mageCustomer[0]['entity_id'];
                            $mageMapCustomerId = $mageCustomer[0]['ec_cust_id'];
                            $mageCustomerEmail = $mageCustomer[0]['email'];
                        } else {
                            $mageCustomerId = '';
                            $mageMapCustomerId = '';
                            $mageCustomerEmail = '';
                        }

                        if((!empty($mageCustomerId)) || $mageCustomerId != '' || $mageCustomerId != null) {
                            $orderData = [
                                'currency_id' => trim($order->Currency),
                                'email' => $mageCustomerEmail, //buyer email id
                                'billing_address' => $billingAddress,
                                'shipping_address' => $shippingAddress,
                                'items' => $orderDataItems
                            ];

                            $newUpdateParameters = array(
                                'EcOrderSyncStatus' => 2,
                                'EcOrderInternalid' => $ecSalesOrderInternalId,
                                'EcOrderId' => $ecSalesOrderNumber,
                                'EcPoNum' => $ecPoNum,
                                'EcOrderLastsyncdate' => $timeNow,
                                'EcCustId' => $mageCustomerId,
                                'EcMapCustId' => $mageMapCustomerId,
                                'mage_custEmail' => $mageCustomerEmail
                            );

                            // Check 4 - if Order already exists
                            $mageOrder = $this->runSelectQuery('sales_order', '*', 'ec_order_internalid',
                                $ecSalesOrderInternalId);

                            if(!empty($mageOrder)) {
                                //echo "(2) Mage Order ID = ".$EcSalesOrderNumber." exist. CustiD = ".$mage_custID." Need to Update By Internal ID. ";
                                $this->ebizLog()->info("(2) Mage Order ID = " . $ecSalesOrderNumber . " exist. CustiD = " . $mageCustomerId);
                                //$this->createMageOrder($orderData, $EcSalesOrderNumber, $newUpdateParameters);
                                $this->ecUpdateSingleOrderSofwareID($order);
                                $updateCount++;

                            } else {
                                $mageOrder = $this->runSelectQuery('sales_order', '*', 'increment_id',
                                    $ecSalesOrderNumber);
                                if((!empty ($mageOrder)) && ($mageOrder[0]['customer_email'] == $mageCustomerEmail)) {
                                    $orderOfCustomer = $mageOrder[0]['customer_id'];
                                    $this->ebizLog()->info($counter . " (3) Mage Order ID = " . $ecSalesOrderNumber . " by Customer (" . $orderOfCustomer . ") exist. CustiD = " . $mageCustomerId);
                                    $this->ecUpdateSingleOrderSofwareID($order);
                                    $updateCount++;

                                } else {
                                    $this->ebizLog()->info($counter . " (4) We will insert new order #" . $ecSalesOrderNumber . " For Customer local " . $mageCustomerId . " Live " . $mageMapCustomerId);

                                    $createMageOrder = $this->createMageOrder($orderData, $ecSalesOrderNumber,
                                        $newUpdateParameters);

                                    if($createMageOrder == 'Not Found') {
                                        $this->ebizLog()->info("Order #" . $ecSalesOrderNumber . " not saved.");
                                        continue;
                                    } else {
                                        $this->ecUpdateSingleOrderSofwareID($order);
                                        $_processedCount++;
                                        $addCount++;
                                    }

                                }
                            }


                        } else {
                            $this->ebizLog()->info($counter . " (5) Mage Cust against Ebiz Customer = " . $ecCustomerId . " Not exist. We will create new.");

                            $getCustomerEbiz = $this->getCustomerEbiz($ecCustomerId);

                            $orderData = [
                                'currency_id' => trim($order->Currency),
                                'email' => $getCustomerEbiz->Email,
                                'billing_address' => $billingAddress,
                                'shipping_address' => $shippingAddress,
                                'items' => $orderDataItems
                            ];

                            if((!empty($getCustomerEbiz))
                                || ($getCustomerEbiz != null)
                                || ($getCustomerEbiz != '')
                            ) {
                                $customerResult = $this->addSingleCustomerToMagento($ecCustomerId);
                                $this->ebizLog()->info("Customer Status: " . $customerResult);

                                $newUpdateParameters = array(
                                    'EcOrderSyncStatus' => 2,
                                    'EcOrderInternalid' => $ecSalesOrderInternalId,
                                    'EcOrderId' => $ecSalesOrderNumber,
                                    'EcPoNum' => $ecPoNum,
                                    'EcOrderLastsyncdate' => $timeNow,
                                    'EcCustId' => $getCustomerEbiz->CustomerId,
                                    'EcMapCustId' => $getCustomerEbiz->CustomerId,
                                    'mage_custEmail' => $getCustomerEbiz->Email
                                );

                                if($customerResult == "Added") {
                                    $createMageOrderNewCustomer = $this->createMageOrderNewCustomer($orderData,
                                        $ecSalesOrderNumber, $newUpdateParameters);

                                    if($createMageOrderNewCustomer == 'Not Found') {
                                        $this->ebizLog()->info("Order #" . $ecSalesOrderNumber . " not saved.");
                                        continue;
                                    } else {
                                        $this->ecUpdateSingleOrderSofwareID($order);
                                        $_processedCount++;
                                        $addCount++;
                                    }

                                } else {
                                    $this->ebizLog()->info("Unable to add customer.");
                                    $this->ebizLog()->info("Order #" . $ecSalesOrderNumber . " not saved.");
                                    //$this->ECupdateSingleOrderSofwareID($order);
                                    continue;
                                }
                            } else {
                                $this->ebizLog()->info("Unable to save order.");
                                continue;
                            }
                        }
                    }
                }
                $counter++;
                $_processedCount++;
            }

        } else {

            $_messages[] = array("type" => 'error', "message" => "There are no Orders to be download.");
            $this->_prepareMessages($_messages);
            exit;
        }
    }

    /**
     * @param $itemsObject
     * @param $ecSalesOrderNumber
     * @return array
     */
    public function itemDetailDownload($itemsObject, $ecSalesOrderNumber)
    {
        $items = array();
        if(!empty($itemsObject)) {

            foreach ($itemsObject as $line) {
                if((empty($line->ItemId)) || (empty($line->Name))) {
                    $this->ebizLog()->info("This item has null/empty ID.");
                    continue;
                }

                $localItemData = $this->runSelectQueryItem('catalog_product_entity', 'ec_item_id', trim($line->ItemId));

                if((!isset($localItemData[0]['entity_id'])) ||
                    (empty($localItemData[0]['entity_id'])) ||
                    ($localItemData[0]['entity_id'] == null) ||
                    ($localItemData[0]['entity_id'] == '')) {

                    $localItemId = trim($line->ItemId);
                    $this->ebizLog()->info("There are no valid Item(s) found for Order #" . $ecSalesOrderNumber . " Will add new one.");

                    $addItemResult = $this->addSingleItemToMagento(1, $localItemId, '');

                    if($addItemResult) {
                        $localItemData = $this->runSelectQueryItem('catalog_product_entity', 'ec_item_id',
                            trim($line->ItemId));
                    } else {
                        $this->ebizLog()->info("Item  " . $line->Name . " not available , skipped from order.");
                        continue;
                    }
                }

                $localItemId = $localItemData[0]['entity_id'];
                $localItemTypeId = $localItemData[0]['type_id'];

                // Get each Item remaining quantity in Stock
                $productStockObj = $this->stockRegistry->getStockItem($localItemId);

                if(($productStockObj != null) || !empty($productStockObj)) {
                    $productStockObjFinal = $productStockObj->getData('qty');
                } else {
                    $productStockObjFinal = '';
                }

                if(($localItemTypeId == 'configurable') ||
                    ($productStockObjFinal < round($line->Qty)) ||
                    (trim($line->ItemId) == null) ||
                    (trim($line->ItemId) == '')
                ) {
                    $this->ebizLog()->info("In Order #" . $ecSalesOrderNumber . " this item (" . $line->Name . ") is invalid, not saleable or quantity not available.");
                    continue;
                } else {
                    // Ordered single item data
                    $item = array(
                        'ItemId_live' => trim($line->ItemId),
                        'product_id' => $localItemId,
                        'product_type_id' => $localItemTypeId,
                        'price' => trim($line->UnitPrice),
                        'qty' => round($line->Qty),
                        'name' => trim($line->Name),
                        'description' => trim($line->Description)
                    );
                }
                // Ordered multiple items data
                $items[] = $item;
            }

            if(sizeof($items) > 0) {
                return $items;
            }

            return [];
        }

    }

    /**
     * @param $order
     * @param $billingAddress
     * @param $regionID
     * @return array
     */
    public function shippingAddressDetail($order, $billingAddress, $regionID)
    {
        if(!empty($order->ShippingAddress->FirstName)) {
            $state = !empty($order->ShippingAddress->State) ? $order->ShippingAddress->State : 'CA';
            $country = !empty($order->ShippingAddress->Country) ? $order->ShippingAddress->Country : 'US';
            $address1 = !empty($order->ShippingAddress->Address1) ? $order->ShippingAddress->Address1 : '20, pacifica';
            $city = !empty($order->ShippingAddress->City) ? $order->ShippingAddress->City : 'irvine';
            $zipCode = !empty($order->ShippingAddress->ZipCode) ? $order->ShippingAddress->ZipCode : '92618';

            $shippingAddress = array(
                'firstname' => $order->ShippingAddress->FirstName, //address Details
                'lastname' => $order->ShippingAddress->LastName,
                'street' => $address1,
                'city' => $city,
                'country_id' => $country,
                'region' => $state,
                'postcode' => $zipCode,
                'telephone' => '0123456789',
                'region_id' => $regionID,
                'save_in_address_book' => 0
            );
        } else {
            $shippingAddress = $billingAddress;
        }

        return $shippingAddress;
    }

    /**
     * @param $ecSalesOrderInternalId
     * @param $ccSalesOrderNumber
     * @param $ecPoNum
     * @param $orderDataItems
     * @param $currency
     * @return array|string
     */
    public function createGuestOrder($ecSalesOrderInternalId, $ccSalesOrderNumber, $ecPoNum, $orderDataItems, $currency)
    {
        $this->ebizLog()->info(__METHOD__);

        $billingAddressLocal = array(
            'firstname' => 'Guest',
            'lastname' => 'Guest',
            'street' => 'Guest Street',
            'city' => 'Irvine',
            'country_id' => 'US',
            'region' => 'CA',
            'postcode' => '92618',
            'telephone' => '0123456789',
            'region_id' => '12',
            'save_in_address_book' => 0
        );

        $orderDataGuest = [
            'fname' => 'Guest',
            'lname' => 'Guest',
            'currency_id' => trim($currency),
            'email' => 'guest@guest.com',
            'billing_address' => $billingAddressLocal,
            'shipping_address' => $billingAddressLocal,
            'items' => $orderDataItems
        ];

        $newUpdateParametersGuest = array(
            'EcOrderSyncStatus' => 2,
            'EcOrderInternalid' => $ecSalesOrderInternalId,
            'EcOrderId' => $ccSalesOrderNumber,
            'EcPoNum' => $ecPoNum,
            'EcOrderLastsyncdate' => date('Y-m-d h:i:s'),
            'EcCustId' => 'Guest',
            'EcMapCustId' => 'Guest',
            'mage_custEmail' => 'guest@guest.com'
        );

        $createMageOrderGuest = $this->createMageOrderGuest($orderDataGuest, $ccSalesOrderNumber,
            $newUpdateParametersGuest);

        return $createMageOrderGuest;
    }

    /**
     * @param $limit
     * @param $itemId
     * @param $itemInternalId
     * @return bool
     */
    public function addSingleItemToMagento($limit, $itemId, $itemInternalId)
    {
        $counter = 0;
        $addCount = 0;
        $updateCount = 0;
        $ecUpdateCount = 0;
        $_errorCount = 0;
        $_processedCount = 0;
        $_messages = array();
        $localItemId = '';
        $localItemInternalId = '';

        // Get Full List
        //$Items_obj = $this->downloadMergedListItems("Search","Items","Item","eq", 1000, $itemId, $itemInternalId);
        $itemsObj = $this->downloadMergedListItems("Search", "Items", "Item", '', $limit, $itemId, $itemInternalId);

        if((!isset($itemsObj)) || (empty($itemsObj)) || ($itemsObj == null) || ($itemsObj == '')) {

            $this->ebizLog()->info('There is no such Item ' . $itemId . ' exist on Econnect!');
            return false;
        }
        // instance of object manager
        try {
            foreach ($itemsObj as $ecconnectItem) {

                if((empty($ecconnectItem->ItemId)) || (empty($ecconnectItem->Name))) {
                    $this->ebizLog()->info('Invalid item ' . $ecconnectItem->ItemId . ', unable to download. Item name or ID is empty.',
                        '');
                    return false;
                } else {
                    // For Item Add Process
                    $product = $this->productFactory->create();
                    // Live ID's
                    $liveItemID = $ecconnectItem->ItemId;
                    $liveItemInternalId = $ecconnectItem->ItemInternalId;
                    $nextAutoIncrement = $this->getNextAutoincrement('catalog_product_entity');
                    $local_itemID_bysku = $product->getIdBySku($ecconnectItem->SKU);

                    //get root category id
                    $getRootCategoryId = $this->storeManager->getStore()->getRootCategoryId();

                    // Get full Item For Item Update Process
                    //$get_item = $this->productFactory->create()->load($product->getIdBySku($ec_item->SKU));
                    $localEntityId = $this->runDownloadSelectQuery('catalog_product_entity', '*', 'ec_item_id',
                        $liveItemID);

                    $qtyOnHand = intval($ecconnectItem->QtyOnHand);
                    $itemType = $ecconnectItem->ItemType;
                    $itemTypeFinal = $this->getItemTypeMap($itemType);

                    $active = $ecconnectItem->Active;
                    switch ($active) {
                        case "true":
                            $activeFinal = 1;
                            break;
                        case "false":
                            $activeFinal = 2;
                            break;
                        default:
                            $activeFinal = 1;
                    }

                    if(empty($ecconnectItem->SKU)) {
                        $ecItemSku = strtolower($ecconnectItem->Name);
                        $ecconnectItem->SKU = str_replace(" ", "-", $ecItemSku);
                    }

                    if(isset($localEntityId[0]['entity_id'])) {
                        try {
                            $localItemId = $localEntityId[0]['entity_id'];
                            $localItemInternalId = $localEntityId[0]['ec_item_internalid'];
                            // Econnect Update Start
                            if($localItemInternalId == $liveItemInternalId) {
                                // Delete URL if exist
                                $this->ebizLog()->info('Product updating ' . $ecconnectItem->Name . ': LocalID ' . $localItemId . ' = LiveID ' . $ecconnectItem->ItemId);

                                $this->runDeleteUrl('url_rewrite', $localItemId);
                                $this->runDeleteUrlPath('url_rewrite', $ecconnectItem->SKU);
                                // Mage Add Start
                                $product->setEntityId($localItemId); // ID of the product
                                // Get the default attribute set id
                                //$attributeSetId = $product->getDefaultAttributeSetId();
                                $attributeSetId = $localEntityId[0]['attribute_set_id'];
                                $sku = $urlKey = $ecconnectItem->SKU;
                                $product->setSku($sku); // sku of the product
                                $product->setName($ecconnectItem->Name); // name of the product
                                //$product->setUrlKey($urlKey.'-'.$local_itemID); // url key of the product
                                $product->setUrlKey($urlKey); // url key of the product
                                $product->setAttributeSetId($attributeSetId); // attribute set id
                                $product->setStatus($activeFinal); // enabled = 1, disabled = 2
                                // visibilty of product, 1 = Not Visible Individually, 2 = Catalog, 3 = Search, 4 = Catalog, Search
                                $product->setVisibility(4);
                                $product->setTaxClassId(0); // Tax class id, 0 = None, 2 = Taxable Goods, etc.
                                $product->setTypeId($itemTypeFinal); // type of product (simple/virtual/downloadable/configurable)
                                $product->setProductHasWeight(1); // 1 = simple product, 0 = virtual product
                                $product->setWeight(1.000); // weight of product
                                $product->setPrice($ecconnectItem->UnitPrice); // price of the product
                                $product->setWebsiteIds(array(1)); // Assign product to Websites
                                $product->setCategoryIds(array($getRootCategoryId)); // Assign product to categories
                                $product->setCreatedAt($ecconnectItem->DateTimeCreated);
                                $product->setUpdatedAt($ecconnectItem->DateTimeModified);
                                $product->setEcItemSyncStatus(2); // EC Status
                                $product->setEcItemInternalid($ecconnectItem->ItemInternalId); // EC ItemInternalid
                                $product->setEcItemId($ecconnectItem->ItemId); // EC ItemId
                                $product->setEcItemLastsyncdate($ecconnectItem->DateTimeModified); // EC ItemLastsyncdate
                                $product->setStockData(
                                    array(
                                        'use_config_manage_stock' => 0,
                                        'manage_stock' => 1,
                                        'is_in_stock' => 1,
                                        'qty' => $qtyOnHand
                                    )
                                );
                                $product->save();

                                $this->ebizLog()->info('Magento Product Update : LocalID ' . $product->getIdBySku($ecconnectItem->SKU) . " = LiveID " . $ecconnectItem->ItemId);
                                // EC update
                                $this->ECupdateSingleItemSofwareID($liveItemID, $liveItemInternalId);
                                // Update counter
                                //$updateCount ++;
                                return true;
                            }

                        } catch (\Exception $ex) {
                            $this->ebizLog()->info('Item not added! error: ' . $ex->getMessage());
                            return false;
                        }
                    } else {
                        try {
                            // Delete URL if exist
                            $this->ebizLog()->info('Product Adding ' . $ecconnectItem->Name . ': LocalID ' . $nextAutoIncrement . ' = LiveID ' . $ecconnectItem->ItemId);
                            $this->runDeleteUrl('url_rewrite', $nextAutoIncrement);
                            $this->runDeleteUrlPath('url_rewrite', $ecconnectItem->SKU);
                            // Mage Add Start
                            $product->setEntityId($nextAutoIncrement); // ID of the product
                            // Get the default attribute set id
                            $attributeSetId = $product->getDefaultAttributeSetId();
                            $sku = $urlKey = $ecconnectItem->SKU;
                            $product->setSku($sku); // sku of the product
                            $product->setName($ecconnectItem->Name); // name of the product
                            //$product->setUrlKey($urlKey.'-'.$NextAutoincrement); // url key of the product
                            $product->setUrlKey($urlKey); // url key of the product
                            $product->setAttributeSetId($attributeSetId); // attribute set id
                            $product->setStatus($activeFinal); // enabled = 1, disabled = 0
                            // visibilty of product, 1 = Not Visible Individually, 2 = Catalog, 3 = Search, 4 = Catalog, Search
                            $product->setVisibility(4);
                            $product->setTaxClassId(0); // Tax class id, 0 = None, 2 = Taxable Goods, etc.
                            $product->setTypeId($itemTypeFinal); // type of product (simple/virtual/downloadable/configurable)
                            $product->setProductHasWeight(1); // 1 = simple product, 0 = virtual product
                            $product->setWeight(1.000); // weight of product
                            $product->setPrice($ecconnectItem->UnitPrice); // price of the product
                            $product->setWebsiteIds(array(1)); // Assign product to Websites
                            $product->setCategoryIds(array()); // Assign product to categories // $getRootCategoryId
                            $product->setCreatedAt($ecconnectItem->DateTimeCreated);
                            $product->setUpdatedAt($ecconnectItem->DateTimeModified);
                            $product->setEcItemSyncStatus(2); // EC Status
                            $product->setEcItemInternalid($ecconnectItem->ItemInternalId); // EC ItemInternalid
                            $product->setEcItemId($ecconnectItem->ItemId); // EC ItemId
                            $product->setEcItemLastsyncdate($ecconnectItem->DateTimeModified); // EC ItemLastsyncdate
                            $product->setStockData(
                                array(
                                    'use_config_manage_stock' => 0,
                                    'manage_stock' => 1,
                                    'is_in_stock' => 1,
                                    'qty' => $qtyOnHand
                                )
                            );
                            $product->save();

                            $this->ebizLog()->info('Magento Product Add : LocalID ' . $product->getId() . '-' . $nextAutoIncrement . ' = LiveID ' . $ecconnectItem->ItemId);
                            // EC Update
                            $this->ECupdateSingleItemSofwareID($liveItemID, $liveItemInternalId);
                            // Add Counter
                            return true;
                        } catch (\Exception $ex) {
                            $this->ebizLog()->info('Item not updated! error: ' . $ex->getMessage());
                            return false;
                        }
                    }
                }
                $counter++;
            }
        } catch (\Exception $ex) {
            $this->ebizLog()->info('Item ' . $itemId . ' not added! error: ' . $ex->getMessage());
            return false;
        }

    }

    /**
     * @param $ecCustomerId
     * @return mixed
     */
    public function getCustomerEbiz($ecCustomerId)
    {
        // Get full Customer
        $getCustomer = $this->tranApi->getClient()->GetCustomer(
            array(
                'securityToken' => $this->tranApi->getUeSecurityToken(),
                'customerId' => $ecCustomerId
            ));

        return $getCustomer->GetCustomerResult;
    }

    /**
     * Create Order On Your Store for existing customer
     * @param $orderData
     * @param $existingOrderId
     * @param $newUpdateParameters
     * @return array|string
     */
    public function createMageOrder($orderData, $existingOrderId, $newUpdateParameters)
    {
        try {
            // Select 1st active shipping method
            $getShippingMethod = $this->getShippingMethods();
            // Select 1st active payment method
            $getPaymentMethod = $this->getPaymentMethods();

            if(empty($orderData['items']) || count($orderData['items']) == 0) {
                $this->ebizLog()->info("There are no saleable Items for Order #" . $existingOrderId);
                $result = 'Not Found';
                return $result;
            }

            $getTransactionsResultObject = $this->ecGetOrderPaymentsSaved($newUpdateParameters['EcMapCustId'],
                $existingOrderId, $getPaymentMethod);

            if($getTransactionsResultObject == 'Not Found') {
                $result = 'Not Found';
                return $result;
            }

            if($getPaymentMethod == 'ebizcharge_ebizcharge') {
                $paymentObject = [
                    'method' => $getPaymentMethod,
                    'po_number' => $newUpdateParameters['EcPoNum'],
                    'so_number' => $existingOrderId,
                    'mage_cust_id' => $newUpdateParameters['EcCustId'],
                    'additional_data' => $getTransactionsResultObject['additional_data']
                ];
            } else {
                $paymentObject = [
                    'method' => $getPaymentMethod,
                    'po_number' => $newUpdateParameters['EcPoNum'],
                    'so_number' => $existingOrderId,
                    'mage_cust_id' => $newUpdateParameters['EcCustId']
                ];
            }

            $customer = $this->customerFactory->create();
            $customer->setWebsiteId($this->storeManager->getStore()->getWebsiteId());
            $customer->loadByEmail($orderData['email']);// load customet by email address
            // check and add customer address to order
            $billingAddress = $customer->getDefaultBillingAddress();
            if(empty($billingAddress->getStreet())) {
                $getStreet = array(0 => '20, pacifica');
            } else {
                $getStreet = $billingAddress->getStreet();
            }

            if(empty($billingAddress->getRegionId())) {
                $getRegionId = '12';
            } else {
                $getRegionId = $billingAddress->getRegionId();
            }

            //$billingAddressStreet = $billingAddress->getStreet();
            //$regionID = $this->getRegionIdFinal($order->BillingAddress->State, $order->BillingAddress->Country);
            $billingAddressLocal = array(
                'firstname' => $billingAddress->getFirstname(),
                'lastname' => $billingAddress->getLastname(),
                'street' => $getStreet[0],
                'city' => $billingAddress->getCity(),
                'country_id' => $billingAddress->getCountryId(),
                'region' => $billingAddress->getRegion(),
                'postcode' => $billingAddress->getPostcode(),
                'telephone' => $billingAddress->getTelephone(),
                'region_id' => $getRegionId,
                'save_in_address_book' => 0
            );

            if(empty($orderData['billing_address'])) {
                $orderData['billing_address'] = $billingAddressLocal;
            }

            if(empty($orderData['shipping_address'])) {
                $orderData['shipping_address'] = $billingAddressLocal;
            }

            $quote = $this->quoteFactory->create(); //Create object of quote
            $quote->setCurrentStore($this->storeManager->getStore()->getCode()); //set store for which you create quote

            // if you have already buyer id then you can load customer directly
            $customer = $this->customerRepository->getById($customer->getEntityId());
            $quote->setCurrency();
            $quote->assignCustomer($customer); //Assign quote to customer

            //add items in quote
            foreach ($orderData['items'] as $item) {
                $product = $this->productFactory->create()->setStoreId($this->storeManager->getStore()->getId())->load($item['product_id']);
                $product->setPrice($item['price']);
                $quote->addProduct(
                    $product,
                    intval($item['qty'])
                );
            }

            //Set Address to quote
            $quote->getBillingAddress()->addData($orderData['billing_address']);
            $quote->getShippingAddress()->addData($orderData['shipping_address']);
            // Collect Rates and Set Shipping & Payment Method
            $shippingAddress = $quote->getShippingAddress();
            $shippingAddress->setCollectShippingRates(true)
                ->collectShippingRates()
                ->setShippingMethod($getShippingMethod); //shipping method // 'freeshipping_freeshipping'

            $quote->setPaymentMethod($getPaymentMethod); //payment method // 'ebizcharge_ebizcharge'
            $quote->setInventoryProcessed(false); //not effect inventory
            $quote->save(); //Now Save quote and your quote is ready
            // Set Sales Order Payment
            //$quote->getPayment()->importData(['method' => 'checkmo']);
            $quote->getPayment()->importData($paymentObject);
            // Collect Totals & Save Quote
            $quote->collectTotals()->save();
            // Create Order From Quote
            $order = $this->quoteManagement->submit($quote);
            $order->setEmailSent(0);
            // Add order data in update totals
            $getShippingAmount = $paymentObject['additional_data']['Shipping'];
            $order->setShippingAmount($getShippingAmount);
            $order->setBaseShippingAmount($getShippingAmount);
            $order->setShippingInvoiced($getShippingAmount);
            $order->setBaseShippingInvoiced($getShippingAmount);
            $order->setShippingCaptured($getShippingAmount);
            $order->setBaseShippingCaptured($getShippingAmount);
            // Tax object
            $getTaxAmount = $paymentObject['additional_data']['Tax'];
            $order->setTaxAmount($getTaxAmount);
            $order->setBaseTaxAmount($getTaxAmount);
            $order->setTaxInvoiced($getTaxAmount);
            $order->setBaseTaxInvoiced($getTaxAmount);
            // Totals object
            $order->setBaseGrandTotal($order->getBaseGrandTotal() + $order->getBaseShippingAmount() + $order->getBaseTaxAmount());
            $order->setGrandTotal($order->getGrandTotal() + $order->getShippingAmount() + $order->getTaxAmount());
            $order->setBaseTotalPaid($order->getBaseGrandTotal());
            $order->setTotalPaid($order->getGrandTotal());
            $order->setBaseTotalInvoiced($order->getBaseGrandTotal());
            $order->setTotalInvoiced($order->getGrandTotal());
            $order->save();

            if($order->getEntityId()) {
                $result['order_id'] = $order->getRealOrderId();
                $this->ebizLog()->info("New Order # " . $order->getRealOrderId() . " Created in magento.");
                $this->runUpdateQueryOrders('sales_order', 'order', $newUpdateParameters['EcOrderSyncStatus'],
                    $newUpdateParameters['EcOrderInternalid'], $newUpdateParameters['EcOrderId'],
                    $newUpdateParameters['EcMapCustId'], $order->getEntityId());
            } else {
                $result = ['error' => 1, 'msg' => 'Order not saved!'];
                $this->ebizLog()->info("Order not saved!");
            }

            return $result;

        } catch (\Exception $ex) {
            $this->ebizLog()->info('Not Found ' . $ex->getMessage());
            $result = 'Not Found';
            return $result;
        }
    }

    /**
     * Create Order On Your Store with new customer
     * @param $orderData
     * @param $existingOrderID
     * @param $newUpdateParameters
     * @return string
     */
    public function createMageOrderNewCustomer($orderData, $existingOrderID, $newUpdateParameters)
    {
        try {
            // Select 1st active shipping method
            $getShippingMethod = $this->getShippingMethods();
            // Select 1st active payment method
            $getPaymentMethod = $this->getPaymentMethods();

            if((empty($orderData['items'])) ||
                ($orderData['items'] == '') ||
                (count($orderData['items']) == 0)) {

                $this->ebizLog()->info("There are no saleable Items for Order #" . $existingOrderID);
                $result = 'Not Found';
                return $result;
            }

            $getTransactionsResultObject = $this->ecGetOrderPaymentsSaved($newUpdateParameters['EcMapCustId'],
                $existingOrderID, $getPaymentMethod);

            if($getTransactionsResultObject == 'Not Found') {
                $result = 'Not Found';
                return $result;
            }

            if($getPaymentMethod == 'ebizcharge_ebizcharge') {
                $paymentObject = [
                    'method' => $getPaymentMethod,
                    'po_number' => $newUpdateParameters['EcPoNum'],
                    'so_number' => $existingOrderID,
                    'mage_cust_id' => $newUpdateParameters['EcCustId'],
                    'additional_data' => $getTransactionsResultObject['additional_data']
                ];
            } else {
                $paymentObject = [
                    'method' => $getPaymentMethod,
                    'po_number' => $newUpdateParameters['EcPoNum'],
                    'so_number' => $existingOrderID,
                    'mage_cust_id' => $newUpdateParameters['EcCustId']
                ];
            }

            $websiteId = $this->storeManager->getStore()->getWebsiteId();
            $customer = $this->customerFactory->create();
            $customer->setWebsiteId($websiteId);
            $customer->loadByEmail($orderData['email']);// load customet by email address
            // check and add customer address to order

            $billingAddress = $customer->getDefaultBillingAddress();

            if(empty($billingAddress->getStreet())) {
                $getStreet = array(0 => '20, pacifica');
            } else {
                $getStreet = $billingAddress->getStreet();
            }

            if(empty($billingAddress->getRegionId())) {
                $getRegionId = '12';
            } else {
                $getRegionId = $billingAddress->getRegionId();
            }

            //$billingAddressStreet = $billingAddress->getStreet();
            //$regionID = $this->getRegionIdFinal($order->BillingAddress->State, $order->BillingAddress->Country);
            $billingAddressLocal = array(
                'firstname' => $billingAddress->getFirstname(), //address Details
                'lastname' => $billingAddress->getLastname(),
                'street' => $getStreet[0],
                'city' => $billingAddress->getCity(),
                'country_id' => $billingAddress->getCountryId(),
                'region' => $billingAddress->getRegion(),
                'postcode' => $billingAddress->getPostcode(),
                'telephone' => $billingAddress->getTelephone(),
                'region_id' => $getRegionId,
                'save_in_address_book' => 0
            );

            if(empty($orderData['billing_address'])) {
                $orderData['billing_address'] = $billingAddressLocal;
            }

            if(empty($orderData['shipping_address'])) {
                $orderData['shipping_address'] = $billingAddressLocal;
            }

            $quote = $this->quoteFactory->create(); //Create object of quote
            //$quote->setStore($store->getStore()); //set store for which you create quote
            $quote->setCurrentStore($this->storeManager->getStore()->getCode()); //set store for which you create quote
            // if you have allready buyer id then you can load customer directly
            $customer = $this->customerRepository->getById($customer->getEntityId());
            $quote->setCurrency();
            $quote->assignCustomer($customer); //Assign quote to customer

            //add items in quote
            foreach ($orderData['items'] as $item) {
                $product = $this->productFactory->create()->setStoreId($this->storeManager->getStore()->getId())->load($item['product_id']);
                $product->setPrice($item['price']);
                $quote->addProduct(
                    $product,
                    intval($item['qty'])
                );
            }
            //Set Address to quote
            $quote->getBillingAddress()->addData($orderData['billing_address']);
            $quote->getShippingAddress()->addData($orderData['shipping_address']);

            // Collect Rates and Set Shipping & Payment Method
            $shippingAddress = $quote->getShippingAddress();
            $shippingAddress->setCollectShippingRates(true)
                ->collectShippingRates()
                ->setShippingMethod($getShippingMethod); //shipping method // 'freeshipping_freeshipping'

            $quote->setPaymentMethod($getPaymentMethod); //payment method // 'ebizcharge_ebizcharge'
            $quote->setInventoryProcessed(false); //not effect inventory
            $quote->save(); //Now Save quote and your quote is ready
            // Set Sales Order Payment
            //$quote->getPayment()->importData(['method' => 'checkmo']);
            $quote->getPayment()->importData($paymentObject);
            // Collect Totals & Save Quote
            $quote->collectTotals()->save();
            // Create Order From Quote
            $order = $this->quoteManagement->submit($quote);
            $order->setEmailSent(0);
            // Add order data in update totals
            $getShippingAmount = $paymentObject['additional_data']['Shipping'];
            $order->setShippingAmount($getShippingAmount);
            $order->setBaseShippingAmount($getShippingAmount);
            $order->setShippingInvoiced($getShippingAmount);
            $order->setBaseShippingInvoiced($getShippingAmount);
            $order->setShippingCaptured($getShippingAmount);
            $order->setBaseShippingCaptured($getShippingAmount);
            // Tax object
            $getTaxAmount = $paymentObject['additional_data']['Tax'];
            $order->setTaxAmount($getTaxAmount);
            $order->setBaseTaxAmount($getTaxAmount);
            $order->setTaxInvoiced($getTaxAmount);
            $order->setBaseTaxInvoiced($getTaxAmount);
            // Totals object
            $order->setBaseGrandTotal($order->getBaseGrandTotal() + $order->getBaseShippingAmount() + $order->getBaseTaxAmount());
            $order->setGrandTotal($order->getGrandTotal() + $order->getShippingAmount() + $order->getTaxAmount());
            $order->setBaseTotalPaid($order->getBaseGrandTotal());
            $order->setTotalPaid($order->getGrandTotal());
            $order->setBaseTotalInvoiced($order->getBaseGrandTotal());
            $order->setTotalInvoiced($order->getGrandTotal());
            $order->save();

            if($order->getEntityId()) {
                //$result['order_id'] = $order->getRealOrderId();
                $result = $order->getRealOrderId();
                $this->ebizLog()->info("New Order # " . $order->getRealOrderId() . " Created In magento.");
                $this->runUpdateQueryOrders('sales_order', 'order', $newUpdateParameters['EcOrderSyncStatus'],
                    $newUpdateParameters['EcOrderInternalid'], $newUpdateParameters['EcOrderId'],
                    $newUpdateParameters['EcMapCustId'], $order->getEntityId());

            } else {
                $result = 'Not Found';
                $this->ebizLog()->info("Order not saved!");
            }

            return $result;

        } catch (\Exception $ex) {
            $this->ebizLog()->info("Not Found " . $ex->getMessage());
            $result = 'Not Found';
            return $result;
        }
    }

    /**
     * Create Order On Your Store with Guest
     * @param $orderData
     * @param $existingOrderId
     * @param $newUpdateParameters
     * @return array|string
     */
    public function createMageOrderGuest($orderData, $existingOrderId, $newUpdateParameters)
    {
        try {
            // Select 1st active shipping method
            $getShippingMethod = $this->getShippingMethods();
            // Select 1st active payment method
            $getPaymentMethod = $this->getPaymentMethods();

            if((empty($orderData['items'])) ||
                ($orderData['items'] == '') ||
                (count($orderData['items']) == 0)) {

                $this->ebizLog()->info("There are no saleable Items for Order #" . $existingOrderId);
                $result = 'Not Found';
                return $result;
            }

            $getTransactionsResultObject = $this->ecGetOrderPaymentsSaved($newUpdateParameters['EcCustId'],
                $existingOrderId, $getPaymentMethod);

            if($getTransactionsResultObject == 'Not Found') {
                $result = 'Not Found';
                return $result;
            }

            if($getPaymentMethod == 'ebizcharge_ebizcharge') {
                $paymentObject = [
                    'method' => $getPaymentMethod,
                    'po_number' => $newUpdateParameters['EcPoNum'],
                    'so_number' => $existingOrderId,
                    'mage_cust_id' => $newUpdateParameters['EcCustId'],
                    'additional_data' => $getTransactionsResultObject['additional_data']
                ];
            } else {
                $paymentObject = [
                    'method' => $getPaymentMethod,
                    'po_number' => $newUpdateParameters['EcPoNum'],
                    'so_number' => $existingOrderId,
                    'mage_cust_id' => $newUpdateParameters['EcCustId'],
                ];
            }

            $websiteId = $this->storeManager->getStore()->getWebsiteId();

            $BillingAddress_local = array(
                'firstname' => 'Guest',
                'lastname' => 'Guest',
                'street' => 'Guest Street',
                'city' => 'Irvine',
                'country_id' => 'US',
                'region' => 'CA',
                'postcode' => '92618',
                'telephone' => '0123456789',
                'region_id' => '12',
                'save_in_address_book' => 0
            );

            if(empty($orderData['billing_address'])) {
                $orderData['billing_address'] = $BillingAddress_local;
            }

            if(empty($orderData['shipping_address'])) {
                $orderData['shipping_address'] = $BillingAddress_local;
            }

            $quote = $this->quoteFactory->create(); //Create object of quote
            //$quote->setStore($store->getStore()); //set store for which you create quote
            $tempstorecode = $this->storeManager->getStore()->getCode();
            $quote->setCurrentStore($tempstorecode); //set store for which you create quote
            // if you have allready buyer id then you can load customer directly
            //$customer = $this->customerRepository->getById($customer->getEntityId());
            $quote->setCurrency();
            //$quote->assignCustomer(null); //Assign quote to customer
            $quote->setCustomerFirstname($orderData['fname']);
            $quote->setCustomerLastname($orderData['lname']);
            $quote->setCustomerEmail($orderData['email']);
            $quote->setCustomerIsGuest(true);
            //add items in quote
            foreach ($orderData['items'] as $item) {
                $product = $this->productFactory->create()->setStoreId($this->storeManager->getStore()->getId())->load($item['product_id']);
                $product->setPrice($item['price']);
                $quote->addProduct(
                    $product,
                    intval($item['qty'])
                );
            }
            //Set Address to quote
            $quote->getBillingAddress()->addData($orderData['billing_address']);
            $quote->getShippingAddress()->addData($orderData['shipping_address']);
            // Collect Rates and Set Shipping & Payment Method
            $shippingAddress = $quote->getShippingAddress();
            $shippingAddress->setCollectShippingRates(true)
                ->collectShippingRates()
                ->setShippingMethod($getShippingMethod);
            //->setShippingMethod('freeshipping_freeshipping'); //shipping method
            //$quote->setPaymentMethod('ebizcharge_ebizcharge'); //payment method
            $quote->setPaymentMethod($getPaymentMethod); //payment method
            $quote->setInventoryProcessed(false); //not effect inventory
            $quote->save(); //Now Save quote and your quote is ready
            // Set Sales Order Payment
            //$quote->getPayment()->importData(['method' => 'ebizcharge_ebizcharge']);
            //$quote->getPayment()->importData(['method' => 'checkmo']);
            $quote->getPayment()->importData($paymentObject);
            // Collect Totals & Save Quote
            $quote->collectTotals()->save();
            // Create Order From Quote
            $order = $this->quoteManagement->submit($quote);
            $order->setEmailSent(0);
            // Add order data in update totals
            $getShippingAmount = $paymentObject['additional_data']['Shipping'];
            $order->setShippingAmount($getShippingAmount);
            $order->setBaseShippingAmount($getShippingAmount);
            $order->setShippingInvoiced($getShippingAmount);
            $order->setBaseShippingInvoiced($getShippingAmount);
            $order->setShippingCaptured($getShippingAmount);
            $order->setBaseShippingCaptured($getShippingAmount);
            // Tax object
            $getTaxAmount = $paymentObject['additional_data']['Tax'];
            $order->setTaxAmount($getTaxAmount);
            $order->setBaseTaxAmount($getTaxAmount);
            $order->setTaxInvoiced($getTaxAmount);
            $order->setBaseTaxInvoiced($getTaxAmount);
            // Totals object
            $order->setBaseGrandTotal($order->getBaseGrandTotal() + $order->getBaseShippingAmount() + $order->getBaseTaxAmount());
            $order->setGrandTotal($order->getGrandTotal() + $order->getShippingAmount() + $order->getTaxAmount());
            $order->setBaseTotalPaid($order->getBaseGrandTotal());
            $order->setTotalPaid($order->getGrandTotal());
            $order->setBaseTotalInvoiced($order->getBaseGrandTotal());
            $order->setTotalInvoiced($order->getGrandTotal());
            $order->save();

            if($order->getEntityId()) {
                $result['order_id'] = $order->getRealOrderId();
                $this->ebizLog()->info("New Order # " . $order->getRealOrderId() . " Created In magento.");
                //$this->runUpdateQueryOrders('sales_order', 'order', $newUpdateParameters['EcOrderSyncStatus'], $newUpdateParameters['EcOrderInternalid'], $newUpdateParameters['EcOrderId'], $newUpdateParameters['EcMapCustId'], $order->getEntityId());
            } else {
                $result = ['error' => 1, 'msg' => 'Order not saved!'];
                $this->ebizLog()->info("Order not saved!");
            }
            //return $result;
        } catch (\Exception $ex) {
            $this->ebizLog()->info("Not Found " . $ex->getMessage());
            $result = 'Not Found';
            //return $result;
        }
        return $result;
    }

    /**
     * return Econnect customer id if found in mapping else return local magento customer id
     * @param $magCustomerId
     * @return mixed
     */
    public function getEconnectMappedCustomerId($magCustomerId)
    {
        return $this->tranApi->getMappedCustomerId($magCustomerId);
    }

    /**
     * @param null $status
     * @return mixed
     */
    public function getRecurringItemsListDb($status = null)
    {
        $this->ebizCronLog()->info(__METHOD__);
        $selectQueryParameters = array(
            'option' => 'select',
            'tableName' => 'ebizcharge_recurring',
            'tableFields' => '*',
            'whereKey' => 'rec_status',
            'whereValue' => $status
        );

        return $this->tranApi->runSelectQuery($selectQueryParameters);
    }

    public function loadMagentoItem($itemId)
    {
        $this->ebizCronLog()->info(__METHOD__);
        $item = $this->productFactory->create()->load($itemId);

        // Get each Item remaining quantity in Stock
        $productStockObj = $this->stockRegistry->getStockItem($itemId);

        if(($productStockObj != null) || !empty($productStockObj)) {
            $productQuantity = $productStockObj->getData('qty');
            if($productStockObj->getData('is_in_stock') == 0) {
                $productQuantity = 0;
            }
        } else {
            $productQuantity = 0;
        }

        $itemPrice = (!empty($item->getData('special_price')))
            ? $item->getData('special_price')
            : $item->getData('price');

        $itemDetails = [];
        if(($item->getData('type_id') != 'configurable')) {
            $itemDetails = array(
                'itemId' => $item->getData('entity_id'),
                'itemPrice' => $itemPrice,
                'itemType' => $item->getData('type_id'),
                'QtyOnHand' => (int)$productQuantity
            );
        }

        return $itemDetails;
    }

    /** Main check item stock functions end **/
    /**
     * @param $tableName
     * @param $colums
     * @param $whereKey
     * @param $whereValue
     * @return mixed
     */
    public function getMagentoDbData($tableName, $colums, $whereKey, $whereValue)
    {
        $this->ebizCronLog()->info(__METHOD__);

        $selectQueryParameters = array(
            'option' => 'select',
            'tableName' => $tableName,
            'tableFields' => $colums,
            'whereKey' => $whereKey,
            'whereValue' => $whereValue
        );
        $dbData = $this->tranApi->runSelectQuery($selectQueryParameters);

        return isset($dbData[0]) ? $dbData[0] : [];
    }

    /** Main create orders functions end **/

    /** Main items stock functions start **/
    public function checkItemsStock()
    {
        $recurringItems = $this->getRecurringItemsListDb(0);

        foreach ($recurringItems as $listItem) {
            $item = $this->loadMagentoItem($listItem['mage_item_id']);
            $custId = $this->getEconnectMappedCustomerId($listItem['mage_cust_id']);

            if($listItem['rec_status'] == 0) {
                $itemData = [
                    'product_id' => (int)$listItem['mage_item_id'],
                    'product_name' => $listItem['mage_item_name'],
                    'qty_ordered' => (int)$listItem['qty_ordered'],
                    'price' => (float)$item['itemPrice'],
                    'qtyOnHand' => (int)$item['QtyOnHand'],
                    'itemType' => $item['itemType'],
                    'excludeAmount' => ($listItem['qty_ordered'] * $item['itemPrice']),
                    'custId' => $custId,
                    //'customerEmail' => $customerData[0]['email'],
                    //'customerEmail' => !empty($customerData[0]['email']) ? $customerData[0]['email'] : '',
                    'storeAdminEmails' => $this->getStoreAdminEmails()
                ];

                if(in_array($item['itemType'], ['downloadable', 'virtual'])) {
                    $this->ebizCronLog()->info((int)$listItem['mage_item_id'] . ' - Downloadable item, Qty not required!');

                } else {
                    if($listItem['qty_ordered'] > $item['QtyOnHand']) {
                        $this->ebizCronLog()->info((int)$listItem['mage_item_id'] . ' - Physical item, Qty not available. Send notification!');
                        // //0 Active //1 Suspended //2 Expired //3 Canceled
                        // not be put on Suspension
                        //Because We would have to track Subscriptions to re-enable them when the product comes back in stock.
                        //$this->tranApi->suspendScheduledRecurringPaymentStatus($listItem, 1);
                        // Send email to customer and Cc to admin
                        $this->sendLowStockMail($itemData);
                    }
                }
            }
        }
    }

    public function sendLowStockMail($itemData)
    {
        $this->ebizCronLog()->info(__METHOD__);
        //$toCustomer = $itemData['customerEmail'];
        //$toAdmingName = $itemData['storeAdminEmails']['generalName'];
        $toAdmingEmail = $itemData['storeAdminEmails']['generalEmail'];
        //$toAdminsName = $itemData['storeAdminEmails']['salesName'];
        $toAdminsEmail = $itemData['storeAdminEmails']['salesEmail'];
        $subject = "Item Low Stock Notification";

        /*        $messageCust = '
		<html>
		<head>
		<title>Item Low Stock Notification</title>
		</head>
		<body>
		<p>This is system generated email for item low stock!</p>
		<table>
			<tr>
			   <td>
				   <div class="greeting">Hi Admin,</div>
				   <div>
					   We need to inform you that below items marked for subscription are out of stock. Please contact your store support.
					   <p class="greeting">' . $itemData["product_name"] . '</p>
				   </div>
			   </td>
		   </tr>
		</table>
		</body>
		</html>';*/

        $messageAdmin = '
		<html>
		<head>
		<title>Item Low Stock Notification</title>
		</head>
		<body>
		<p>This is system generated email for item low stock!</p>
		<table>
			<tr>
			   <td>
				   <div class="greeting">Dear store admin,</div>
				   <div>
					   We need to inform you that below items marked for subscription are out of stock. Please check item stock.
					   <p class="greeting">[' . $itemData["product_id"] . '] ' . $itemData["product_name"] . '</p>
				   </div>
			   </td>
		   </tr>
		</table>
		</body>
		</html>
		';

        // Always set content-type when sending HTML email
        $headers = "MIME-Version: 1.0" . "\r\n";
        $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";

        // More headers
        $headers .= 'From: <' . $toAdmingEmail . '>' . "\r\n";
        $headers .= 'Cc: ' . $toAdmingEmail . "\r\n";
        //$headers .= 'Bcc: '.$toAdmingEmail . "\r\n";

        $mail = mail($toAdminsEmail, $subject, $messageAdmin, $headers);

        if($mail == true) {
            $this->ebizCronLog()->info('Email sent!');
        } else {
            $this->ebizCronLog()->info('Email not sent!');
        }

    }
    /** Main items stock functions end **/

    /**
     * @param $customerId
     * @param string $selectedAddress
     * @return string
     */
    public function getCustomerAddressList($customerId, $selectedAddress = '')
    {
        $addresses = "<option value=''>No customer address found</option>";

        if(!empty($customerId)) {
            try {
                $selectQueryParameters = array(
                    'option' => 'select',
                    'tableName' => 'customer_address_entity',
                    'tableFields' => '*',
                    'whereKey' => 'parent_id',
                    'whereValue' => $customerId
                );

                $data = "";
                $customerData = $this->tranApi->runSelectQuery($selectQueryParameters);

                foreach ($customerData as $customer) {
                    $regionName = 'N/A';
                    if($region = $this->getRegionById($customer['region_id'])) {
                        $regionName = isset($region['name']) ? $region['name'] : $regionName;
                    }

                    $address = join(' - ', array_filter(array(
                            $customer['firstname'],
                            $customer['lastname'],
                            $customer['street'],
                            $customer['postcode'],
                            $regionName,
                            $customer['country_id']
                        )
                    ));

                    $addressId = $customer['entity_id'];
                    $selected = ($addressId == $selectedAddress) ? 'selected' : '';
                    $data .= "<option value='" . $addressId . "' $selected >" . $address . "</option>";
                }

                return $data;

            } catch (\Exception $ex) {
                $this->ebizLog()->info(__METHOD__ . $ex->getMessage());
            }
        } else {
            $addresses = "<option value=''>Invalid Customer ID</option>";
        }

        return $addresses;
    }

    public function getRegionById($regionId)
    {
        if(!empty($regionId)) {
            $region = $this->regionFactory->create()->load($regionId);
            return $region->getData();
        }
        return null;
    }

    /** ============= SecurityID validation checks start =============== **/

    public function validateApiKey()
    {
        $this->ebizLog()->info(__METHOD__);
        $_messages = array();

        try {
            $customer = $this->soapClient->GetCustomer(
                [
                    'securityToken' => $this->tranApi->getUeSecurityToken(),
                    'customerId' => '1'
                ]
            );

            if(isset($customer->GetCustomerResult)) {
                $this->ebizLog()->info('EbizCharge SecurityID is valid.');
            }

        } catch (\Exception $ex) {

            if($ex->getMessage() == "Invalid Credentials ") {
                $this->ebizLog()->info("EbizCharge SecurityID is incorrect ( " . $ex->getMessage() . ")");

                $this->updateConfig("active", 0);
                $this->flushCache();

                $_messages[] = array(
                    "type" => 'error',
                    "message" => "EbizCharge SecurityID is incorrect ( " . $ex->getMessage() . ")"
                );
            }

            if($ex->getMessage() == "Not Found") {
                $this->ebizLog()->info("EbizCharge SecurityID is valid but Customer (1) (" . $ex->getMessage() . " )");
            }
        }

        array_unshift($_messages, array("type" => 'notice', "message" => "EbizCharge configuration updated."));

        if($this->isBackend()) {
            $this->_prepareMessages($_messages);
        }

    }

    /**
     * Update core_config_date
     *
     * @param $column
     * @param $value
     * @return bool
     */
    public function updateConfig($column, $value): bool
    {
        $configDataToUpdate = [
            'section' => 'payment',
            'website' => ScopeInterface::SCOPE_WEBSITES,
            'store' => ScopeInterface::SCOPE_STORES,
            'groups' => [
                'ebizcharge_ebizcharge' => [
                    'fields' => [
                        $column => ['value' => $value]
                    ]
                ]
            ]
        ];
        try {
            $configModel = $this->coreConfigData->create(['data' => $configDataToUpdate]);
            $configModel->save();
            return true;
        } catch (\Exception $e) {
            $this->ebizLog()->info($e->getMessage());
            return false;
        }
    }

    public function flushCache()
    {
        $_types = ['config'];

        foreach ($_types as $type) {
            $this->cacheTypeList->cleanType($type);
        }

        foreach ($this->cacheFrontendPool as $cacheFrontend) {
            $cacheFrontend->getBackend()->clean();
        }
    }

    /**
     * Save new customer token to `ebizcharge_token` table
     *
     * @param int $customerId
     * @param int $ebizCustomerNumber
     * @return \Ebizcharge\Ebizcharge\Api\Data\TokenInterface|false|null
     */
    public function saveEbizToken(int $customerId, int $ebizCustomerNumber)
    {
        $tokenModel = $this->tokenInterfaceFactory->create();
        $tokenModel->setMageCustId($customerId);
        $tokenModel->setEbzcCustId($ebizCustomerNumber);
        return $this->tokenRepository->save($tokenModel);
    }

    /** ============= SecurityID validation checks end =============== **/

// Class end
}
