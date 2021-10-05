<?php
/**
 * Handles all the payment functions - authorize, capture, refund, etc.
 * @author      Century Business Solutions <support@centurybizsolutions.com>
 * @copyright   Copyright (c) 2020 Century Business Solutions  (www.centurybizsolutions.com)
 */

namespace Ebizcharge\Ebizcharge\Model;

use Magento\Backend\Model\Auth\Session as AdminSession;
use Magento\Backend\Model\Session\Quote as BackendQuote;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Customer\Model\SessionFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Payment\Model\InfoInterface;
use Magento\Payment\Model\Method\AbstractMethod;
use Magento\Payment\Model\MethodInterface;
use Magento\Quote\Api\Data\CartInterface as CartInterface;

/**
 * Payment model
 *
 * Class Payment
 * @package Ebizcharge\Ebizcharge\Model
 */
class Payment
{
    const CODE = 'ebizcharge_ebizcharge';
    const ACH = 'ACH';

    /**
     * @var string
     */
    private $authMode = 'auto';

    /**
     * @var AdminSession
     */
    private $backendAuthSession;

    /**
     * @var CustomerSession
     */
    private $customerSession;

    /**
     * @var Token
     */
    private $token;

    /**
     * @var TranApi
     */
    private $tranApi;

    /**
     * @var Config
     */
    private $ebizConfig;

    /**
     * @var BackendQuote
     */
    private $backendQuote;

    /**
     * @param AdminSession $backendAuthSession
     * @param BackendQuote $backendQuote
     * @param Config $ebizConfig
     * @param SessionFactory $customerSession
     * @param Token $token
     * @param TranApi $tranApi
     */
    public function __construct(
        AdminSession $backendAuthSession,
        BackendQuote $backendQuote,
        Config $ebizConfig,
        SessionFactory $customerSession,
        Token $token,
        TranApi $tranApi
    )
    {
        $this->backendAuthSession = $backendAuthSession;
        $this->backendQuote = $backendQuote;
        $this->ebizConfig = $ebizConfig;
        $this->customerSession = $customerSession->create();
        $this->token = $token;
        $this->tranApi = $tranApi;
    }

