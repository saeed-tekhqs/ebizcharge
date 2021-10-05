<?php
/**
 * @author Century Business Solutions <support@centurybizsolutions.com>
 * @copyriht Copyright (c) 2021 Century Business Solutions  (www.centurybizsolutions.com)
 * Created by PhpStorm
 * User: Mobeen
 * Date: 7/21/21
 * Time: 9:44 PM
 */
declare(strict_types=1);

namespace Ebizcharge\Ebizcharge\Observer;

use Magento\Framework\Event\Observer;
use Magento\Payment\Observer\AbstractDataAssignObserver;
use Magento\Framework\Exception\LocalizedException;
use Magento\Quote\Api\Data\PaymentInterface;
use Magento\Framework\DataObject;
use Magento\Payment\Model\InfoInterface;
use Ebizcharge\Ebizcharge\Model\TranApi;

/**
 * This class assigns preliminary form data to $infoInstance
 *
 * Class DataAssignObserver
 */
class DataAssignObserver extends AbstractDataAssignObserver
{

    const ACH = 'ACH';

    /**
     * @var TranApi
     */
    private $tranApi;

    public function __construct(
        TranApi $tranApi
    ) {
        $this->tranApi = $tranApi;
    }

    /**
     * @inheritDoc
     * @throws LocalizedException
     */
    public function execute(Observer $observer)
    {
        $data = $this->readDataArgument($observer);

        $additionalData = $data->getData(PaymentInterface::KEY_ADDITIONAL_DATA);
        if(!is_array($additionalData)) {
            return;
        }
        $additionalData = new DataObject($additionalData);
        $paymentMethod = $this->readMethodArgument($observer);

        $payment = $observer->getPaymentModel();
        if(!$payment instanceof InfoInterface) {
            $payment = $paymentMethod->getInfoInstance();
        }

        if(!$payment instanceof InfoInterface) {
            throw new LocalizedException(__('Payment model does not provided.'));
        }
        $paymentInfo = $this->readPaymentModelArgument($observer);

        $this->assignData($paymentInfo, $additionalData);
    }


