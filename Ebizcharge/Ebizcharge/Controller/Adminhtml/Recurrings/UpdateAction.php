<?php
/**
 * Deletes the customer's saved payment method.
 *
 * @author      Century Business Solutions <support@centurybizsolutions.com>
 * @copyright   Copyright (c) 2020 Century Business Solutions  (www.centurybizsolutions.com)
 */

namespace Ebizcharge\Ebizcharge\Controller\Adminhtml\Recurrings;

use Ebizcharge\Ebizcharge\Api\RecurringRepositoryInterface;
use Ebizcharge\Ebizcharge\Model\TranApi;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\Request\Http;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Data\Form\FormKey\Validator;
use Magento\Payment\Model\Config as PaymentConfig;

class UpdateAction extends Action implements HttpGetActionInterface,HttpPostActionInterface
{
    const WRONG_REQUEST = 1;
    const WRONG_TOKEN = 2;
    const ACTION_EXCEPTION = 3;

    /**
     * @var array
     */
    private $errorsMap = [];

    /**
     * @var Validator
     */
    private $fkValidator;

    /**
     * @var TranApi
     */
    protected $_tran;

    /**
     * @var PaymentConfig
     */
    private $paymentConfig;

    /**
     * @var RecurringRepositoryInterface
     */
    private $recurringRepository;

    /**
     * @param Context $context
     * @param JsonFactory $jsonFactory
     * @param Validator $fkValidator
     * @param TranApi $tranApi
     * @param RecurringRepositoryInterface $recurringRepository
     * @param PaymentConfig $paymentConfig
     */
    public function __construct(
        Context $context,
        PaymentConfig $paymentConfig,
        RecurringRepositoryInterface $recurringRepository,
        TranApi $tranApi,
        Validator $fkValidator
    ) {
        parent::__construct($context);
        $this->paymentConfig = $paymentConfig;
        $this->recurringRepository = $recurringRepository;
        $this->_tran = $tranApi;
        $this->fkValidator = $fkValidator;

        $this->errorsMap = [
            self::WRONG_TOKEN => __('No token found.'),
            self::WRONG_REQUEST => __('Wrong request.'),
            self::ACTION_EXCEPTION => __('Deletion failure. Please try again.')
        ];
    }

