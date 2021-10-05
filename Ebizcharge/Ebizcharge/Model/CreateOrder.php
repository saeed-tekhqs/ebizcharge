<?php
/**
 * @author Century Business Solutions <support@centurybizsolutions.com>
 * @copyriht Copyright (c) 2021 Century Business Solutions  (www.centurybizsolutions.com)
 * Created by PhpStorm
 * User: Mobeen
 * Date: 6/30/21
 * Time: 5:15 PM
 */
declare(strict_types=1);

namespace Ebizcharge\Ebizcharge\Model;

use Ebizcharge\Ebizcharge\Api\Data\FutureSubscriptionInterface;
use Ebizcharge\Ebizcharge\Api\Data\OrderSubscriptionInterface;
use Ebizcharge\Ebizcharge\Api\Data\RecurringInterface;
use Ebizcharge\Ebizcharge\Api\RecurringRepositoryInterface as RecurringRepository;
use Ebizcharge\Ebizcharge\Model\ResourceModel\FutureSubscription\CollectionFactory as FutureCollection;
use Ebizcharge\Ebizcharge\Model\ResourceModel\OrderSubscription\CollectionFactory as OrderSubscriptionCollection;
use Ebizcharge\Ebizcharge\Model\ResourceModel\Recurring\CollectionFactory as RecurringCollection;
use Magento\Catalog\Model\Product;
use Magento\Customer\Api\AddressRepositoryInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Quote\Model\QuoteFactory;
use Magento\Quote\Model\QuoteManagement;
use Magento\Customer\Api\Data\AddressInterface;
use Magento\Framework\Message\ManagerInterface;
use Magento\Sales\Model\Order;
use Magento\Store\Model\StoreManagerInterface;
use Magento\SalesSequence\Model\Manager as SequenceManager;
use Psr\Log\LoggerInterface;

/**
 * Create orders
 *
 * Class CreateOrder
 */
class CreateOrder
{
    /**
     * @var RecurringRepository
     */
    private $recurringRepository;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var OrderSubscriptionFactory
     */
    private $orderSubscriptionFactory;

    /**
     * @var TranApi
     */
    private $tranApi;

    /**
     * @var Data
     */
    private $dataClass;

    /**
     * @var RecurringCollection
     */
    private $recurringCollection;

    /**
     * @var FutureCollection
     */
    private $futureCollection;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var ResourceModel\Recurring\Collection|null
     */
    private $suspendRecurrings;

    /**
     * @var ResourceModel\Recurring\Collection|null
     */
    private $failedAttempts;

    /**
     * @var OrderSubscriptionCollection
     */
    private $orderSubscriptionCollection;

    /**
     * @var ResourceModel\OrderSubscription\Collection|null
     */
    private $recurringOrders;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var AddressRepositoryInterface
     */
    private $addressRepository;

    /**
     * @var CustomerRepositoryInterface
     */
    private $customerRepository;

    /**
     * @var QuoteFactory
     */
    private $quote;

    /**
     * @var QuoteManagement
     */
    private $quoteManagement;

    /**
     * @var Product
     */
    private $product;

    /**
     * @var Order
     */
    private $order;

    /**
     * @var string|null
     */
    private $paymentInternalId;

    /**
     * @var ManagerInterface
     */
    private $messageManager;

    /**
     * @var array $orders
     */
    private $orders = [];

    /**
     * @var array $failedOrders
     */
    private $failedOrders = [];

    /**
     * @var SequenceManager
     */
    private $sequenceManager;


