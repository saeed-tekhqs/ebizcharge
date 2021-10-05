<?php
/**
 * Saves a payment method to the customer's saved payment methods. This is
 * passed the data from the "Add New Back Account" form.
 *
 * @author      Century Business Solutions <support@centurybizsolutions.com>
 * @copyright   Copyright (c) 2020 Century Business Solutions  (www.centurybizsolutions.com)
 */

namespace Ebizcharge\Ebizcharge\Controller\ACH;

ini_set('memory_limit', '1000M');
ini_set('max_execution_time', '-1');

use Ebizcharge\Ebizcharge\Model\Token;
use Ebizcharge\Ebizcharge\Model\TranApi;
use Magento\Customer\Api\AddressRepositoryInterface;
use Magento\Customer\Api\Data\AddressInterfaceFactory;
use Magento\Customer\Api\Data\RegionInterface;
use Magento\Customer\Api\Data\RegionInterfaceFactory;
use Magento\Customer\Controller\Address;
use Magento\Customer\Model\Metadata\FormFactory;
use Magento\Customer\Model\Session;
use Magento\Directory\Model\ResourceModel\Region\CollectionFactory as RegionCollectionFactory;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\ForwardFactory;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Data\Form\FormKey\Validator as FormKeyValidator;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Reflection\DataObjectProcessor;
use Magento\Framework\View\Result\PageFactory;

/**
 * Save ACH action class
 *
 * Class SaveAction
 */
class SaveAction extends Address
{
    /**
     * @var Token
     */
    private $token;

    /**
     * @var TranApi
     */
    private $_tran;

    private $isDefaultMethod;

    /**
     * @var RegionCollectionFactory
     */
    private $regionCollectionFactory;

