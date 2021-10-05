<?php
/**
 * Saves a payment method to the customer's saved payment methods. This is
 * passed the data from the "Add New Credit Card" form.
 *
 * @author      Century Business Solutions <support@centurybizsolutions.com>
 * @copyright   Copyright (c) 2020 Century Business Solutions  (www.centurybizsolutions.com)
 */

namespace Ebizcharge\Ebizcharge\Controller\Cards;

ini_set('memory_limit', '1000M');
ini_set('max_execution_time', '-1');

use Magento\Customer\Api\AddressRepositoryInterface;
use Magento\Customer\Api\Data\AddressInterfaceFactory;
use Magento\Customer\Api\Data\RegionInterface;
use Magento\Customer\Api\Data\RegionInterfaceFactory;
use Magento\Customer\Model\Metadata\FormFactory;
use Magento\Customer\Model\Session;
use Magento\Directory\Helper\Data as HelperData;
use Magento\Directory\Model\RegionFactory;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\ForwardFactory;
use Magento\Framework\Data\Form\FormKey\Validator as FormKeyValidator;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Reflection\DataObjectProcessor;
use Magento\Framework\View\Result\PageFactory;
// new added
use Magento\Framework\App\Bootstrap;
use Magento\framework\Exception\ValidatorException;

class SaveAction extends \Magento\Customer\Controller\Address
{
    protected $regionFactory;
    protected $helperData;
    protected $token;
    protected $_tran;
    protected $_scopeConfig;
    protected $_paymentconfig;
    protected $isDefaultMethod;