    /**
     * @param AddressRepositoryInterface $addressRepository
     * @param CustomerRepositoryInterface $customerRepository
     * @param Data $dataClass
     * @param FutureCollection $futureCollection
     * @param Order $order
     * @param OrderSubscriptionCollection $orderSubscriptionCollection
     * @param OrderSubscriptionFactory $orderSubscriptionFactory
     * @param Product $product
     * @param QuoteFactory $quote
     * @param QuoteManagement $quoteManagement
     * @param RecurringCollection $recurringCollection
     * @param RecurringRepository $recurringRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param StoreManagerInterface $storeManager
     * @param TranApi $tranApi
     * @param ManagerInterface $messageManager
     * @param LoggerInterface $logger
     * @param SequenceManager $sequenceManager
     */
    public function __construct(
        AddressRepositoryInterface $addressRepository,
        CustomerRepositoryInterface $customerRepository,
        Data $dataClass,
        FutureCollection $futureCollection,
        Order $order,
        OrderSubscriptionCollection $orderSubscriptionCollection,
        OrderSubscriptionFactory $orderSubscriptionFactory,
        Product $product,
        QuoteFactory $quote,
        QuoteManagement $quoteManagement,
        RecurringCollection $recurringCollection,
        RecurringRepository $recurringRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        StoreManagerInterface $storeManager,
        TranApi $tranApi,
        ManagerInterface $messageManager,
        LoggerInterface $logger,
        SequenceManager $sequenceManager
    )
    {
        $this->addressRepository = $addressRepository;
        $this->customerRepository = $customerRepository;
        $this->dataClass = $dataClass;
        $this->futureCollection = $futureCollection;
        $this->order = $order;
        $this->orderSubscriptionCollection = $orderSubscriptionCollection;
        $this->orderSubscriptionFactory = $orderSubscriptionFactory;
        $this->product = $product;
        $this->quote = $quote;
        $this->quoteManagement = $quoteManagement;
        $this->recurringCollection = $recurringCollection;
        $this->recurringRepository = $recurringRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->storeManager = $storeManager;
        $this->tranApi = $tranApi;
        $this->messageManager = $messageManager;
        $this->logger = $logger;
        $this->sequenceManager = $sequenceManager;
        $this->suspendRecurrings = null;
        $this->recurringOrders = null;
    }


    /**
     * @param \DateTime $startDate
     * @param bool $isCron
     * @return bool
     */
    public function checkRecurringOrders(\DateTime $startDate, bool $isCron = false)
    {
        $orderToCreate = $this->getOrderToCreate($startDate);
        $this->processOrders($orderToCreate);
        return $this->createSuccessMessage($isCron);
    }

    /**
     * @param \DateTime $startDate
     * @return mixed
     */
    public function getOrderToCreate(\DateTime $startDate)
    {
        $collection = $this->futureCollection->create();
        $collection->getSelect()
            ->joinInner(
                ['ebizcharge_recurring'],
                'main_table.recurring_id = ebizcharge_recurring.rec_id',
                ['*']
            )->joinLeft(
                ['ebizcharge_recurring_order'],
                'main_table.recurring_id = ebizcharge_recurring_order.recurring_id AND date(main_table.recurring_date) = date(ebizcharge_recurring_order.order_date) AND status = 1',
                ['rec_order_id', 'status', 'order_date']
            );
        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter(
                FutureSubscriptionInterface::RECURRING_DATE,
                [
                    'from' => $startDate->format('Y-m-d'),
                    'to' => date('Y-m-d')
                ]
            )
            ->addFilter(RecurringInterface::REC_STATUS,
                (int)'0'
            )
            ->addFilter(OrderSubscriptionInterface::REC_ORDER_ID,
                ['null' => true]
            );
        return AbstractRepository::searchList($searchCriteria->create(), $collection);
    }