    /**
     * Authorizes payment.
     *
     * @param InfoInterface $payment
     * @param $amount
     * @return Payment
     * @throws LocalizedException
     */
    public function authorize(InfoInterface $payment, $amount)
    {
        /**------------- Downloading orders from admin side  -----------------**/
        if($payment->getAdditionalInformation('ebzc_option') == 'downloadorder') {
            return $this->downloadOrders($payment, $amount);
        }
        // set general payment data
        $this->tranApi->setPaymentData($payment, $amount);

        $order = $payment->getOrder();

        if(!empty($order)) {
            /**--- set order general data ---*/
            $this->tranApi->setOrderData($payment);
            $this->tranApi->setOrderShipping($order);
            $this->tranApi->setOrderBilling($order);

            foreach ($order->getAllVisibleItems() as $item) {
                if(($item->getPrice() > 0) || ($item->getProductType() == 'virtual')) {
                    $this->tranApi->addLine($item);
                    $this->tranApi->addLineItem($item);
                }
            }

            if($this->tranApi->getOrderData()['customerId'] == null) {
                $this->tranApi->setGuestCustomer();
            }
        }
        if($this->ebizConfig->isAuthorizeOnly() == MethodInterface::ACTION_AUTHORIZE && $this->authMode != 'capture') {
            $this->tranApi->setCommand('authonly');
        } else {
            $this->tranApi->setCommand('sale');
        }

        // get magento customer session
        if(!$order->getCustomerId()) {
            // Processing a guest checkout
            $this->tranApi->runTransaction();
        } else {
            // For loggedin Customer
            if($payment->getAdditionalInformation('ebzc_option') == 'new') {
                //new method added by customer
                if($this->ebizConfig->getPaymentSavePayment() || $payment->getAdditionalInformation('ebzc_save_payment')) {
                    if(($payment->getAdditionalInformation('ebzc_cust_id') == null)) {
                        $this->tranApi->tokenProcess($order->getCustomerId(), $payment);
                    } else {
                        // SearchCustomer, AddPaymentMethod
                        $this->tranApi->newPaymentProcess($order->getCustomerId(),
                            $payment->getAdditionalInformation('ebzc_cust_id'), $payment);
                    }
                } else {
                    // Processing a guest checkout
                    $this->tranApi->runTransaction();
                }
            } elseif($payment->getAdditionalInformation('ebzc_option') == 'saved') {
                // Existing payment method selected by customer
                $this->tranApi->savedProcess($payment->getAdditionalInformation('ebzc_cust_id'),
                    $payment->getAdditionalInformation('ebzc_method_id'), $payment);
            } elseif($payment->getAdditionalInformation('ebzc_option') == 'update') {
                // Existing payment method selected by customer
                $this->tranApi->updateProcess($payment->getAdditionalInformation('ebzc_cust_id'),
                    $payment->getAdditionalInformation('ebzc_method_id'), $payment);
            } elseif($payment->getAdditionalInformation('ebzc_option') == 'paylater') {
                // payment will be paid later on gataway
                if($payment->getAdditionalInformation('ebzc_paylater_payment') == 1) {
                    $payment->setIsTransactionClosed(0)->setTransactionAdditionalInfo('trans_id', 'paylater');
                    $payment->setStatus(AbstractMethod::STATUS_APPROVED);
                    return $this;
                } else {
                    throw new LocalizedException(__('Please select Pay Later checkbox to avail this functionality!'));
                }

            } elseif($payment->getAdditionalInformation('ebzc_option') == 'recurring') {

                $deductableAmount = ($amount - $payment->getAdditionalInformation('excludeAmount'));
                if($deductableAmount <= 0) {
                    $payment->setIsTransactionClosed(0)->setTransactionAdditionalInfo('trans_id', 'recurring');
                    $payment->setStatus(AbstractMethod::STATUS_APPROVED);
                    return $this;
                } elseif($deductableAmount > 0) {
                    $payment->getMethodInstance()->getInfoInstance()->setExcludeAmount($payment->getAdditionalInformation('excludeAmount'));

                    $this->tranApi->runCustomerTransaction($payment->getAdditionalInformation('ebzc_cust_id'),
                        $payment->getAdditionalInformation('ebzc_method_id'), $payment, 1);
                } else {
                    return $this;
                }
            } else {
                //first time processing the transaction
                if($this->ebizConfig->getPaymentSavePayment() || $payment->getAdditionalInformation('ebzc_save_payment')) {
                    // AddCustomer, AddPaymentMethod
                    $this->tranApi->tokenProcess($order->getCustomerId(), $payment);
                } else {
                    // Processing a guest checkout
                    $this->tranApi->runTransaction();
                }
            }
        }

        // store response variables
        $payment->setCcApproval($this->tranApi->getAuthorizeData()['authcode'])
            ->setCcTransId($this->tranApi->getAuthorizeData()['refnum'])
            ->setCcAvsStatus($this->tranApi->getAuthorizeData()['avs_result_code'])
            ->setCcCidStatus($this->tranApi->getAuthorizeData()['cvv2_result_code']);

        // add the special ebzc fields to the database
        $payment->getMethodInstance()->getInfoInstance()->setEbzcCustId($payment->getAdditionalInformation('ebzc_cust_id'));
        $payment->getMethodInstance()->getInfoInstance()->setEbzcMethodId($payment->getAdditionalInformation('ebzc_method_id'));
        $payment->getMethodInstance()->getInfoInstance()->setEbzcSavePayment($payment->getAdditionalInformation('ebzc_save_payment'));
        $payment->getMethodInstance()->getInfoInstance()->setEbzcOption($payment->getAdditionalInformation('ebzc_option'));
        $payment->getMethodInstance()->getInfoInstance()->setEbzcAvsStreet($payment->getAdditionalInformation('ebzc_avs_street'));
        $payment->getMethodInstance()->getInfoInstance()->setEbzcAvsZip($payment->getAdditionalInformation('ebzc_avs_zip'));

        if($this->tranApi->getAuthorizeData()['resultcode'] == 'A') {
            if($this->ebizConfig->isAuthorizeOnly() == MethodInterface::ACTION_AUTHORIZE) {
                $payment->setLastTransId('0');
            } else {
                $payment->setLastTransId($this->tranApi->getAuthorizeData()['refnum']);
            }

            if(!$payment->getParentTransactionId() || $this->tranApi->getAuthorizeData()['refnum'] != $payment->getParentTransactionId()) {
                $payment->setTransactionId($this->tranApi->getAuthorizeData()['refnum']);
            }

            $payment->setIsTransactionClosed(0)
                ->setTransactionAdditionalInfo('trans_id', $this->tranApi->getAuthorizeData()['refnum']);

            $payment->setStatus(AbstractMethod::STATUS_APPROVED);

        } elseif($this->tranApi->getAuthorizeData()['resultcode'] == 'D') {
            throw new LocalizedException(__('Payment authorization transaction has been declined:  ' . $this->tranApi->getPaymentError()['error']));
        } else {
            throw new LocalizedException(__('Payment authorization error:  ' . $this->tranApi->getPaymentError()['error'] . '(' . $this->tranApi->getPaymentError()['errorcode'] . ')'));
        }

        return $this;
    }

