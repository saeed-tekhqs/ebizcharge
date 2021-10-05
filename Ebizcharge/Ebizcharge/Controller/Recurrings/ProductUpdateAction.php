<?php
/**
 *
 * @author      Century Business Solutions <support@centurybizsolutions.com>
 * @copyright   Copyright (c) 2020 Century Business Solutions  (www.centurybizsolutions.com)
 */
declare(strict_types=1);

namespace Ebizcharge\Ebizcharge\Controller\Recurrings;

use Ebizcharge\Ebizcharge\Api\Data\RecurringInterface;
use Ebizcharge\Ebizcharge\Api\RecurringRepositoryInterface as RecurringRepository;
use Magento\Customer\Controller\AccountInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\Result\JsonFactory;

/**
 * Update action for product
 *
 * Class ProductUpdateAction
 */
class ProductUpdateAction implements AccountInterface
{
    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var RecurringRepository
     */
    private $recurringRepository;

    /**
     * @var JsonFactory
     */
    private $resultJsonFactory;

    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param RecurringRepository $recurringRepository
     * @param JsonFactory $resultJsonFactory
     * @param RequestInterface $request
     */
    public function __construct(
        JsonFactory $resultJsonFactory,
        RecurringRepository $recurringRepository,
        RequestInterface $request,
        SearchCriteriaBuilder $searchCriteriaBuilder
    ) {
        $this->resultJsonFactory = $resultJsonFactory;
        $this->recurringRepository = $recurringRepository;
        $this->request = $request;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
    }

    /**
     * @return Json
     */
    public function execute()
    {
        $action = 'enable';
        $productId = $this->request->getParam('productidu');
        $customerId = $this->request->getParam('customerIdu');
        $newSdate = $this->request->getParam('newSdateu');

        if ($productId && $customerId) {

            $searchCriteria = $this->searchCriteriaBuilder
                ->addFilter(RecurringInterface::MAGE_CUST_ID, $customerId)
                ->addFilter(RecurringInterface::MAGE_ITEM_ID, $productId);

            $recurringDataEntries = $this->recurringRepository->getList($searchCriteria->create());

            if ($newSdate && count($recurringDataEntries->getItems()) > 0) {
                foreach ($recurringDataEntries->getItems() as $recurringData) {
                    $oldRecurringStartDate = date('Y-m-d', strtotime($recurringData->getData('eb_rec_start_date')));
                    $oldRecurringEndDate = date('Y-m-d', strtotime($recurringData->getData('eb_rec_end_date')));
                    $newRecurringDate = date('Y-m-d', strtotime($newSdate));

                    if (($newRecurringDate >= $oldRecurringStartDate) && ($newRecurringDate <= $oldRecurringEndDate)) {
                        $action = 'disable';
                        return $this->resultJsonFactory->create()->setData(['html_data' => $action]);
                    }
                }
            }

        }
        return $this->resultJsonFactory->create()->setData(['html_data' => $action]);
    }

}