    /**
     * @param Context $context
     * @param Session $customerSession
     * @param FormKeyValidator $formKeyValidator
     * @param FormFactory $formFactory
     * @param AddressRepositoryInterface $addressRepository
     * @param AddressInterfaceFactory $addressDataFactory
     * @param RegionInterfaceFactory $regionDataFactory
     * @param DataObjectProcessor $dataProcessor
     * @param DataObjectHelper $dataObjectHelper
     * @param ForwardFactory $resultForwardFactory
     * @param PageFactory $resultPageFactory
     * @param RegionFactory $regionFactory
     * @param HelperData $helperData
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        Context $context,
        Session $customerSession,
        FormKeyValidator $formKeyValidator,
        FormFactory $formFactory,
        AddressRepositoryInterface $addressRepository,
        AddressInterfaceFactory $addressDataFactory,
        RegionInterfaceFactory $regionDataFactory,
        DataObjectProcessor $dataProcessor,
        DataObjectHelper $dataObjectHelper,
        ForwardFactory $resultForwardFactory,
        PageFactory $resultPageFactory,
        RegionFactory $regionFactory,
        HelperData $helperData,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Payment\Model\Config $paymentconfig,
        \Ebizcharge\Ebizcharge\Model\Token $token,
        \Ebizcharge\Ebizcharge\Model\TranApi $tranApi

    )
    {
        $this->regionFactory = $regionFactory;
        $this->helperData = $helperData;
        $this->token = $token;
        $this->_tran = $tranApi;
        $this->_scopeConfig = $scopeConfig;
        $this->_paymentconfig = $paymentconfig;
        parent::__construct(
            $context,
            $customerSession,
            $formKeyValidator,
            $formFactory,
            $addressRepository,
            $addressDataFactory,
            $regionDataFactory,
            $dataProcessor,
            $dataObjectHelper,
            $resultForwardFactory,
            $resultPageFactory);
    }

    /**
     * Adds the new payment method to the customer's account, and
     * redirects the user to the "Manage My Payment Methods" page.
     *
     * @return \Magento\Framework\Controller\ResultInterface
     * @throws \Magento\Framework\Exception\NotFoundException
     */
    public function execute()
    {
        $url = $this->_buildUrl('*/*/listaction');

        $liveEbzcCustomerId = '';

        if (!$this->_formKeyValidator->validate($this->getRequest())) {
            return $this->resultRedirectFactory->create()->setPath('*/*/listaction');
        }

        $existingAddressData = [];

        $addressForm = $this->_formFactory->create('customer_address', 'customer_address_edit', $existingAddressData);
        $addressData = $addressForm->extractData($this->getRequest());
        $billing = $addressForm->compactData($addressData);

        $this->updateRegionData($billing);

        if (!isset($billing['street'][1])) {
            $billing['street'][1] = 'N/A';
            $billingAVS = $billing['street'][0];

        } else {
            $billingAVS = $billing['street'][0] . ' ' . $billing['street'][1];
        }

        $paymentTypes = $this->_paymentconfig->getCcTypes();
        $payment = $this->getRequest()->getParam('payment');

        $this->isDefaultMethod = isset($payment['default']) ? true : false;

        $methodName = $payment['cc_type'];

        foreach ($paymentTypes as $code => $text) {
            if ($code == $payment['cc_type']) {
                $methodName = $text;
            }
        }

        // Verifies that the expiration date has not already passed.
        $checkExpiration = $payment['cc_exp_year'] . "-" . $payment['cc_exp_month'];
        $currentDate = date('Y-m');

        if (strtotime($currentDate) > strtotime($checkExpiration)) {
            $this->messageManager->addErrorMessage(__('Unable to save the payment method. The credit card was expired.'));
            $url = $this->_buildUrl('*/*/listaction');
            return $this->resultRedirectFactory->create()->setUrl($this->_redirect->error($url));
        }

        //----- New Payment Method --------
        $paymentMethod = array(
            'MethodName' => $methodName . ' ' . substr($payment['cc_number'], -4) . ' - ' . $payment['cc_holder'],
            'AccountHolderName' => $payment['cc_holder'],
            'SecondarySort' => $this->isDefaultMethod ? 0 : 1,
            'Created' => date('Y-m-d\TH:i:s'),
            'Modified' => date('Y-m-d\TH:i:s'),
            'AvsStreet' => $billingAVS,
            'AvsZip' => $billing['postcode'],
            'CardCode' => isset($payment['cc_cid']) ? $payment['cc_cid'] : '',
            'CardExpiration' => $checkExpiration,
            'CardNumber' => $payment['cc_number'],
            'CardType' => $payment['cc_type']
        );
        //----- New Payment Method --------

        $mageCustomerId = $this->_getSession()->getCustomerId();

        $ebzcCustomerId = $this->token->getCollection()
            ->addFieldToFilter('mage_cust_id', $mageCustomerId)
            ->getFirstItem()
            ->getEbzcCustId();

        $magCustomerData = $this->_getSession()->getCustomer()->getData();

        $localEbizCustomerId = $ebzcCustomerId;
        $liveSearchCustomer = $this->_tran->searchCustomers($mageCustomerId);

        if ($liveSearchCustomer == 'Not Found') {

            # Case 1 Local = No, Live = No
            if (($localEbizCustomerId == null)) {

                try {
                    $customerInternalId = $this->addCustomer($mageCustomerId, $magCustomerData, $billing, $paymentMethod);

                    if ($customerInternalId) {

                        $customerToken = $this->_tran->getCustomerToken($mageCustomerId);
                        // Save token in ebizcharge_token table
                        $token = $this->token;
                        $token->setMageCustId((int)$mageCustomerId);
                        $token->setEbzcCustId((int)$customerToken);
                        $token->save();

                        $this->_tran->runUpdateQueryCustomer('customer_entity', 'cust', 1, $customerInternalId, $mageCustomerId, $customerToken, $mageCustomerId);

                        // add new payment method
                        try {
                            $paymentMethodId = $this->addPaymentMethod($customerInternalId, $paymentMethod, $customerToken);
                            if (!empty($paymentMethodId)) {
                                $this->messageManager->addSuccessMessage(__('Credit card saved successfully.'));
                                $url = $this->_buildUrl('*/*/listaction', ['_secure' => true]);
                            } else {
                                $this->messageManager->addErrorMessage(__('Unable to obtain Method ID.'));
                            }
                        } catch (\Exception $e) {
                            $this->messageManager->addExceptionMessage($e, $e->getMessage());
                        }

                    } else {
                        $url = $this->_buildUrl('*/*/listaction');
                        $this->messageManager->addErrorMessage(__('Unable to save customer card.'));
                    }
                } catch (\Exception $ex) {
                    $this->messageManager->addExceptionMessage($ex, 'Unable to save customer card.' . $ex->getMessage());
                }

            } elseif (($localEbizCustomerId != null)) {
                # Case 2 Local = Yes, Live = No
                try {
                    $customerInternalId = $this->addCustomer($mageCustomerId, $magCustomerData, $billing, $paymentMethod);

                    if ($customerInternalId) {
                        $customerToken = $this->_tran->getCustomerToken($mageCustomerId);
                        // Update token in ebizcharge_token table
                        $this->_tran->runUpdateCustomer('ebizcharge_token', $mageCustomerId, $customerToken);
                        $this->_tran->runUpdateQueryCustomer('customer_entity', 'cust', 1, $customerInternalId, $mageCustomerId, $customerToken, $mageCustomerId);

                        // add new payment method
                        try {
                            $paymentMethodId = $this->addPaymentMethod($customerInternalId, $paymentMethod, $customerToken);

                            if (!empty($paymentMethodId)) {
                                $this->messageManager->addSuccessMessage(__('Credit card saved successfully.'));
                                $url = $this->_buildUrl('*/*/listaction', ['_secure' => true]);
                            } else {
                                $this->messageManager->addErrorMessage(__('Unable to obtain Method ID.'));
                            }
                        } catch (\Exception $e) {
                            $this->messageManager->addExceptionMessage($e, $e->getMessage());
                        }

                    } else {
                        $url = $this->_buildUrl('*/*/listaction');
                        $this->messageManager->addErrorMessage(__('Unable to save customer card.'));
                    }
                } catch (\Exception $ex) {
                    $this->messageManager->addExceptionMessage($ex,'Unable to save customer card.' . $ex->getMessage());
                }
            } # Case 6 In all other cases default
            else {
                $this->messageManager->addErrorMessage(__('Error occurred in adding card process.'));
            }

        } else {
            $liveEbzcCustomerId = $this->_tran->getCustomerToken($mageCustomerId);

            # Case 5 Local = Yes, Live = Yes , Token = Same
            if (($localEbizCustomerId != null) && ($liveEbzcCustomerId != null) && ($localEbizCustomerId == $liveEbzcCustomerId)) {

                $updated = $this->updateCustomer($mageCustomerId, $billing, $paymentMethod);
                if(!$updated) {
                    $url = $this->_buildUrl('*/*/addaction');
                }

            } elseif (($localEbizCustomerId != null) && ($liveEbzcCustomerId != null) && ($localEbizCustomerId != $liveEbzcCustomerId)) {
                # Case 4 Local = Yes, Live = Yes , Token = Diff
                $this->messageManager->addErrorMessage(__('Customer already exist and token mismatch.'));
            } else {
                # Case 6 In all other cases default
                $this->messageManager->addErrorMessage(__('Error in adding card process.'));
            }

        }

        return $this->resultRedirectFactory->create()->setUrl($this->_redirect->error($url));
    }