    /**
     * Assigns preliminary form data (ebizcharge.js - getData()) to $infoInstance, which is the payment object.
     * First Load Cards from #14 and #15 and assign to Customer and set for eBiz #16
     *
     * @param InfoInterface $infoInstance
     * @param DataObject $additionalData
     * @throws LocalizedException
     */
    private function assignData(InfoInterface $infoInstance, DataObject $additionalData)
    {
        $tran = $this->tranApi;

        $paymentToken = $additionalData->getDataByKey('paymentToken') ?? null;
        $saveCard = $additionalData->getDataByKey('ebzc_save_payment') ?? false;

        $ebzcAvsStreet = $additionalData->getDataByKey('ebzc_avs_street') ?? null;
        $ebzcAvsZip = $additionalData->getDataByKey('ebzc_avs_zip') ?? null;
        $_ebzc_save_payment = $additionalData->getDataByKey('ebzc_save_payment') ?? null;

        //$paylater_value = isset($additionalDataRef['ebzc_paylater_payment']) ? $additionalDataRef['ebzc_paylater_payment'] : false;


        $infoInstance->setAdditionalInformation('payment_token', $paymentToken);
        $infoInstance->setAdditionalInformation('save_card', $saveCard);
        //$infoInstance->setAdditionalInformation('ebzc_paylater_payment', $paylater_value);


        if($additionalData->getEbzcOption() == "new") {

            if($additionalData->getEbzcOptionType() == self::ACH) {
                $infoInstance->setCcType($additionalData->getCcType())
                    ->setCcOwner($additionalData->getCcOwner())
                    ->setCcLast4(substr($additionalData->getCcNumber(), -4))
                    ->setCcNumber($additionalData->getCcNumber())
                    ->setCcCid($additionalData->getCcCid())
                    ->setAchRoute($additionalData->getAchRouting())
                    ->setAchType($additionalData->getAchType())
                    ->setEbzcOption($additionalData->getEbzcOption())
                    ->setEbzcCustId($additionalData->getEbzcCustId())
                    ->setEbzcSavePayment($additionalData->getEbzcSavePayment());
                $infoInstance->setAdditionalInformation('ach_type', $additionalData->getAchType());
                $infoInstance->setAdditionalInformation('ach_route', $additionalData->getAchRouting());

            } else {

                $infoInstance->setCcType($additionalData->getCcType())
                    ->setCcOwner($additionalData->getCcOwner())
                    ->setCcLast4(substr($additionalData->getCcNumber(), -4))
                    ->setCcNumber($additionalData->getCcNumber())
                    ->setCcCid($additionalData->getCcCid())
                    ->setCcExpMonth($additionalData->getCcExpMonth())
                    ->setCcExpYear($additionalData->getCcExpYear())
                    ->setCcSsIssue($additionalData->getCcSsIssue())
                    ->setCcSsStartMonth($additionalData->getCcSsStartMonth())
                    ->setCcSsStartYear($additionalData->getCcSsStartYear())
                    ->setEbzcOption($additionalData->getEbzcOption())
                    ->setEbzcCustId($additionalData->getEbzcCustId())
                    ->setEbzcSavePayment($additionalData->getEbzcSavePayment());
            }

            $infoInstance->setAdditionalInformation('ebzc_option', $additionalData->getEbzcOption());
            $infoInstance->setAdditionalInformation('ebzc_cust_id', $additionalData->getEbzcCustId());
            $infoInstance->setAdditionalInformation('ebzc_method_id', $additionalData->getEbzcMethodId());
            $infoInstance->setAdditionalInformation('ebzc_save_payment', $additionalData->getEbzcSavePayment());

        } elseif($additionalData->getEbzcOption() == "saved") {
            $tran->setData('savedMethodId', $additionalData->getEbzcMethodId());

            $paymentMethods = $this->tranApi->getCustomerPaymentMethodProfile(
                $additionalData->getEbzcCustId(), $additionalData->getEbzcMethodId()
            );

            if($additionalData->getEbzcOptionType() == self::ACH) {
                $infoInstance->setEbzcOption($additionalData->getEbzcOption())
                    ->setCcType($this->setCardType($paymentMethods))
                    ->setEbzcMethodId($additionalData->getEbzcMethodId())
                    ->setEbzcCustId($additionalData->getEbzcCustId())
                    ->setCcOwner($paymentMethods->MethodName)
                    ->setCcLast4(substr($paymentMethods->Account, -4))
                    ->setCcNumber($paymentMethods->Account)
                    ->setEbzcSavePayment($additionalData->getEbzcSavePayment());

                $infoInstance->setAdditionalInformation('ach_type', $additionalData->getAchType());
                $infoInstance->setAdditionalInformation('ach_route', $additionalData->getAchRouting());

            } else {
                $infoInstance->setEbzcOption($additionalData->getEbzcOption())
                    ->setEbzcMethodId($additionalData->getEbzcMethodId())
                    ->setEbzcCustId($additionalData->getEbzcCustId())
                    ->setCcType($this->setCardType($paymentMethods))
                    ->setCcOwner($paymentMethods->MethodName)
                    ->setCcLast4(substr($paymentMethods->CardNumber, -4))
                    ->setCcNumber($paymentMethods->CardNumber)
                    ->setCcExpMonth(substr($paymentMethods->CardExpiration, 5, 2))
                    ->setCcExpYear(substr($paymentMethods->CardExpiration, 0, 4))
                    ->setCcCid($additionalData->getCcCid())
                    ->setEbzcSavePayment($additionalData->getEbzcSavePayment());
            }

            $infoInstance->setAdditionalInformation('ebzc_option', $additionalData->getEbzcOption());
            $infoInstance->setAdditionalInformation('ebzc_cust_id', $additionalData->getEbzcCustId());
            $infoInstance->setAdditionalInformation('ebzc_method_id', $additionalData->getEbzcMethodId());
            $infoInstance->setAdditionalInformation('ebzc_save_payment', $additionalData->getEbzcSavePayment());

        } elseif($additionalData->getEbzcOption() == "update") {
            $tran->setData('savedMethodId', $additionalData->getEbzcMethodId());

            try {
                $paymentMethods = $this->tranApi->getCustomerPaymentMethodProfile(
                    $additionalData->getEbzcCustId(), $additionalData->getEbzcMethodId()
                );

            } catch (\Exception $ex) {
                throw new \Magento\Framework\Exception\LocalizedException(__('Update Method: ' . $ex->getMessage()));
            }

            $infoInstance->setEbzcOption($additionalData->getEbzcOption())
                ->setEbzcMethodId($additionalData->getEbzcMethodId())
                ->setEbzcCustId($additionalData->getEbzcCustId())
                ->setCcType($this->setCardType($paymentMethods))
                ->setCcOwner($paymentMethods->MethodName)
                ->setCcLast4(substr($paymentMethods->CardNumber, -4))
                ->setCcNumber($paymentMethods->CardNumber)
                ->setCcExpMonth($additionalData->getCcExpMonth())
                ->setCcExpYear($additionalData->getCcExpYear())
                ->setCcCid($additionalData->getCcCid())
                ->setEbzcAvsStreet($additionalData->getEbzcAvsStreet())
                ->setEbzcAvsZip($additionalData->getEbzcAvsZip())
                ->setEbzcSavePayment($additionalData->getEbzcSavePayment());

            $infoInstance->setAdditionalInformation('ebzc_option', $additionalData->getEbzcOption());
            $infoInstance->setAdditionalInformation('ebzc_cust_id', $additionalData->getEbzcCustId());
            $infoInstance->setAdditionalInformation('ebzc_method_id', $additionalData->getEbzcMethodId());
            $infoInstance->setAdditionalInformation('ebzc_save_payment', $additionalData->getEbzcSavePayment());
            $infoInstance->setAdditionalInformation('ebzc_avs_street', $ebzcAvsStreet);
            $infoInstance->setAdditionalInformation('ebzc_avs_zip', $ebzcAvsZip);

        } elseif($additionalData->getEbzcOption() == "paylater") {
            $infoInstance->setAdditionalInformation('ebzc_option', $additionalData->getEbzcOption());
            $infoInstance->setAdditionalInformation('ebzc_cust_id', $additionalData->getEbzcCustId());
            $infoInstance->setAdditionalInformation('ebzc_method_id', $additionalData->getEbzcMethodId());
            $infoInstance->setAdditionalInformation('ebzc_save_payment', $additionalData->getEbzcSavePayment());
            $infoInstance->setAdditionalInformation('ebzc_paylater_payment', $additionalData->getEbzcPaylaterPayment());

        } elseif($additionalData->getEbzcOption() == "downloadorder") {
            $infoInstance->setAdditionalInformation('ebzc_cust_id', $additionalData->getDataByKey('mage_cust_id'));
            $infoInstance->setAdditionalInformation('ebzc_method_id',
                $additionalData->getDataByKey('ebzc_method_id') ?? null);
            $infoInstance->setAdditionalInformation('ebzc_save_payment',
                $additionalData->getDataByKey('ebzc_save_payment'));
            $infoInstance->setAdditionalInformation('ebzc_option', $additionalData->getDataByKey('ebzc_option'));
            $infoInstance->setAdditionalInformation('ebzc_avs_street', $additionalData->getDataByKey('AvsStreet'));
            $infoInstance->setAdditionalInformation('ebzc_avs_zip', $additionalData->getDataByKey('AvsZip'));
            $infoInstance->setAdditionalInformation('authorize_data', $additionalData->getData());

        } elseif($additionalData->getEbzcOption() == "recurring") {
            $tran->setData('savedMethodId', $additionalData->getEbzcMethodId());

            $paymentMethods = $this->tranApi->getCustomerPaymentMethodProfile(
                $additionalData->getEbzcCustId(), $additionalData->getEbzcMethodId()
            );

            if($paymentMethods->MethodType == 'check') {
                $ccNumber = $paymentMethods->Account;
            } else {
                $ccNumber = $paymentMethods->CardNumber;
            }
            $ccLastNumbers = substr($ccNumber, -4);

            $infoInstance->setEbzcOption($additionalData->getEbzcOption())
                ->setEbzcMethodId($additionalData->getEbzcMethodId())
                ->setEbzcCustId($additionalData->getEbzcCustId())
                ->setCcType($this->setCardType($paymentMethods))
                ->setCcOwner($paymentMethods->MethodName)
                ->setCcLast4($ccNumber)
                ->setCcNumber($ccLastNumbers)
                ->setCcExpMonth($additionalData->getCcExpMonth())
                ->setCcExpYear($additionalData->getCcExpYear())
                ->setCcCid($additionalData->getCcCid())
                ->setEbzcAvsStreet($additionalData->getEbzcAvsStreet())
                ->setEbzcAvsZip($additionalData->getEbzcAvsZip())
                ->setEbzcSavePayment($additionalData->getEbzcSavePayment());

            $infoInstance->setAdditionalInformation('ebzc_option', $additionalData->getDataByKey('ebzc_option'));
            $infoInstance->setAdditionalInformation('ebzc_option_new',
                $additionalData->getDataByKey('ebzc_option_new'));
            $infoInstance->setAdditionalInformation('ebzc_option_existing',
                $additionalData->getDataByKey('ebzc_option_existing'));
            $infoInstance->setAdditionalInformation('ebzc_cust_id', $additionalData->getDataByKey('ebzc_cust_id'));
            $infoInstance->setAdditionalInformation('mage_cust_id', $additionalData->getDataByKey('mage_cust_id'));
            $infoInstance->setAdditionalInformation('ebzc_method_id', $additionalData->getDataByKey('ebzc_method_id'));
            $infoInstance->setAdditionalInformation('excludeAmount', $additionalData->getDataByKey('excludeAmount'));

        } else {
            $infoInstance->setEbzcSavePayment($additionalData->getEbzcSavePayment());
            $infoInstance->setAdditionalInformation('ebzc_save_payment', $additionalData->getEbzcSavePayment());
        }

    }

    /**
     * @param $paymentMethod
     * @return string
     */
    public function setCardType($paymentMethod)
    {
        $cardType = '';
        if (!empty($paymentMethod) && isset($paymentMethod->CardType)) {
            if ($paymentMethod->CardType == 'V') {
                $cardType = "VI";
            } elseif ($paymentMethod->CardType == 'M') {
                $cardType = "MC";
            } elseif ($paymentMethod->CardType == 'A') {
                $cardType = "AE";
            } elseif ($paymentMethod->CardType == 'DS') {
                $cardType = "DS";
            }
        } else if ($paymentMethod->MethodType == 'check') {
            $cardType = self::ACH;
        }

        return $cardType;
    }
}