    public function processOrders($orderToCreate)
    {
        foreach ($orderToCreate->getItems() as $recurring) {
            $recurringDate = $recurring->getData('recurring_date');
            try {
                if ($recurring->getData('eb_rec_remaining') == 0) {
                    $this->tranApi->cronlog('All recurring are completed for ' . $recurring->getData('mage_item_name') . '. Status is marked as suspended on gateway.');
                    // Suspend subscription on Econnect //0 Active //1 Suspended //2 Expired //3 Canceled
                    $result = $this->tranApi->suspendScheduledRecurringPaymentStatus($recurring, 1);
                    $this->processSuspendRecurring($recurring->getData('rec_id'));
                    continue;
                }
                $magCustomerId = $recurring->getData('mage_cust_id');

                $this->paymentInternalId = $this->tranApi->searchRecurringPayment(
                    $magCustomerId, $recurring->getData('eb_rec_scheduled_payment_internal_id'), $recurringDate
                );

                if (empty($this->paymentInternalId)) {
                    $msg = 'Payment is not paid or failed.';
                    $this->tranApi->cronlog($msg);
                    $this->insertInRecurringOrder($recurring, 0, $msg);
                    continue;
                }

                $customerData = $this->tranApi->getMagentoCustomer($magCustomerId, '*');

                if (empty($customerData)) {
                    $msg = 'Error in loading customer ID(' . $magCustomerId . ')  against order (' . $recurring->getData('mage_order_id') . ')';
                    $this->tranApi->cronlog($msg);
                    $this->insertInRecurringOrder($recurring, 0, $msg);
                    continue;
                } else {
                    $customerData = $customerData[0];
                }

                $savedShippingMethod = $recurring->getData('shipping_method');

                // load existing order info
                if (!empty($recurring->getData('mage_order_id'))) {
                    $savedOrderDetails = $this->loadMagentoOrderPaymentData($recurring->getData('mage_order_id'));
                    $savedOrderData = $savedOrderDetails['orderData'] ?? '';
                    $savedOrderPaymentData = $savedOrderDetails['orderPaymentData'] ?? '';

                    if (empty($savedOrderData) || empty($savedOrderPaymentData)) {
                        $msg = 'Error in loading parent saved Order (' . $recurring->getData('mage_order_id') . ')';
                        $this->tranApi->cronlog($msg);
                        $this->insertInRecurringOrder($recurring, 0, $msg);
                        continue;
                    }

                    if (empty($savedShippingMethod)) {
                        $savedShippingMethod = $savedOrderData['shipping_method'];
                    }

                    $orderAddress = $this->getExistingOrderAddress($savedOrderData['entity_id']);
                    $savedBillingAddress = $orderAddress['billingAddress'];
                    $savedShippingAddress = $orderAddress['shippingAddress'];

                } else { // load admin subscription info
                    $savedBillingAddress = $this->getRecurringBillingAddress((int)$recurring->getData('billing_address_id'));
                    $savedShippingAddress = $this->getRecurringShippingAddress((int)$recurring->getData('shipping_address_id'));
                    $savedOrderPaymentData = [];
                }

                if (empty($savedShippingAddress)) {
                    $msg = 'Shipping address is empty or invalid.';
                    $this->tranApi->cronlog($msg);
                    $this->insertInRecurringOrder($recurring, 0, $msg);
                    // Suspend subscription on Econnect //0 Active //1 Suspended //2 Expired //3 Canceled
                    //$this->_tran->suspendScheduledRecurringPaymentStatus($recurring, 1);
                    continue;
                }

                $item = $this->dataClass->loadMagentoItem($recurring->getData('mage_item_id'));

                if (empty($item)) {
                    $msg = 'Product not found or has configurable type.';
                    $this->tranApi->cronlog($msg);
                    $this->insertInRecurringOrder($recurring, 0, $msg);
                    continue;
                }

                if ((int)$item['QtyOnHand'] < (int)$recurring->getData('qty_ordered')) {
                    $msg = "Product #" . $recurring->getData('mage_item_id') . ' is out of stock.';
                    $this->insertInRecurringOrder($recurring, 0, $msg);
                    $this->tranApi->cronlog($msg);
                    continue;
                }

                $shipmentMethod = $this->getShipmentMethod($item['itemType'], $savedShippingMethod);
                if (empty($shipmentMethod)) {
                    $this->tranApi->cronlog('Shipping method is empty and order cannot be created.');
                    $this->insertInRecurringOrder($recurring, 0, 'Shipping method is empty');
                    continue;
                }

                $excludeAmount = ($recurring->getData('qty_ordered') * $item['itemPrice']);

                if (!empty($recurring->getData('amount'))) {
                    $excludeAmountDb = $recurring->getData('amount');
                    if ($excludeAmount != $excludeAmountDb) {
                        $excludeAmount = $excludeAmountDb;
                    }
                }

                $orderData = [
                    'items' => [
                        'item' => [
                            'product_id' => $recurring->getData('mage_item_id'),
                            'qty' => $recurring->getData('qty_ordered'),
                            'price' => $item['itemPrice'],
                            'qtyOnHand' => $item['QtyOnHand'],
                            'itemType' => $item['itemType'],
                            'excludeAmount' => $excludeAmount
                        ]
                    ],
                    'excludeAmount' => $excludeAmount,
                    'custId' => $recurring->getData('mage_cust_id'),
                    'currency_id' => $this->storeManager->getStore()->getCurrentCurrencyCode(),
                    'email' => $customerData['email'],
                    'billing_address' => $savedBillingAddress,
                    'shipping_address' => $savedShippingAddress,
                    'discountCoupon' => $savedOrderData['coupon_code'] ?? ''
                ];

                $dateToday = date("Y-m-d");
                $recurringDate = date("Y-m-d", strtotime($recurring->getData('recurring_date')));

                $this->tranApi->cronlog('Today = ' . $dateToday . ', Recurring due date = ' . $recurringDate);

                if (strtotime($recurringDate) <= strtotime($dateToday)) {

                    if ((int)$item['QtyOnHand'] > (int)$recurring->getData('qty_ordered') || in_array($item['itemType'],
                            ['downloadable', 'virtual'])) {
                        $this->createMageOrderRecurring($recurring, $orderData, $savedOrderPaymentData, $customerData,
                            $shipmentMethod);
                    }

                } else {
                    $this->tranApi->cronlog('No recurring order is due today for recurring product #' . $recurring->getData('mage_item_id'));
                }

            } catch (\Exception $ex) {
                $msg = 'Exception: ' . $ex->getMessage();
                $this->tranApi->cronlog($msg);
                $this->insertInRecurringOrder($recurring, 0, $msg);
                //return $this->createErrorResponse('Failed: ' . $e->getMessage());
            }

        }
        try {
            $this->saveRecurrings();
            $this->saveRecurringOrders();
        } catch (\Exception $e) {
            $this->logger->critical($e->getMessage());
            return false;
        }
        return true;
    }

