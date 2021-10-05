<?php
/**
 * @author Century Business Solutions <support@centurybizsolutions.com>
 * @copyriht Copyright (c) 2021 Century Business Solutions  (www.centurybizsolutions.com)
 * Created by PhpStorm
 * User: Mobeen
 * Date: 6/10/21
 * Time: 12:54 PM
 */
declare(strict_types=1);

namespace Ebizcharge\Ebizcharge\Block\Admin;

use Ebizcharge\Ebizcharge\Model\Config as EbizConfig;
use Magento\Customer\Model\CustomerFactory;
use Magento\Framework\View\Element\Template;
use Magento\Payment\Model\Config as PaymentConfig;
use Magento\Shipping\Model\Config as ShippingConfig;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Ebizcharge\Ebizcharge\Helper\Data;

/**
 * Block to add new scheduled recurrion for customer
 *
 * Class AddRecurrion
 */
class AddRecurrion extends Template
{
    /**
     * @var CollectionFactory
     */
    private $productCollection;

    /**
     * @var CustomerFactory
     */
    private $customerFactory;

    /**
     * @var PaymentConfig
     */
    private $paymentConfig;

    /**
     * @var ShippingConfig
     */
    private $shippingConfig;

    /**
     * @var Data
     */
    private $helper;

    /**
     * @var EbizConfig
     */
    private $ebizConfig;

    /**
     * @param Template\Context $context
     * @param CustomerFactory $customerFactory
     * @param PaymentConfig $paymentConfig
     * @param ShippingConfig $shippingConfig
     * @param CollectionFactory $productCollection
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        CustomerFactory $customerFactory,
        PaymentConfig $paymentConfig,
        ShippingConfig $shippingConfig,
        CollectionFactory $productCollection,
        EbizConfig $ebizConfig,
        Data $helper,
        array $data = []
    )
    {
        parent::__construct($context, $data);
        $this->customerFactory = $customerFactory;
        $this->paymentConfig = $paymentConfig;
        $this->shippingConfig = $shippingConfig;
        $this->productCollection = $productCollection;
        $this->helper = $helper;
        $this->ebizConfig = $ebizConfig;
    }

    /* Add new payment functions */
    public function getRequestCardCodeAdmin(): bool
    {
        if ($this->helper->getRequestCardCodeAdmin() == 1) {
            return true;
        } else {
            return false;
        }
    }

    public function getPaymentCctypes()
    {
        return explode(',', $this->helper->getPaymentCctypes());
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

    //*********** for add new subscription **************//

    public function getAllItemsList()
    {
        $collection = $this->productCollection->create()
            ->addAttributeToSelect('*')
            ->addAttributeToSort('name')
            ->load();

        foreach ($collection as $product) {
            if (($product->getTypeId() != 'configurable') && ($product->getTypeId() != 'grouped') && ($product->getPrice() > 0)) {
                ?>
                <option value="<?php echo $product->getId(); ?>"><?php echo $product->getName(); ?>
                    (<?php echo "Price: " . number_format((float)$product->getPrice(), 2, '.', ''); ?>)
                </option>
                <?php
            }
        }
    }

    public function getAllActiveCustomersList()
    {
        $customerFactory = $this->customerFactory->create();

        //Get customer collection
        $customerCollection = $customerFactory->getCollection()
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

    /**
     * @param null $selectedMethod
     */
    public function getShippingMethods($selectedMethod = null)
    {
        $carriers = $this->shippingConfig->getAllCarriers();
        foreach ($carriers as $carrierCode => $carrierModel) {
            if ($carrierModel->isActive()) {
                $carrierMethods = $carrierModel->getAllowedMethods();
                if ($carrierMethods) {
                    //$carrierTitle = $this->_scopeConfig->getValue('carriers/' . $carrierCode . '/title',\Magento\Store\Model\ScopeInterface::SCOPE_STORE);
                    foreach ($carrierMethods as $methodCode => $methodTitle) {
                        $value = $carrierCode . '_' . $methodCode;
                        $title = $methodTitle . ' [' . $carrierCode . ']';
                        ?>
                        <option value="<?php echo $value ?>"<?php if ($value == $selectedMethod) {echo 'selected';} ?>>
                            <?php echo $title ?>
                        </option>

                        <?php
                    }
                }
            }
        }

    }

    /**
     * Get available frequencies for subscription
     *
     * @param null $selectedFrequency
     */
    public function getConfiguredFrequencies($selectedFrequency = null)
    {
        $this->ebizConfig->getRecurringFrequencyOptions($selectedFrequency);
    }
}
