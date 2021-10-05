<?php
/**
 * @author      Century Business Solutions <support@centurybizsolutions.com>
 * @copyright   Copyright (c) 2020 Century Business Solutions  (www.centurybizsolutions.com)
 */
declare(strict_types=1);

namespace Ebizcharge\Ebizcharge\Controller\Recurrings;

use Ebizcharge\Ebizcharge\Api\Data\RecurringInterface;
use Ebizcharge\Ebizcharge\Api\RecurringRepositoryInterface as RecurringRepository;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Customer\Controller\AccountInterface;
use Magento\Customer\Model\Session;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\Result\JsonFactory;

/**
 *
 * Class ProductAction
 */
class ProductAction implements AccountInterface
{
    /**
     * @var CheckoutSession
     */
    private $checkoutSession;

    /**
     * @var JsonFactory
     */
    private $resultJsonFactory;

    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @var RecurringRepository
     */
    private $recurringRepository;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param RecurringRepository $recurringRepository
     * @param JsonFactory $resultJsonFactory
     * @param RequestInterface $request
     * @param Session $customerSession
     * @param CheckoutSession $checkoutSession
     */
    public function __construct(
        SearchCriteriaBuilder $searchCriteriaBuilder,
        RecurringRepository $recurringRepository,
        JsonFactory $resultJsonFactory,
        RequestInterface $request,
        Session $customerSession,
        CheckoutSession $checkoutSession
    ) {
        $this->checkoutSession = $checkoutSession;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->request = $request;
        $this->recurringRepository = $recurringRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
    }

    /**
     * Deletes customer's payment method.
     *
     * @return Json
     */
    public function execute()
    {
        $productId = $this->request->getParam('productid');
        $customerId = $this->request->getParam('customerId');
        $newSdate = $this->request->getParam('newSdate');
        $action = 'enable';

        if ($productId && $customerId) {
            $checkExistiningItemAction = $this->checkQuoteItems($productId);

            if ($checkExistiningItemAction == 'exist') {
                return $this->resultJsonFactory->create()->setData(['html_data' => $checkExistiningItemAction]);
            }

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

    /**
     * @param $productid
     * @return string
     */
    public function checkQuoteItems($productid)
    {
        $action = 'enable';
        try {
            if ($this->checkoutSession->getQuote()->hasProductId($productid)) {
                //product is available in the cart
                $action = 'exist';
            }
        } catch (\Exception $e) {
            return $action;
        }
        return $action;

    }

}