    /**
     * Processes quickSale.
     *
     * @param InfoInterface $payment
     * @param $amount
     * @return Payment
     * @throws LocalizedException
     */
    public function quickSale(InfoInterface $payment, $amount)
    {
        if(!$payment->getLastTransId()) {
            throw new LocalizedException(__('Unable to find previous transaction to reference'));
        }
        // initialize transaction object
        $tran = $this->tranApi;
        $tran->setData('command', 'capture');
        $tran->setData('amount', $amount);

        $orderid = $payment->getOrder()->getIncrementId();

        $paymentDescription = "Order #" . $orderid;
        if($this->ebizConfig->getPaymentDescription()) {
            $paymentDescription = str_replace('[orderid]', $orderid, $this->ebizConfig->getPaymentDescription());
        }
        $tran->setData('description', $paymentDescription);

        $tran->setTransactionData($payment);

        $tran->runTransaction();

        if($tran->getData('resultcode') == 'A') {
            if($tran->getData('refnum')) {
                $payment->setLastTransId($tran->getData('refnum'));
            }
            $payment->setStatus(AbstractMethod::STATUS_APPROVED);

            if(!$payment->getParentTransactionId() ||
                $tran->getData('refnum') != $payment->getParentTransactionId()) {
                $payment->setTransactionId($tran->getData('refnum'));
            }
            $payment->setIsTransactionClosed(0)
                ->setTransactionAdditionalInfo('trans_id', $tran->getData('refnum'));
        } elseif($tran->getData('resultcode') == 'D') {
            throw new LocalizedException(__('Payment authorization transaction has been declined:  ' . $tran->getData('error')));
        } else {
            throw new LocalizedException(__('Payment authorization error:  ' . $tran->getData('error') . '(' . $tran->getData('errorcode') . ')'));
        }

        return $this;
    }