    /**
     * @param AddressInterfaceFactory $addressDataFactory
     * @param AddressRepositoryInterface $addressRepository
     * @param Context $context
     * @param DataObjectHelper $dataObjectHelper
     * @param DataObjectProcessor $dataProcessor
     * @param FormFactory $formFactory
     * @param FormKeyValidator $formKeyValidator
     * @param ForwardFactory $resultForwardFactory
     * @param PageFactory $resultPageFactory
     * @param RegionCollectionFactory $regionCollectionFactory
     * @param RegionInterfaceFactory $regionDataFactory
     * @param Session $customerSession
     * @param Token $token
     * @param TranApi $tranApi
     */
    public function __construct(
        AddressInterfaceFactory $addressDataFactory,
        AddressRepositoryInterface $addressRepository,
        Context $context,
        DataObjectHelper $dataObjectHelper,
        DataObjectProcessor $dataProcessor,
        FormFactory $formFactory,
        FormKeyValidator $formKeyValidator,
        ForwardFactory $resultForwardFactory,
        PageFactory $resultPageFactory,
        RegionCollectionFactory $regionCollectionFactory,
        RegionInterfaceFactory $regionDataFactory,
        Session $customerSession,
        Token $token,
        TranApi $tranApi

    ) {
        $this->token = $token;
        $this->_tran = $tranApi;
        $this->regionCollectionFactory = $regionCollectionFactory;
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
     * @return Redirect
     * @throws LocalizedException
     */
    public function execute()
    {
        $url = '*/*/listaction';

        $achRoute = $this->getRequest()->getParam('ach_route');
        $achType = $this->getRequest()->getParam('ach_type');
        $achNumber = $this->getRequest()->getParam('ach_number');
        $achHolder = $this->getRequest()->getParam('ach_holder');
        $this->isDefaultMethod = $this->getRequest()->getParam('default');

        if (!$this->_formKeyValidator->validate($this->getRequest())) {
            return $this->resultRedirectFactory->create()->setPath('*/*/listaction');
        }

        $existingAddressData = [];

        $addressForm = $this->_formFactory->create('customer_address', 'customer_address_edit', $existingAddressData);
        $addressData = $addressForm->extractData($this->getRequest());
        $billing = $addressForm->compactData($addressData);

        $this->updateRegionData($billing);

        //----- New Payment Method --------
        $paymentMethod = array(
            'MethodName' => $achType . ' ' . substr($achNumber, -4) . ' - ' . $achHolder,
            'Created' => date('Y-m-d\TH:i:s'),
            'Modified' => date('Y-m-d\TH:i:s'),
            'Account' => $achNumber,
            'AccountType' => $achType,
            'AccountHolderName' => $achHolder ?? '',
            'Routing' => $achRoute,
            'MethodType' => 'ACH'
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
            if ($localEbizCustomerId == null) {

                try {
                    $customerInternalId = $this->addCustomer($mageCustomerId, $magCustomerData, $billing,
                        $paymentMethod);

                    if ($customerInternalId) {

                        $getCustomerTokenResult = $this->_tran->getCustomerToken($mageCustomerId);
                        // Save token in ebizcharge_token table
                        $token = $this->token;
                        $token->setMageCustId((int)$mageCustomerId);
                        $token->setEbzcCustId((int)$getCustomerTokenResult);
                        $token->save();

                        $this->_tran->runUpdateQueryCustomer('customer_entity', 'cust', 1, $customerInternalId,
                            $mageCustomerId, $getCustomerTokenResult, $mageCustomerId);

                        // add new payment method
                        $paymentMethodId = $this->addPaymentMethod($customerInternalId, $paymentMethod,
                            $getCustomerTokenResult);

                        if (!empty($paymentMethodId)) {
                            $this->messageManager->addSuccessMessage(__('Bank Account saved successfully.'));
                        } else {
                            $this->messageManager->addErrorMessage(__('Unable to obtain Method ID.'));
                        }

                    } else {
                        $this->messageManager->addErrorMessage(__('Unable to save customer payment method.'));
                    }
                } catch (\Exception $ex) {
                    $this->messageManager->addExceptionMessage($ex,
                        __('Unable to save customer payment method.' . $ex->getMessage()));
                }

            } elseif ($localEbizCustomerId != null) {
                # Case 2 Local = Yes, Live = No
                try {
                    $customerInternalId = $this->addCustomer($mageCustomerId, $magCustomerData, $billing,
                        $paymentMethod);

                    if ($customerInternalId) {
                        $getCustomerTokenResult = $this->_tran->getCustomerToken($mageCustomerId);
                        // Update token in ebizcharge_token table
                        $this->_tran->runUpdateCustomer('ebizcharge_token', $mageCustomerId, $getCustomerTokenResult);
                        $this->_tran->runUpdateQueryCustomer('customer_entity', 'cust', 1, $customerInternalId,
                            $mageCustomerId, $getCustomerTokenResult, $mageCustomerId);
                        // add new payment method
                        $paymentMethodId = $this->addPaymentMethod($customerInternalId, $paymentMethod,
                            $getCustomerTokenResult);

                        if (!empty($paymentMethodId)) {
                            $this->messageManager->addSuccessMessage(__('Bank Account saved successfully.'));
                        } else {
                            $this->messageManager->addErrorMessage(__('Unable to obtain Method ID.'));
                        }

                    } else {
                        $this->messageManager->addErrorMessage(__('Unable to save customer payment method.'));
                    }
                } catch (\Exception $ex) {
                    $this->messageManager->addExceptionMessage($ex,
                        __('Unable to save customer payment method.' . $ex->getMessage()));
                }
            } # Case 6 In all other cases default
            else {
                $this->messageManager->addErrorMessage(__('Error occured in adding process.'));
            }

        } else {
            $liveEbzcCustomerId = $this->_tran->getCustomerToken($mageCustomerId);
            # Case 5 Local = Yes, Live = Yes , Token = Same
            if (($localEbizCustomerId != null) && ($liveEbzcCustomerId != null) && ($localEbizCustomerId == $liveEbzcCustomerId)) {
                $updated  = $this->updateCustomer($mageCustomerId, $billing, $paymentMethod);
                if(!$updated) {
                    $url = '*/*/addaction';
                }

            } elseif (($localEbizCustomerId != null) && ($liveEbzcCustomerId != null) && ($localEbizCustomerId != $liveEbzcCustomerId)) {
                # Case 4 Local = Yes, Live = Yes , Token = Diff
                $this->messageManager->addErrorMessage(__('Customer already exist and token mismatch.'));
            } else {
                # Case 6 In all other cases default
                $this->messageManager->addErrorMessage(__('Error in adding process.'));
            }

        }

        return $this->resultRedirectFactory->create()->setPath($url);
    }

    /**
     * @param $mageCustomerId
     * @param $billing
     * @param $paymentMethod
     * @return bool
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
                // add payment method
                try {
                    $paymentMethodId = $this->addPaymentMethod($ebizCustomer->CustomerInternalId, $paymentMethod,
                        $ebizCustomer->CustomerToken);

                    if (!empty($paymentMethodId)) {
                        $this->messageManager->addSuccessMessage(__('Bank Account saved successfully.'));
                        return true;
                    } else {
                        $this->messageManager->addErrorMessage(__('Unable to obtain Method ID.'));
                    }
                } catch (\Exception $e) {
                    $this->messageManager->addExceptionMessage($e, str_replace('addCustomerPaymentMethod', '', $e->getMessage()));
                }
            }

        } catch (\Exception $ex) {
            $this->messageManager->addExceptionMessage($ex, __('Exception:' . $ex->getMessage()));
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
            'Phone' => $billing['telephone'] ?? '',
            'CellPhone' => $billing['telephone'] ?? '',
            'Fax' => $billing['fax'] ?? '',
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
            'Address1' => $billing['street'][0] ?? '',
            'Address2' => $billing['street'][1] ?? '',
            'City' => $billing['city'],
            'State' => $billing['region'],
            'ZipCode' => $billing['postcode'],
            'Country' => $billing['country_id']
        );
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
            if (!empty($this->isDefaultMethod)) {
                $this->_tran->setDefaultPaymentMethod($customerToken, $paymentMethodId);
            }

            return $paymentMethodId;
        }

        return false;
    }

    /**
     * update billing region data
     * @param $attributeValues
     */
    protected function updateRegionData(&$attributeValues)
    {
        if (!empty($attributeValues['region_id'])) {
            $newRegion = $this->regionCollectionFactory->create()->getItemById($attributeValues['region_id']);
            $attributeValues['region_code'] = $newRegion->getCode();
            $attributeValues['region'] = $newRegion->getDefaultName();
        }

        $regionData = [
            RegionInterface::REGION_ID => !empty($attributeValues['region_id']) ? $attributeValues['region_id'] : null,
            RegionInterface::REGION => !empty($attributeValues['region']) ? $attributeValues['region'] : null,
            RegionInterface::REGION_CODE => !empty($attributeValues['region_code'])
                ? $attributeValues['region_code']
                : null
        ];

        array_merge($attributeValues, $regionData);
    }

}
