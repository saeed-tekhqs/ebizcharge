<?php
/**
 * Updates the details of the edited payment method.
 *
 * @author      Century Business Solutions <support@centurybizsolutions.com>
 * @copyright   Copyright (c) 2020 Century Business Solutions  (www.centurybizsolutions.com)
 */

namespace Ebizcharge\Ebizcharge\Controller\Recurrings;

use Ebizcharge\Ebizcharge\Api\RecurringRepositoryInterface as RecurringRepository;
use Ebizcharge\Ebizcharge\Model\TranApi;
use Magento\Customer\Controller\AccountInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\Data\Form\FormKey\Validator as FormKeyValidator;
use Magento\Framework\Message\ManagerInterface;
use Magento\Payment\Model\Config;

/**
 * update action class
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
     * @var RequestInterface
     */
    private $request;

    /**
     * @var Config
     */
    private $paymentConfig;

    /**
     * @var RecurringRepository
     */
    private $recurringRepository;

    /**
     * @var FormKeyValidator
     */
    private $formKeyValidator;

    /**
     * @var RedirectFactory
     */
    private $redirectFactory;

    /**
     * @var ManagerInterface
     */
    private $messageManager;

    /**
     * @param Config $paymentConfig
     * @param FormKeyValidator $formKeyValidator
     * @param ManagerInterface $messageManager
     * @param RecurringRepository $recurringRepository
     * @param RedirectFactory $redirectFactory
     * @param RequestInterface $request
     * @param TranApi $tranApi
     */
    public function __construct(
        Config $paymentConfig,
        FormKeyValidator $formKeyValidator,
        ManagerInterface $messageManager,
        RecurringRepository $recurringRepository,
        RedirectFactory $redirectFactory,
        RequestInterface $request,
        TranApi $tranApi
    ) {
        $this->paymentConfig = $paymentConfig;
        $this->tranApi = $tranApi;
        $this->request = $request;
        $this->recurringRepository = $recurringRepository;
        $this->formKeyValidator = $formKeyValidator;
        $this->redirectFactory = $redirectFactory;
        $this->messageManager = $messageManager;
    }

    /**
     * Saves the updated payment information.
     *
     * @return mixed
     */
    public function execute()
    {
        $recurringId = $this->request->getParam('table_rec_id');
        $cid = $this->request->getParam('cid');
        $paymentMethodId = $this->request->getParam('method_id');
        $oldMethodId = $this->request->getParam('eb_rec_method_id');
        $schedulePaymentInternalId = trim($this->request->getParam('mid'));
        $custIntId = $this->request->getParam('custIntId');
        $paymentErrorReturnUrl = '/cid/' . $cid . '/mid/' . $schedulePaymentInternalId . '/';
        $paymentMethodName = trim($this->request->getParam('payment_method_name'));

        if (!$this->formKeyValidator->validate($this->request)) {
            return $this->redirectFactory->create()->setPath('*/*/listaction');
        }

        $addNewForm = $this->request->getParam('payment');

        if ((isset($addNewForm['add_new']))) {
            $paymentTypes = $this->paymentConfig->getCcTypes();
            $cardType = $addNewForm['cc_type'];

            foreach ($paymentTypes as $code => $text) {
                if ($code == $addNewForm['cc_type']) {
                    $cardType = $text;
                }
            }

            $cardExpiration = $addNewForm['cc_exp_year'] . "-" . $addNewForm['cc_exp_month'];

            if ((isset($addNewForm['cc_cid']))) {
                $cc_cid = $addNewForm['cc_cid'];
            } else {
                $cc_cid = '';
            }

            $paymentMethodName = $cardType . ' ' . substr($addNewForm['cc_number'],
                    -4) . ' - ' . $addNewForm['cc_owner'];
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
            );

            $paymentMethodId = $this->addCustomerPaymentMethod($custIntId, $paymentParameters, $paymentErrorReturnUrl);
        }

        if ($schedulePaymentInternalId) {
            $ueSecurityToken = $this->tranApi->getUeSecurityToken();
            $client = $this->tranApi->getClient();

            try {
                $amount = $this->request->getParam('amount');
                $schedule = $this->request->getParam('schedule');
                $enabled = $this->request->getParam('enabled');
                $rec_indefinitely = $this->request->getParam('rec_indefinitely');
                $qty = $this->request->getParam('qty');
                $start = $this->request->getParam('start_date');
                $expire = $this->request->getParam('expire_date');
                $repeatCount = $this->request->getParam('repeatcount');
                $scheduleName = $this->request->getParam('schedulename');
                $sendCustomerReceipt = $this->request->getParam('sendcustomerreceipt');
                $receiptNote = $this->request->getParam('receiptnote');

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
                if (!empty($methodId) && $oldMethodId !== $paymentMethodId) {
                    $paymentMethodProfileStatus = $this->tranApi->modifyRecurringPaymentMethod($paymentMethodId,
                        $schedulePaymentInternalId);
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
                    'scheduledPaymentInternalId' => $schedulePaymentInternalId,
                    'recurringBilling' => $recurringBilling
                );

                $modifyScheduled = $client->ModifyScheduledRecurringPayment_RecurringBilling($recurringObject);

                if (isset($modifyScheduled->ModifyScheduledRecurringPayment_RecurringBillingResult) && $paymentMethodProfileStatus == 1) {
                    $this->messageManager->addSuccessMessage(__('Subscription changes successfully saved.'));

                    $recurringDates = $this->tranApi->getRecurringScheduledDates($schedulePaymentInternalId);

                    $recurringDatesSerialize = serialize($recurringDates);
                    $totalRecurrings = count($recurringDates);

                    $nextRecurringDate = $this->tranApi->getNextRecurringDate($recurringDates);

                    try {
                        $recurring = $this->recurringRepository->getById($schedulePaymentInternalId,
                            'eb_rec_scheduled_payment_internal_id');
                        $updatedRecurring = $recurring->setRecIndefinitely((int)$rec_indefinitely)
                            ->setEbRecFrequency($schedule)
                            ->setQtyOrdered((string)$qty)
                            ->setEbRecStartDate(trim($start))
                            ->setEbRecEndDate(trim($expire))
                            ->setEbRecMethodId($paymentMethodId)
                            ->setEbRecTotal($totalRecurrings)
                            ->setEbRecProcessed('0')
                            ->setEbRecNext($nextRecurringDate)
                            ->setEbRecRemaining($totalRecurrings)
                            ->setEbRecDueDates($recurringDatesSerialize)
                            ->setAmount($amount)
                            ->setPaymentMethodName($paymentMethodName);
                        $this->recurringRepository->save($updatedRecurring);

                        $this->tranApi->insertScheduleDates($recurringId, $recurringDates);

                    } catch (\Exception $e) {
                        $this->messageManager->addExceptionMessage($e, __('Unable to update subscription.'));
                    }
                    return $this->redirectFactory->create()->setPath('*/*/listaction', ['_secure' => true]);
                }
            } catch (\Exception $ex) {
                $this->messageManager->addExceptionMessage($ex, __('Unable to update subscription.'));
            }
        }
        $this->messageManager->addErrorMessage(__('Unable to update subscription.'));
        return $this->redirectFactory->create()->setPath('*/*/listaction');
    }

    /**
     * @param $customerInternalId
     * @param $parameters
     * @param $paymentErrorReturnUrl
     * @return Redirect
     */
    private function addCustomerPaymentMethod($customerInternalId, $parameters, $paymentErrorReturnUrl)
    {
        try {
            $paymentMethodId = $this->tranApi->addCustomerPaymentMethod($customerInternalId, $parameters);

            if ($paymentMethodId !== null) {
                $this->messageManager->addSuccessMessage(__('Payment method successfully saved.'));
                return $paymentMethodId;
            }

        } catch (\Exception $ex) {
            $this->messageManager->addErrorMessage(__('Exception: ' . $ex->getMessage()));
        }
        $this->messageManager->addErrorMessage(__('Unable to save the payment method.'));
        return $this->redirectFactory->create()->setPath('*/*/editaction' . $paymentErrorReturnUrl);
    }
}