    public function processSuspendRecurring($recId = false)
    {
        if ($recId) {
            $this->suspendRecurrings = $this->suspendRecurrings ?? $this->recurringCollection->create();
            return $this->suspendRecurrings->getItemById((int)$recId)->setData(RecurringInterface::REC_STATUS, 1);
        } elseif ($this->suspendRecurrings !== null) {
            return $this->suspendRecurrings->save();
        }
    }

    /**
     * @param $recurring
     * @param $status
     * @param $message
     * @param null $orderId
     * @param null $entityId
     * @return bool
     */
    private function insertInRecurringOrder($recurring, $status, $message, $orderId = null, $entityId = null)
    {
//        @todo 'commented due to Error in bulk saving records: Item (Ebizcharge\Ebizcharge\Model\OrderSubscription) with the same ID "1" already exists'

//        if($this->recurringOrders === null) {
//            $this->recurringOrders = $this->orderSubscriptionCollection->create();
//            $this->recurringOrders = $this->recurringOrders->removeAllItems();
//        }
       return $dataToSave = $this->setDataToCollection($recurring, $status, $message, $orderId, $entityId);
//        try {
//            $this->recurringOrders->addItem($dataToSave);
//            if($status == 1) {
//                $this->orders[] = $orderId;
//            } else {
//                $this->failedOrders[] = 0;
//                $this->processFailedAttempts($recurring);
//            }
//        } catch (\Exception $e) {
//            $this->logger->critical($e->getMessage());
//            $this->logger->alert('function name is: ' . __FUNCTION__ . ': Log by Create order cron: Exception3:' . $e->getMessage());
//        }
//        return true;
    }

    /**
     * @param $recurring
     * @param $status
     * @param $message
     * @param null $orderId
     * @param null $entityId
     * @return OrderSubscription
     */
    private function setDataToCollection($recurring, $status, $message, $orderId = null, $entityId = null)
    {
        $orderDate = $recurring->getData('recurring_date') ?? date('Y-m-d H:i:s');
        $orderId = $orderId == null ? $orderId : (int)$orderId;
        $entityId = $entityId == null ? $entityId : (int)$entityId;
        try {
            $dataObject = $this->orderSubscriptionFactory->create()
                ->setData(
                    [
                        'id' => null,
                        'recurring_id' => (int)$recurring->getData('rec_id'),
                        'rec_order_id' => $orderId,
                        'created_date' => date('Y-m-d H:i:s'),
                        'order_date' => date('Y-m-d', strtotime($orderDate)),
                        'status' => $status,
                        'message' => $message,
                        'order_entity_id' => $entityId
                    ]
                );

            $dataObject->save();
            return true;
        } catch (\Exception $e) {
            $this->logger->alert('function name is: ' . __FUNCTION__ . ': Log by Create order cron: Exception6:' . $e->getMessage());
            return false;
        }
//        @todo 'commented due to Error in bulk saving records: Item (Ebizcharge\Ebizcharge\Model\OrderSubscription) with the same ID "1" already exists'
//        $dataObject->isObjectNew(true);
//        return $dataObject;
    }

