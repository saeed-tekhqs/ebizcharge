<?php
/**
 * @author      Century Business Solutions <support@centurybizsolutions.com>
 * @copyright   Copyright (c) 2020 Century Business Solutions  (www.centurybizsolutions.com)
 */
declare(strict_types=1);

namespace Ebizcharge\Ebizcharge\Controller\Cards;

use Ebizcharge\Ebizcharge\Model\TranApi;
use Magento\Customer\Controller\AccountInterface;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\Data\Form\FormKey\Validator as FormKeyValidator;
use Magento\Framework\Message\ManagerInterface;
use Magento\Payment\Model\Config;

/**
 * Updates the details of the edited payment method.
 *
 * Class UpdateAction
 */
class UpdateAction implements AccountInterface, HttpGetActionInterface, HttpPostActionInterface
{
    /**
     * @var TranApi
     */
    private $tranApi;

    /**
     * @var FormKeyValidator
     */
    private $formKeyValidator;

    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @var RedirectFactory
     */
    private $redirectFactory;

    /**
     * @var Config
     */
    private $paymentConfig;

    /**
     * @var ManagerInterface
     */
    private $messageManager;

    /**
     * @param Config $paymentConfig
     * @param FormKeyValidator $formKeyValidator
     * @param ManagerInterface $messageManager
     * @param RedirectFactory $redirectFactory
     * @param RequestInterface $request
     * @param TranApi $tranApi
     */
    public function __construct(
        Config $paymentConfig,
        FormKeyValidator $formKeyValidator,
        ManagerInterface $messageManager,
        RedirectFactory $redirectFactory,
        RequestInterface $request,
        TranApi $tranApi
    ) {
        $this->paymentConfig = $paymentConfig;
        $this->formKeyValidator = $formKeyValidator;
        $this->messageManager = $messageManager;
        $this->redirectFactory = $redirectFactory;
        $this->request = $request;
        $this->tranApi = $tranApi;
    }

    /**
     * Saves the updated payment information.
     *
     * @return Redirect
     */
    public function execute()
    {
        if (!$this->formKeyValidator->validate($this->request)) {
            return $this->redirectFactory->create()->setPath('*/*/listaction');
        }

        $cid = $this->request->getParam('cid');
        $mid = $this->request->getParam('mid');

        if ($cid && $mid) {
            $ccExpMonth = $this->request->getParam('cc_exp_month');
            $ccExpYear = $this->request->getParam('cc_exp_year');
            $avsStreet = $this->request->getParam('avs_street');
            $avszip = $this->request->getParam('avs_zip');
            $ccType = $this->request->getParam('method_type');
            $ccHolder = $this->request->getParam('cc_holder');
            $default = $this->request->getParam('default');

            $ueSecurityToken = $this->tranApi->getUeSecurityToken();
            $client = $this->tranApi->getClient();

            $paymentTypes = $this->paymentConfig->getCcTypes();
            $methodName = $ccType;

            foreach ($paymentTypes as $code => $text) {
                if ($code == $ccType) {
                    $methodName = $text;
                }
            }

            try {
                $paymentMethod = $this->tranApi->getCustomerPaymentMethodProfile($cid, $mid);

                $paymentMethod->AccountHolderName = $ccHolder;
                $paymentMethod->CardNumber = 'XXXXXX' . substr($paymentMethod->CardNumber, 6);
                $paymentMethod->MethodName = $methodName . ' ' . substr($paymentMethod->CardNumber,
                        -4) . ' - ' . $ccHolder;
                $paymentMethod->CardExpiration = $ccExpYear . '-' . $ccExpMonth;
                $paymentMethod->AvsStreet = $avsStreet;
                $paymentMethod->AvsZip = $avszip;

                if ($default) {
                    $paymentMethod->SecondarySort = $default ? 0 : 1;
                }

                $updatedMethodProfile = $client->updateCustomerPaymentMethodProfile(
                    array(
                        'securityToken' => $ueSecurityToken,
                        'customerToken' => $cid,
                        'paymentMethodProfile' => $paymentMethod
                    ));

                if (isset($updatedMethodProfile->UpdateCustomerPaymentMethodProfileResult)) {
                    if (!empty($default)) {
                        $this->setDefaultMethod($cid, $mid);
                    }

                    $this->messageManager->addSuccessMessage(__('Payment method updated successfully.'));

                    return $this->redirectFactory->create()->setPath('*/*/listaction', ['_secure' => true]);
                } else {
                    $this->messageManager->addErrorMessage(__('Unable to update card.'));
                }
            } catch (\Exception $ex) {
                $this->messageManager->addExceptionMessage($ex, __('Unable to update customer card.'));
            }
        } else {
            $this->messageManager->addErrorMessage(__('Unable to update card.'));
        }

        return $this->redirectFactory->create()->setPath('*/*/listaction');
    }

    private function setDefaultMethod($customerToken, $methodId)
    {
        $setDefaultMethod = $this->tranApi->getClient()->SetDefaultCustomerPaymentMethodProfile(
            array(
                'securityToken' => $this->tranApi->getUeSecurityToken(),
                'customerToken' => $customerToken,
                'paymentMethodId' => $methodId
            ));

        if (isset($setDefaultMethod->SetDefaultCustomerPaymentMethodProfileResult)) {
            return true;
        }

        return false;
    }

}
