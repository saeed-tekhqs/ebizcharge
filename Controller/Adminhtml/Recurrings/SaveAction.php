<?php
/**
 * Deletes the customer's saved payment method.
 *
 * @author      Century Business Solutions <support@centurybizsolutions.com>
 * @copyright   Copyright (c) 2020 Century Business Solutions  (www.centurybizsolutions.com)
 */

namespace Ebizcharge\Ebizcharge\Controller\Adminhtml\Recurrings;

use Ebizcharge\Ebizcharge\Model\Config;
use Ebizcharge\Ebizcharge\Model\Token;
use Ebizcharge\Ebizcharge\Model\TranApi;
use Magento\Catalog\Model\ProductFactory;
use Magento\Customer\Model\Session;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Request\Http;
use Magento\Framework\App\ResponseFactory;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Data\Form\FormKey\Validator;
use Magento\Framework\Exception\NotFoundException;
use Magento\Framework\App\RequestInterface;
use Magento\Backend\App\Action;
use Magento\Framework\UrlInterface;

class SaveAction extends Action implements HttpPostActionInterface
{
    const WRONG_REQUEST = 1;
    const WRONG_TOKEN = 2;
    const ACTION_EXCEPTION = 3;

    private $errorsMap = [];
    private $jsonFactory;
    private $fkValidator;
    private $token;
    protected $_tran;
    protected $_scopeConfig;
    protected $config;

    /**
     * @var \Ebizcharge\Ebizcharge\Model\Data
     */
    private $dataClass;

    /**
     * @var \Magento\Payment\Model\Config
     */
    private $paymentConfig;

    /**
     * @param Context $context
     * @param Session $customerSession
     * @param JsonFactory $jsonFactory
     * @param Validator $fkValidator
     * @param ScopeConfigInterface $scopeConfig
     * @param Config $config
     * @param Token $token
     * @param TranApi $tranApi
     * @param ResponseFactory $responseFactory
     * @param UrlInterface $url
     * @param ProductFactory $productFactory
     */
    public function __construct(
        Context $context,
        Session $customerSession,
        JsonFactory $jsonFactory,
        Validator $fkValidator,
        ScopeConfigInterface $scopeConfig,
        Config $config,
        Token $token,
        TranApi $tranApi,
        ResponseFactory $responseFactory,
        UrlInterface $url,
        ProductFactory $productFactory,
        \Magento\Payment\Model\Config $paymentConfig,
        \Ebizcharge\Ebizcharge\Model\Data $dataClass
    )
    {
        parent::__construct($context);
        $this->jsonFactory = $jsonFactory;
        $this->fkValidator = $fkValidator;
        $this->token = $token;
        $this->_tran = $tranApi;
        $this->responseFactory = $responseFactory;
        $this->url = $url;
        $this->_scopeConfig = $scopeConfig;
        $this->config = $config;
        $this->productFactory = $productFactory;

        $this->errorsMap = [
            self::WRONG_TOKEN => __('No token found.'),
            self::WRONG_REQUEST => __('Wrong request.'),
            self::ACTION_EXCEPTION => __('Failure. Please try again.')];
        $this->dataClass = $dataClass;
        $this->paymentConfig = $paymentConfig;
    }

    private function getProduct()
    {
        return $this->productFactory->create();
    }

    private function getPriceById($id)
    {
        $product = $this->getProduct()->load($id);
        $itemSpecialPrice = $product->getSpecialPrice();

        return (!empty($itemSpecialPrice)) ? $itemSpecialPrice : $product->getPrice();
    }

    private function getNameById($id)
    {
        return $this->getProduct()->load($id)->getName();
    }

    private function getPriceBySku($sku)
    {
        return $this->getProduct()->loadByAttribute('sku', $sku)->getPrice();
    }