    public function processFailedAttempts($recurring = false)
    {
        $recId = $recurring ? (int)$recurring->getData('rec_id') : false;
        if ($recId) {
            $failedCount = (int)$recurring->getData('failed_attempts') + 1;
            $this->failedAttempts = $this->suspendRecurrings !== null ? $this->suspendRecurrings : $this->recurringCollection->create();
            if ($failedCount > 2) {
                $this->tranApi->cronlog('This is the 3rd failed attempt of recurring record# ' . $recurring->getData('rec_id') . ' Suspending subscription.');
                $result = $this->tranApi->suspendScheduledRecurringPaymentStatus($recurring, 1);
            }
            return $this->failedAttempts->getItemById($recId)->setData(RecurringInterface::FAILED_ATTEMPTS,
                $failedCount);
        } elseif ($this->failedAttempts !== null) {
            return $this->failedAttempts->save();
        }
    }

    /**
     * @param $orderIncrementId
     * @return array
     */
    public function loadMagentoOrderPaymentData($orderIncrementId)
    {
        $this->tranApi->cronlog(__METHOD__);
        $orderFullData = [];
        $order = $this->dataClass->getMagentoDbData('sales_order', '*', 'increment_id', $orderIncrementId);

        if (!empty($order)) {
            $orderPaymentData = $this->dataClass->getMagentoDbData('sales_order_payment', '*', 'parent_id',
                $order['entity_id']);
            $orderFullData['orderData'] = $order;
            $orderFullData['orderPaymentData'] = $orderPaymentData;
        }

        return $orderFullData;
    }

    public function getExistingOrderAddress($orderId): array
    {
        $order = $this->order->load($orderId);
        $orderBilling = $order->getBillingAddress();
        $orderShipping = $order->getShippingAddress();

        return [
            'billingAddress' => $this->address($orderBilling),
            'shippingAddress' => $this->address($orderShipping)
        ];
    }

    /**
     * @param AddressInterface|OrderAddressInterface $address
     * @return array
     */
    private function address($address): array
    {
        if(empty($address)) {
            return [];
        }
        return [
            'firstname' => $address->getFirstName(),
            'lastname' => $address->getLastName(),
            'company' => $address->getCompany(),
            'street' => $address->getStreet(),
            'city' => $address->getCity(),
            'country_id' => $address->getCountryId(),
            'region' => $address->getRegion(),
            'region_id' => $address->getRegionId(),
            'postcode' => $address->getPostcode(),
            'telephone' => $address->getTelephone(),
            'save_in_address_book' => 0,
        ];
    }

    private function getRecurringBillingAddress(int $billingAddressId)
    {
        try {
            $billing = $this->addressRepository->getById($billingAddressId);
            if (!empty($billing)) {
                return $this->address($billing);
            }
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
        }
        return [];
    }

    private function getRecurringShippingAddress(int $shippingAddressId)
    {
        try {
            $shipping = $this->addressRepository->getById($shippingAddressId);
            if (!empty($shipping)) {
                return $this->address($shipping);
            }
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
        }
        return [];
    }

    private function getShipmentMethod($productType, $orderShippingMethod)
    {
        $shippingMethod = (!empty($orderShippingMethod))
            ? $orderShippingMethod
            : $this->dataClass->getShippingMethods(); //

        if (in_array($productType, ['virtual', 'downloadable'])) {
            $shippingMethod = 'freeshipping_freeshipping';
        }

        return $shippingMethod;
    }