    public function execute()
    {
        if(!$this->_request instanceof Http) {
            $this->createErrorResponse($this->errorsMap[self::WRONG_REQUEST]);
        }

        if(!$this->fkValidator->validate($this->_request)) {
            $this->createErrorResponse($this->errorsMap[self::WRONG_TOKEN]);
        }

        $recurringId = $this->getRequest()->getParam('table_rec_id');
        $custIntId = $this->getRequest()->getParam('custIntId');
        $schedulePaymentInternalId = $this->getRequest()->getParam('mid');
        $methodId = $this->getRequest()->getParam('method_id');
        $oldMethodId = $this->getRequest()->getParam('eb_rec_method_id');
        $paymentMethodName = trim($this->getRequest()->getParam('payment_method_name'));
        $shippingMethod = trim($this->getRequest()->getParam('shipping_method'));
        $shippingId = trim($this->getRequest()->getParam('addressShip'));
        $billingId = trim($this->getRequest()->getParam('addressBill'));

        $addNewForm = $this->getRequest()->getParam('payment');

        if ((isset($addNewForm['add_new']))) {

            if ($addNewForm['ebzc_option_type'] == 'credit_card') {

                $cardType = $addNewForm['cc_type'];
                $cardType = $this->paymentConfig->getCcTypes()[$cardType] ?? $cardType;

                $cardExpiration = $addNewForm['cc_exp_year'] . "-" . $addNewForm['cc_exp_month'];

                if ((isset($addNewForm['cc_cid']))) {
                    $cc_cid = $addNewForm['cc_cid'];
                } else {
                    $cc_cid = '';
                }

                $paymentMethodName = $cardType . ' ' . substr($addNewForm['cc_number'], -4) . ' - ' . $addNewForm['cc_owner'];
                $paymentParameters = array(
                    'MethodName' => $paymentMethodName,
                    'AccountHolderName' => $addNewForm['cc_owner'],
                    'SecondarySort' => 1,
                    'Created' => date('Y-m-d\TH:i:s'),
                    'Modified' => date('Y-m-d\TH:i:s'),
                    'CardCode' => $cc_cid,
                    'CardExpiration' => $cardExpiration,
                    'CardNumber' => $addNewForm['cc_number'],
                    'CardType' => $addNewForm['cc_type'],
                    'Balance' => 0,
                    'MaxBalance' => 0,
                    'AvsStreet' => isset($addNewForm['avs_street']) ? $addNewForm['avs_street'] : '',
                    'AvsZip' => isset($addNewForm['avs_zip']) ? $addNewForm['avs_zip'] : ''
                );

                $methodId = $this->addCustomerPaymentMethod($custIntId, $paymentParameters);
            }
        }

        if ($schedulePaymentInternalId) {
            $ueSecurityToken = $this->_tran->getUeSecurityToken();
            $client = $this->_tran->getClient();

            try {
                $amount = $this->getRequest()->getParam('amount');
                $schedule = $this->getRequest()->getParam('schedule');
                $enabled = $this->getRequest()->getParam('enabled');
                $rec_indefinitely = $this->getRequest()->getParam('rec_indefinitely');
                $rec_indefinitely_db = $this->getRequest()->getParam('rec_indefinitely_db');
                $qty = $this->getRequest()->getParam('qty');
                $start = $this->getRequest()->getParam('start_date');
                $expire = $this->getRequest()->getParam('expire_date');
                $repeatCount = $this->getRequest()->getParam('repeatcount');
                $scheduleName = $this->getRequest()->getParam('schedulename');
                $sendCustomerReceipt = $this->getRequest()->getParam('sendcustomerreceipt');
                $receiptNote = $this->getRequest()->getParam('receiptnote');
                if ($qty == 0) {
                    $qty = 1;
                }

                if (!empty($start)) {
                    $start = date("Y-m-d", strtotime($start));
                }

                if ($rec_indefinitely == 1) {
                    $expire = date('Y-m-d', strtotime('+10 years'));
                } else {
                    $rec_indefinitely = 0;
                    if (!empty($expire)) {
                        $expire = date("Y-m-d", strtotime($expire));
                    } else {
                        $expire = date('Y-m-d', strtotime('+10 years'));
                    }
                }

                // update payment method only when there is a changes
                $paymentMethodProfileStatus = 1;
                if (!empty($methodId) && $oldMethodId !== $methodId) {
                    $paymentMethodProfileStatus = $this->_tran->modifyRecurringPaymentMethod($methodId, $schedulePaymentInternalId);
                }

                $amount = number_format((float)trim($amount * $qty), 2, '.', '');

                $recurringBilling = array(
                    'Amount' => $amount,
                    'Enabled' => true,
                    'Start' => trim($start),
                    'Expire' => trim($expire),
                    'Next' => trim($expire),
                    'Schedule' => trim($schedule),
                    'ScheduleName' => trim($scheduleName),
                    'ReceiptNote' => $receiptNote,
                    'ReceiptTemplateName' => false,
                    'SendCustomerReceipt' => true
                );
                $recurringObject = array(
                    'securityToken' => $ueSecurityToken,
                    'scheduledPaymentInternalId' => trim($schedulePaymentInternalId),
                    'recurringBilling' => $recurringBilling
                );

                $modifyScheduled = $client->ModifyScheduledRecurringPayment_RecurringBilling($recurringObject);

                if (isset($modifyScheduled->ModifyScheduledRecurringPayment_RecurringBillingResult) && $paymentMethodProfileStatus == 1) {
                    ///////////////// update table/////////////
                    $recurringDates = $this->_tran->getRecurringScheduledDates(trim($schedulePaymentInternalId));
                    $recurringDatesSerialize = serialize($recurringDates);
                    $totalRecurrings = count($recurringDates);

                    $nextRecurringDate = $this->_tran->getNextRecurringDate($recurringDates);

                    try {
                        $recordToUpdate = $this->recurringRepository->getById($schedulePaymentInternalId, 'eb_rec_scheduled_payment_internal_id');
                        $recordToUpdate->setRecIndefinitely((int)$rec_indefinitely)
                            ->setEbRecFrequency($schedule)
                            ->setQtyOrdered((string)$qty)
                            ->setEbRecStartDate((string)trim($start))
                            ->setEbRecEndDate((string)trim($expire))
                            ->setEbRecMethodId($methodId)
                            ->setEbRecTotal((int)$totalRecurrings)
                            ->setEbRecProcessed((int)0)
                            ->setEbRecNext((string)$nextRecurringDate)
                            ->setEbRecRemaining((int)$totalRecurrings)
                            ->setEbRecDueDates((string)$recurringDatesSerialize)
                            ->setAmount((string)$amount)
                            ->setPaymentMethodName((string)$paymentMethodName)
                            ->setShippingMethod((string)$shippingMethod)
                            ->setBillingAddressId((int)$billingId)
                            ->setShippingAddressId((int)$shippingId);
                        $this->recurringRepository->save($recordToUpdate);

                        $this->_tran->insertScheduleDates($recurringId, $recurringDates);

                        return $this->createSuccessMessage('Subscription changes successfully saved.');

                    } catch (\Exception $e) {
                        $this->_tran->ebizLog()->crit($e->getMessage());
                        return $this->createErrorResponse('Exception: ' . $e->getMessage());
                    }

                } else {
                    return $this->createErrorResponse('Unable to update changes.');
                }
            } catch (\Exception $ex) {
                $this->_tran->ebizLog()->crit($ex->getMessage());
                return $this->createErrorResponse('Exception: ' . $ex->getMessage());
            }
        } else {
            return $this->createErrorResponse('Unable to find method ID.');
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

    /**
     * @param $customerInternalId
     * @param $parameters
     * @return ResponseInterface|string|null
     */
    private function addCustomerPaymentMethod($customerInternalId, $parameters)
    {
        try {
            $paymentMethodId = $this->_tran->addCustomerPaymentMethod($customerInternalId, $parameters);

            if ($paymentMethodId !== null) {
                $this->createSuccessMessage('Payment method successfully saved.');
                return $paymentMethodId;
            } else {
                $this->createSuccessMessage('Unable to save the payment method.');
            }

        } catch (\Exception $ex) {
            return $this->createErrorResponse('Exception: ' . $ex->getMessage());
        }
    }

    /**
     * Creates an error message, and passes it to the "Manage
     * My Payment Methods" page.
     *
     * @param int|string $errorCode
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