    /**
     * @param $mageCustomerId
     * @param $billing
     * @param $paymentMethod
     */
    private function updateCustomer($mageCustomerId, $billing, $paymentMethod)
    {
        $client = $this->_tran->getClient();
        $securityToken = $this->_tran->getUeSecurityToken();

        // Customer Update Start
        try {
            // Address Update start
            $ebizCustomer = $this->_tran->getCustomer($mageCustomerId);

            if ($ebizCustomer != null) {

                $customerDataUpdated = array(
                    'CustomerInternalId' => $ebizCustomer->CustomerInternalId,
                    'CustomerId' => $ebizCustomer->CustomerId,
                    'FirstName' => $ebizCustomer->FirstName,
                    'LastName' => $ebizCustomer->LastName,
                    'CompanyName' => $ebizCustomer->CompanyName,
                    'Phone' => $ebizCustomer->Phone,
                    'CellPhone' => $ebizCustomer->CellPhone,
                    'Fax' => $ebizCustomer->Fax,
                    'Email' => $ebizCustomer->Email,
                    'WebSite' => $ebizCustomer->WebSite,
                    'BillingAddress' => $this->getCustomerBillingInfo($billing),
                    'ShippingAddress' => $ebizCustomer->ShippingAddress,
                    'PaymentMethodProfiles' => $ebizCustomer->PaymentMethodProfiles,
                );

                $updatedMethodProfile = $client->updateCustomer(
                    array(
                        'securityToken' => $securityToken,
                        'customer' => $customerDataUpdated,
                        'customerID' => $ebizCustomer->CustomerId,
                        'customerInternalId' => $ebizCustomer->CustomerInternalId,
                    )
                );

                // For ebiz data update
                if (($updatedMethodProfile->UpdateCustomerResult->Status) == 'Success') //if ($updateCustomerData)
                {
                    $this->messageManager->addSuccessMessage(__('Address updated successfully.'));
                } else {
                    $this->messageManager->addErrorMessage(__('Address is not updated.'));
                }
                // Address Update end

                // add new payment method
                try {
                    $paymentMethodId = $this->addPaymentMethod($ebizCustomer->CustomerInternalId, $paymentMethod, $ebizCustomer->CustomerToken);

                    if (!empty($paymentMethodId)) {
                        $this->messageManager->addSuccessMessage(__('Credit card saved successfully.'));
                        $this->_buildUrl('*/*/listaction', ['_secure' => true]);
                        return true;
                    } else {
                        $this->messageManager->addErrorMessage(__('Unable to obtain Method ID.'));
                    }
                } catch (\Exception $e) {
                    $this->messageManager->addExceptionMessage($e, str_replace('addCustomerPaymentMethod', '', $e->getMessage()));
                }
            }

        } catch (\Exception $ex) {
            $this->messageManager->addErrorMessage($ex->getMessage());
        }
        return false;
    }

