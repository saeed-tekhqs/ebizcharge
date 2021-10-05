<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Ebizcharge\Ebizcharge\Block\Customer\Account;

use Ebizcharge\Ebizcharge\Model\ResourceModel\Recurring\CollectionPayment as Collection;
use Ebizcharge\Ebizcharge\Model\TranApi;
use Magento\Customer\Model\SessionFactory;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Theme\Block\Html\Pager;

/**
 * Sales order history block
 *
 * @api
 * @since 100.0.2
 */
class PaymentHistory extends Template
{
    /**
     * @var string
     */
    protected $_template = 'Ebizcharge_Ebizcharge::recurrings_history.phtml';

    /**
     * @var Collection
     */
    private $recurringCollectionFactory;

    /**
     * @var SessionFactory
     */
    private $customerSession;

    /**
     * @param Context $context
     * @param Collection $recurringCollectionFactory
     * @param SessionFactory $customerSession
     * @param TranApi $tranApi
     * @param array $data
     */

    public function __construct(
        Context $context,
        Collection $recurringCollectionFactory,
        SessionFactory $customerSession,
        TranApi $tranApi,
        array $data = []
    ) {
        $this->recurringCollectionFactory = $recurringCollectionFactory;
        $this->customerSession = $customerSession;
        $this->tranApi = $tranApi;
        parent::__construct($context, $data);
    }

    /**
     * @inheritDoc
     */
    protected function _construct()
    {
        parent::_construct();
        $this->pageConfig->getTitle()->set(__('My Subscriptions'));
    }

    /**
     * Get customer recurrings
     *
     */
    public function getRecurrings()
    {
        if (!($customerId = $this->getMageCustId())) {
            return [];
        }

        $recurringPayments = $this->getAllSearchRecurringPayments($customerId);

        $collection = $this->recurringCollectionFactory
            ->addDataToCollection($this->recurringCollectionFactory, $recurringPayments);

        return $collection;
    }


    public function getAllSearchRecurringPayments($customerId = null, $paymentDate = false): array
    {
        $start = $this->getPageNumber() * $this->getPageSize();
        $limit = $this->getPageSize();

        return $this->tranApi->getSearchTransactions($customerId, $start, $limit, $paymentDate);
    }

    public function getReceiptRefNumber()
    {
        return $this->tranApi->getReceiptRefNumber();
    }

    /**
     * @inheritDoc
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();

        $pager = $this->getLayout()->createBlock(
            Pager::class,
            'ebizcharge.history.list.pager'
        )->setCollection(
            $this->getrecurrings()
        );

        $this->setChild('pager', $pager);

        $this->getRecurrings()->load();

        return $this;
    }

    /**
     * Get Pager child block output
     *
     * @return string
     */
    public function getPagerHtml()
    {
        return $this->getChildHtml('pager');
    }


    public function getMageCustId()
    {
        return $this->customerSession->create()->getCustomerId();
    }

    public function getPageSize()
    {
        return $this->getRequest()->getParam('limit', 2);
    }

    public function getPageNumber()
    {
        return $this->getRequest()->getParam('p', 0);
    }

}