    /**
     * Refunds payment.
     *
     * @param InfoInterface $payment
     * @param $amount
     * @return Payment
     * @throws LocalizedException
     */
    public function refund(InfoInterface $payment, $amount)
    {
        if(!$payment->getLastTransId()) {
            throw new LocalizedException(__('Unable to find previous transaction to reference'));
        }

        $error = false;
        $tran = $this->tranApi;

        $tran->setData('command', 'refund');
        $tran->setData('amount', $amount);

        $orderId = $payment->getOrder()->getIncrementId();

        $paymentDescription = "Order #" . $orderId;
        if($this->ebizConfig->getPaymentDescription()) {
            $paymentDescription = str_replace('[orderid]', $orderId, $this->ebizConfig->getPaymentDescription());
        }
        $tran->setData('description', $paymentDescription);

        $tran->setTransactionData($payment);

        if(!$tran->refundTransaction()) {
            $payment->setStatus(AbstractMethod::STATUS_ERROR);
            throw new LocalizedException(__('Payment Declined: ' . $tran->getData('error') . $tran->getData('errorcode')));
        } else {
            $payment->setStatus(AbstractMethod::STATUS_APPROVED);
            if($tran->getData('refnum') != $payment->getParentTransactionId()) {
                $payment->setTransactionId($tran->getData('refnum'));
            }
            $shouldCloseCaptureTransaction = $payment->getOrder()->canCreditmemo() ? 0 : 1;
            $payment->setIsTransactionClosed(1)
                ->setShouldCloseParentTransaction($shouldCloseCaptureTransaction)
                ->setTransactionAdditionalInfo('trans_id', $tran->getData('refnum'));
        }

        if($error !== false) {
            throw new LocalizedException($error);
        }
        return $this;
    }

    /**
     * Captures payment.
     *
     * @param InfoInterface
     * @param float
     * @return Payment
     * @throws \Exception
     * n authorised -> invoiced -> online capture
     */
    public function capture($payment, $amount)
    {
        // we have already captured the original auth,  we need to do full sale
        if($payment->getLastTransId() && $payment->getOrder()->getTotalPaid() > 0) {
            return $this->quickSale($payment, $amount);
        }
        // if we don't have a transid than we are need to authorize
        if(!$payment->getParentTransactionId()) {
            $this->authMode = 'capture';
            return $this->authorize($payment, $amount);
        }

        $tran = $this->tranApi;
        $tran->setData('command', 'capture');
        $tran->setData('amount', $amount);

        $orderId = $payment->getOrder()->getIncrementId();

        $paymentDescription = "Order #" . $orderId;
        if($this->ebizConfig->getPaymentDescription()) {
            $paymentDescription = str_replace('[orderid]', $orderId, $this->ebizConfig->getPaymentDescription());
        }
        $tran->setData('description', $paymentDescription);

        $tran->setTransactionData($payment);

        $tran->runTransaction();

        // look at result code
        if($tran->getData('resultcode') == 'A') {
            $payment->setStatus(AbstractMethod::STATUS_APPROVED);
            $payment->setLastTransId($tran->getData('refnum'));

            if(!$payment->getParentTransactionId() ||
                $tran->getData('refnum') != $payment->getParentTransactionId()) {
                $payment->setTransactionId($tran->getData('refnum'));
            }
            $payment->setIsTransactionClosed(0)
                ->setTransactionAdditionalInfo('trans_id', $tran->getData('refnum'));

            return $this;
        } elseif($tran->getData('resultcode') == 'D') {
            throw new LocalizedException(__('Payment authorization transaction has been declined: ' . $tran->getData('error')));
        } else {
            throw new LocalizedException(__('Payment authorization error: ' . $tran->getData('error') . '(' . $tran->getData('errorcode') . ')'));
        }
    }

    public function canVoid(): bool
    {
        return true;
    }

    /**
     * Voids transaction.
     *
     * @param InfoInterface $payment
     * @return Payment
     * @throws LocalizedException
     */

