<?php
/**
 * @author Century Business Solutions <support@centurybizsolutions.com>
 * @copyriht Copyright (c) 2021 Century Business Solutions  (www.centurybizsolutions.com)
 * Created by PhpStorm
 * User: Mobeen
 * Date: 6/14/21
 * Time: 10:30 AM
 */
declare(strict_types=1);

namespace Ebizcharge\Ebizcharge\Model\PaymentHistory;

use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Framework\Data\Collection as CoreCollection;
use Magento\Framework\Data\Collection\EntityFactoryInterface;
use Magento\Framework\DataObjectFactory as DataObject;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\Stdlib\DateTime\DateTime;
/**
 * Payment history collection using third party api
 *
 * Class Collection
 */
class Collection extends CoreCollection
{
    /**
     * @var DataObject
     */
    private $dataObject;

    /**
     * @var CustomerRepositoryInterface
     */
    private $customerRepository;
    /**
     * @var DateTime
     */
    private $dateTime;

    /**
     * @param DateTime $dateTime
     * @param CustomerRepositoryInterface $customerRepository
     * @param DataObject $dataObject
     * @param EntityFactoryInterface $entityFactory
     */
    public function __construct(
        DateTime $dateTime,
        CustomerRepositoryInterface $customerRepository,
        DataObject $dataObject,
        EntityFactoryInterface $entityFactory
    ) {
        parent::__construct($entityFactory);
        $this->dataObject = $dataObject;
        $this->customerRepository = $customerRepository;
        $this->dateTime = $dateTime;
    }

    /**
     * @param $field
     * @param $condition
     * @return Collection
     */
    public function addFieldToFilter($field, $condition): Collection
    {
        return $this;
    }

    /**
     * @param CoreCollection $collection
     * @param array $recurringPayments
     * @return CoreCollection
     */
    public function addDataToCollection(CoreCollection $collection, array $recurringPayments): CoreCollection
    {
        foreach ($recurringPayments as $key => $value) {
            if ($value->Response->ResultCode == 'A') {
                $status = 'Approved';
            } elseif ($value->Response->ResultCode == 'D') {
                $status = 'Declined';
            } else {
                $status = 'Rejected';
            }
            $cardInfo = '';
            if(isset($value->CreditCardData->CardType)) {
                $ccType = $value->CreditCardData->CardType;
                if ($ccType == 'A') {
                    $cardType = 'American Express';
                } elseif ($ccType == 'M') {
                    $cardType = 'Master';
                } elseif ($ccType == 'V') {
                    $cardType = 'Visa';
                } elseif ($ccType == 'DS') {
                    $cardType = 'Discover';
                } else {
                    $cardType = $ccType;
                }
                $cardInfo = $value->CreditCardData->CardNumber . ' - ' . $cardType;
            } else if(isset($value->CheckData->AccountType)) {
                $accountType = $value->CheckData->AccountType;
                $accountNumber = $value->CheckData->Account;
                $cardInfo = $accountNumber . ' - '. $accountType;
            }
            $customerName = '';
            $customerEmail = '';
            if ($customer = $this->getCustomerById((int) $value->CustomerID)) {
                $customerName = $customer->getFirstname(). ' '. $customer->getLastname();
                $customerEmail = $customer->getEmail();
            }
            $dataObject = $this->dataObject->create()->setData(
                [
                    'massActionField' => $value->Response->RefNum . '#'. $customerEmail,
                    'customerId' => $value->CustomerID,
                    'customerName' => $customerName,
                    'customerEmail' => $customerEmail,
                    'paymentDate' => $this->dateTime->date('Y-m-d', $value->DateTime),
//                    'paymentDate' => date("Y-m-d", strtotime($value->DateTime)),
                    'paymentAmount' => $value->Details->Amount,
                    'cardInfo' => $cardInfo,
                    'refNum' => $value->Response->RefNum,
                    'resultStatus' => $status,
                ]
            );
            try {
                $collection = $collection->addItem($dataObject);
            } catch (\Exception $e) {
                $error = $e->getMessage();
            }
        }
        $collection->_totalRecords = 1000;
        return $collection;
    }

    /**
     * Get customer details
     * @param int $customerId
     * @return false|CustomerInterface
     */
    public function getCustomerById(int $customerId) {
        try {
            return $this->customerRepository->getById($customerId);
        } catch (\Exception $e) {
            $error = $e->getMessage();
        }
        return false;
    }
}
