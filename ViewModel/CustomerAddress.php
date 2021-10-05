<?php
/**
 * @author Century Business Solutions <support@centurybizsolutions.com>
 * @copyriht Copyright (c) 2021 Century Business Solutions  (www.centurybizsolutions.com)
 * Created by PhpStorm
 * User: Mobeen
 * Date: 9/21/21
 * Time: 3:46 PM
 */
declare(strict_types=1);

namespace Ebizcharge\Ebizcharge\ViewModel;

use Ebizcharge\Ebizcharge\Api\Data\RecurringInterface;
use Ebizcharge\Ebizcharge\Api\RecurringRepositoryInterface;
use Magento\Customer\Model\SessionFactory as CustomerSessionFactory;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\View\Element\Block\ArgumentInterface;

/**
 * Customer Address used in subscriptions or not
 *
 * Class CustomerAddress
 * @package Ebizcharge\Ebizcharge\ViewModel
 */
class CustomerAddress implements ArgumentInterface
{
    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var RecurringRepositoryInterface
     */
    private $recurringRepository;

    /**
     * @var CustomerSessionFactory
     */
    private $customerSession;

    /**
     * @param RecurringRepositoryInterface $recurringRepository
     * @param CustomerSessionFactory $customerSession
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     */
    public function __construct(
        RecurringRepositoryInterface $recurringRepository,
        CustomerSessionFactory $customerSession,
        SearchCriteriaBuilder $searchCriteriaBuilder
    ) {
        $this->recurringRepository = $recurringRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->customerSession = $customerSession;
    }

    public function isShippingAddressUsedInSubscription($shippingAddressId): string
    {
        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter(RecurringInterface::MAGE_CUST_ID, $this->customerSession->create()->getCustomer()->getId())
            ->addFilter(RecurringInterface::SHIPPING_ADDRESS_ID, $shippingAddressId);

        $records = $this->recurringRepository->getList($searchCriteria->create());
        return count($records->getItems()) > 0 ? 'Yes' : 'No';

    }

}
