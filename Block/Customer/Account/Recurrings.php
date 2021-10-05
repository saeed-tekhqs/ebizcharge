<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Ebizcharge\Ebizcharge\Block\Customer\Account;

use Ebizcharge\Ebizcharge\Api\Data\RecurringInterface;
use Ebizcharge\Ebizcharge\Api\RecurringRepositoryInterface;
use Ebizcharge\Ebizcharge\Model\Config as EbizConfig;
use Ebizcharge\Ebizcharge\Model\ResourceModel\Recurring\Collection;
use Ebizcharge\Ebizcharge\Model\ResourceModel\Recurring\CollectionFactory as RecurringCollectionFactory;
use Ebizcharge\Ebizcharge\Model\TranApi;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Customer\Block\Address\Renderer\RendererInterface;
use Magento\Customer\Model\SessionFactory;
use Magento\Framework\App\Response\Http;
use Magento\Framework\App\Response\RedirectInterface;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Payment\Model\Config;
use Magento\Theme\Block\Html\Pager;

/**
 * Sales order history block
 *
 * @api
 * @since 100.0.2
 */
class Recurrings extends Template
{
    /**
     * @var string
     */
    protected $_template = 'Ebizcharge_Ebizcharge::recurrings_list.phtml';

    /**
     * @var TranApi
     */
    private $tranApi;

    /**
     * @var Config
     */
    private $paymentConfig;

    /**
     * @var RecurringCollectionFactory
     */
    private $recurringCollectionFactory;

    /**
     * @var EbizConfig
     */
    private $myConfig;

    /**
     * @var SessionFactory
     */
    private $customerSession;

    /**
     * @var RecurringRepositoryInterface
     */
    private $recurringRepository;
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
     * @var \Magento\Sales\Api\OrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @param Config $paymentConfig
     * @param Context $context
     * @param EbizConfig $config
     * @param Http $response
     * @param ManagerInterface $messageManager
     * @param RecurringCollectionFactory $recurringCollectionFactory
     * @param RecurringRepositoryInterface $recurringRepository
     * @param RedirectInterface $redirect
     * @param SessionFactory $customerSession
     * @param TranApi $tranApi
     * @param array $data
     */
    public function __construct(
        Config $paymentConfig,
        Context $context,
        EbizConfig $config,
        Http $response,
        ManagerInterface $messageManager,
        RecurringCollectionFactory $recurringCollectionFactory,
        RecurringRepositoryInterface $recurringRepository,
        RedirectInterface $redirect,
        SessionFactory $customerSession,
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
        \Magento\Customer\Api\AddressRepositoryInterface $addressRepository,
        \Magento\Customer\Model\Address\Config $addressConfig,
        \Magento\Customer\Model\Address\Mapper $addressMapper,
        TranApi $tranApi,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->recurringCollectionFactory = $recurringCollectionFactory;
        $this->customerSession = $customerSession;
        $this->tranApi = $tranApi;
        $this->myConfig = $config;
        $this->paymentConfig = $paymentConfig;
        $this->recurringRepository = $recurringRepository;
        $this->messageManager = $messageManager;
        $this->response = $response;
        $this->redirect = $redirect;
        $this->verifyEditAction();
        $this->orderRepository = $orderRepository;
        $this->addressRepository = $addressRepository;
        $this->addressConfig = $addressConfig;
        $this->addressMapper = $addressMapper;
        $this->addressMapper = $addressMapper;
        $this->productRepository = $productRepository;
    }

    /**
     * Get customer recurrings
     *
     * @return array|Collection
     */
    public function getRecurrings()
    {
        if (!($customerId = $this->getMageCustId())) {
            return [];
        }

        return $this->recurringCollectionFactory->create()
            ->addFieldToFilter('mage_cust_id', $customerId)
            ->addFieldToFilter('rec_status', array('neq' => 3));
    }