    public function createMageOrderRecurring(
        $recurring,
        $orderData,
        $savedOrderPaymentData,
        $customerData,
        $shipmentMethod
    )
    {
        $this->tranApi->cronlog(__METHOD__);
        // Select 1st active shipping method
        $order = $this->createOrderQuote($recurring, $orderData, $savedOrderPaymentData, $customerData, $shipmentMethod);

        $order->setEmailSent(0);

        if ($order->getEntityId()) {

            $this->tranApi->cronlog("New recurring order #" . $order->getIncrementId() . " created in magento.");
            // Updating db recurring table start
            $this->updateRecurringTable($recurring);
            // mark this recurring payment as applied
            if (!empty($this->paymentInternalId)) {
                $this->tranApi->markRecurringPaymentAsApplied($this->paymentInternalId);
            }
            // add new order recurring order
            $this->insertInRecurringOrder($recurring, 1, 'Order Added.', $order->getIncrementId(),
                $order->getEntityId());

            $this->tranApi->cronlog('End: Order saved successfully.');
        } else {
            $this->insertInRecurringOrder($recurring, 0, 'Order not saved!');

            $this->tranApi->cronlog("Order not saved!");
        }
    }

    private function createOrderQuote($recurring, $orderData, $savedOrderPaymentData, $customerData, $shipmentMethod)
    {
        $paymentMethodName = (!empty($savedOrderPaymentData['method']))
            ? $savedOrderPaymentData['method']
            : $this->dataClass->getPaymentMethods(); // Select Magento 1st active payment method

        $paymentObject = $this->getPaymentInfo($recurring, $savedOrderPaymentData, $customerData, $paymentMethodName,
            $orderData['excludeAmount']);
        $store = $this->storeManager->getDefaultStoreView();
        $quote = $this->quote->create(); //Create object of quote
        $quote->setStoreId($store->getId()); // set store for which you create quote

        // if you have already buyer id then you can load customer directly
        $customer = $this->customerRepository->getById($customerData['entity_id']);
        $quote->setCurrency();
        $quote->assignCustomer($customer); //Assign quote to customer

        if($maxReserveId = $this->getMaxReserveId((int)$store->getId())) {
            $quote->setReservedOrderId($maxReserveId);
        }

        //add items in quote
        foreach ($orderData['items'] as $item) {
            $product = $this->product->load($item['product_id']);
            $product->setPrice($item['price']);
            $quote->addProduct($product, intval($item['qty']));
        }

        //Set Address to quote
        $quote->getBillingAddress()->addData($orderData['billing_address']);
        $quote->getShippingAddress()->addData($orderData['shipping_address']);

        // Collect Rates and Set Shipping & Payment Method
        $quote->getShippingAddress()
            ->setCollectShippingRates(true)
            ->collectShippingRates()
            ->setShippingMethod($shipmentMethod); //shipping method

        $quote->setPaymentMethod($paymentMethodName); //payment method
        $quote->setInventoryProcessed(true); // update inventory

        $quote->save(); //Now Save quote and your quote is ready
        // Set Sales Order Payment
        $quote->getPayment()->importData($paymentObject);
        // Collect Totals & Save Quote
        $quote->collectTotals()->save();

        // Create Order From Quote
        return $this->quoteManagement->submit($quote);
    }

    private function getPaymentInfo(
        $recurring,
        $savedOrderPaymentData,
        $customerData,
        $paymentMethodName,
        $excludeAmount
    )
    {
        $paymentMethodId = !empty($recurring['eb_rec_method_id']) ?: $savedOrderPaymentData['ebzc_method_id'];

        $customerEntityId = $customerData['entity_id'];
        $additionalData = [
            'method' => $paymentMethodName,
            'ebzc_option' => 'recurring',
            'ebzc_option_new' => 'recurring',
            'ebzc_option_existing' => $savedOrderPaymentData['ebzc_option'] ?? '',
            'ebzc_cust_id' => $customerData['ec_cust_token'],
            'ebzc_method_id' => $paymentMethodId,
            'ebzc_avs_street' => $savedOrderPaymentData['ebzc_avs_street'] ?? '',
            'ebzc_avs_zip' => $savedOrderPaymentData['ebzc_avs_zip'] ?? '',
            'ebzc_save_payment' => false, //$savedOrderPaymentData['ebzc_save_payment'],
            'mage_cust_id' => $customerData['entity_id'],
            'excludeAmount' => $excludeAmount //$orderData['excludeAmount']
        ];

        if ($paymentMethodName == 'ebizcharge_ebizcharge') {
            $paymentObject = [
                'method' => $paymentMethodName,
                'so_number' => $recurring['mage_order_id'],
                'po_number' => $recurring['mage_order_id'],
                'mage_cust_id' => $customerEntityId,
                'additional_data' => $additionalData
            ];
        } else {
            $paymentObject = [
                'method' => $paymentMethodName,
                'so_number' => $recurring['mage_order_id'],
                'po_number' => $recurring['mage_order_id'],
                'mage_cust_id' => $customerEntityId
            ];
        }

        return $paymentObject;
    }