    public function execute()
    {
        $post = $this->getRequest()->getPost();

        $customerId = isset($post['customer_id']) ? $post['customer_id'] : '';

        $customerInternalId = $this->dataClass->getMappedAndAddCustomer($customerId, true);

        if (isset($post['method_id'])) {
            $paymentMethodId = $post['method_id'];
        }

        $paymentMethodName = '';
        if (isset($post['payment_method_name'])) {
            $paymentMethodName = $post['payment_method_name'];
        }

        $addNewForm = $this->getRequest()->getParam('payment');
        if ((isset($addNewForm['add_new']))) {

			if($addNewForm['ebzc_option_type'] == 'credit_card'){
            list($paymentMethodId, $paymentMethodName) = $this->addNewPaymentMethod($customerInternalId, $addNewForm);
        }

			if($addNewForm['ebzc_option_type'] == 'ACH'){
				list($paymentMethodId, $paymentMethodName) = $this->addNewPaymentAccount($customerInternalId, $addNewForm);
			}
        }

        if (!empty($paymentMethodId) && !empty($customerInternalId)) {

            $ueSecurityToken = $this->_tran->getUeSecurityToken();

            try {
                $shippingMethod = $post['shipping_method'];
                $productId = $post['product_id'];
                $productPrice = $this->getPriceById($productId);
                $productName = $this->getNameById($productId);
                $amountGross = ($productPrice * $post['qty']);
                $amount = number_format((float)$amountGross, 2, '.', '');

                $schedule = $post['schedule'];
                $recIndefinitely = isset($post['rec_indefinitely']) ? 1 : 0;
                $qty = $post['qty'];
                $start = isset($post['start_date']) ? $post['start_date'] : '';
                $expire = isset($post['expire_date']) ? $post['expire_date'] : '';
                $scheduleName = $productId . '-' . $customerId . '-' . $schedule . '-' . $paymentMethodId;

                if (empty($qty)) {
                    $qty = 1;
                }

                if (!empty($start)) {
                    $start = date("Y-m-d", strtotime($start));
                }

                if ($recIndefinitely == 1) {
                    $expire = date('Y-m-d', strtotime('+10 years'));
                } else {
                    $recIndefinitely = 0;

                    if (!empty($expire)) {
                        $expire = date("Y-m-d", strtotime($expire));
                    } else {
                        $expire = date('Y-m-d', strtotime('+10 years'));
                    }
                }

                $recurringBilling = array(
                    'Amount' => $amount,
                    'Enabled' => true,
                    'Start' => $start,
                    'Expire' => $expire,
                    'Next' => $expire,
                    'Schedule' => $schedule,
                    'ScheduleName' => $scheduleName,
                    'ReceiptNote' => 'Item [' . $productId . '-' . $productName . '] recurring payment added.',
                    'ReceiptTemplateName' => false,
                    'SendCustomerReceipt' => true
                );

                $addRecurrParameters = array(
                    'securityToken' => $ueSecurityToken,
                    'customerInternalId' => $customerInternalId,
                    'paymentMethodProfileId' => $paymentMethodId,
                    'recurringBilling' => $recurringBilling
                );

                $transaction = $this->_tran->getClient()->ScheduleRecurringPayment($addRecurrParameters);
                $scheduledPaymentInternalId = $transaction->ScheduleRecurringPaymentResult;

                if (!empty($scheduledPaymentInternalId)) {
                    $recurringDates = $this->_tran->getRecurringScheduledDates($scheduledPaymentInternalId);
                    $recurringDatesSerialize = serialize($recurringDates);
                    $ebzRecurringTotal = count($recurringDates);

                    $insertQueryParameters = array(
                        'option' => 'insert',
                        'tableName' => 'ebizcharge_recurring',
                        'data' => array(
                            'rec_status' => 0,
                            'rec_indefinitely' => $recIndefinitely,
                            'mage_cust_id' => $customerId,
                            'mage_order_id' => '0',
                            'mage_item_id' => $productId,
                            'mage_parent_item_id' => $productId,
                            'mage_item_name' => $productName,
                            'qty_ordered' => $qty,
                            'eb_rec_start_date' => $start,
                            'eb_rec_end_date' => $expire,
                            'eb_rec_frequency' => $schedule,
                            'eb_rec_method_id' => $paymentMethodId,
                            'eb_rec_scheduled_payment_internal_id' => $scheduledPaymentInternalId,
                            'eb_rec_total' => $ebzRecurringTotal,
                            'eb_rec_processed' => 0,
                            'eb_rec_next' => $this->_tran->getNextRecurringDate($recurringDates),
                            'eb_rec_remaining' => $ebzRecurringTotal,
                            'eb_rec_due_dates' => $recurringDatesSerialize,
                            'billing_address_id' => $post['addressBill'],
                            'shipping_address_id' => $post['addressShip'],
                            'amount' => $amount,
                            'payment_method_name' => $paymentMethodName,
                            'shipping_method' => $shippingMethod
                        )
                    );

                    $recurringId = $this->_tran->runInsertQuery($insertQueryParameters);

                    $this->_tran->insertScheduleDates($recurringId, $recurringDates);

                    return $this->createSuccessMessage('Subscription successfully saved.');
                } else {
                    return $this->createErrorResponse('Unable to save subscription.');
                }

            } catch (\Exception $ex) {
                //return $this->createErrorResponse(self::ACTION_EXCEPTION);
                return $this->createErrorResponse('Exception: ' . $ex->getMessage());
            }
        } else {
            return $this->createErrorResponse('Unable to find payment methodID.');
        }
    }