    /**
     * @param $customerInternalId
     * @param $paymentMethod
     * @param $customerToken
     * @return bool
     */
    private function addPaymentMethod($customerInternalId, $paymentMethod, $customerToken)
    {
        $paymentMethodId = $this->_tran->addCustomerPaymentMethod($customerInternalId, $paymentMethod);

        if ($paymentMethodId != null) {
            if ($this->isDefaultMethod) {
                $this->_tran->setDefaultPaymentMethod($customerToken, $paymentMethodId);
            }

            return $paymentMethodId;
        }

        return false;
    }

    /**
     * @param $mageCustomerId
     * @param $magCustomerData
     * @param $billing
     * @param $paymentMethod
     * @return mixed
     */
    private function addCustomer($mageCustomerId, $magCustomerData, $billing, $paymentMethod)
    {
        $customerData = array(
            'CustomerId' => $mageCustomerId,
            'FirstName' => $magCustomerData['firstname'],
            'LastName' => $magCustomerData['lastname'],
            'CompanyName' => $billing['company'],
            'Phone' => isset($billing['telephone']) ? $billing['telephone'] : '',
            'CellPhone' => isset($billing['telephone']) ? $billing['telephone'] : '',
            'Fax' => isset($billing['fax']) ? $billing['fax'] : '',
            'Email' => $magCustomerData['email'],
            'WebSite' => '',
            'SoftwareId' => 'Magento2',
            'BillingAddress' => $this->getCustomerBillingInfo($billing),
            'ShippingAddress' => $this->getCustomerBillingInfo($billing),
            'PaymentMethodProfiles' => $paymentMethod
        );

        try {
            $addCustomerEbiz = $this->_tran->getClient()->AddCustomer(
                array(
                    'securityToken' => $this->_tran->getUeSecurityToken(),
                    'customer' => $customerData
                ));

            if (($addCustomerEbiz->AddCustomerResult->Status) == 'Success') {
                return $addCustomerEbiz->AddCustomerResult->CustomerInternalId;
            }

            return false;

        } catch (\Exception $exception) {
            $this->_tran->log(__METHOD__ . $exception->getTraceAsString());
            return false;
        }
    }

    /**
     * @param $billing
     * @return array
     */
    private function getCustomerBillingInfo($billing)
    {
        return array(
            'FirstName' => $billing['firstname'],
            'LastName' => $billing['lastname'],
            'CompanyName' => $billing['company'],
            'Address1' => isset($billing['street'][0]) ? $billing['street'][0] : '',
            'Address2' => isset($billing['street'][1]) ? $billing['street'][1] : '',
            'City' => $billing['city'],
            'State' => $billing['region'],
            'ZipCode' => $billing['postcode'],
            'Country' => $billing['country_id']
        );
    }

    /**
     * update billing region data
     * @param $attributeValues
     */
    protected function updateRegionData(&$attributeValues)
    {
        if (!empty($attributeValues['region_id'])) {
            $newRegion = $this->regionFactory->create()->load($attributeValues['region_id']);
            $attributeValues['region_code'] = $newRegion->getCode();
            $attributeValues['region'] = $newRegion->getDefaultName();
        }

        $regionData = [
            RegionInterface::REGION_ID => !empty($attributeValues['region_id']) ? $attributeValues['region_id'] : null,
            RegionInterface::REGION => !empty($attributeValues['region']) ? $attributeValues['region'] : null,
            RegionInterface::REGION_CODE => !empty($attributeValues['region_code'])
                ? $attributeValues['region_code']
                : null];

        array_merge($attributeValues, $regionData);
    }

}