    protected function updateRecurringTable($recurring)
    {
        $recProcessedNew = ((int)$recurring['eb_rec_processed'] + 1);
        $recRemainingNew = ((int)$recurring['eb_rec_remaining'] - 1);
        $recNextDueDateArray = unserialize($recurring['eb_rec_due_dates']);

        $updateRecord = $this->recurringRepository->getById($recurring['rec_id']);
        $updateRecord = $updateRecord->setEbRecProcessed($recProcessedNew)
            ->setEbRecNext($recNextDueDateArray[$recProcessedNew] ?? '')
            ->setEbRecRemaining($recRemainingNew);

        return $this->recurringRepository->save($updateRecord);
    }

    /**
     * save the update recurrings in the table
     */
    public function saveRecurrings()
    {
        $upatedRecurrings = $this->suspendRecurrings !== null ? $this->suspendRecurrings : $this->failedAttempts;
        if ($upatedRecurrings !== null) {
            try {
                $upatedRecurrings->save();
                return true;
            } catch (\Exception $e) {
                $this->logger->critical($e->getMessage());
            }
        }
        return false;

    }

    /**
     * @return bool
     */
    private function saveRecurringOrders(): bool
    {
        if ($this->recurringOrders !== null) {
            try {
                $this->recurringOrders->save();
                return true;
            } catch (\Exception $e) {
                $this->logger->critical($e->getMessage());
            }
        }
        return false;
    }

    /**
     * if the Subscription fails on the Third time it is put on Suspension.
     * @param $recurring
     * @return bool
     */
//    private function addFailedAttempts($recurring)
//    {
//        $failedAttempts = (int)$recurring->getData('failed_attempts') + 1;
//        $recurringRecord = $this->recurringRepository->getById((int)$recurring->getData('rec_id'));
//        $recurringRecord->setFailedAttempts($failedAttempts);
//        $this->recurringRepository->save($recurringRecord);
//
//        if ($failedAttempts > 2) {
//            $this->tranApi->cronlog('This is the 3rd failed attempt of recurring record# ' . $recurring->getData('rec_id') . ' Suspending subscription.');
//            $result = $this->tranApi->suspendScheduledRecurringPaymentStatus($recurring, 1);
//        }
//        return true;
//    }

    /**
     * Creates a success message, and passes it to the "Manage
     *
     * @param bool $cron
     * @return bool
     */
    private function createSuccessMessage($cron = false): bool
    {
        $message = 'No new order created';
        $numberOfOrders = count($this->orders);
        $failedOrders = count($this->failedOrders);
        if($numberOfOrders > 0) {
            $message = $numberOfOrders . ' Order(s) are created successfully';
        }
        if($failedOrders > 0) {
            $message .= " and $failedOrders order(s) are failed.";
        }
        if(!$cron) {
            $this->messageManager->addSuccessMessage($message);
        }
        $this->logger->notice('Order create cron message: ' . $message);

        return true;
    }

    /**
     * Get max reserved order id
     *
     * @param int $storeId
     * @return int|false
     */
    private function getMaxReserveId(int $storeId)
    {
        $defaultAdminStoreId = 0;
        try {
            $defaultAdminStoreMaxId = $this->sequenceManager->getSequence(
                Order::ENTITY,
                $defaultAdminStoreId
            )->getNextValue();
            $currentStoreMaxId = $this->sequenceManager->getSequence(
                Order::ENTITY,
                $storeId
            )->getNextValue();

            return max($currentStoreMaxId, $defaultAdminStoreMaxId);

        } catch (\Exception $e) {
            return false;
        }
    }
}
