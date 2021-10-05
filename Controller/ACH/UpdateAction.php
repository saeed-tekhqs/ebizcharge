<?php
/**
 * Updates the details of the edited payment method.
 *
 * @author      Century Business Solutions <support@centurybizsolutions.com>
 * @copyright   Copyright (c) 2020 Century Business Solutions  (www.centurybizsolutions.com)
 */

namespace Ebizcharge\Ebizcharge\Controller\ACH;

use Ebizcharge\Ebizcharge\Model\TranApi;
use Magento\Customer\Controller\AccountInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\Data\Form\FormKey\Validator;
use Magento\Framework\Data\Form\FormKey\Validator as FormKeyValidator;
use Magento\Framework\Message\ManagerInterface;
use Magento\Store\Model\ScopeInterface;


/**
 * Update Action for ACH
 *
 * Class UpdateAction
 */
class UpdateAction implements AccountInterface
{
    /**
     * @var TranApi
     */
    private $tranApi;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var Validator
     */
    private $formKeyValidator;

    /**
     * @var RedirectFactory
     */
    private $redirectFactory;

    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @var ManagerInterface
     */
    private $messageManager;

    /**
     * @param ManagerInterface $messageManager
     * @param RequestInterface $request
     * @param RedirectFactory $redirectFactory
     * @param FormKeyValidator $formKeyValidator
     * @param ScopeConfigInterface $scopeConfig
     * @param TranApi $tranApi
     */
    public function __construct(
        ManagerInterface $messageManager,
        RedirectFactory $redirectFactory,
        RequestInterface $request,
        ScopeConfigInterface $scopeConfig,
        TranApi $tranApi,
        Validator $formKeyValidator
    ) {
        $this->messageManager = $messageManager;
        $this->redirectFactory = $redirectFactory;
        $this->request = $request;
        $this->scopeConfig = $scopeConfig;
        $this->tranApi = $tranApi;
        $this->formKeyValidator = $formKeyValidator;
    }

    /**
     * Saves the updated payment information.
     *
     * @return Redirect
     */
    public function execute()
    {
        if(!$this->formKeyValidator->validate($this->request)) {
            return $this->redirectFactory->create()->setPath('*/*/listaction');
        }

        $cid = $this->request->getParam('cid');
        $mid = $this->request->getParam('mid');

        if($cid && $mid) {
            $MethodName = $this->request->getParam('ach_method_name');
            $ach_route = $this->request->getParam('ach_route');
            $achType = $this->request->getParam('ach_type');
            $ach_number = $this->request->getParam('ach_number');
            $accountHolder = $this->request->getParam('ach_holder');
            $default = $this->request->getParam('default');

            // transaction initiation start
            $this->tranApi->setData('key', $this->scopeConfig->getValue('payment/ebizcharge_ebizcharge/sourcekey',
                ScopeInterface::SCOPE_STORE));
            $this->tranApi->setData('userid', $this->scopeConfig->getValue('payment/ebizcharge_ebizcharge/sourceid',
                ScopeInterface::SCOPE_STORE));
            $this->tranApi->setData('pin', $this->scopeConfig->getValue('payment/ebizcharge_ebizcharge/sourcepin',
                ScopeInterface::SCOPE_STORE));

            $this->tranApi->setData('software','Magento2');
            // transaction initiation end

            $ueSecurityToken = $this->tranApi->getUeSecurityToken();
            $client = $this->tranApi->getClient();

            try {
                $methodProfiles = $client->GetCustomerPaymentMethodProfile(
                    array(
                        'securityToken' => $ueSecurityToken,
                        'customerToken' => $cid,
                        'paymentMethodId' => $mid
                    ));

                $paymentMethod = $methodProfiles->GetCustomerPaymentMethodProfileResult;
                //$paymentMethod->Account = 'XXXXX' . substr($paymentMethod->Account, 4);
                $paymentMethod->AccountType = $achType;
                $paymentMethod->AccountHolderName = $accountHolder ?? '';
                $paymentMethod->MethodName = $achType . ' ' . substr($paymentMethod->Account,
                        -4) . ' - ' . $accountHolder;
                $paymentMethod->Modified = date('Y-m-d\TH:i:s');

                if($default) {
                    $paymentMethod->SecondarySort = $default ? 0 : 1;
                }

                $updatedMethodProfile = $client->updateCustomerPaymentMethodProfile(
                    array(
                        'securityToken' => $ueSecurityToken,
                        'customerToken' => $cid,
                        'paymentMethodProfile' => $paymentMethod
                    ));

                if(isset($updatedMethodProfile->UpdateCustomerPaymentMethodProfileResult)) {
                    if(!empty($default)) {
                        $this->setDefaultMethod($cid, $mid);
                    }

                    $this->messageManager->addSuccessMessage(__('Bank account has been updated successfully.'));
                    return $this->redirectFactory->create()->setPath('*/*/listaction', ['_secure' => true]);
                } else {
                    $this->messageManager->addErrorMessage(__('Unable to update bank account.'));
                }
            } catch (\Exception $ex) {
                $this->messageManager->addExceptionMessage($ex, __('Unable to update bank account.'));
            }
        } else {
            $this->messageManager->addErrorMessage(__('Unable to update card.'));
        }

        return $this->redirectFactory->create()->setPath('*/*/listaction');
    }

    /**
     * This function sets default method
     *
     * @param $customerToken
     * @param $methodId
     * @return bool
     */
    private function setDefaultMethod($customerToken, $methodId)
    {
        $setDefaultMethod = $this->tranApi->getClient()->SetDefaultCustomerPaymentMethodProfile(
            array(
                'securityToken' => $this->tranApi->getUeSecurityToken(),
                'customerToken' => $customerToken,
                'paymentMethodId' => $methodId
            ));

        if(isset($setDefaultMethod->SetDefaultCustomerPaymentMethodProfileResult)) {
            return true;
        }

        return false;
    }
}