    public function void(InfoInterface $payment)
    {
        if($payment->getCcTransId()) {
            $order = $payment->getOrder();
            $tran = $this->tranApi;
            $tran->setData('amount', $order->getGrandTotal());
            $tran->setData('command', 'creditvoid');

            $tran->setTransactionData($payment);

            $tran->runTransaction();

            if($tran->getData('resultcode') == 'A') {
                $payment->setStatus(AbstractMethod::STATUS_SUCCESS);
            } elseif($tran->getData('resultcode') == 'D') {
                $payment->setStatus(AbstractMethod::STATUS_ERROR);
                throw new LocalizedException(__('Payment authorization transaction has been declined: ' . $tran->getData('error')));
            } else {
                $payment->setStatus(AbstractMethod::STATUS_ERROR);
                throw new LocalizedException(__('Payment authorization error: ' . $tran->getData('error') . '(' . $tran->getData('errorcode') . ')'));
            }
        } else {
            $payment->setStatus(AbstractMethod::STATUS_ERROR);
            throw new LocalizedException(__('Invalid transaction id '));
        }
        return $this;
    }

    /**
     * Cancels transaction.
     *
     * @param InfoInterface $payment
     * @return Payment
     * order -> dropdown -> cancel order
     * @throws LocalizedException
     */

    public function cancel(InfoInterface $payment)
    {
        if($payment->getCcTransId()) {
            $order = $payment->getOrder();
            $tran = $this->tranApi;
            $tran->setData('amount', $order->getGrandTotal());
            $tran->setData('command', 'creditvoid');

            $tran->setTransactionData($payment);

            if($tran->runTransaction()) {
                return $this;
            } else {
                throw new LocalizedException(__('Transaction not void'));
            }
        } else {
            $payment->setStatus(AbstractMethod::STATUS_ERROR);
            throw new LocalizedException(__('Invalid transaction id '));
        }
    }

    /**
     * Returns Ebizcharge customer ID.
     * #1 for delete customer card
     */
    public function getEbzcCustId()
    {
        if($this->backendAuthSession->isLoggedIn()) {
            $customerId = $this->backendQuote->getCustomerId();
        } else {
            $customerId = $this->customerSession->getId();
        }

        return $this->token->getCollection()
            ->addFieldToFilter('mage_cust_id', $customerId)
            ->getFirstItem()
            ->getEbzcCustId();
    }

    /**
     * Returns saved payment methods.
     * getSavedCards Load in Dropdown Frontend #14 added by IF Done
     */

    public function getSavedAccounts()
    {
        return $this->tranApi->getSavedAccounts($this->getEbzcCustId());
    }

    public function getSavedCards()
    {
        $ebzcCustomerId = $this->getEbzcCustId();

        if($ebzcCustomerId) {
            $paymentMethods = $this->tranApi->getCustomerPaymentMethods($ebzcCustomerId);

            $paymentMethodsNew = array();
            foreach ($paymentMethods as $key => $payment) {
                if($payment->MethodType != 'check') {
                    $paymentMethodsNew[] = $payment;
                }
            }

            return $paymentMethodsNew;
        }

        return [];
    }

    /**
     * Check whether an EBizCharge customer ID is
     * associated with current magento customer ID
     *
     * @return boolean
     */
    public function hasToken(): bool
    {
        if($this->backendAuthSession->isLoggedIn()) {
            $customerId = $this->backendQuote->getCustomerId();
        } else {
            $customerId = $this->customerSession->getId();
        }

        $_ebzc_cust_id = $this->token->getCollection()->addFieldToFilter('mage_cust_id', $customerId)
            ->getFirstItem()
            ->getEbzcCustId();

        return empty($_ebzc_cust_id) ? false : true;
    }

    /**
     * Determine method availability based on quote amount and config data
     *
     * @param CartInterface|null $quote
     * @return bool
     */
    public function isAvailable(CartInterface $quote = null): bool
    {
        return true;

        if($quote &&
            ($quote->getBaseGrandTotal() < $this->ebizConfig->getPaymentMinordertotal()
                ||
                (
                    $this->ebizConfig->getPaymentMaxordertotal() && $quote->getBaseGrandTotal() > $this->ebizConfig->getPaymentMaxordertotal()
                )
            )) {
            return false;
        }

        $hasSourceKey = $this->ebizConfig->getSourceKey();

        if(!$hasSourceKey) {
            return false;
        }

        //return parent::isAvailable($quote);
    }