    /**
     * @inheritDoc
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        if ($this->getrecurrings()) {
            $pager = $this->getLayout()->createBlock(
                Pager::class,
                'ebizcharge.recurrings.list.pager'
            )->setCollection(
                $this->getrecurrings()
            );
            $this->setChild('pager', $pager);
            $this->getRecurrings()->load();
        }
        return $this;
    }

    /**
     * Get Pager child block output
     *
     * @return string
     */
    public function getPagerHtml()
    {
        return $this->getChildHtml('pager');
    }

    /**
     * Get rerecurring URL
     *
     * @return mixed|null
     */

    public function getSearchScheduledRecurringPayments()
    {
        return $this->tranApi->getSearchScheduledRecurringPayments(
            $this->getMageCustId(),
            $this->getEbzcCustInternalId(),
            $this->getMID()
        );
    }

    public function getMageCustId()
    {
        return $this->customerSession->create()->getCustomerId();
    }

    public function getEbzcMethodId()
    {
        return $this->getRequest()->getParam('mid');
    }

    public function getMID()
    {
        return $this->getRequest()->getParam('mid');
    }

    public function getEbzcCustInternalId()
    {
        $customer = $this->getCustomerDetail($this->getMageCustId());

        return isset($customer['ec_cust_internalid']) ? $customer['ec_cust_internalid'] : '';
    }

    public function getPaymentMethods()
    {
        $ebizCustomer = $this->tranApi->getCustomer($this->getMageCustId());

        if ($ebizCustomer !== null) {
            $profiles = isset($ebizCustomer->PaymentMethodProfiles)
                ? $ebizCustomer->PaymentMethodProfiles->PaymentMethodProfile
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

    public function getPaymentCctypes()
    {
        return explode(',', $this->myConfig->getPaymentCctypes());
    }

    public function getCcAvailableTypes()
    {
        $applicableTypes = $this->getPaymentCctypes();
        $types = $this->paymentConfig->getCcTypes();

        foreach (array_keys($types) as $code) {
            if (!in_array($code, $applicableTypes)) {
                unset($types[$code]);
            }
        }

        return $types;
    }

    public function getConfiguredFrequencies($selectedFrequency = null)
    {
        $this->myConfig->getRecurringFrequencyOptions($selectedFrequency);
    }

    public function getCcMonths()
    {
        $months = $this->getData('cc_months');

        if ($months === null) {
            $months[0] = __('Month');
            $months = array_merge($months, $this->paymentConfig->getMonths());
            $this->setData('cc_months', $months);
        }

        return $months;
    }

    public function getCcYears()
    {
        $years = $this->getData('cc_years');

        if ($years === null) {
            $years = $this->paymentConfig->getYears();
            $years = [0 => __('Year')] + $years;
            $this->setData('cc_years', $years);
        }

        return $years;
    }

    public function getPaymentSavePayment()
    {
        return $this->myConfig->getPaymentSavePayment();
    }

    /**
     * Get customer recurrings
     *
     * @return RecurringInterface|null
     */
    public function getCustomerRecurring()
    {
        try {
            $paymentInternalId = $this->getEbzcMethodId();
            $record = $this->recurringRepository->getById($paymentInternalId, 'eb_rec_scheduled_payment_internal_id');
            if ($record) {
                return $record->getData();
            }
        } catch (\Exception $e) {
            $this->tranApi->ebizLog()->info(__METHOD__ . $e->getMessage());
        }
        return null;
    }

    /**
     * @return void
     */
    private function verifyEditAction(): void
    {
        if ($this->getRequest()->getFullActionName() == 'ebizcharge_recurrings_editaction') {
            $mid = $this->getRequest()->getParam('mid');
            if ($mid) {
                return;
            }
            $this->messageManager->addErrorMessage(__('Unable to update recurrings payment.'));
            $this->redirect->redirect($this->response, '*/*/listaction');
        }

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
            $this->tranApi->ebizLog()->err($e->getMessage());
            return null;
        }
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
