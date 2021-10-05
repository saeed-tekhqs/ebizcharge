<?php
/**
 * @author Century Business Solutions <support@centurybizsolutions.com>
 * @copyriht Copyright (c) 2021 Century Business Solutions  (www.centurybizsolutions.com)
 * Created by PhpStorm
 * User: Mobeen
 * Date: 6/9/21
 * Time: 1:54 PM
 */
declare(strict_types=1);

namespace Ebizcharge\Ebizcharge\Controller\Adminhtml\Recurrings;

use Ebizcharge\Ebizcharge\Model\TranApi;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Ui\Component\MassAction\Filter;
use Ebizcharge\Ebizcharge\Model\ResourceModel\Recurring\CollectionFactory;
use Psr\Log\LoggerInterface;

/**
 *  Class used to suspend and unsubscribe the subscriptions
 *
 * Class SuspendUnsubscribe
 */
class SuspendUnsubscribe extends Action
{
    /**
     * @var TranApi
     */
    private $tranApi;

    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var Filter
     */
    private $filter;
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param Context $context
     * @param TranApi $tranApi
     * @param CollectionFactory $collectionFactory
     * @param LoggerInterface $logger
     * @param Filter $filter
     */
    public function __construct(
        Context $context,
        TranApi $tranApi,
        CollectionFactory $collectionFactory,
        LoggerInterface $logger,
        Filter $filter
    )
    {
        parent::__construct($context);
        $this->tranApi = $tranApi;
        $this->collectionFactory = $collectionFactory;
        $this->filter = $filter;
        $this->logger = $logger;
    }

    /**
     * @inheritDoc
     */
    public function execute()
    {
        $internalIds = $this->getSelectedItems();
        if ( $internalIds && $this->suspendUnsubscribe($internalIds)) {
            $this->messageManager->addSuccessMessage('Subscriptions updated successfully!');
        } else {
            $this->messageManager->addErrorMessage('Updation failure. Please try again.');
        }
        return $this->_redirect('ebizcharge_ebizcharge/recurrings');
    }

    /**
     * get selected records from grid
     *
     * @return array
     */
    private function getSelectedItems(): array
    {
        try {
            return $this->filter->getCollection($this->collectionFactory->create())
                ->getColumnValues('eb_rec_scheduled_payment_internal_id');
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
            return [];
        }
    }

    /**
     * @param array $paymentInternalId
     * @return bool
     */
    private function suspendUnsubscribe(array $paymentInternalId): bool
    {
        $statusId = $actionName = $this->getActionName();

        if (!empty($paymentInternalId)) {
            $securityToken = $this->tranApi->getUeSecurityToken();
            foreach ($paymentInternalId as $id) {
                $params = array(
                    'securityToken' => $securityToken,
                    'scheduledPaymentInternalId' => $id,
                    'statusId' => $statusId,
                );

                $res = $this->tranApi->getClient()->ModifyScheduledRecurringPaymentStatus($params);
                $ModifyScheduledRecurringPaymentStatusResult = $res->ModifyScheduledRecurringPaymentStatusResult;
                if (!empty($ModifyScheduledRecurringPaymentStatusResult)) {
                    if ($ModifyScheduledRecurringPaymentStatusResult->StatusCode == 1) {
                        $this->updateStatus($id, $statusId);
                    } else {
                        return false;
                    }
                }
            }
            return true;
        }
        return false;
    }

    private function getActionName() {
        $actionName =  $this->_request->getParam('actionName');
        if ($actionName == 'suspend') {
            $statusId = 3;
        } else if ($actionName == 'unsubscribe') {
            $statusId = 1;
        } else {
            return false;
        }
        return $statusId;
    }

    /**
     * update status in db table
     *
     * @param $internalId
     * @param $statusId
     * @return bool
     */
    private function updateStatus($internalId, $statusId)
    {
        try {
            $collection = $this->collectionFactory->create()
                ->addFieldToFilter('eb_rec_scheduled_payment_internal_id', $internalId);
            foreach ($collection as $item) {
                $item->setData('rec_status', $statusId);
            }
            $collection->save();
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
            return false;
        }
    }

}