    /**
     *  This method Download orders from admin side
     *
     * @param InfoInterface $payment
     * @param $amount
     * @return Payment|false
     * @throws LocalizedException
     */
    public function downloadOrders(InfoInterface $payment, $amount)
    {
        if($payment->getAdditionalInformation('ebzc_option') != 'downloadorder') {
            return false;
        }
        $authorizeData = $payment->getAdditionalInformation('authorize_data');
        $this->tranApi->setAuthorizeData($authorizeData);

        // store response variables
        $payment->setCcApproval($this->tranApi->getAuthorizeData()['authcode'])
            ->setCcTransId($this->tranApi->getAuthorizeData()['refnum'])
            ->setCcAvsStatus($this->tranApi->getAuthorizeData()['avs_result_code'])
            ->setCcCidStatus($this->tranApi->getAuthorizeData()['cvv2_result_code']);

        $setLastTransId = $authorizeData['RefNum'] . '-' . $authorizeData['TransactionType'];

        // add the special ebzc fields to the database
        $payment->getMethodInstance()->getInfoInstance()->setEbzcCustId($authorizeData['CustNum']);
        $payment->getMethodInstance()->getInfoInstance()->setEbzcMethodId($authorizeData['ebzc_method_id']);
        $payment->getMethodInstance()->getInfoInstance()->setEbzcSavePayment($authorizeData['ebzc_save_payment']);
        $payment->getMethodInstance()->getInfoInstance()->setEbzcOption($authorizeData['ebzc_option']);
        $payment->getMethodInstance()->getInfoInstance()->setEbzcAvsStreet($authorizeData['AvsStreet']);
        $payment->getMethodInstance()->getInfoInstance()->setEbzcAvsZip($authorizeData['AvsZip']);
        // New params
        $payment->getMethodInstance()->getInfoInstance()->setShippingCaptured($authorizeData['Shipping']);
        $payment->getMethodInstance()->getInfoInstance()->setBaseShippingCaptured($authorizeData['Shipping']);
        $payment->getMethodInstance()->getInfoInstance()->setCcExpMonth($authorizeData['cc_exp_month']);
        $payment->getMethodInstance()->getInfoInstance()->setCcApproval($authorizeData['AuthCode']);
        $payment->getMethodInstance()->getInfoInstance()->setCcLast4(substr($authorizeData['CardNumber'], -4));
        $payment->getMethodInstance()->getInfoInstance()->setCcOwner($authorizeData['cc_owner']);
        $payment->getMethodInstance()->getInfoInstance()->setCcType($authorizeData['cc_type']);
        $payment->getMethodInstance()->getInfoInstance()->setPoNumber($authorizeData['PONum']);
        $payment->getMethodInstance()->getInfoInstance()->setCcExpYear($authorizeData['cc_exp_year']);
        $payment->getMethodInstance()->getInfoInstance()->setCcAvsStatus($authorizeData['AvsResultCode']);
        //$payment->getMethodInstance()->getInfoInstance()->setLastTransId($setLastTransId);
        //$payment->getMethodInstance()->getInfoInstance()->setCcTransId($authorizeData['RefNum']);


        $payment->setLastTransId($this->tranApi->getAuthorizeData()['refnum']);
        $payment->setTransactionId($this->tranApi->getAuthorizeData()['refnum']);
        $payment->setIsTransactionClosed(0)->setTransactionAdditionalInfo('trans_id',
            $this->tranApi->getAuthorizeData()['refnum']);

        if($this->tranApi->getAuthorizeData()['resultcode'] == 'A') {
            $payment->setStatus(AbstractMethod::STATUS_APPROVED);
        } else {
            $payment->setStatus(AbstractMethod::STATUS_ERROR);
            $this->tranApi->ebizLog()->info('Transaction not completed for ' . $this->tranApi->getAuthorizeData()['refnum']);
        }

        return $this;
    }
}
