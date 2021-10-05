<?php
/**
 * Provides access to the admin settings for Ebizcharge.
 *
 * @author      Century Business Solutions <support@centurybizsolutions.com>
 * @copyright   Copyright (c) 2020 Century Business Solutions  (www.centurybizsolutions.com)
 */

namespace Ebizcharge\Ebizcharge\Model;

use Ebizcharge\Ebizcharge\Model\Source\RecurringFrequencyType;

class Config
{
    use EbizLogger;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_scopeConfigInterface;
    protected $customerSession;
    protected $appState;
    protected $storeManager;
    protected $urlBuilder;
    protected $frequencyType;

    const KEY_ACTIVE = 'active';

    /**
     * @var \Magento\Backend\Model\Session\Quote
     */
    private $sessionQuote;

    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $configInterface,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Backend\Model\Session\Quote $sessionQuote,
        \Magento\Framework\App\State $appState,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\UrlInterface $urlBuilder,
        RecurringFrequencyType $frequencyType
    )
    {
        $this->_scopeConfigInterface = $configInterface;
        $this->customerSession = $customerSession;
        $this->sessionQuote = $sessionQuote;
        $this->appState = $appState;
        $this->storeManager = $storeManager;
        $this->urlBuilder = $urlBuilder;
        $this->frequencyType = $frequencyType;
    }

    public function log($message, $level = null)
    {
        $this->ebizLog()->info($message);
    }

    /**
     * @return bool
     */
    public function isActive()
    {
        return (bool)(int)$this->_scopeConfigInterface->getValue('payment/ebizcharge_ebizcharge/active', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }
    public function isAchActive()
    {
        return $this->_scopeConfigInterface->getValue('payment/ebizcharge_ebizcharge/enableAch', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    public function getPaymentCctypes()
    {
        return $this->_scopeConfigInterface->getValue('payment/ebizcharge_ebizcharge/cctypes', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    public function getPaymentCurrency()
    {
        return $this->_scopeConfigInterface->getValue('payment/ebizcharge_ebizcharge/currency', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    public function getPaymentSavePayment()
    {
        return $this->_scopeConfigInterface->getValue('payment/ebizcharge_ebizcharge/save_payment', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    public function getPaymentMinordertotal()
    {
        return $this->_scopeConfigInterface->getValue('payment/ebizcharge_ebizcharge/min_order_total', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    public function getPaymentMaxordertotal()
    {
        return $this->_scopeConfigInterface->getValue('payment/ebizcharge_ebizcharge/max_order_total', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    public function isAuthorizeOnly()
    {
        return $this->_scopeConfigInterface->getValue('payment/ebizcharge_ebizcharge/payment_action', \Magento\Store\Model\ScopeInterface::SCOPE_STORE) == 'authorize';
    }

    public function saveCard()
    {
        return $this->_scopeConfigInterface->getValue('payment/ebizcharge_ebizcharge/save_card', \Magento\Store\Model\ScopeInterface::SCOPE_STORE) && ($this->customerSession->isLoggedIn() || $this->sessionQuote->getCustomerId());
    }

    public function getEbizActive()
    {
        return $this->_scopeConfigInterface->getValue('payment/ebizcharge_ebizcharge/active', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    public function getSourceKey()
    {
        return $this->_scopeConfigInterface->getValue('payment/ebizcharge_ebizcharge/sourcekey', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    public function getSourceId()
    {
        return $this->_scopeConfigInterface->getValue('payment/ebizcharge_ebizcharge/sourceid', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    public function getSourcePin()
    {
        return $this->_scopeConfigInterface->getValue('payment/ebizcharge_ebizcharge/sourcepin', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    public function getPaymentDescription()
    {
        return $this->_scopeConfigInterface->getValue('payment/ebizcharge_ebizcharge/description', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    public function debugMode($code)
    {
        return !!$this->_scopeConfigInterface->getValue('payment/' . $code . '/debug', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    public function getRequestCardCode()
    {
        return 1;
        // Removed setting from admin
        //return $this->_scopeConfigInterface->getValue('payment/ebizcharge_ebizcharge/request_card_code', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    public function getRequestCardCodeAdmin()
    {
        return $this->_scopeConfigInterface->getValue('payment/ebizcharge_ebizcharge/request_card_code_admin', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }
    // disabled as per request of Frank
    /*public function getCustreceipt()
    {
        return $this->_scopeConfigInterface->getValue('payment/ebizcharge_ebizcharge/custreceipt', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    public function getCustreceiptTemplate()
    {
        return $this->_scopeConfigInterface->getValue('payment/ebizcharge_ebizcharge/custreceipt_template', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }*/

    public function getPaymentAction()
    {
        return $this->_scopeConfigInterface->getValue('payment/ebizcharge_ebizcharge/payment_action', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    public function getCustreceiptTemplate()
    {
        return $this->_scopeConfigInterface->getValue('payment/ebizcharge_ebizcharge/custreceipt_template', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    public function getArea()
    {
        return $this->appState->getAreaCode();
    }

    public function getDeleteURL()
    {
        return $this->urlBuilder->getUrl('ebizcharge/cards/inlineaction');
        //$this->_storeManager->getStore()->getUrl('ebizcharge/cards/inlineaction');
    }

    public function getBaseDeleteURL()
    {
        return $this->urlBuilder->getBaseUrl();
    }

    public function getConfig($path)
    {
        return $this->_scopeConfigInterface->getValue($path, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    public function getEconnect()
    {
        return $this->_scopeConfigInterface->getValue('payment/ebizcharge_ebizcharge/econnect', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    public function isEbizchargeActive()
    {
        return $this->_scopeConfigInterface->getValue('payment/ebizcharge_ebizcharge/active', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

	public function isRecurringActive()
    {
        return $this->_scopeConfigInterface->getValue('payment/ebizcharge_ebizcharge/recurring_payments', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

	public function isRecurringEnabled()
    {
        return $this->_scopeConfigInterface->getValue('payment/ebizcharge_ebizcharge/recurring_payments', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    public function isEconnectUploadEnabled()
    {
        return $this->_scopeConfigInterface->getValue('payment/ebizcharge_ebizcharge/uploadeconnect', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    public function isEconnectDownlaodEnabled()
    {
        return $this->_scopeConfigInterface->getValue('payment/ebizcharge_ebizcharge/downloadeconnect', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    public function isShippingSelected()
    {
        return $this->_scopeConfigInterface->getValue('payment/ebizcharge_ebizcharge/shippingmethod', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    public function getObjectManagerInstance()
    {
        return \Magento\Framework\App\ObjectManager::getInstance();
    }

    public function getQueryResourceConnection()
    {
        // Getting Db resource connection for query Run start
        return $this->getObjectManagerInstance()->get('Magento\Framework\App\ResourceConnection');
    }

    public function getRecurringFrequencies()
    {
        return $this->_scopeConfigInterface->getValue('payment/ebizcharge_ebizcharge/recurring_frequency', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    public function getRecurringFrequencyOptions($selectedFrequency = null)
    {
        $frequencies = $this->frequencyType->options();

        $selectedOptions = explode(',', $this->getRecurringFrequencies());
        if (empty($selectedOptions)) {
            $selectedOptions = array_keys($frequencies);
        }

        foreach ($selectedOptions as $option) { ?>
            <option value="<?php echo $option ?>"<?php if ($option == $selectedFrequency) {
                echo 'selected';
            } ?>>
                <?php echo isset($frequencies[$option]) ?  $frequencies[$option] : $option;?>
            </option>
            <?Php
        }
    }

    public function getCartPageURL()
    {
        return $this->storeManager->getStore()->getUrl('checkout/cart');
    }

}
