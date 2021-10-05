<?php
/**
 * Deletes the customer's saved payment method.
 *
 * @author      Century Business Solutions <support@centurybizsolutions.com>
 * @copyright   Copyright (c) 2020 Century Business Solutions  (www.centurybizsolutions.com)
 */

namespace Ebizcharge\Ebizcharge\Controller\Adminhtml\Recurrings;

use Ebizcharge\Ebizcharge\Api\Data\RecurringInterface;
use Ebizcharge\Ebizcharge\Api\RecurringRepositoryInterface as RecurringRepository;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\Response\Http\FileFactory;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\File\Csv;

/**
 * Export dates
 *
 * Class DatesExportAction
 */
class DatesExportAction extends Action implements HttpGetActionInterface
{
    /**
     * @var RecurringRepository
     */
    private $recurringRepository;
    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;
    /**
     * @var Csv
     */
    private $csvProcessor;
    /**
     * @var DirectoryList
     */
    private $directoryList;
    /**
     * @var FileFactory
     */
    private $fileFactory;

    /**
     * @param RecurringRepository $recurringRepository
     * @param Csv $csvProcessor
     * @param FileFactory $fileFactory
     * @param DirectoryList $directoryList
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param Context $context
     */
    public function __construct(
        RecurringRepository $recurringRepository,
        Csv $csvProcessor,
        FileFactory $fileFactory,
        DirectoryList $directoryList,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        Context $context
    )
    {
        parent::__construct($context);
        $this->recurringRepository = $recurringRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->csvProcessor = $csvProcessor;
        $this->directoryList = $directoryList;
        $this->fileFactory = $fileFactory;
    }

    /**
     * Deletes customer's payment method.
     *
     * @return ResultInterface|ResponseInterface
     */
    public function execute()
    {
        $id = $this->getRequest()->getParam('mid');
        if (!empty($id)) {
            try {
                if ($records = $this->getRecords()) {
                    $this->getCsv($records);
                    exit;
                }
            } catch (\Exception $ex) {
                exit;
            }
        }
        exit;
    }

    /**
     * @param array $records
     * @return false|ResponseInterface
     */
    private function getCsv(array $records)
    {
        if (empty($records)) {
            return false;
        }

        $fileName = 'Subscriptions.csv';
        $content[] = [
            'recurring_date' => __('Due Date'),
            'mage_item_name' => __('Item Name'),
            'mage_cust_id' => __('Customer Id'),
            'customer_email' => __('Customer Name'),
            'required_options' => __('Customer Email'),
            'eb_rec_frequency' => __('Frequency'),
            'amount' => __('Amount'),
        ];
        foreach ($records as $record) {
            $content[] = [
                $record['eb_rec_start_date'] ?? '',
                $record['mage_item_name'] ?? '',
                $record['mage_cust_id'] ?? '',
                $record['customer_name'] ?? '',
                $record['customer_email'] ?? '',
                $record['eb_rec_frequency'] ?? '',
                $record['amount'] ?? ''
            ];
        }
        try {
            $filePath = $this->directoryList->getPath(DirectoryList::MEDIA) . "/" . $fileName;
            $this->csvProcessor->setEnclosure('"')->setDelimiter(',')->saveData($filePath, $content);
            return $this->fileFactory->create(
                $fileName,
                [
                    'type' => "filename",
                    'value' => $fileName,
                    'rm' => true, // True => File will be remove from directory after download.
                ],
                DirectoryList::MEDIA,
                'text/csv',
                null
            );
        } catch (\Exception $ex) {
            return false;
        }
    }

    /**
     * Get records to export
     * @return bool|mixed
     */
    private function getRecords()
    {
        $id = $this->getRequest()->getParam('mid');

        $searchCriteria = $this->searchCriteriaBuilder->addFilter(
            RecurringInterface::EB_REC_SCHEDULED_PAYMENT_INTERNAL_ID, $id
        );
        $records = $this->recurringRepository->getList($searchCriteria->create());

        return !empty($records->getItems()) ? $records->getItems() : false;
    }
}