    /**
     * @param $customerInternalId
     * @param $addNewForm
     * @return array
     */
	private function addNewPaymentAccount($customerInternalId, $addNewForm)
    {
        $paymentMethodName = $addNewForm['cc_owner_ach'] . ' ' . substr($addNewForm['cc_number_ach'], -4) . ' - ' . $addNewForm['cc_type_ach'];
        $paymentParameters = array(
            'MethodName' => $paymentMethodName,
            'Created' => date('Y-m-d\TH:i:s'),
            'Modified' => date('Y-m-d\TH:i:s'),
            'Account' => $addNewForm['cc_number_ach'],
            'AccountType' => $addNewForm['cc_type_ach'],
            'AccountHolderName' => isset($addNewForm['cc_owner_ach']) ? $addNewForm['cc_owner_ach'] : '',
            'Routing' => $addNewForm['cc_routing_ach'],
            'MethodType' => 'ACH'
        );

        $methodId = $this->addCustomerPaymentMethod($customerInternalId, $paymentParameters);

        return [$methodId, $paymentMethodName];
    }

    private function addNewPaymentMethod($customerInternalId, $addNewForm)
    {
        $paymentTypes = $this->paymentConfig->getCcTypes();

        $cardType = $addNewForm['cc_type'];

        foreach ($paymentTypes as $code => $text) {
            if ($code == $addNewForm['cc_type']) {
                $cardType = $text;
                break;
            }
        }

        $cardExpiration = $addNewForm['cc_exp_year'] . "-" . $addNewForm['cc_exp_month'];

        if ((isset($addNewForm['cc_cid']))) {
            $cardCode = $addNewForm['cc_cid'];
        } else {
            $cardCode = '';
        }

        $paymentMethodName = $cardType . ' ' . substr($addNewForm['cc_number'], -4) . ' - ' . $addNewForm['cc_owner'];
        $paymentParameters = array(
            'MethodName' => $paymentMethodName,
            'AccountHolderName' => $addNewForm['cc_owner'],
            'SecondarySort' => 1,
            'Created' => date('Y-m-d\TH:i:s'),
            'Modified' => date('Y-m-d\TH:i:s'),
            'CardCode' => $cardCode,
            'CardExpiration' => $cardExpiration,
            'CardNumber' => $addNewForm['cc_number'],
            'CardType' => $addNewForm['cc_type'],
            'Balance' => 0,
            'MaxBalance' => 0,
            'AvsStreet' => isset($addNewForm['avs_street']) ? $addNewForm['avs_street'] : '',
            'AvsZip' => isset($addNewForm['avs_zip']) ? $addNewForm['avs_zip'] : ''
        );

        $methodId = $this->addCustomerPaymentMethod($customerInternalId, $paymentParameters);

        return [$methodId, $paymentMethodName];
    }

    /**
     * @param $customerInternalId
     * @param $parameters
     */
    private function addCustomerPaymentMethod($customerInternalId, $parameters)
    {
        try {
            $paymentMethodId = $this->_tran->addCustomerPaymentMethod($customerInternalId, $parameters);

            if ($paymentMethodId !== null) {
                $this->createSuccessMessage('Payment method successfully saved.');
                return $paymentMethodId;
            } else {
                //return null;
                $this->createErrorResponse('Unable to save the payment method.');
            }

        } catch (\Exception $ex) {
            return $this->createErrorResponse('Exception: ' . $ex->getMessage());
        }
    }

    /**
     * Creates an error message, and passes it to the "Manage
     * My Payment Methods" page.
     *
     * @param int $errorCode
     * @return ResponseInterface
     */
    private function createErrorResponse($errorCode)
    {
        $this->messageManager->addErrorMessage(__($errorCode));
        return $this->_redirect('ebizcharge_ebizcharge/recurrings');
    }

    /**
     * Creates a success message, and passes it to the "Manage
     * My Payment Methods" page.
     *
     * @return ResponseInterface
     */
    private function createSuccessMessage($type)
    {
        $this->messageManager->addSuccessMessage(__($type));
        return $this->_redirect('ebizcharge_ebizcharge/recurrings');
    }
}
